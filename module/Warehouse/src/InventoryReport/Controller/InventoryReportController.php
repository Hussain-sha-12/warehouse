<?php

namespace Warehouse\InventoryReport\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;

use Warehouse\InventoryReport\Model\InventoryReportTable;

class InventoryReportController extends AbstractActionController
{
    private $inventoryReportTable;

    public function __construct(
        InventoryReportTable $inventoryReportTable
    ) {
        $this->inventoryReportTable =
            $inventoryReportTable;
    }

    /*
     * Inventory Report
     */
    public function indexAction()
    {
        $summary =
            $this->inventoryReportTable
                ->getSummary();

        $stock =
            $this->inventoryReportTable
                ->getStockReport();

        return new ViewModel([
            'summary' => $summary,
            'stock'   => $stock
        ]);
    }

    /*
     * Stock Transactions Report
     */
    public function transactionsAction()
    {
        $request = $this->getRequest();

        /*
         * Default filter values
         */
        $warehouseId = '';
        $transactionType = '';
        $fromDate = '';
        $toDate = '';

        /*
         * Default pagination
         */
        $page = 1;
        $perPage = 25;

        $error = null;

        /*
         * Read filters from POST
         */
        if ($request->isPost()) {

            $warehouseId =
                trim(
                    $request->getPost(
                        'WarehouseID',
                        ''
                    )
                );

            $transactionType =
                trim(
                    $request->getPost(
                        'TransactionType',
                        ''
                    )
                );

            $fromDate =
                trim(
                    $request->getPost(
                        'FromDate',
                        ''
                    )
                );

            $toDate =
                trim(
                    $request->getPost(
                        'ToDate',
                        ''
                    )
                );

            /*
             * New filter search always starts
             * from page 1.
             */
            $page = 1;
        }

        /*
         * Read pagination from GET.
         *
         * Pagination links use GET so that
         * filters can remain in the URL.
         */
        if (!$request->isPost()) {

            $page =
                (int) $this->params()
                    ->fromQuery('page', 1);

            $perPage =
                (int) $this->params()
                    ->fromQuery('perPage', 25);

            $warehouseId =
                trim(
                    $this->params()
                        ->fromQuery(
                            'WarehouseID',
                            ''
                        )
                );

            $transactionType =
                trim(
                    $this->params()
                        ->fromQuery(
                            'TransactionType',
                            ''
                        )
                );

            $fromDate =
                trim(
                    $this->params()
                        ->fromQuery(
                            'FromDate',
                            ''
                        )
                );

            $toDate =
                trim(
                    $this->params()
                        ->fromQuery(
                            'ToDate',
                            ''
                        )
                );
        }

        /*
         * Make sure page is valid.
         */
        if ($page < 1) {
            $page = 1;
        }

        /*
         * Allow only these page sizes.
         */
        $allowedPerPage = [
            10,
            25,
            50,
            100
        ];

        if (!in_array($perPage, $allowedPerPage)) {
            $perPage = 25;
        }

        /*
         * Validate Warehouse ID.
         */
        if ($warehouseId !== '') {

            if (
                !ctype_digit($warehouseId) ||
                (int) $warehouseId <= 0
            ) {
                $error =
                    'Invalid warehouse selected.';

                $warehouseId = '';
            }
        }

        /*
         * Validate Transaction Type.
         */
        if (
            $transactionType !== '' &&
            !in_array(
                $transactionType,
                ['IN', 'OUT']
            )
        ) {
            $error =
                'Invalid transaction type.';

            $transactionType = '';
        }

        /*
         * Strict From Date validation.
         *
         * Required format:
         *
         * YYYY-MM-DD
         *
         * Examples:
         *
         * 2026-09-01 = valid
         * 2026-2-1    = invalid
         * 2026/09/01 = invalid
         * 26-09-01    = invalid
         * 2026-02-30  = invalid
         */
        $validFromDate = true;

        if ($fromDate !== '') {

            if (
                !preg_match(
                    '/^\d{4}-\d{2}-\d{2}$/',
                    $fromDate
                )
            ) {

                $validFromDate = false;

            } else {

                $date =
                    \DateTime::createFromFormat(
                        '!Y-m-d',
                        $fromDate
                    );

                $validFromDate =
                    $date !== false &&
                    $date->format('Y-m-d')
                        === $fromDate;
            }
        }

        /*
         * Strict To Date validation.
         */
        $validToDate = true;

        if ($toDate !== '') {

            if (
                !preg_match(
                    '/^\d{4}-\d{2}-\d{2}$/',
                    $toDate
                )
            ) {

                $validToDate = false;

            } else {

                $date =
                    \DateTime::createFromFormat(
                        '!Y-m-d',
                        $toDate
                    );

                $validToDate =
                    $date !== false &&
                    $date->format('Y-m-d')
                        === $toDate;
            }
        }

        /*
         * Transaction result defaults.
         */
        $transactions = [];

        $totalRecords = 0;

        $totalPages = 0;

        /*
         * Date validation.
         */
        if (!$validFromDate || !$validToDate) {

            $error =
                'Invalid date format. Please use YYYY-MM-DD.';

        /*
         * Date range validation.
         */
        } elseif (
            $fromDate !== '' &&
            $toDate !== '' &&
            $fromDate > $toDate
        ) {

            $error =
                'From Date cannot be greater than To Date.';

        } else {

            /*
             * Get total transaction count.
             */
            $totalRecords =
                $this->inventoryReportTable
                    ->getTransactionCount(
                        $warehouseId,
                        $transactionType,
                        $fromDate,
                        $toDate
                    );

            /*
             * Calculate total pages.
             */
            if ($totalRecords > 0) {

                $totalPages =
                    (int) ceil(
                        $totalRecords / $perPage
                    );

            } else {

                $totalPages = 0;
            }

            /*
             * If requested page is greater
             * than the last page, move to
             * the last available page.
             */
            if (
                $totalPages > 0 &&
                $page > $totalPages
            ) {

                $page = $totalPages;
            }

            /*
             * Fetch current page.
             */
            if ($totalRecords > 0) {

                $transactions =
                    $this->inventoryReportTable
                        ->getTransactions(
                            $warehouseId,
                            $transactionType,
                            $fromDate,
                            $toDate,
                            $page,
                            $perPage
                        );
            }
        }

        /*
         * Get active warehouses for
         * the filter dropdown.
         */
        $warehouses =
            $this->inventoryReportTable
                ->getWarehouses();

        return new ViewModel([
            'transactions'    => $transactions,
            'warehouses'      => $warehouses,

            'warehouseId'     => $warehouseId,
            'transactionType' => $transactionType,

            'fromDate'        => $fromDate,
            'toDate'          => $toDate,

            'page'            => $page,
            'perPage'         => $perPage,

            'totalRecords'     => $totalRecords,
            'totalPages'      => $totalPages,

            'error'           => $error
        ]);
    }

    /*
     * Product Movement Report
     */
    public function movementAction()
    {
        $movement =
            $this->inventoryReportTable
                ->getProductMovement();

        return new ViewModel([
            'movement' => $movement
        ]);
    }
}