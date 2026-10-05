<?php

namespace Warehouse\StockOut\Model;

use Zend\Db\Adapter\Adapter;

class StockOutTable
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
            ORDER BY p.ProductName ASC
        ";

        return $this->adapter->query(
            $sql,
            Adapter::QUERY_MODE_EXECUTE
        );
    }

    public function getInventoryByProduct($productId)
    {
        $sql = "
            SELECT
                InventoryID,
                ProductID,
                Quantity,
                ReorderLevel,
                LastUpdated
            FROM Inventory
            WHERE ProductID = ?
        ";

        $statement = $this->adapter->query(
            $sql,
            Adapter::QUERY_MODE_PREPARE
        );

        return $statement->execute([
            $productId
        ])->current();
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
              AND IsActive = 1
        ";

        $statement = $this->adapter->query(
            $sql,
            Adapter::QUERY_MODE_PREPARE
        );

        return $statement->execute([
            $productId
        ])->current();
    }

    public function removeStock(
        $productId,
        $warehouseId,
        $quantity,
        $createdBy
    ) {
        $connection =
            $this->adapter
                ->getDriver()
                ->getConnection();

        $connection->beginTransaction();

        try {

            /*
             * Reduce inventory only when
             * sufficient stock exists.
             */

            $inventorySql = "
                UPDATE Inventory
                SET
                    Quantity = Quantity - ?,
                    LastUpdated = GETDATE()
                WHERE ProductID = ?
                  AND Quantity >= ?
            ";

            $inventoryStatement = $this->adapter->query(
                $inventorySql,
                Adapter::QUERY_MODE_PREPARE
            );

            $result = $inventoryStatement->execute([
                $quantity,
                $productId,
                $quantity
            ]);

            /*
             * Make sure stock was actually reduced.
             */

            if ($result->getAffectedRows() !== 1) {
                throw new \Exception(
                    'Insufficient stock available.'
                );
            }

            /*
             * Record Stock OUT transaction.
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
                    'OUT',
                    ?,
                    'StockOut',
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
                $productId,
                $createdBy
            ]);

            $connection->commit();

            return true;

        } catch (\Exception $e) {

            $connection->rollback();

            throw $e;
        }
    }
}