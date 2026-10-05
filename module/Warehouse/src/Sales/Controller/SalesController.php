<?php

namespace Warehouse\Sales\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use Zend\Session\Container;
use Warehouse\Sales\Model\SalesTable;

class SalesController extends AbstractActionController
{
    private $salesTable;

    public function __construct(SalesTable $salesTable)
    {
        $this->salesTable = $salesTable;
    }


    /*
     * Sales Order List
     */
    public function indexAction()
    {
        $salesOrders =
            $this->salesTable
                ->getSalesOrders();

        return new ViewModel([
            'salesOrders' => $salesOrders
        ]);
    }


    /*
     * Create Sales Order
     */
    public function createAction()
    {
        $request = $this->getRequest();

        $error = null;


        if ($request->isPost()) {

            $customerId =
                (int) $request->getPost(
                    'CustomerID',
                    0
                );

            $productId =
                (int) $request->getPost(
                    'ProductID',
                    0
                );

            $quantity =
                (int) $request->getPost(
                    'Quantity',
                    0
                );


            /*
             * Get Product
             */
            $product =
                $this->salesTable
                    ->getProduct($productId);


            /*
             * Validate Customer
             */
            if ($customerId <= 0) {

                $error =
                    'Please select a customer.';

            }

            /*
             * Validate Product
             */
            elseif ($productId <= 0) {

                $error =
                    'Please select a product.';

            }

            /*
             * Product Not Found
             */
            elseif (!$product) {

                $error =
                    'Selected product was not found.';

            }

            /*
             * Validate Quantity
             */
            elseif ($quantity <= 0) {

                $error =
                    'Quantity must be greater than 0.';

            }

            else {

                $unitPrice =
                    (float) $product['UnitPrice'];


                if ($unitPrice <= 0) {

                    $error =
                        'Selected product does not have a valid unit price.';

                }

                else {

                    $session =
                        new Container('warehouse');

                    $createdBy =
                        (int) $session->userId;


                    /*
                     * Currently using Warehouse ID 1
                     */
                    $warehouseId = 1;


                    try {

                        $this->salesTable
                            ->createSale(
                                $customerId,
                                $warehouseId,
                                $productId,
                                $quantity,
                                $unitPrice,
                                $createdBy
                            );


                        return $this->redirect()
                            ->toRoute('sales');

                    }

                    catch (\Exception $e) {

                        $error =
                            $e->getMessage();
                    }
                }
            }
        }


        /*
         * Load dropdown data
         * AFTER POST processing.
         */
        $products =
            $this->salesTable
                ->getProducts();

        $customers =
            $this->salesTable
                ->getCustomers();


        return new ViewModel([
            'products'  => $products,
            'customers' => $customers,
            'error'     => $error
        ]);
    }


    /*
     * View Sales Order Details
     */
    public function viewAction()
    {
        $salesOrderId =
            (int) $this->params()
                ->fromRoute('id', 0);


        if ($salesOrderId <= 0) {

            return $this->redirect()
                ->toRoute('sales');
        }


        /*
         * Get Sales Order
         */
        $sale =
            $this->salesTable
                ->getSalesOrder(
                    $salesOrderId
                );


        if (!$sale) {

            return $this->redirect()
                ->toRoute('sales');
        }


        /*
         * Get Sales Items
         */
        $items =
            $this->salesTable
                ->getSalesOrderItems(
                    $salesOrderId
                );


        return new ViewModel([
            'sale'  => $sale,
            'items' => $items
        ]);
    }
    /*
     * Dispatch Sales Order
     */
    public function dispatchAction()
    {
        $salesOrderId =
            (int) $this->params()
                ->fromRoute('id', 0);

        if ($salesOrderId <= 0) {
            return $this->redirect()
                ->toRoute('sales');
        }

        $this->salesTable
            ->dispatchSalesOrder($salesOrderId);

        return $this->redirect()
            ->toRoute('sales');
    }

    public function deliveredAction()
    {
        $salesOrderId = (int) $this->params()->fromRoute('id', 0);

        if ($salesOrderId <= 0) {
            return $this->redirect()->toRoute('sales');
        }

        $this->salesTable->deliverSalesOrder($salesOrderId);

        return $this->redirect()->toRoute('sales');
    }
}
