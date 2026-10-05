<?php

namespace Warehouse\ReorderRequest\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use Zend\Session\Container;
use Warehouse\ReorderRequest\Model\ReorderRequestTable;

class ReorderRequestController extends AbstractActionController
{
    private $reorderRequestTable;

    public function __construct(
        ReorderRequestTable $reorderRequestTable
    ) {
        $this->reorderRequestTable = $reorderRequestTable;
    }

    public function indexAction()
    {
        $status = $this->params()->fromQuery('status', '');

        $requests = iterator_to_array(
            $this->reorderRequestTable->getRequests($status)
        );

        $products = iterator_to_array(
            $this->reorderRequestTable->getProducts()
        );

        $warehouses = iterator_to_array(
            $this->reorderRequestTable->getWarehouses()
        );

        return new ViewModel([
            'requests'   => $requests,
            'products'   => $products,
            'warehouses' => $warehouses,
            'status'     => $status
        ]);
    }

    public function createAction()
    {
        if (!$this->getRequest()->isPost()) {
            return $this->redirect()
                ->toRoute('reorder-request');
        }

        $data = $this->getRequest()
            ->getPost()
            ->toArray();

        $productId = (int) ($data['ProductID'] ?? 0);
        $warehouseId = (int) ($data['WarehouseID'] ?? 0);
        $quantity = (int) ($data['RequestedQuantity'] ?? 0);
        $notes = trim($data['Notes'] ?? '');

        if (
            $productId <= 0 ||
            $warehouseId <= 0 ||
            $quantity <= 0
        ) {
            return $this->redirect()
                ->toRoute('reorder-request');
        }

        $session = new Container('warehouse');

        $requestedBy = isset($session->userId)
            ? (int) $session->userId
            : null;

        $this->reorderRequestTable->createRequest(
            $productId,
            $warehouseId,
            $quantity,
            $requestedBy,
            $notes
        );

        return $this->redirect()
            ->toRoute('reorder-request');
    }

    public function statusAction()
    {
        if (!$this->getRequest()->isPost()) {
            return $this->redirect()
                ->toRoute('reorder-request');
        }

        $data = $this->getRequest()
            ->getPost()
            ->toArray();

        $requestId = (int) ($data['ReorderRequestID'] ?? 0);
        $newStatus = trim($data['Status'] ?? '');
        $receivedQuantity = (int) ($data['ReceivedQuantity'] ?? 0);

        if ($requestId <= 0 || $newStatus === '') {
            return $this->redirect()
                ->toRoute('reorder-request');
        }

        $session = new Container('warehouse');

        $userId = isset($session->userId)
            ? (int) $session->userId
            : null;

        $this->reorderRequestTable->updateStatus(
        $requestId,
        $newStatus,
        $userId,
        $receivedQuantity
    );

        return $this->redirect()
            ->toRoute('reorder-request');
    }
}
