<?php

namespace Warehouse\Sales\Model;

use Zend\Db\Adapter\Adapter;

class SalesTable
{
    private $adapter;


    public function __construct(Adapter $adapter)
    {
        $this->adapter = $adapter;
    }


    /*
     * Get Products
     */
    public function getProducts()
    {
        $sql = "
            SELECT
                p.ProductID,
                p.ProductCode,
                p.ProductName,
                p.UnitPrice,
                ISNULL(i.Quantity, 0) AS StockQuantity
            FROM Products p
            LEFT JOIN Inventory i
                ON i.ProductID = p.ProductID
            WHERE p.IsActive = 1
            ORDER BY p.ProductName ASC
        ";

        return $this->adapter->query(
            $sql,
            Adapter::QUERY_MODE_EXECUTE
        );
    }


    /*
     * Get Customers
     */
    public function getCustomers()
    {
        $sql = "
            SELECT
                CustomerID,
                CustomerCode,
                CustomerName,
                Phone
            FROM Customers
            WHERE IsActive = 1
            ORDER BY CustomerName ASC
        ";

        return $this->adapter->query(
            $sql,
            Adapter::QUERY_MODE_EXECUTE
        );
    }


    /*
     * Get Single Product
     */
    public function getProduct($productId)
    {
        $sql = "
            SELECT
                ProductID,
                ProductCode,
                ProductName,
                UnitPrice
            FROM Products
            WHERE ProductID = ?
              AND IsActive = 1
        ";

        $statement =
            $this->adapter->query(
                $sql,
                Adapter::QUERY_MODE_PREPARE
            );


        $result =
            $statement->execute([
                $productId
            ]);


        return $result->current();
    }


    /*
     * Create Sale
     */
    public function createSale(
        $customerId,
        $warehouseId,
        $productId,
        $quantity,
        $unitPrice,
        $createdBy
    ) {

        $connection =
            $this->adapter
                ->getDriver()
                ->getConnection();


        $pdo =
            $connection->getResource();


        /*
         * Start Transaction
         */
        $pdo->beginTransaction();


        try {

            /*
             * 1. REMOVE STOCK
             */
            $sql = "
                UPDATE Inventory
                SET
                    Quantity = Quantity - :quantity,
                    LastUpdated = GETDATE()
                WHERE ProductID = :productId
                  AND Quantity >= :quantityCheck
            ";


            $statement =
                $pdo->prepare($sql);


            $statement->execute([
                ':quantity' =>
                    $quantity,

                ':productId' =>
                    $productId,

                ':quantityCheck' =>
                    $quantity
            ]);


            /*
             * Check stock update
             */
            if ($statement->rowCount() !== 1) {

                throw new \Exception(
                    'Insufficient stock available.'
                );
            }


            $statement->closeCursor();


            /*
             * 2. CALCULATE TOTAL
             */
            $totalAmount =
                $quantity * $unitPrice;


            /*
             * 3. GENERATE SALES ORDER NUMBER
             */
            $salesOrderNumber =
                'SO-' .
                date('YmdHis') .
                rand(100, 999);


            /*
             * 4. INSERT SALES ORDER
             */
            $sql = "
                INSERT INTO SalesOrders
                (
                    SalesOrderNumber,
                    CustomerID,
                    WarehouseID,
                    OrderDate,
                    Status,
                    TotalAmount,
                    CreatedBy
                )
                OUTPUT INSERTED.SalesOrderID
                VALUES
                (
                    :orderNumber,
                    :customerId,
                    :warehouseId,
                    GETDATE(),
                    'Completed',
                    :totalAmount,
                    :createdBy
                )
            ";


            $statement =
                $pdo->prepare($sql);


            $statement->execute([
                ':orderNumber' =>
                    $salesOrderNumber,

                ':customerId' =>
                    $customerId,

                ':warehouseId' =>
                    $warehouseId,

                ':totalAmount' =>
                    $totalAmount,

                ':createdBy' =>
                    $createdBy
            ]);


            $salesOrderId =
                $statement->fetchColumn();


            $statement->closeCursor();


            if (!$salesOrderId) {

                throw new \Exception(
                    'Unable to create sales order.'
                );
            }


            /*
             * 5. INSERT SALES ORDER ITEM
             *
             * IMPORTANT:
             *
             * TotalAmount is a COMPUTED column.
             *
             * DO NOT insert TotalAmount here.
             */
            $sql = "
                INSERT INTO SalesOrderItems
                (
                    SalesOrderID,
                    ProductID,
                    Quantity,
                    UnitPrice
                )
                VALUES
                (
                    :salesOrderId,
                    :productId,
                    :quantity,
                    :unitPrice
                )
            ";


            $statement =
                $pdo->prepare($sql);


            $statement->execute([
                ':salesOrderId' =>
                    $salesOrderId,

                ':productId' =>
                    $productId,

                ':quantity' =>
                    $quantity,

                ':unitPrice' =>
                    $unitPrice
            ]);


            $statement->closeCursor();


            /*
             * 6. CREATE STOCK OUT TRANSACTION
             */
            $sql = "
                INSERT INTO StockTransactions
                (
                    ProductID,
                    WarehouseID,
                    TransactionType,
                    Quantity,
                    ReferenceType,
                    ReferenceID,
                    TransactionDate,
                    CreatedBy
                )
                VALUES
                (
                    :productId,
                    :warehouseId,
                    'OUT',
                    :quantity,
                    'SalesOrder',
                    :referenceId,
                    GETDATE(),
                    :createdBy
                )
            ";


            $statement =
                $pdo->prepare($sql);


            $statement->execute([
                ':productId' =>
                    $productId,

                ':warehouseId' =>
                    $warehouseId,

                ':quantity' =>
                    $quantity,

                ':referenceId' =>
                    $salesOrderId,

                ':createdBy' =>
                    $createdBy
            ]);


            $statement->closeCursor();


            /*
             * 7. COMMIT TRANSACTION
             */
            $pdo->commit();


            return (int) $salesOrderId;


        } catch (\Exception $e) {


            /*
             * Rollback if anything fails
             */
            if ($pdo->inTransaction()) {

                $pdo->rollBack();
            }


            throw $e;
        }
    }


    /*
     * Get Sales Orders
     */
    public function getSalesOrders()
    {
        $sql = "
            SELECT
                so.SalesOrderID,
                so.SalesOrderNumber,
                c.CustomerName,
                so.OrderDate,
                so.Status,
                so.TotalAmount,
                so.DispatchStatus,
                so.DispatchDate,
                so.DeliveredDate
            FROM SalesOrders so
            INNER JOIN Customers c
                ON c.CustomerID = so.CustomerID
            ORDER BY so.SalesOrderID DESC
        ";


        return $this->adapter->query(
            $sql,
            Adapter::QUERY_MODE_EXECUTE
        );
    }


    /*
     * Get One Sales Order
     */
    public function getSalesOrder($salesOrderId)
    {
        $sql = "
            SELECT
                so.SalesOrderID,
                so.SalesOrderNumber,
                c.CustomerCode,
                c.CustomerName,
                c.Phone,
                c.Email,
                c.Address,
                so.OrderDate,
                so.Status,
                so.TotalAmount,
                so.DispatchStatus,
                so.DispatchDate,
                so.DeliveredDate
            FROM SalesOrders so
            INNER JOIN Customers c
                ON c.CustomerID = so.CustomerID
            WHERE so.SalesOrderID = ?
        ";


        $statement =
            $this->adapter->query(
                $sql,
                Adapter::QUERY_MODE_PREPARE
            );


        return $statement
            ->execute([
                $salesOrderId
            ])
            ->current();
    }


    /*
     * Get Sales Order Items
     */
    public function getSalesOrderItems($salesOrderId)
    {
        $sql = "
            SELECT
                soi.SalesOrderItemID,
                soi.ProductID,
                p.ProductCode,
                p.ProductName,
                soi.Quantity,
                soi.UnitPrice,
                soi.TotalAmount
            FROM SalesOrderItems soi
            INNER JOIN Products p
                ON p.ProductID = soi.ProductID
            WHERE soi.SalesOrderID = ?
            ORDER BY soi.SalesOrderItemID ASC
        ";


        $statement =
            $this->adapter->query(
                $sql,
                Adapter::QUERY_MODE_PREPARE
            );


        return $statement
            ->execute([
                $salesOrderId
            ]);
    }
    /*
     * Dispatch Sales Order
     */
    public function dispatchSalesOrder($salesOrderId)
    {
        $sql = "
            UPDATE SalesOrders
            SET
                DispatchStatus = 'Dispatched',
                DispatchDate = GETDATE()
            WHERE SalesOrderID = ?
              AND DispatchStatus = 'Pending'
        ";

        $statement =
            $this->adapter->query(
                $sql,
                Adapter::QUERY_MODE_PREPARE
            );

        $statement->execute([
            $salesOrderId
        ]);
    }
    /*
     * Mark Sales Order Delivered
     */
    public function deliverSalesOrder($salesOrderId)
    {
        $sql = "
            UPDATE SalesOrders
            SET
                DispatchStatus = 'Delivered',
                DeliveredDate = GETDATE()
            WHERE SalesOrderID = ?
              AND DispatchStatus = 'Dispatched'
        ";

        $statement =
            $this->adapter->query(
                $sql,
                Adapter::QUERY_MODE_PREPARE
            );

        $statement->execute([
            $salesOrderId
        ]);
    }
}
