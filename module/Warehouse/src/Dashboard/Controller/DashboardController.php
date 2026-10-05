<?php

namespace Warehouse\Dashboard\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use Zend\Session\Container;
use Warehouse\Dashboard\Model\DashboardTable;

class DashboardController extends AbstractActionController
{
    private $dashboardTable;

    public function __construct(DashboardTable $dashboardTable)
    {
        $this->dashboardTable = $dashboardTable;
    }

    public function indexAction()
    {
        $session = new Container('warehouse');

        $data = $this->dashboardTable->getDashboardData();

        return new ViewModel([
            'username' => $session->username,
            'roleId'   => $session->roleId,
            'data'     => $data
        ]);
    }
}