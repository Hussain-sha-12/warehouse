<?php

namespace Warehouse\ReorderManagement\Model;

use Zend\Db\Adapter\Adapter;

class ReorderManagementTable
{
    private $adapter;

    public function __construct(Adapter $adapter)
    {
        $this->adapter = $adapter;
    }

    public function getReorderItems($status = '')
    {
        $sql = "
            SELECT
                wi.WarehouseInventoryID,
                p.ProductID,
                p.ProductCode,
                p.ProductName,
                w.WarehouseID,
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

                CASE
                    WHEN wi.Quantity < wi.ReorderLevel
                        THEN wi.ReorderLevel - wi.Quantity
                    ELSE 0
                END AS SuggestedReorderQuantity,

                wi.LastUpdated

            FROM WarehouseInventory wi

            INNER JOIN Products p
                ON p.ProductID = wi.ProductID

            INNER JOIN Warehouses w
                ON w.WarehouseID = wi.WarehouseID

            WHERE p.IsActive = 1
        ";

        if ($status === 'Out of Stock') {
            $sql .= " AND wi.Quantity = 0 ";
        }

        if ($status === 'Low Stock') {
            $sql .= "
                AND wi.Quantity > 0
                AND wi.Quantity <= wi.ReorderLevel
            ";
        }

        if ($status === 'Available') {
            $sql .= "
                AND wi.Quantity > wi.ReorderLevel
            ";
        }

        $sql .= "
            ORDER BY
                CASE
                    WHEN wi.Quantity = 0 THEN 1
                    WHEN wi.Quantity <= wi.ReorderLevel THEN 2
                    ELSE 3
                END,
                p.ProductName
        ";

        return $this->adapter->query(
            $sql,
            Adapter::QUERY_MODE_EXECUTE
        );
    }

    public function getStatusCounts()
    {
        $sql = "
            SELECT
                COUNT(*) AS AllStock,

                SUM(
                    CASE
                        WHEN wi.Quantity > wi.ReorderLevel
                            THEN 1
                        ELSE 0
                    END
                ) AS AvailableStock,

                SUM(
                    CASE
                        WHEN wi.Quantity > 0
                         AND wi.Quantity <= wi.ReorderLevel
                            THEN 1
                        ELSE 0
                    END
                ) AS LowStock,

                SUM(
                    CASE
                        WHEN wi.Quantity = 0
                            THEN 1
                        ELSE 0
                    END
                ) AS OutOfStock

            FROM WarehouseInventory wi

            INNER JOIN Products p
                ON p.ProductID = wi.ProductID

            WHERE p.IsActive = 1
        ";

        $result = $this->adapter->query(
            $sql,
            Adapter::QUERY_MODE_EXECUTE
        );

        return $result->current();
    }
}

