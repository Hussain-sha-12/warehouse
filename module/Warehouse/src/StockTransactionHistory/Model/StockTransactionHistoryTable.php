<?php

namespace Warehouse\StockTransactionHistory\Model;

use Zend\Db\Adapter\Adapter;

class StockTransactionHistoryTable
{
    private $adapter;

    public function __construct(Adapter $adapter)
    {
        $this->adapter = $adapter;
    }

    public function getTransactions()
    {
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
                st.TransactionDate,
                u.Username
            FROM StockTransactions st
            INNER JOIN Products p
                ON p.ProductID = st.ProductID
            INNER JOIN Warehouses w
                ON w.WarehouseID = st.WarehouseID
            LEFT JOIN Users u
                ON u.UserID = st.CreatedBy
            ORDER BY st.TransactionID DESC
        ";

        return $this->adapter->query(
            $sql,
            Adapter::QUERY_MODE_EXECUTE
        );
    }
}
