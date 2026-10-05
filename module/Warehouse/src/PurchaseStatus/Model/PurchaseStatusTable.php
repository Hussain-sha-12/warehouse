<?php

namespace Warehouse\PurchaseStatus\Model;

use Zend\Db\Adapter\Adapter;

class PurchaseStatusTable
{
    private $adapter;

    public function __construct(Adapter $adapter)
    {
        $this->adapter = $adapter;
    }

    public function getPurchaseOrders()
    {
        $sql = "
            SELECT
                po.PurchaseOrderID,
                po.PurchaseOrderNumber,
                s.SupplierName,
                w.WarehouseName,
                po.OrderDate,
                po.Status,
                po.TotalAmount
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

    public function updatePurchaseOrderStatus($purchaseOrderId)
    {
        $sql = "
            SELECT
                poi.PurchaseOrderItemID,
                poi.Quantity AS OrderedQuantity,

                ISNULL(
                    (
                        SELECT SUM(st.Quantity)
                        FROM StockTransactions st

                        WHERE st.TransactionType = 'IN'
                          AND st.ReferenceType = 'PurchaseOrderItem'
                          AND st.ReferenceID =
                              poi.PurchaseOrderItemID
                    ),
                    0
                ) AS ReceivedQuantity

            FROM PurchaseOrderItems poi

            WHERE poi.PurchaseOrderID = ?
        ";

        $statement = $this->adapter->query(
            $sql,
            Adapter::QUERY_MODE_PREPARE
        );

        $items = $statement->execute([
            $purchaseOrderId
        ]);

        $totalItems = 0;
        $fullyReceived = 0;
        $hasReceived = false;

        foreach ($items as $item) {

            $totalItems++;

            $ordered =
                (int) $item['OrderedQuantity'];

            $received =
                (int) $item['ReceivedQuantity'];

            if ($received > 0) {
                $hasReceived = true;
            }

            if ($received >= $ordered) {
                $fullyReceived++;
            }
        }

        if ($totalItems === 0) {
            return false;
        }

        if ($fullyReceived === $totalItems) {

            $status = 'Received';

        } elseif ($hasReceived) {

            $status = 'Partial';

        } else {

            $status = 'Pending';
        }

        $updateSql = "
            UPDATE PurchaseOrders

            SET Status = ?

            WHERE PurchaseOrderID = ?
              AND Status <> 'Cancelled'
        ";

        $updateStatement = $this->adapter->query(
            $updateSql,
            Adapter::QUERY_MODE_PREPARE
        );

        $updateStatement->execute([
            $status,
            $purchaseOrderId
        ]);

        return true;
    }
}