<?php

namespace Warehouse\InventoryReport\Model;

use Zend\Db\Adapter\Adapter;

class InventoryReportTable
{
    private $adapter;

    public function __construct(Adapter $adapter)
    {
        $this->adapter = $adapter;
    }

    public function getStockReport()
    {
        $sql = "
            SELECT
                p.ProductID,
                p.ProductCode,
                p.ProductName,
                ISNULL(i.Quantity, 0) AS Quantity,
                ISNULL(i.ReorderLevel, p.ReorderLevel) AS ReorderLevel,
                i.LastUpdated,
                CASE
                    WHEN ISNULL(i.Quantity, 0) = 0
                        THEN 'Out of Stock'
                    WHEN ISNULL(i.Quantity, 0)
                         <= ISNULL(i.ReorderLevel, p.ReorderLevel)
                        THEN 'Low Stock'
                    ELSE 'Available'
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

    public function getWarehouses()
    {
        $sql = "
            SELECT
                WarehouseID,
                WarehouseCode,
                WarehouseName
            FROM Warehouses
            WHERE IsActive = 1
            ORDER BY WarehouseName ASC
        ";

        return $this->adapter->query(
            $sql,
            Adapter::QUERY_MODE_EXECUTE
        );
    }

    public function getTransactionCount(
        $warehouseId = '',
        $transactionType = '',
        $fromDate = '',
        $toDate = ''
    ) {
        $sql = "
            SELECT COUNT(*) AS Total
            FROM StockTransactions st
            WHERE 1 = 1
        ";

        $params = [];

        if ($warehouseId !== '') {
            $sql .= " AND st.WarehouseID = ?";
            $params[] = (int) $warehouseId;
        }

        if ($transactionType !== '') {
            $sql .= " AND st.TransactionType = ?";
            $params[] = $transactionType;
        }

        if ($fromDate !== '') {
            $sql .= " AND CAST(st.TransactionDate AS DATE) >= ?";
            $params[] = $fromDate;
        }

        if ($toDate !== '') {
            $sql .= " AND CAST(st.TransactionDate AS DATE) <= ?";
            $params[] = $toDate;
        }

        $statement = $this->adapter->query(
            $sql,
            Adapter::QUERY_MODE_PREPARE
        );

        $result = $statement->execute($params)->current();

        return (int) $result['Total'];
    }

   public function getTransactions(
    $warehouseId = '',
    $transactionType = '',
    $fromDate = '',
    $toDate = '',
    $page = 1,
    $perPage = 25
) {
    $page = max(1, (int) $page);

    $allowedPerPage = [10, 25, 50, 100];

    if (!in_array($perPage, $allowedPerPage)) {
        $perPage = 25;
    }

    $perPage = (int) $perPage;

    $offset = (int) (($page - 1) * $perPage);

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
            st.CreatedBy

        FROM StockTransactions st

        INNER JOIN Products p
            ON p.ProductID = st.ProductID

        INNER JOIN Warehouses w
            ON w.WarehouseID = st.WarehouseID

        WHERE 1 = 1
    ";

    $params = [];

    if ($warehouseId !== '') {
        $sql .= " AND st.WarehouseID = ?";
        $params[] = (int) $warehouseId;
    }

    if ($transactionType !== '') {
        $sql .= " AND st.TransactionType = ?";
        $params[] = $transactionType;
    }

    if ($fromDate !== '') {
        $sql .= " AND CAST(st.TransactionDate AS DATE) >= ?";
        $params[] = $fromDate;
    }

    if ($toDate !== '') {
        $sql .= " AND CAST(st.TransactionDate AS DATE) <= ?";
        $params[] = $toDate;
    }

    /*
     * IMPORTANT:
     * OFFSET and FETCH values are inserted as validated integers.
     * Do NOT use ? parameters here with SQL Server ODBC 17.
     */
    $sql .= "
        ORDER BY st.TransactionID DESC
        OFFSET {$offset} ROWS
        FETCH NEXT {$perPage} ROWS ONLY
    ";

    $statement = $this->adapter->query(
        $sql,
        Adapter::QUERY_MODE_PREPARE
    );

    return $statement->execute($params);
} 

    public function getProductMovement()
    {
        $sql = "
            SELECT
                p.ProductID,
                p.ProductCode,
                p.ProductName,

                ISNULL(
                    SUM(
                        CASE
                            WHEN st.TransactionType = 'IN'
                            THEN st.Quantity
                            ELSE 0
                        END
                    ),
                    0
                ) AS TotalStockIn,

                ISNULL(
                    SUM(
                        CASE
                            WHEN st.TransactionType = 'OUT'
                            THEN st.Quantity
                            ELSE 0
                        END
                    ),
                    0
                ) AS TotalStockOut,

                ISNULL(i.Quantity, 0) AS CurrentStock

            FROM Products p

            LEFT JOIN StockTransactions st
                ON st.ProductID = p.ProductID

            LEFT JOIN Inventory i
                ON i.ProductID = p.ProductID

            WHERE p.IsActive = 1

            GROUP BY
                p.ProductID,
                p.ProductCode,
                p.ProductName,
                i.Quantity

            ORDER BY p.ProductName ASC
        ";

        return $this->adapter->query(
            $sql,
            Adapter::QUERY_MODE_EXECUTE
        );
    }

    public function getSummary()
    {
        $sql = "
            SELECT
                COUNT(*) AS TotalProducts,

                ISNULL(SUM(i.Quantity), 0) AS TotalStock,

                ISNULL(
                    SUM(
                        CASE
                            WHEN ISNULL(i.Quantity, 0) = 0
                            THEN 1
                            ELSE 0
                        END
                    ),
                    0
                ) AS OutOfStock,

                ISNULL(
                    SUM(
                        CASE
                            WHEN ISNULL(i.Quantity, 0) > 0
                             AND ISNULL(i.Quantity, 0)
                                 <= ISNULL(
                                     i.ReorderLevel,
                                     p.ReorderLevel
                                 )
                            THEN 1
                            ELSE 0
                        END
                    ),
                    0
                ) AS LowStock

            FROM Products p

            LEFT JOIN Inventory i
                ON i.ProductID = p.ProductID

            WHERE p.IsActive = 1
        ";

        return $this->adapter->query(
            $sql,
            Adapter::QUERY_MODE_EXECUTE
        )->current();
    }
}