<?php

namespace Warehouse\Customer\Model;

use Zend\Db\Adapter\Adapter;

class CustomerTable
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
                CustomerID,
                CustomerCode,
                CustomerName,
                Phone,
                Email,
                Address,
                IsActive,
                CreatedDate
            FROM Customers
            ORDER BY CustomerID DESC
        ";

        return $this->adapter->query(
            $sql,
            Adapter::QUERY_MODE_EXECUTE
        );
    }

    public function getCustomer($id)
    {
        $sql = "
            SELECT
                CustomerID,
                CustomerCode,
                CustomerName,
                Phone,
                Email,
                Address,
                IsActive,
                CreatedDate
            FROM Customers
            WHERE CustomerID = ?
        ";

        $statement = $this->adapter->query(
            $sql,
            Adapter::QUERY_MODE_PREPARE
        );

        return $statement->execute([$id])->current();
    }

    public function saveCustomer(Customer $customer)
    {
        $sql = "
            INSERT INTO Customers
            (
                CustomerCode,
                CustomerName,
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
            $customer->CustomerCode,
            $customer->CustomerName,
            $customer->Phone,
            $customer->Email,
            $customer->Address
        ]);
    }

    public function updateCustomer($id, Customer $customer)
    {
        $sql = "
            UPDATE Customers
            SET
                CustomerCode = ?,
                CustomerName = ?,
                Phone = ?,
                Email = ?,
                Address = ?,
                IsActive = ?
            WHERE CustomerID = ?
        ";

        $statement = $this->adapter->query(
            $sql,
            Adapter::QUERY_MODE_PREPARE
        );

        return $statement->execute([
            $customer->CustomerCode,
            $customer->CustomerName,
            $customer->Phone,
            $customer->Email,
            $customer->Address,
            $customer->IsActive,
            $id
        ]);
    }

    public function deleteCustomer($id)
    {
        $sql = "
            UPDATE Customers
            SET IsActive = 0
            WHERE CustomerID = ?
        ";

        $statement = $this->adapter->query(
            $sql,
            Adapter::QUERY_MODE_PREPARE
        );

        return $statement->execute([$id]);
    }
}