<?php

namespace Warehouse\StockIn\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use Zend\Session\Container;
use Warehouse\StockIn\Model\StockInTable;

class StockInController extends AbstractActionController
{
    private $stockInTable;

    public function __construct(StockInTable $stockInTable)
    {
        $this->stockInTable = $stockInTable;
    }

    public function indexAction()
    {
        $purchaseOrders =
            $this->stockInTable->getPurchaseOrders();

        return new ViewModel([
            'purchaseOrders' => $purchaseOrders
        ]);
    }

    public function itemsAction()
    {
        $purchaseOrderId =
            (int) $this->params()->fromRoute('id', 0);

        if ($purchaseOrderId <= 0) {
            return $this->redirect()->toRoute('stock-in');
        }

        $items =
            $this->stockInTable
                ->getPurchaseOrderItems($purchaseOrderId);

        return new ViewModel([
            'purchaseOrderId' => $purchaseOrderId,
            'items'           => $items
        ]);
    }

    public function receiveAction()
    {
        $purchaseOrderItemId =
            (int) $this->params()->fromRoute('id', 0);

        if ($purchaseOrderItemId <= 0) {
            return $this->redirect()->toRoute('stock-in');
        }

        $item =
            $this->stockInTable
                ->getPurchaseOrderItem(
                    $purchaseOrderItemId
                );

        if (!$item) {
            return $this->redirect()->toRoute('stock-in');
        }

        $product =
            $this->stockInTable
                ->getProduct($item['ProductID']);

        if (!$product) {
            return $this->redirect()->toRoute('stock-in');
        }

        $received =
            $this->stockInTable
                ->getReceivedQuantity(
                    $purchaseOrderItemId
                );

        $remaining =
            (int) $item['Quantity'] - $received;

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

            } elseif ($quantity > $remaining) {

                $error =
                    'Received quantity cannot exceed the remaining quantity.';

            } else {

                $session =
                    new Container('warehouse');

                $createdBy =
                    $session->userId;

                try {

                    /*
                     * 1. Add stock to Inventory
                     * 2. Create StockTransactions IN record
                     */
                    $this->stockInTable->receiveStock(
                        $item['ProductID'],
                        $item['WarehouseID'],
                        $quantity,
                        $item['UnitCost'],
                        $purchaseOrderItemId,
                        $createdBy
                    );

                    /*
                     * 3. Update Purchase Order status
                     */
                    $this->updatePurchaseOrderStatus(
                        $item['PurchaseOrderID']
                    );

                    return $this->redirect()
                        ->toRoute('stock-in');

                } catch (\Exception $e) {

                    /*
                     * Temporarily show the real error.
                     * We can replace this with a friendly message later.
                     */
                    $error =
                        $e->getMessage();
                }
            }
        }

        return new ViewModel([
            'item'      => $item,
            'product'   => $product,
            'received'  => $received,
            'remaining' => $remaining,
            'error'     => $error
        ]);
    }

    private function updatePurchaseOrderStatus($purchaseOrderId)
    {
        $adapter =
            $this->stockInTable->getAdapter();

        $sql = "
            SELECT
                poi.PurchaseOrderItemID,
                poi.Quantity AS OrderedQuantity,

                ISNULL(
                    (
                        SELECT SUM(st.Quantity)
                        FROM StockTransactions st
                        WHERE st.TransactionType = 'IN'
                          AND st.ReferenceType = 'PurchaseOrderItem'
                          AND st.ReferenceID =
                              poi.PurchaseOrderItemID
                    ),
                    0
                ) AS ReceivedQuantity

            FROM PurchaseOrderItems poi

            WHERE poi.PurchaseOrderID = ?
        ";

        $statement = $adapter->query(
            $sql,
            \Zend\Db\Adapter\Adapter::QUERY_MODE_PREPARE
        );

        $items = $statement->execute([
            $purchaseOrderId
        ]);

        $totalItems = 0;
        $fullyReceived = 0;
        $hasReceived = false;

        foreach ($items as $item) {

            $totalItems++;

            $ordered =
                (int) $item['OrderedQuantity'];

            $received =
                (int) $item['ReceivedQuantity'];

            if ($received > 0) {
                $hasReceived = true;
            }

            if ($received >= $ordered) {
                $fullyReceived++;
            }
        }

        if ($totalItems === 0) {
            return;
        }

        /*
         * Your database allows only:
         *
         * Pending
         * Received
         * Cancelled
         *
         * Therefore we do NOT use "Partial".
         */

        if ($fullyReceived === $totalItems) {

            $status = 'Received';

        } elseif ($hasReceived) {

            /*
             * Partial receiving cannot use "Partial"
             * because of the database CHECK constraint.
             *
             * Keep it as Pending until everything is received.
             */
            $status = 'Pending';

        } else {

            $status = 'Pending';
        }

        $updateSql = "
            UPDATE PurchaseOrders
            SET Status = ?
            WHERE PurchaseOrderID = ?
              AND Status <> 'Cancelled'
        ";

        $updateStatement = $adapter->query(
            $updateSql,
            \Zend\Db\Adapter\Adapter::QUERY_MODE_PREPARE
        );

        $updateStatement->execute([
            $status,
            $purchaseOrderId
        ]);
    }
    public function getAdapter()
{
    return $this->adapter;
}
}
