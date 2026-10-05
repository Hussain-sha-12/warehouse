<?php

namespace Warehouse\StockIn\Model;

use Zend\Db\Adapter\Adapter;

class StockInTable
{
    private $adapter;

    public function __construct(Adapter $adapter)
    {
        $this->adapter = $adapter;
    }

    public function getPurchaseOrders()
    {
        $sql = "
            SELECT
                PurchaseOrderID,
                PurchaseOrderNumber,
                WarehouseID,
                Status
            FROM PurchaseOrders
            ORDER BY PurchaseOrderID DESC
        ";

        return $this->adapter
            ->query($sql, Adapter::QUERY_MODE_EXECUTE);
    }

    public function getPurchaseOrderItems($purchaseOrderId)
    {
        $sql = "
            SELECT
                PurchaseOrderItemID,
                PurchaseOrderID,
                ProductID,
                Quantity,
                UnitCost,
                TotalAmount
            FROM PurchaseOrderItems
            WHERE PurchaseOrderID = ?
        ";

        $statement = $this->adapter->query(
            $sql,
            Adapter::QUERY_MODE_PREPARE
        );

        return $statement->execute([
            $purchaseOrderId
        ]);
    }

    public function getPurchaseOrderItem($purchaseOrderItemId)
    {
        $sql = "
            SELECT
                poi.PurchaseOrderItemID,
                poi.PurchaseOrderID,
                poi.ProductID,
                poi.Quantity,
                poi.UnitCost,
                poi.TotalAmount,
                po.WarehouseID
            FROM PurchaseOrderItems poi
            INNER JOIN PurchaseOrders po
                ON po.PurchaseOrderID = poi.PurchaseOrderID
            WHERE poi.PurchaseOrderItemID = ?
        ";

        $statement = $this->adapter->query(
            $sql,
            Adapter::QUERY_MODE_PREPARE
        );

        return $statement
            ->execute([
                $purchaseOrderItemId
            ])
            ->current();
    }

    public function getProduct($productId)
    {
        $sql = "
            SELECT
                ProductID,
                ProductCode,
                ProductName
            FROM Products
            WHERE ProductID = ?
        ";

        $statement = $this->adapter->query(
            $sql,
            Adapter::QUERY_MODE_PREPARE
        );

        return $statement
            ->execute([
                $productId
            ])
            ->current();
    }

    public function getReceivedQuantity($purchaseOrderItemId)
    {
        $sql = "
            SELECT
                ISNULL(SUM(Quantity), 0) AS ReceivedQuantity
            FROM StockTransactions
            WHERE TransactionType = 'IN'
              AND ReferenceType = 'PurchaseOrderItem'
              AND ReferenceID = ?
        ";

        $statement = $this->adapter->query(
            $sql,
            Adapter::QUERY_MODE_PREPARE
        );

        $result = $statement
            ->execute([
                $purchaseOrderItemId
            ])
            ->current();

        return (int) $result['ReceivedQuantity'];
    }

    public function receiveStock(
        $productId,
        $warehouseId,
        $quantity,
        $unitCost,
        $purchaseOrderItemId,
        $createdBy
    ) {
        $connection =
            $this->adapter
                ->getDriver()
                ->getConnection();

        $connection->beginTransaction();

        try {

            /*
             * 1. Update Inventory.
             *
             * If the product already has an Inventory row,
             * increase the quantity.
             *
             * Otherwise create the row.
             */

            $inventorySql = "
                IF EXISTS
                (
                    SELECT 1
                    FROM Inventory
                    WHERE ProductID = ?
                )
                BEGIN

                    UPDATE Inventory
                    SET
                        Quantity = Quantity + ?,
                        LastUpdated = GETDATE()
                    WHERE ProductID = ?

                END
                ELSE
                BEGIN

                    INSERT INTO Inventory
                    (
                        ProductID,
                        Quantity,
                        ReorderLevel
                    )
                    SELECT
                        ProductID,
                        ?,
                        ReorderLevel
                    FROM Products
                    WHERE ProductID = ?

                END
            ";

            $inventoryStatement = $this->adapter->query(
                $inventorySql,
                Adapter::QUERY_MODE_PREPARE
            );

            $inventoryStatement->execute([
                $productId,
                $quantity,
                $productId,
                $quantity,
                $productId
            ]);

            /*
             * 2. Insert Stock IN transaction.
             */

            $transactionSql = "
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
                    ?,
                    ?,
                    'IN',
                    ?,
                    'PurchaseOrderItem',
                    ?,
                    GETDATE(),
                    ?
                )
            ";

            $transactionStatement = $this->adapter->query(
                $transactionSql,
                Adapter::QUERY_MODE_PREPARE
            );

            $transactionStatement->execute([
                $productId,
                $warehouseId,
                $quantity,
                $purchaseOrderItemId,
                $createdBy
            ]);

            /*
             * 3. Commit both operations.
             */

            $connection->commit();

            return true;

        } catch (\Exception $e) {

            /*
             * If anything fails, undo both operations.
             */

            $connection->rollback();

            throw $e;
        }
    }
    public function getAdapter()
{
    return $this->adapter;
}
}