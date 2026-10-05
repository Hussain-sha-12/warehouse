<?php

namespace Warehouse\PurchaseItem\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use Warehouse\PurchaseItem\Model\PurchaseItem;
use Warehouse\PurchaseItem\Model\PurchaseItemTable;

class PurchaseItemController extends AbstractActionController
{
    private $purchaseItemTable;

    public function __construct(PurchaseItemTable $purchaseItemTable)
    {
        $this->purchaseItemTable = $purchaseItemTable;
    }

    /**
     * Purchase Item List
     */
    public function indexAction()
    {
        $items = $this->purchaseItemTable->fetchAll();

        return new ViewModel([
            'items' => $items
        ]);
    }

    /**
     * Add Purchase Item
     */
    public function addAction()
    {
        $request = $this->getRequest();

        // Load dropdown data
        $purchaseOrders = $this->purchaseItemTable->getPurchaseOrders();
        $products = $this->purchaseItemTable->getProducts();

        if ($request->isPost()) {

            $data = $request->getPost()->toArray();

            // Create PurchaseItem object
            $item = new PurchaseItem();

            $item->exchangeArray([
                'PurchaseOrderID' => $data['PurchaseOrderID'] ?? null,
                'ProductID'       => $data['ProductID'] ?? null,
                'Quantity'        => $data['Quantity'] ?? 0,
                'UnitCost'        => $data['UnitCost'] ?? 0
            ]);

            // Save item
            // TotalAmount is a computed column in SQL Server,
            // so we do NOT insert it manually.
            $this->purchaseItemTable->savePurchaseItem($item);

            return $this->redirect()->toRoute('purchase-item');
        }

        return new ViewModel([
            'purchaseOrders' => $purchaseOrders,
            'products'       => $products
        ]);
    }

    /**
     * Edit Purchase Item
     */
    public function editAction()
    {
        $id = (int) $this->params()->fromRoute('id', 0);

        if ($id <= 0) {
            return $this->redirect()->toRoute('purchase-item');
        }

        // Get existing purchase item
        $item = $this->purchaseItemTable->getPurchaseItem($id);

        if (!$item) {
            return $this->redirect()->toRoute('purchase-item');
        }

        // Load dropdown data
        $purchaseOrders = $this->purchaseItemTable->getPurchaseOrders();
        $products = $this->purchaseItemTable->getProducts();

        $request = $this->getRequest();

        if ($request->isPost()) {

            $data = $request->getPost()->toArray();

            // Create updated object
            $updatedItem = new PurchaseItem();

            $updatedItem->exchangeArray([
                'PurchaseOrderID' => $data['PurchaseOrderID'] ?? null,
                'ProductID'       => $data['ProductID'] ?? null,
                'Quantity'        => $data['Quantity'] ?? 0,
                'UnitCost'        => $data['UnitCost'] ?? 0
            ]);

            // Update item
            // TotalAmount is computed by SQL Server.
            $this->purchaseItemTable->updatePurchaseItem(
                $id,
                $updatedItem
            );

            return $this->redirect()->toRoute('purchase-item');
        }

        return new ViewModel([
            'item'           => $item,
            'id'             => $id,
            'purchaseOrders' => $purchaseOrders,
            'products'       => $products
        ]);
    }

    /**
     * Delete Purchase Item
     */
    public function deleteAction()
    {
        $id = (int) $this->params()->fromRoute('id', 0);

        if ($id > 0) {
            $this->purchaseItemTable->deletePurchaseItem($id);
        }

        return $this->redirect()->toRoute('purchase-item');
    }
}