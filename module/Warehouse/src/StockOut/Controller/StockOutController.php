<?php

namespace Warehouse\StockOut\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use Zend\Session\Container;
use Warehouse\StockOut\Model\StockOutTable;

class StockOutController extends AbstractActionController
{
    private $stockOutTable;

    public function __construct(StockOutTable $stockOutTable)
    {
        $this->stockOutTable = $stockOutTable;
    }

    public function indexAction()
    {
        $products =
            $this->stockOutTable->getProducts();

        return new ViewModel([
            'products' => $products
        ]);
    }

    public function removeAction()
    {
        $productId =
            (int) $this->params()->fromRoute('id', 0);

        if ($productId <= 0) {
            return $this->redirect()->toRoute('stock-out');
        }

        $product =
            $this->stockOutTable
                ->getProduct($productId);

        $inventory =
            $this->stockOutTable
                ->getInventoryByProduct($productId);

        if (!$product) {
            return $this->redirect()->toRoute('stock-out');
        }

        $availableQuantity =
            $inventory
                ? (int) $inventory['Quantity']
                : 0;

        $error = null;

        $request = $this->getRequest();

        if ($request->isPost()) {

            $quantity =
                (int) $request->getPost(
                    'Quantity',
                    0
                );

            if ($quantity <= 0) {

                $error =
                    'Quantity must be greater than 0.';

            } elseif ($quantity > $availableQuantity) {

                $error =
                    'Quantity cannot exceed available stock.';

            } else {

                $session =
                    new Container('warehouse');

                $createdBy =
                    $session->userId;

                /*
                 * Current warehouse.
                 *
                 * Your Purchase Orders currently
                 * use WarehouseID = 1.
                 */

                $warehouseId = 1;

                try {

                    $this->stockOutTable->removeStock(
                        $productId,
                        $warehouseId,
                        $quantity,
                        $createdBy
                    );

                    return $this->redirect()
                        ->toRoute('stock-out');

                } catch (\Exception $e) {

                    $error =
                        'Unable to remove stock. Please try again.';
                }
            }
        }

        return new ViewModel([
            'product'          => $product,
            'inventory'        => $inventory,
            'availableQuantity' => $availableQuantity,
            'error'            => $error
        ]);
    }
}