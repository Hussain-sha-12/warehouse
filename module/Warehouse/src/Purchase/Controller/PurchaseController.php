<?php

namespace Warehouse\Purchase\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use Zend\Session\Container;
use Warehouse\Purchase\Model\PurchaseTable;

class PurchaseController extends AbstractActionController
{
    private $purchaseTable;

    public function __construct(PurchaseTable $purchaseTable)
    {
        $this->purchaseTable = $purchaseTable;
    }

    public function indexAction()
    {
        $purchases = $this->purchaseTable->fetchAll();

        return new ViewModel([
            'purchases' => $purchases
        ]);
    }

    public function addAction()
    {
        $request = $this->getRequest();

        $error = null;

        if ($request->isPost()) {

            $supplierId =
                (int) $request->getPost('SupplierID', 0);

            $warehouseId =
                (int) $request->getPost('WarehouseID', 0);

            $productId =
                (int) $request->getPost('ProductID', 0);

            $quantity =
                (int) $request->getPost('Quantity', 0);

            $unitCost =
                (float) $request->getPost('UnitCost', 0);

            if ($supplierId <= 0) {

                $error = 'Please select a supplier.';

            } elseif ($warehouseId <= 0) {

                $error = 'Please select a warehouse.';

            } elseif ($productId <= 0) {

                $error = 'Please select a product.';

            } elseif ($quantity <= 0) {

                $error = 'Quantity must be greater than 0.';

            } elseif ($unitCost <= 0) {

                $error = 'Unit cost must be greater than 0.';

            } else {

                $session =
                    new Container('warehouse');

                $createdBy =
                    (int) $session->userId;

                try {

                    $this->purchaseTable->createPurchase(
                        $supplierId,
                        $warehouseId,
                        $productId,
                        $quantity,
                        $unitCost,
                        $createdBy
                    );

                    return $this->redirect()
                        ->toRoute('purchase');

                } catch (\Exception $e) {

                    $error = $e->getMessage();
                }
            }
        }

        /*
         * Load dropdown data AFTER POST processing.
         * This avoids SQL Server active-resultset problems.
         */

        $suppliers =
            $this->purchaseTable->getSuppliers();

        $warehouses =
            $this->purchaseTable->getWarehouses();

        $products =
            $this->purchaseTable->getProducts();

        return new ViewModel([
            'suppliers'  => $suppliers,
            'warehouses' => $warehouses,
            'products'   => $products,
            'error'      => $error
        ]);
    }

    public function viewAction()
    {
        $purchaseOrderId =
            (int) $this->params()
                ->fromRoute('id', 0);

        if ($purchaseOrderId <= 0) {

            return $this->redirect()
                ->toRoute('purchase');
        }

        $purchase =
            $this->purchaseTable
                ->getPurchaseOrder($purchaseOrderId);

        if (!$purchase) {

            return $this->redirect()
                ->toRoute('purchase');
        }

        $items =
            $this->purchaseTable
                ->getPurchaseOrderItems(
                    $purchaseOrderId
                );

        return new ViewModel([
            'purchase' => $purchase,
            'items'    => $items
        ]);
    }

    public function editAction()
    {
        /*
         * Edit functionality can be added later.
         * For now redirect to purchase list.
         */

        return $this->redirect()
            ->toRoute('purchase');
    }

    public function deleteAction()
    {
        /*
         * Delete functionality can be added later.
         * For now redirect to purchase list.
         */

        return $this->redirect()
            ->toRoute('purchase');
    }
}