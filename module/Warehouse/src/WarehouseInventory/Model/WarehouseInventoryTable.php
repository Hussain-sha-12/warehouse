<?php

namespace Warehouse\WarehouseInventory\Model;

use Zend\Db\Adapter\Adapter;

class WarehouseInventoryTable
{
    private $adapter;

    public function __construct(Adapter $adapter)
    {
        $this->adapter = $adapter;
    }

    public function getInventory()
    {
        $sql = "
            SELECT
                wi.WarehouseInventoryID,
                p.ProductCode,
                p.ProductName,
                w.WarehouseCode,
                w.WarehouseName,
                wi.Quantity,
                wi.ReorderLevel,
                CASE
                    WHEN wi.Quantity = 0
                        THEN 'Out of Stock'
                    WHEN wi.Quantity <= wi.ReorderLevel
                        THEN 'Low Stock'
                    ELSE 'Available'
                END AS StockStatus,
                wi.LastUpdated
            FROM WarehouseInventory wi
            INNER JOIN Products p
                ON p.ProductID = wi.ProductID
            INNER JOIN Warehouses w
                ON w.WarehouseID = wi.WarehouseID
            ORDER BY
                w.WarehouseName,
                p.ProductName
        ";

        return $this->adapter->query(
            $sql,
            Adapter::QUERY_MODE_EXECUTE
        );
    }
}
