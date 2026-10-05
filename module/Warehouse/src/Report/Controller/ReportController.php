<?php

namespace Warehouse\Report\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use Warehouse\Report\Model\ReportTable;

class ReportController extends AbstractActionController
{
    private $reportTable;

    public function __construct(ReportTable $reportTable)
    {
        $this->reportTable = $reportTable;
    }

    public function indexAction()
{
    return new ViewModel([
        'stock' => $this->reportTable->getStockSummary()
    ]);
}

public function stockAction()
{
    return new ViewModel([
        'stock' => $this->reportTable->getStockSummary()
    ]);
}

    public function transactionsAction()
    {
        $request = $this->getRequest();

        $transactionType = '';
        $fromDate = '';
        $toDate = '';

        if ($request->isPost()) {

            $transactionType =
                trim($request->getPost(
                    'TransactionType',
                    ''
                ));

            $fromDate =
                trim($request->getPost(
                    'FromDate',
                    ''
                ));

            $toDate =
                trim($request->getPost(
                    'ToDate',
                    ''
                ));
        }

        $transactions =
            $this->reportTable->getStockTransactions(
                $transactionType,
                $fromDate,
                $toDate
            );

        return new ViewModel([
            'transactions'     => $transactions,
            'transactionType'  => $transactionType,
            'fromDate'         => $fromDate,
            'toDate'           => $toDate
        ]);
    }

    public function salesAction()
    {
        return new ViewModel([
            'sales' =>
                $this->reportTable->getSalesReport()
        ]);
    }

    public function purchasesAction()
    {
        return new ViewModel([
            'purchases' =>
                $this->reportTable->getPurchaseReport()
        ]);
    }
}