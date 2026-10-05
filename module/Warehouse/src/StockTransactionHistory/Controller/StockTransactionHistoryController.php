<?php

namespace Warehouse\StockTransactionHistory\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use Warehouse\StockTransactionHistory\Model\StockTransactionHistoryTable;

class StockTransactionHistoryController
    extends AbstractActionController
{
    private $stockTransactionHistoryTable;

    public function __construct(
        StockTransactionHistoryTable $stockTransactionHistoryTable
    ) {
        $this->stockTransactionHistoryTable =
            $stockTransactionHistoryTable;
    }

    public function indexAction()
    {
        $transactions =
            iterator_to_array(
                $this->stockTransactionHistoryTable
                    ->getTransactions()
            );

        return new ViewModel([
            'transactions' => $transactions
        ]);
    }
}
