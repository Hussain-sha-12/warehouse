<?php

namespace Warehouse\Purchase\Model;

use Zend\Db\Adapter\Adapter;

class PurchaseTable
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
                po.PurchaseOrderID,
                po.PurchaseOrderNumber,
                po.SupplierID,
                s.SupplierCode,
                s.SupplierName,
                po.WarehouseID,
                w.WarehouseCode,
                w.WarehouseName,
                po.OrderDate,
                po.Status,
                po.TotalAmount,
                po.CreatedBy
            FROM PurchaseOrders po
            INNER JOIN Suppliers s
                ON s.SupplierID = po.SupplierID
            INNER JOIN Warehouses w
                ON w.WarehouseID = po.WarehouseID
            ORDER BY po.PurchaseOrderID DESC
        ";

        return $this->adapter->query(
            $sql,
            Adapter::QUERY_MODE_EXECUTE
        );
    }

    public function getSuppliers()
    {
        $sql = "
            SELECT SupplierID, SupplierCode, SupplierName
            FROM Suppliers
            WHERE IsActive = 1
            ORDER BY SupplierName ASC
        ";

        return $this->adapter->query(
            $sql,
            Adapter::QUERY_MODE_EXECUTE
        );
    }

    public function getWarehouses()
    {
        $sql = "
            SELECT WarehouseID, WarehouseCode, WarehouseName
            FROM Warehouses
            WHERE IsActive = 1
            ORDER BY WarehouseName ASC
        ";

        return $this->adapter->query(
            $sql,
            Adapter::QUERY_MODE_EXECUTE
        );
    }

    public function getProducts()
    {
        $sql = "
            SELECT ProductID, ProductCode, ProductName, UnitPrice
            FROM Products
            WHERE IsActive = 1
            ORDER BY ProductName ASC
        ";

        return $this->adapter->query(
            $sql,
            Adapter::QUERY_MODE_EXECUTE
        );
    }

    public function createPurchase(
        $supplierId,
        $warehouseId,
        $productId,
        $quantity,
        $unitCost,
        $createdBy
    ) {
        $numberSql = "
            SELECT
                'PO' +
                RIGHT(
                    '0000' +
                    CAST(
                        ISNULL(
                            MAX(
                                TRY_CAST(
                                    SUBSTRING(
                                        PurchaseOrderNumber,
                                        3,
                                        20
                                    ) AS INT
                                )
                            ),
                            0
                        ) + 1 AS VARCHAR(20)
                    ),
                    4
                ) AS PurchaseOrderNumber
            FROM PurchaseOrders
        ";

        $numberRow = $this->adapter
            ->query(
                $numberSql,
                Adapter::QUERY_MODE_EXECUTE
            )
            ->current();

        $poNumber = $numberRow['PurchaseOrderNumber'];

        $totalAmount = $quantity * $unitCost;

        $insertPo = "
            INSERT INTO PurchaseOrders
            (
                PurchaseOrderNumber,
                SupplierID,
                WarehouseID,
                OrderDate,
                Status,
                TotalAmount,
                CreatedBy
            )
            VALUES
            (
                ?,
                ?,
                ?,
                GETDATE(),
                'Pending',
                ?,
                ?
            );

            SELECT SCOPE_IDENTITY() AS PurchaseOrderID;
        ";

        $result = $this->adapter
            ->query(
                $insertPo,
                Adapter::QUERY_MODE_EXECUTE
            );

        $poId = null;

        foreach ($result as $row) {
            if (isset($row['PurchaseOrderID'])) {
                $poId = (int) $row['PurchaseOrderID'];
                break;
            }
        }

        if (!$poId) {
            throw new \RuntimeException(
                'Could not create Purchase Order.'
            );
        }

        $insertItem = "
            INSERT INTO PurchaseOrderItems
            (
                PurchaseOrderID,
                ProductID,
                Quantity,
                UnitCost,
                TotalAmount
            )
            VALUES
            (
                ?,
                ?,
                ?,
                ?,
                ?
            )
        ";

        $this->adapter
            ->query(
                $insertItem,
                Adapter::QUERY_MODE_PREPARE
            )
            ->execute([
                $poId,
                $productId,
                $quantity,
                $unitCost,
                $totalAmount
            ]);

        return $poId;
    }

    public function getPurchaseOrder($purchaseOrderId)
    {
        $sql = "
            SELECT
                po.PurchaseOrderID,
                po.PurchaseOrderNumber,
                po.SupplierID,
                s.SupplierCode,
                s.SupplierName,
                po.WarehouseID,
                w.WarehouseCode,
                w.WarehouseName,
                po.OrderDate,
                po.Status,
                po.TotalAmount,
                po.CreatedBy
            FROM PurchaseOrders po
            INNER JOIN Suppliers s
                ON s.SupplierID = po.SupplierID
            INNER JOIN Warehouses w
                ON w.WarehouseID = po.WarehouseID
            WHERE po.PurchaseOrderID = ?
        ";

        return $this->adapter
            ->query(
                $sql,
                Adapter::QUERY_MODE_PREPARE
            )
            ->execute([
                $purchaseOrderId
            ])
            ->current();
    }

    public function getPurchaseOrderItems($purchaseOrderId)
    {
        $sql = "
            SELECT
                poi.PurchaseOrderItemID,
                poi.PurchaseOrderID,
                poi.ProductID,
                p.ProductCode,
                p.ProductName,
                poi.Quantity,
                poi.UnitCost,
                poi.TotalAmount
            FROM PurchaseOrderItems poi
            INNER JOIN Products p
                ON p.ProductID = poi.ProductID
            WHERE poi.PurchaseOrderID = ?
            ORDER BY poi.PurchaseOrderItemID ASC
        ";

        return $this->adapter
            ->query(
                $sql,
                Adapter::QUERY_MODE_PREPARE
            )
            ->execute([
                $purchaseOrderId
            ]);
    }
}
