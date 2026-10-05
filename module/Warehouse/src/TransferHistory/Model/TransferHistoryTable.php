<?php

namespace Warehouse\TransferHistory\Model;

use Zend\Db\Adapter\Adapter;

class TransferHistoryTable
{
    private $adapter;

    public function __construct(Adapter $adapter)
    {
        $this->adapter = $adapter;
    }

    public function getTransfers()
    {
        $sql = "
            SELECT
                outTx.TransactionID AS TransferOutID,
                inTx.TransactionID AS TransferInID,
                p.ProductCode,
                p.ProductName,
                fw.WarehouseCode AS FromWarehouseCode,
                fw.WarehouseName AS FromWarehouseName,
                tw.WarehouseCode AS ToWarehouseCode,
                tw.WarehouseName AS ToWarehouseName,
                outTx.Quantity,
                outTx.TransactionDate,
                u.Username
            FROM StockTransactions outTx
            INNER JOIN StockTransactions inTx
                ON inTx.ProductID = outTx.ProductID
                AND inTx.Quantity = outTx.Quantity
                AND inTx.ReferenceType = 'Transfer'
                AND inTx.TransactionType = 'IN'
                AND inTx.TransactionID > outTx.TransactionID
            INNER JOIN Products p
                ON p.ProductID = outTx.ProductID
            INNER JOIN Warehouses fw
                ON fw.WarehouseID = outTx.WarehouseID
            INNER JOIN Warehouses tw
                ON tw.WarehouseID = inTx.WarehouseID
            LEFT JOIN Users u
                ON u.UserID = outTx.CreatedBy
            WHERE outTx.ReferenceType = 'Transfer'
              AND outTx.TransactionType = 'OUT'
            ORDER BY outTx.TransactionID DESC
        ";

        return $this->adapter->query(
            $sql,
            Adapter::QUERY_MODE_EXECUTE
        );
    }
}
