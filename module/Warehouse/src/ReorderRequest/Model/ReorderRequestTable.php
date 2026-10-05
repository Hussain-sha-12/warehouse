<?php

namespace Warehouse\ReorderRequest\Model;

use Zend\Db\Adapter\Adapter;

class ReorderRequestTable
{
    private $adapter;

    public function __construct(Adapter $adapter)
    {
        $this->adapter = $adapter;
    }

    public function getProducts()
    {
        $sql = "
            SELECT
                p.ProductID,
                p.ProductCode,
                p.ProductName,
                ISNULL(wi.Quantity, 0) AS Quantity,
                ISNULL(wi.ReorderLevel, p.ReorderLevel) AS ReorderLevel
            FROM Products p
            LEFT JOIN WarehouseInventory wi
                ON wi.ProductID = p.ProductID
                AND wi.WarehouseID =
                (
                    SELECT TOP 1 WarehouseID
                    FROM Warehouses
                    WHERE WarehouseCode = 'WH001'
                      AND IsActive = 1
                )
            WHERE p.IsActive = 1
            ORDER BY p.ProductName
        ";

        return $this->adapter->query(
            $sql,
            Adapter::QUERY_MODE_EXECUTE
        );
    }

    public function getWarehouses()
    {
        $sql = "
            SELECT
                WarehouseID,
                WarehouseCode,
                WarehouseName
            FROM Warehouses
            WHERE IsActive = 1
            ORDER BY WarehouseName
        ";

        return $this->adapter->query(
            $sql,
            Adapter::QUERY_MODE_EXECUTE
        );
    }

    public function getRequests($status = null)
    {
        $sql = "
            SELECT
                rr.ReorderRequestID,
                rr.ProductID,
                p.ProductCode,
                p.ProductName,
                rr.WarehouseID,
                w.WarehouseCode,
                w.WarehouseName,
                rr.RequestedQuantity,
                rr.ReceivedQuantity,
                rr.RemainingQuantity,
                rr.RequestDate,
                rr.Status,
                rr.RequestedBy,
                u.Username AS RequestedByName,
                rr.ApprovedBy,
                rr.ApprovedDate,
                rr.PurchaseOrderID,
                rr.CompletedDate,
                rr.Notes
            FROM ReorderRequests rr
            INNER JOIN Products p
                ON p.ProductID = rr.ProductID
            INNER JOIN Warehouses w
                ON w.WarehouseID = rr.WarehouseID
            LEFT JOIN Users u
                ON u.UserID = rr.RequestedBy
        ";

        $params = [];

        if ($status !== null && $status !== '') {
            $sql .= " WHERE rr.Status = ?";
            $params[] = $status;
        }

        $sql .= "
            ORDER BY
                rr.RequestDate DESC,
                rr.ReorderRequestID DESC
        ";

        $statement = $this->adapter->query(
            $sql,
            Adapter::QUERY_MODE_PREPARE
        );

        return $statement->execute($params);
    }

    public function createRequest(
        $productId,
        $warehouseId,
        $requestedQuantity,
        $requestedBy,
        $notes = null
    ) {
        $checkSql = "
            SELECT COUNT(*) AS RequestCount
            FROM ReorderRequests
            WHERE ProductID = ?
              AND WarehouseID = ?
              AND Status IN
              (
                  'Requested',
                  'Approved',
                  'Purchase Ordered',
                  'Received'
              )
        ";

        $checkStatement = $this->adapter->query(
            $checkSql,
            Adapter::QUERY_MODE_PREPARE
        );

        $checkResult = $checkStatement->execute([
            $productId,
            $warehouseId
        ]);

        $checkRow = $checkResult->current();

        if (
            $checkRow &&
            (int) $checkRow['RequestCount'] > 0
        ) {
            return false;
        }

        $insertSql = "
            INSERT INTO ReorderRequests
            (
                ProductID,
                WarehouseID,
                RequestedQuantity,
                RequestDate,
                Status,
                RequestedBy,
                Notes
            )
            VALUES
            (
                ?,
                ?,
                ?,
                GETDATE(),
                'Requested',
                ?,
                ?
            )
        ";

        try {
            $insertStatement = $this->adapter->query(
                $insertSql,
                Adapter::QUERY_MODE_PREPARE
            );

            $insertStatement->execute([
                $productId,
                $warehouseId,
                $requestedQuantity,
                $requestedBy,
                $notes
            ]);

            return true;

        } catch (\Exception $e) {
            return false;
        }
    }

    public function updateStatus(
        $requestId,
        $newStatus,
        $userId,
        $receivedQuantity = 0
    ) {
        $selectSql = "
            SELECT
                rr.ReorderRequestID,
                rr.ProductID,
                rr.WarehouseID,
                rr.RequestedQuantity,
            rr.ReceivedQuantity,
            rr.RemainingQuantity,
            rr.Status
            FROM ReorderRequests rr
            WHERE rr.ReorderRequestID = ?
        ";

        $row = $this->adapter
            ->query(
                $selectSql,
                Adapter::QUERY_MODE_PREPARE
            )
            ->execute([
                $requestId
            ])
            ->current();

        if (!$row) {
            return [
                'success' => false,
                'message' => 'Reorder request not found.'
            ];
        }

        $currentStatus = $row['Status'];

        $allowed = [
            'Requested' => [
                'Approved',
                'Rejected'
            ],
            'Approved' => [
                'Purchase Ordered'
            ],
            'Purchase Ordered' => [
                'Received'
            ],
            'Received' => [
                'Received',
                'Completed'
            ]
        ];

        if (
            !isset($allowed[$currentStatus]) ||
            !in_array($newStatus, $allowed[$currentStatus])
        ) {
            return [
                'success' => false,
                'message' =>
                    'Invalid status transition: ' .
                    $currentStatus .
                    ' -> ' .
                    $newStatus
            ];
        }

        /*
         * When moving from Approved to Purchase Ordered,
         * automatically create a Purchase Order.
         */
        if ($newStatus === 'Purchase Ordered') {

            $supplierSql = "
                SELECT TOP 1
                    SupplierID,
                    UnitCost
                FROM ProductSuppliers
                WHERE ProductID = ?
                  AND IsActive = 1
                ORDER BY
                    IsPreferred DESC,
                    ProductSupplierID ASC
            ";

            $supplier = $this->adapter
                ->query(
                    $supplierSql,
                    Adapter::QUERY_MODE_PREPARE
                )
                ->execute([
                    $row['ProductID']
                ])
                ->current();

            if (!$supplier) {
                return [
                    'success' => false,
                    'message' =>
                        'No active supplier is mapped to this product.'
                ];
            }

            $unitCost = (float) $supplier['UnitCost'];

            if ($unitCost <= 0) {
                return [
                    'success' => false,
                    'message' =>
                        'Supplier unit cost is not configured.'
                ];
            }

            /*
             * Generate next PO number.
             */
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

            $totalAmount =
                (int) $row['RequestedQuantity'] * $unitCost;

            /*
             * Create Purchase Order.
             */
            $poSql = "
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
    )
";

$this->adapter
    ->query(
        $poSql,
        Adapter::QUERY_MODE_PREPARE
    )
    ->execute([
        $poNumber,
        $supplier['SupplierID'],
        $row['WarehouseID'],
        $totalAmount,
        $userId
    ]);

$idSql = "
    SELECT PurchaseOrderID
    FROM PurchaseOrders
    WHERE PurchaseOrderNumber = ?
";

$idResult = $this->adapter
    ->query(
        $idSql,
        Adapter::QUERY_MODE_PREPARE
    )
    ->execute([
        $poNumber
    ]);

$purchaseOrderId = null;

foreach ($idResult as $poRow) {
    if (isset($poRow['PurchaseOrderID'])) {
        $purchaseOrderId = (int) $poRow['PurchaseOrderID'];
        break;
    }
}

if (!$purchaseOrderId) {
    return [
        'success' => false,
        'message' => 'Purchase Order could not be created.'
    ];
}
        /*
             * Create Purchase Order Item.
             */
            $itemSql = "
    INSERT INTO PurchaseOrderItems
    (
        PurchaseOrderID,
        ProductID,
        Quantity,
        UnitCost
    )
    VALUES
    (
        ?,
        ?,
        ?,
        ?
    )
";

            $this->adapter
                ->query(
                    $itemSql,
                    Adapter::QUERY_MODE_PREPARE
                )
                ->execute([
                    $purchaseOrderId,
                    $row['ProductID'],
                    $row['RequestedQuantity'],
                $unitCost
                ]);

            /*
             * Link Purchase Order to Reorder Request.
             */
            $updateSql = "
                UPDATE ReorderRequests
                SET
                    Status = ?,
                    PurchaseOrderID = ?
                WHERE ReorderRequestID = ?
            ";

            $this->adapter
                ->query(
                    $updateSql,
                    Adapter::QUERY_MODE_PREPARE
                )
                ->execute([
                    $newStatus,
                    $purchaseOrderId,
                    $requestId
                ]);

            return [
                'success' => true,
                'message' =>
                    'Purchase Order created successfully.'
            ];
        }

        if ($newStatus === 'Approved') {

            $sql = "
                UPDATE ReorderRequests
                SET
                    Status = ?,
                    ApprovedBy = ?,
                    ApprovedDate = GETDATE()
                WHERE ReorderRequestID = ?
            ";

            $params = [
                $newStatus,
                $userId,
                $requestId
            ];

        } elseif ($newStatus === 'Received') {

            $quantity = (int) $receivedQuantity;

            if ($quantity <= 0) {
                $quantity = (int) $row['RemainingQuantity'];
            }

            $remaining = (int) $row['RemainingQuantity'];

            if ($quantity > $remaining) {
                return [
                    'success' => false,
                    'message' => 'Received quantity cannot exceed remaining quantity.'
                ];
            }

            $inventorySql = "
                UPDATE WarehouseInventory
                SET
                    Quantity = Quantity + ?,
                    LastUpdated = GETDATE()
                WHERE ProductID = ?
                  AND WarehouseID = ?
            ";

            $this->adapter
                ->query($inventorySql, Adapter::QUERY_MODE_PREPARE)
                ->execute([
                    $quantity,
                    $row['ProductID'],
                    $row['WarehouseID']
                ]);

            $receivedTotal = (int) $row['ReceivedQuantity'] + $quantity;
            $remainingTotal = (int) $row['RequestedQuantity'] - $receivedTotal;

            $sql = "
                UPDATE ReorderRequests
                SET
                    ReceivedQuantity = ?,
                    RemainingQuantity = ?,
                    Status = 'Received'
                WHERE ReorderRequestID = ?
            ";

            $params = [
                $receivedTotal,
                $remainingTotal,
                $requestId
            ];

        } elseif ($newStatus === 'Completed') {

            $sql = "
                UPDATE ReorderRequests
                SET
                    Status = ?,
                    CompletedDate = GETDATE()
                WHERE ReorderRequestID = ?
            ";

            $params = [
                $newStatus,
                $requestId
            ];

        } else {

            $sql = "
                UPDATE ReorderRequests
                SET
                    Status = ?
                WHERE ReorderRequestID = ?
            ";

            $params = [
                $newStatus,
                $requestId
            ];
        }

        $this->adapter
            ->query(
                $sql,
                Adapter::QUERY_MODE_PREPARE
            )
            ->execute($params);

        return [
            'success' => true,
            'message' => 'Status updated successfully.'
        ];
    }
}


