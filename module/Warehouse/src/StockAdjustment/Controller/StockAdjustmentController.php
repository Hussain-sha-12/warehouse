<?php

namespace Warehouse\StockAdjustment\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use Warehouse\StockAdjustment\Model\StockAdjustmentTable;

class StockAdjustmentController
    extends AbstractActionController
{
    private $stockAdjustmentTable;

    public function __construct(
        StockAdjustmentTable $stockAdjustmentTable
    ) {
        $this->stockAdjustmentTable =
            $stockAdjustmentTable;
    }

    public function indexAction()
    {
        $products =
            $this->stockAdjustmentTable
                ->getProducts();

        $warehouses =
            $this->stockAdjustmentTable
                ->getWarehouses();

        return new ViewModel([
            'products' => $products,
            'warehouses' => $warehouses,
        ]);
    }

    public function adjustAction()
    {
        if (!$this->getRequest()->isPost()) {
            return $this->redirect()
                ->toRoute('stock-adjustment');
        }

        $data =
            $this->getRequest()
                ->getPost()
                ->toArray();

        $productId =
            (int) ($data['ProductID'] ?? 0);

        $warehouseId =
            (int) ($data['WarehouseID'] ?? 0);

        $quantity =
            (int) ($data['Quantity'] ?? 0);

        $adjustmentType =
            $data['AdjustmentType'] ?? '';

        $reason =
            trim($data['Reason'] ?? '');

        /*
         * Reason is stored as part of the
         * adjustment reference information.
         */
        if ($reason === '') {
            $reason = 'Manual Adjustment';
        }

        $session =
            new \Zend\Session\Container('warehouse');

        $createdBy =
            isset($session->userId)
                ? (int) $session->userId
                : null;

        $success =
            $this->stockAdjustmentTable
                ->adjustStock(
                    $productId,
                    $warehouseId,
                    $quantity,
                    $adjustmentType,
                    $reason,
                    $createdBy
                );

        if ($success) {
            return $this->redirect()
                ->toRoute('stock-adjustment');
        }

        return $this->redirect()
            ->toRoute('stock-adjustment');
    }
}