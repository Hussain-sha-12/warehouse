<?php

namespace Warehouse\StockAdjustment\Model;

use Zend\Db\Adapter\Adapter;

class StockAdjustmentTable
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

    public function adjustStock(
        $productId,
        $warehouseId,
        $quantity,
        $adjustmentType,
        $reason,
        $createdBy = null
    ) {
        $productId = (int) $productId;
        $warehouseId = (int) $warehouseId;
        $quantity = (int) $quantity;

        if (
            $productId <= 0 ||
            $warehouseId <= 0 ||
            $quantity <= 0
        ) {
            return false;
        }

        if (!in_array(
            $adjustmentType,
            ['IN', 'OUT'],
            true
        )) {
            return false;
        }

        $connection =
            $this->adapter->getDriver()
                ->getConnection();

        $connection->beginTransaction();

        try {
            /*
             * Check current inventory.
             */
            $sql = "
                SELECT Quantity
                FROM Inventory
                WHERE ProductID = ?
            ";

            $statement = $this->adapter->query(
                $sql,
                Adapter::QUERY_MODE_PREPARE
            );

            $result = $statement->execute([
                $productId
            ]);

            $row = $result->current();

            $currentQuantity = $row
                ? (int) $row['Quantity']
                : 0;

            /*
             * Calculate new quantity.
             */
            if ($adjustmentType === 'IN') {
                $newQuantity =
                    $currentQuantity + $quantity;
            } else {
                $newQuantity =
                    $currentQuantity - $quantity;

                /*
                 * Never allow negative inventory.
                 */
                if ($newQuantity < 0) {
                    $connection->rollback();
                    return false;
                }
            }

            /*
             * Create Inventory row if it does not exist.
             */
            if ($row) {
                $sql = "
                    UPDATE Inventory
                    SET
                        Quantity = ?,
                        LastUpdated = GETDATE()
                    WHERE ProductID = ?
                ";

                $statement = $this->adapter->query(
                    $sql,
                    Adapter::QUERY_MODE_PREPARE
                );

                $statement->execute([
                    $newQuantity,
                    $productId
                ]);
            } else {
                $sql = "
                    INSERT INTO Inventory
                    (
                        ProductID,
                        Quantity,
                        ReorderLevel,
                        LastUpdated
                    )
                    SELECT
                        ProductID,
                        ?,
                        ReorderLevel,
                        GETDATE()
                    FROM Products
                    WHERE ProductID = ?
                ";

                $statement = $this->adapter->query(
                    $sql,
                    Adapter::QUERY_MODE_PREPARE
                );

                $statement->execute([
                    $newQuantity,
                    $productId
                ]);
            }

            /*
             * Record stock transaction.
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
                    ?,
                    ?,
                    'Adjustment',
                    NULL,
                    GETDATE(),
                    ?
                )
            ";

            $statement = $this->adapter->query(
                $sql,
                Adapter::QUERY_MODE_PREPARE
            );

            $statement->execute([
                $productId,
                $warehouseId,
                $adjustmentType,
                $quantity,
                $createdBy
            ]);

            $connection->commit();

            return true;

        } catch (\Exception $e) {

            $connection->rollback();

            return false;
        }
    }
}
