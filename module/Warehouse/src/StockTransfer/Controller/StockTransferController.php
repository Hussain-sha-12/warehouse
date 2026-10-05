<?php

namespace Warehouse\StockTransfer\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use Warehouse\StockTransfer\Model\StockTransferTable;

class StockTransferController extends AbstractActionController
{
    private $stockTransferTable;

    public function __construct(
        StockTransferTable $stockTransferTable
    ) {
        $this->stockTransferTable =
            $stockTransferTable;
    }

    public function indexAction()
    {
        $products =
            iterator_to_array(
                $this->stockTransferTable
                    ->getProducts()
            );

        $warehouses =
            iterator_to_array(
                $this->stockTransferTable
                    ->getWarehouses()
            );

        return new ViewModel([
            'products' => $products,
            'warehouses' => $warehouses,
        ]);
    }

    public function transferAction()
    {
        if (!$this->getRequest()->isPost()) {
            return $this->redirect()
                ->toRoute('stock-transfer');
        }

        $data =
            $this->getRequest()
                ->getPost()
                ->toArray();

        $productId =
            (int) ($data['ProductID'] ?? 0);

        $fromWarehouseId =
            (int) ($data['FromWarehouseID'] ?? 0);

        $toWarehouseId =
            (int) ($data['ToWarehouseID'] ?? 0);

        $quantity =
            (int) ($data['Quantity'] ?? 0);

        $session =
            new \Zend\Session\Container('warehouse');

        $createdBy =
            isset($session->userId)
                ? (int) $session->userId
                : null;

        $this->stockTransferTable
            ->transferStock(
                $productId,
                $fromWarehouseId,
                $toWarehouseId,
                $quantity,
                $createdBy
            );

        return $this->redirect()
            ->toRoute('stock-transfer');
    }
}
