<?php

namespace Warehouse\ReorderManagement\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use Warehouse\ReorderManagement\Model\ReorderManagementTable;

class ReorderManagementController
    extends AbstractActionController
{
    private $reorderManagementTable;

    public function __construct(
        ReorderManagementTable $reorderManagementTable
    ) {
        $this->reorderManagementTable =
            $reorderManagementTable;
    }

    public function indexAction()
    {
        $status =
            trim(
                $this->params()
                    ->fromQuery('status', '')
            );

        $allowedStatuses = [
            '',
            'Out of Stock',
            'Low Stock',
            'Available'
        ];

        if (!in_array($status, $allowedStatuses, true)) {
            $status = '';
        }

        $items =
            iterator_to_array(
                $this->reorderManagementTable
                    ->getReorderItems($status)
            );

        $counts =
            $this->reorderManagementTable
                ->getStatusCounts();

        return new ViewModel([
            'items' => $items,
            'selectedStatus' => $status,
            'counts' => $counts
        ]);
    }
}
