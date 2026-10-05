<?php

namespace Warehouse\Warehouse\Model;

use Zend\Db\Adapter\Adapter;

class WarehouseTable
{
    private $adapter;

    public function __construct(Adapter $adapter)
    {
        $this->adapter = $adapter;
    }

    public function fetchAll()
    {
        $sql = "
            SELECT
                WarehouseID,
                WarehouseCode,
                WarehouseName,
                Location,
                IsActive,
                CreatedDate
            FROM Warehouses
            ORDER BY WarehouseID DESC
        ";

        return $this->adapter->query(
            $sql,
            Adapter::QUERY_MODE_EXECUTE
        );
    }

    public function getWarehouse($id)
    {
        $sql = "
            SELECT
                WarehouseID,
                WarehouseCode,
                WarehouseName,
                Location,
                IsActive,
                CreatedDate
            FROM Warehouses
            WHERE WarehouseID = ?
        ";

        $statement = $this->adapter->query(
            $sql,
            Adapter::QUERY_MODE_PREPARE
        );

        return $statement->execute([$id])->current();
    }

    public function saveWarehouse(Warehouse $warehouse)
    {
        $sql = "
            INSERT INTO Warehouses
            (
                WarehouseCode,
                WarehouseName,
                Location,
                IsActive,
                CreatedDate
            )
            VALUES
            (
                ?,
                ?,
                ?,
                1,
                GETDATE()
            )
        ";

        $statement = $this->adapter->query(
            $sql,
            Adapter::QUERY_MODE_PREPARE
        );

        return $statement->execute([
            $warehouse->WarehouseCode,
            $warehouse->WarehouseName,
            $warehouse->Location
        ]);
    }

    public function updateWarehouse($id, Warehouse $warehouse)
    {
        $sql = "
            UPDATE Warehouses
            SET
                WarehouseCode = ?,
                WarehouseName = ?,
                Location = ?,
                IsActive = ?
            WHERE WarehouseID = ?
        ";

        $statement = $this->adapter->query(
            $sql,
            Adapter::QUERY_MODE_PREPARE
        );

        return $statement->execute([
            $warehouse->WarehouseCode,
            $warehouse->WarehouseName,
            $warehouse->Location,
            $warehouse->IsActive,
            $id
        ]);
    }

    public function deleteWarehouse($id)
    {
        $sql = "
            UPDATE Warehouses
            SET IsActive = 0
            WHERE WarehouseID = ?
        ";

        $statement = $this->adapter->query(
            $sql,
            Adapter::QUERY_MODE_PREPARE
        );

        return $statement->execute([$id]);
    }
}