<?php

namespace Warehouse\TransferHistory\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use Warehouse\TransferHistory\Model\TransferHistoryTable;

class TransferHistoryController
    extends AbstractActionController
{
    private $transferHistoryTable;

    public function __construct(
        TransferHistoryTable $transferHistoryTable
    ) {
        $this->transferHistoryTable =
            $transferHistoryTable;
    }

    public function indexAction()
    {
        $transfers =
            iterator_to_array(
                $this->transferHistoryTable
                    ->getTransfers()
            );

        return new ViewModel([
            'transfers' => $transfers
        ]);
    }
}
