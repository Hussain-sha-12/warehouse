<?php

namespace Warehouse\StockTransfer\Model;

use Zend\Db\Adapter\Adapter;

class StockTransferTable
{
    private $adapter;

    public function __construct(Adapter $adapter)
    {
        $this->adapter = $adapter;
    }

    public function getProducts()
    {
        $sql = "
            SELECT
                p.ProductID,
                p.ProductCode,
                p.ProductName,
                ISNULL(i.Quantity, 0) AS Quantity
            FROM Products p
            LEFT JOIN Inventory i
                ON i.ProductID = p.ProductID
            WHERE p.IsActive = 1
            ORDER BY p.ProductName
        ";

        return $this->adapter->query(
            $sql,
            Adapter::QUERY_MODE_EXECUTE
        );
    }

    public function getWarehouses()
    {
        $sql = "
            SELECT
                WarehouseID,
                WarehouseCode,
                WarehouseName
            FROM Warehouses
            WHERE IsActive = 1
            ORDER BY WarehouseName
        ";

        return $this->adapter->query(
            $sql,
            Adapter::QUERY_MODE_EXECUTE
        );
    }

    public function transferStock(
        $productId,
        $fromWarehouseId,
        $toWarehouseId,
        $quantity,
        $createdBy = null
    ) {
        $productId =
            (int) $productId;

        $fromWarehouseId =
            (int) $fromWarehouseId;

        $toWarehouseId =
            (int) $toWarehouseId;

        $quantity =
            (int) $quantity;

        if (
            $productId <= 0 ||
            $fromWarehouseId <= 0 ||
            $toWarehouseId <= 0 ||
            $quantity <= 0
        ) {
            return false;
        }

        if ($fromWarehouseId === $toWarehouseId) {
            return false;
        }

        $connection =
            $this->adapter
                ->getDriver()
                ->getConnection();

        $connection->beginTransaction();

        try {

            /*
             * Current Inventory table stores one quantity
             * per ProductID, not per WarehouseID.
             *
             * Therefore we validate and keep the total
             * product quantity unchanged during transfer.
             */

            $sql = "
                SELECT Quantity
                FROM Inventory
                WHERE ProductID = ?
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

            $row =
                $result->current();

            $currentQuantity =
                $row
                    ? (int) $row['Quantity']
                    : 0;

            if ($currentQuantity < $quantity) {
                $connection->rollback();
                return false;
            }

            /*
             * Record stock OUT from source warehouse.
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
                    ?,
                    ?,
                    'OUT',
                    ?,
                    'Transfer',
                    NULL,
                    GETDATE(),
                    ?
                )
            ";

            $statement =
                $this->adapter->query(
                    $sql,
                    Adapter::QUERY_MODE_PREPARE
                );

            $statement->execute([
                $productId,
                $fromWarehouseId,
                $quantity,
                $createdBy
            ]);

            /*
             * Record stock IN at destination warehouse.
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
                    ?,
                    ?,
                    'IN',
                    ?,
                    'Transfer',
                    NULL,
                    GETDATE(),
                    ?
                )
            ";

            $statement =
                $this->adapter->query(
                    $sql,
                    Adapter::QUERY_MODE_PREPARE
                );

            $statement->execute([
                $productId,
                $toWarehouseId,
                $quantity,
                $createdBy
            ]);

            /*
             * Total Inventory remains unchanged because
             * stock is moved between warehouses.
             */

            $connection->commit();

            return true;

        } catch (\Exception $e) {

            $connection->rollback();

            return false;
        }
    }
}
