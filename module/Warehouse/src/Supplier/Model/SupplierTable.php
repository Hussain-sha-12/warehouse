<?php

namespace Warehouse\Supplier\Model;

use Zend\Db\Adapter\Adapter;

class SupplierTable
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
                SupplierID,
                SupplierCode,
                SupplierName,
                Phone,
                Email,
                Address,
                IsActive,
                CreatedDate
            FROM Suppliers
            ORDER BY SupplierID DESC
        ";

        return $this->adapter->query(
            $sql,
            Adapter::QUERY_MODE_EXECUTE
        );
    }

    public function getSupplier($id)
    {
        $sql = "
            SELECT
                SupplierID,
                SupplierCode,
                SupplierName,
                Phone,
                Email,
                Address,
                IsActive,
                CreatedDate
            FROM Suppliers
            WHERE SupplierID = ?
        ";

        $statement = $this->adapter->query(
            $sql,
            Adapter::QUERY_MODE_PREPARE
        );

        return $statement->execute([$id])->current();
    }

    public function saveSupplier(Supplier $supplier)
    {
        $sql = "
            INSERT INTO Suppliers
            (
                SupplierCode,
                SupplierName,
                Phone,
                Email,
                Address,
                IsActive,
                CreatedDate
            )
            VALUES
            (
                ?,
                ?,
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
            $supplier->SupplierCode,
            $supplier->SupplierName,
            $supplier->Phone,
            $supplier->Email,
            $supplier->Address
        ]);
    }

    public function updateSupplier($id, Supplier $supplier)
    {
        $sql = "
            UPDATE Suppliers
            SET
                SupplierCode = ?,
                SupplierName = ?,
                Phone = ?,
                Email = ?,
                Address = ?,
                IsActive = ?
            WHERE SupplierID = ?
        ";

        $statement = $this->adapter->query(
            $sql,
            Adapter::QUERY_MODE_PREPARE
        );

        return $statement->execute([
            $supplier->SupplierCode,
            $supplier->SupplierName,
            $supplier->Phone,
            $supplier->Email,
            $supplier->Address,
            $supplier->IsActive,
            $id
        ]);
    }

    public function deleteSupplier($id)
    {
        $sql = "
            UPDATE Suppliers
            SET IsActive = 0
            WHERE SupplierID = ?
        ";

        $statement = $this->adapter->query(
            $sql,
            Adapter::QUERY_MODE_PREPARE
        );

        return $statement->execute([$id]);
    }
}