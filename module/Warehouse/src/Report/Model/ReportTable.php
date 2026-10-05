<?php

namespace Warehouse\Report\Model;

use Zend\Db\Adapter\Adapter;

class ReportTable
{
    private $adapter;

    public function __construct(Adapter $adapter)
    {
        $this->adapter = $adapter;
    }

    public function getStockSummary()
    {
        $sql = "
            SELECT
                p.ProductCode,
                p.ProductName,
                ISNULL(i.Quantity, 0) AS Quantity,
                ISNULL(i.ReorderLevel, 0) AS ReorderLevel,
                CASE
                    WHEN ISNULL(i.Quantity, 0) <= ISNULL(i.ReorderLevel, 0)
                    THEN 'LOW STOCK'
                    ELSE 'AVAILABLE'
                END AS StockStatus
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

    public function getStockTransactions(
        $transactionType = '',
        $fromDate = '',
        $toDate = ''
    ) {
        $sql = "
            SELECT
                st.TransactionID,
                p.ProductCode,
                p.ProductName,
                w.WarehouseCode,
                w.WarehouseName,
                st.TransactionType,
                st.Quantity,
                st.ReferenceType,
                st.ReferenceID,
                st.TransactionDate
            FROM StockTransactions st
            INNER JOIN Products p
                ON p.ProductID = st.ProductID
            INNER JOIN Warehouses w
                ON w.WarehouseID = st.WarehouseID
            WHERE 1 = 1
        ";

        $params = [];

        if ($transactionType !== '') {
            $sql .= "
                AND st.TransactionType = ?
            ";

            $params[] = $transactionType;
        }

        if ($fromDate !== '') {
            $sql .= "
                AND CAST(st.TransactionDate AS DATE) >= ?
            ";

            $params[] = $fromDate;
        }

        if ($toDate !== '') {
            $sql .= "
                AND CAST(st.TransactionDate AS DATE) <= ?
            ";

            $params[] = $toDate;
        }

        $sql .= "
            ORDER BY st.TransactionID DESC
        ";

        $statement = $this->adapter->query(
            $sql,
            Adapter::QUERY_MODE_PREPARE
        );

        return $statement->execute($params);
    }

    public function getSalesReport()
    {
        $sql = "
            SELECT
                so.SalesOrderID,
                so.SalesOrderNumber,
                c.CustomerName,
                so.OrderDate,
                so.Status,
                so.TotalAmount
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

    public function getPurchaseReport()
    {
        $sql = "
            SELECT
                po.PurchaseOrderID,
                po.PurchaseOrderNumber,
                s.SupplierName,
                po.OrderDate,
                po.Status,
                po.TotalAmount
            FROM PurchaseOrders po
            INNER JOIN Suppliers s
                ON s.SupplierID = po.SupplierID
            ORDER BY po.PurchaseOrderID DESC
        ";

        return $this->adapter->query(
            $sql,
            Adapter::QUERY_MODE_EXECUTE
        );
    }
}