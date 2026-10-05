<?php

namespace Warehouse\PurchaseStatus\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use Warehouse\PurchaseStatus\Model\PurchaseStatusTable;

class PurchaseStatusController extends AbstractActionController
{
    private $purchaseStatusTable;

    public function __construct(
        PurchaseStatusTable $purchaseStatusTable
    ) {
        $this->purchaseStatusTable =
            $purchaseStatusTable;
    }

    public function indexAction()
    {
        $purchaseOrders =
            $this->purchaseStatusTable
                ->getPurchaseOrders();

        return new ViewModel([
            'purchaseOrders' => $purchaseOrders
        ]);
    }

    public function updateAction()
    {
        $purchaseOrderId =
            (int) $this->params()
                ->fromRoute('id', 0);

        if ($purchaseOrderId <= 0) {

            return $this->redirect()
                ->toRoute('purchase-status');
        }

        $this->purchaseStatusTable
            ->updatePurchaseOrderStatus(
                $purchaseOrderId
            );

        return $this->redirect()
            ->toRoute('purchase-status');
    }
}