<?php

namespace Warehouse\Dashboard\Model;

use Zend\Db\Adapter\Adapter;

class DashboardTable
{
    private $adapter;

    public function __construct(Adapter $adapter)
    {
        $this->adapter = $adapter;
    }

    private function getCount($table)
    {
        $sql = "SELECT COUNT(*) AS Total FROM " . $table;

        $result = $this->adapter
            ->query($sql, Adapter::QUERY_MODE_EXECUTE)
            ->current();

        return (int) $result['Total'];
    }

    public function getDashboardData()
    {
        $products = $this->getCount('Products');
        $categories = $this->getCount('Categories');
        $suppliers = $this->getCount('Suppliers');
        $purchaseOrders = $this->getCount('PurchaseOrders');
        $inventoryItems = $this->getCount('Inventory');

        $result = $this->adapter->query(
            "SELECT
                ISNULL(SUM(Quantity), 0) AS TotalStock,
                SUM(
                    CASE
                        WHEN Quantity <= ReorderLevel
                        THEN 1
                        ELSE 0
                    END
                ) AS LowStock
             FROM Inventory",
            Adapter::QUERY_MODE_EXECUTE
        )->current();

        return [
            'products'       => $products,
            'categories'     => $categories,
            'suppliers'      => $suppliers,
            'purchaseOrders' => $purchaseOrders,
            'inventoryItems' => $inventoryItems,
            'totalStock'     => (int) $result['TotalStock'],
            'lowStock'      => (int) $result['LowStock'],
        ];
    }
}