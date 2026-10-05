<?php

namespace Warehouse;

use Warehouse\Product;
use Warehouse\Category;
use Warehouse\Supplier;
use Warehouse\Purchase;
use Warehouse\PurchaseItem;
use Warehouse\Inventory;
use Warehouse\StockIn;
use Warehouse\StockOut;
use Warehouse\User;
use Warehouse\Dashboard;

return [

    /*
    |--------------------------------------------------------------------------
    | Controllers
    |--------------------------------------------------------------------------
    */

    'controllers' => [
        
        'factories' => [

            // Product
            Product\Controller\ProductController::class => function ($container) {
                $adapter = $container->get('Zend\Db\Adapter\Adapter');

                $productTable = new Product\Model\ProductTable($adapter);

                return new Product\Controller\ProductController(
                    $productTable
                );
            },

            // Category
            Category\Controller\CategoryController::class => function ($container) {
                $adapter = $container->get('Zend\Db\Adapter\Adapter');

                $categoryTable = new Category\Model\CategoryTable($adapter);

                return new Category\Controller\CategoryController(
                    $categoryTable
                );
            },

            // Supplier
            Supplier\Controller\SupplierController::class => function ($container) {
                $adapter = $container->get('Zend\Db\Adapter\Adapter');

                $supplierTable = new Supplier\Model\SupplierTable($adapter);

                return new Supplier\Controller\SupplierController(
                    $supplierTable
                );
            },

            // Purchase
            Purchase\Controller\PurchaseController::class => function ($container) {
                $adapter = $container->get('Zend\Db\Adapter\Adapter');

                $purchaseTable = new Purchase\Model\PurchaseTable($adapter);

                return new Purchase\Controller\PurchaseController(
                    $purchaseTable
                );
            },

            // Purchase Items
            PurchaseItem\Controller\PurchaseItemController::class => function ($container) {
                $adapter = $container->get('Zend\Db\Adapter\Adapter');

                $purchaseItemTable =
                    new PurchaseItem\Model\PurchaseItemTable($adapter);

                return new PurchaseItem\Controller\PurchaseItemController(
                    $purchaseItemTable
                );
            },
            Inventory\Controller\InventoryController::class => function ($container) {
    $adapter = $container->get('Zend\Db\Adapter\Adapter');
    $inventoryTable = new Inventory\Model\InventoryTable($adapter);

    return new Inventory\Controller\InventoryController($inventoryTable);
           },
           StockIn\Controller\StockInController::class => function ($container) {
    $adapter = $container->get('Zend\Db\Adapter\Adapter');
    $stockInTable = new StockIn\Model\StockInTable($adapter);

    return new StockIn\Controller\StockInController($stockInTable);
          },
          StockOut\Controller\StockOutController::class => function ($container) {
    $adapter = $container->get('Zend\Db\Adapter\Adapter');

    $stockOutTable = new StockOut\Model\StockOutTable($adapter);

    return new StockOut\Controller\StockOutController($stockOutTable);
          },
          User\Controller\UserController::class => function ($container) {

    $adapter = $container->get('Zend\Db\Adapter\Adapter');

    $userTable = new User\Model\UserTable($adapter);

    return new User\Controller\UserController($userTable);
       },
  Dashboard\Controller\DashboardController::class => function ($container) {

    $adapter = $container->get('Zend\Db\Adapter\Adapter');

    $dashboardTable =
        new Dashboard\Model\DashboardTable($adapter);

    return new Dashboard\Controller\DashboardController(
        $dashboardTable
    ); 
       },
       PurchaseStatus\Controller\PurchaseStatusController::class => function ($container) {

    $adapter =
        $container->get('Zend\Db\Adapter\Adapter');

    $purchaseStatusTable =
        new PurchaseStatus\Model\PurchaseStatusTable(
            $adapter
        );

    return new PurchaseStatus\Controller\PurchaseStatusController(
        $purchaseStatusTable
    );
        },
        Sales\Controller\SalesController::class => function ($container) {

    $adapter = $container->get(
        'Zend\Db\Adapter\Adapter'
    );

    $salesTable = new Sales\Model\SalesTable(
        $adapter
    );

    return new Sales\Controller\SalesController(
        $salesTable
    );
      },
      Sales\Controller\SalesController::class => function ($container) {
    $adapter = $container->get('Zend\Db\Adapter\Adapter');

    $salesTable =
        new Sales\Model\SalesTable($adapter);

    return new Sales\Controller\SalesController(
        $salesTable
    );
},
Customer\Controller\CustomerController::class => function ($container) {

    $adapter =
        $container->get('Zend\Db\Adapter\Adapter');

    $customerTable =
        new Customer\Model\CustomerTable($adapter);

    return new Customer\Controller\CustomerController(
        $customerTable
    );
},
Report\Controller\ReportController::class => function ($container) {

    $adapter =
        $container->get('Zend\Db\Adapter\Adapter');

    $reportTable =
        new Report\Model\ReportTable($adapter);

    return new Report\Controller\ReportController(
        $reportTable
    );
},
Warehouse\Controller\WarehouseController::class => function ($container) {

    $adapter =
        $container->get('Zend\Db\Adapter\Adapter');

    $warehouseTable =
        new Warehouse\Model\WarehouseTable($adapter);

    return new Warehouse\Controller\WarehouseController(
        $warehouseTable
    );
},

UserManagement\Controller\UserManagementController::class => function ($container) {

    $adapter =
        $container->get('Zend\Db\Adapter\Adapter');

    $userManagementTable =
        new UserManagement\Model\UserManagementTable(
            $adapter
        );

    return new UserManagement\Controller\UserManagementController(
        $userManagementTable
    );
},
InventoryReport\Controller\InventoryReportController::class => function ($container) {

    $adapter =
        $container->get('Zend\Db\Adapter\Adapter');

    $inventoryReportTable =
        new InventoryReport\Model\InventoryReportTable(
            $adapter
        );

    return new InventoryReport\Controller\InventoryReportController(
        $inventoryReportTable
    );
},
// StockAdjustment\Model\StockAdjustmentTable::class =>
//     function ($container) {
//         return new StockAdjustment\Model\StockAdjustmentTable(
//             $container->get(
//                 'Zend\Db\Adapter\Adapter'
//             )
//         );
//     },

        ReorderRequest\Controller\ReorderRequestController::class =>
            function ($container) {
                return new ReorderRequest\Controller\ReorderRequestController(
                    $container->get(
                        ReorderRequest\Model\ReorderRequestTable::class
                    )
                );
            },

        ReorderManagement\Controller\ReorderManagementController::class =>
            function ($container) {
                return new ReorderManagement\Controller\ReorderManagementController(
                    $container->get(
                        ReorderManagement\Model\ReorderManagementTable::class
                    )
                );
            },

        WarehouseInventory\Controller\WarehouseInventoryController::class =>
            function ($container) {
                return new WarehouseInventory\Controller\WarehouseInventoryController(
                    $container->get(
                        WarehouseInventory\Model\WarehouseInventoryTable::class
                    )
                );
            },

        TransferHistory\Controller\TransferHistoryController::class =>
            function ($container) {
                return new TransferHistory\Controller\TransferHistoryController(
                    $container->get(
                        TransferHistory\Model\TransferHistoryTable::class
                    )
                );
            },

StockTransfer\Controller\StockTransferController::class =>
    function ($container) {
        return new StockTransfer\Controller\StockTransferController(
            $container->get(
                StockTransfer\Model\StockTransferTable::class
            )
        );
    },
StockAdjustment\Controller\StockAdjustmentController::class =>
    function ($container) {
        return new StockAdjustment\Controller\StockAdjustmentController(
            $container->get(
                StockAdjustment\Model\StockAdjustmentTable::class
            )
        );
    },


        StockTransactionHistory\Controller\StockTransactionHistoryController::class =>
            function ($container) {
                return new StockTransactionHistory\Controller\StockTransactionHistoryController(
                    $container->get(
                        StockTransactionHistory\Model\StockTransactionHistoryTable::class
                    )
                );
            },

        ],
    ],
'service_manager' => [
    'factories' => [

        ReorderRequest\Model\ReorderRequestTable::class =>
            function ($container) {
                return new ReorderRequest\Model\ReorderRequestTable(
                    $container->get(
                        'Zend\Db\Adapter\Adapter'
                    )
                );
            },

        ReorderManagement\Model\ReorderManagementTable::class =>
            function ($container) {
                return new ReorderManagement\Model\ReorderManagementTable(
                    $container->get(
                        'Zend\Db\Adapter\Adapter'
                    )
                );
            },

        WarehouseInventory\Model\WarehouseInventoryTable::class =>
            function ($container) {
                return new WarehouseInventory\Model\WarehouseInventoryTable(
                    $container->get(
                        'Zend\Db\Adapter\Adapter'
                    )
                );
            },

        TransferHistory\Model\TransferHistoryTable::class =>
            function ($container) {
                return new TransferHistory\Model\TransferHistoryTable(
                    $container->get(
                        'Zend\Db\Adapter\Adapter'
                    )
                );
            },


        StockTransfer\Model\StockTransferTable::class =>
            function ($container) {
                return new StockTransfer\Model\StockTransferTable(
                    $container->get(
                        'Zend\Db\Adapter\Adapter'
                    )
                );
            },

        StockTransactionHistory\Model\StockTransactionHistoryTable::class =>
            function ($container) {
                return new StockTransactionHistory\Model\StockTransactionHistoryTable(
                    $container->get(
                        'Zend\Db\Adapter\Adapter'
                    )
                );
            },
        StockAdjustment\Model\StockAdjustmentTable::class =>
            function ($container) {

                return new StockAdjustment\Model\StockAdjustmentTable(
                    $container->get(
                        'Zend\Db\Adapter\Adapter'
                    )
                );
            },

    ],
],

    /*
    |--------------------------------------------------------------------------
    | Router
    |--------------------------------------------------------------------------
    */

    'router' => [
        'routes' => [

            /*
            |--------------------------------------------------------------------------
            | Product
            |--------------------------------------------------------------------------
            */

            'product' => [
                'type' => 'Literal',

                'options' => [
                    'route' => '/product',

                    'defaults' => [
                        'controller' => Product\Controller\ProductController::class,
                        'action' => 'index',
                    ],
                ],

                'may_terminate' => true,

                'child_routes' => [

                    'add' => [
                        'type' => 'Literal',

                        'options' => [
                            'route' => '/add',

                            'defaults' => [
                                'controller' => Product\Controller\ProductController::class,
                                'action' => 'add',
                            ],
                        ],
                    ],

                    'edit' => [
                        'type' => 'Segment',

                        'options' => [
                            'route' => '/edit[/:id]',

                            'defaults' => [
                                'controller' => Product\Controller\ProductController::class,
                                'action' => 'edit',
                            ],

                            'constraints' => [
                                'id' => '[0-9]+',
                            ],
                        ],
                    ],

                    'delete' => [
                        'type' => 'Segment',

                        'options' => [
                            'route' => '/delete[/:id]',

                            'defaults' => [
                                'controller' => Product\Controller\ProductController::class,
                                'action' => 'delete',
                            ],

                            'constraints' => [
                                'id' => '[0-9]+',
                            ],
                        ],
                    ],

                ],
            ],


            /*
            |--------------------------------------------------------------------------
            | Category
            |--------------------------------------------------------------------------
            */

            'category' => [
                'type' => 'Literal',

                'options' => [
                    'route' => '/category',

                    'defaults' => [
                        'controller' => Category\Controller\CategoryController::class,
                        'action' => 'index',
                    ],
                ],

                'may_terminate' => true,

                'child_routes' => [

                    'add' => [
                        'type' => 'Literal',

                        'options' => [
                            'route' => '/add',

                            'defaults' => [
                                'controller' => Category\Controller\CategoryController::class,
                                'action' => 'add',
                            ],
                        ],
                    ],

                    'edit' => [
                        'type' => 'Segment',

                        'options' => [
                            'route' => '/edit[/:id]',

                            'defaults' => [
                                'controller' => Category\Controller\CategoryController::class,
                                'action' => 'edit',
                            ],

                            'constraints' => [
                                'id' => '[0-9]+',
                            ],
                        ],
                    ],

                    'delete' => [
                        'type' => 'Segment',

                        'options' => [
                            'route' => '/delete[/:id]',

                            'defaults' => [
                                'controller' => Category\Controller\CategoryController::class,
                                'action' => 'delete',
                            ],

                            'constraints' => [
                                'id' => '[0-9]+',
                            ],
                        ],
                    ],

                ],
            ],


            /*
            |--------------------------------------------------------------------------
            | Supplier
            |--------------------------------------------------------------------------
            */

            'supplier' => [
                'type' => 'Literal',

                'options' => [
                    'route' => '/supplier',

                    'defaults' => [
                        'controller' => Supplier\Controller\SupplierController::class,
                        'action' => 'index',
                    ],
                ],

                'may_terminate' => true,

                'child_routes' => [

                    'add' => [
                        'type' => 'Literal',

                        'options' => [
                            'route' => '/add',

                        'defaults' => [
                            'controller' => Supplier\Controller\SupplierController::class,
                            'action' => 'create',
                            ],
                        ],
                    ],

                    'edit' => [
                        'type' => 'Segment',

                        'options' => [
                            'route' => '/edit[/:id]',

                            'defaults' => [
                                'controller' => Supplier\Controller\SupplierController::class,
                                'action' => 'edit',
                            ],

                            'constraints' => [
                                'id' => '[0-9]+',
                            ],
                        ],
                    ],

                    'delete' => [
                        'type' => 'Segment',

                        'options' => [
                            'route' => '/delete[/:id]',

                            'defaults' => [
                                'controller' => Supplier\Controller\SupplierController::class,
                                'action' => 'delete',
                            ],

                            'constraints' => [
                                'id' => '[0-9]+',
                            ],
                        ],
                    ],

                ],
            ],


            /*
            |--------------------------------------------------------------------------
            | Purchase Orders
            |--------------------------------------------------------------------------
            */

            'purchase' => [
    'type' => 'Literal',

    'options' => [
        'route' => '/purchase',

        'defaults' => [
            'controller' => Purchase\Controller\PurchaseController::class,
            'action' => 'index',
        ],
    ],

    'may_terminate' => true,

    'child_routes' => [

        'view' => [
            'type' => 'Segment',

            'options' => [
                'route' => '/view[/:id]',

                'defaults' => [
                    'controller' => Purchase\Controller\PurchaseController::class,
                    'action' => 'view',
                ],

                'constraints' => [
                    'id' => '[0-9]+',
                ],
            ],
        ],

        'add' => [
            'type' => 'Literal',

            'options' => [
                'route' => '/add',

                'defaults' => [
                    'controller' => Purchase\Controller\PurchaseController::class,
                    'action' => 'add',
                ],
            ],
        ],

        'edit' => [
            'type' => 'Segment',

            'options' => [
                'route' => '/edit[/:id]',

                'defaults' => [
                    'controller' => Purchase\Controller\PurchaseController::class,
                    'action' => 'edit',
                ],

                'constraints' => [
                    'id' => '[0-9]+',
                ],
            ],
        ],

        'delete' => [
            'type' => 'Segment',

            'options' => [
                'route' => '/delete[/:id]',

                'defaults' => [
                    'controller' => Purchase\Controller\PurchaseController::class,
                    'action' => 'delete',
                ],

                'constraints' => [
                    'id' => '[0-9]+',
                ],
            ],
        ],

    ],
],


            /*
            |--------------------------------------------------------------------------
            | Purchase Order Items
            |--------------------------------------------------------------------------
            */

            'purchase-item' => [
                'type' => 'Literal',

                'options' => [
                    'route' => '/purchase-item',

                    'defaults' => [
                        'controller' => PurchaseItem\Controller\PurchaseItemController::class,
                        'action' => 'index',
                    ],
                ],

                'may_terminate' => true,

                'child_routes' => [

                    'add' => [
                        'type' => 'Literal',

                        'options' => [
                            'route' => '/add',

                            'defaults' => [
                                'controller' => PurchaseItem\Controller\PurchaseItemController::class,
                                'action' => 'add',
                            ],
                        ],
                    ],

                    'edit' => [
                        'type' => 'Segment',

                        'options' => [
                            'route' => '/edit[/:id]',

                            'defaults' => [
                                'controller' => PurchaseItem\Controller\PurchaseItemController::class,
                                'action' => 'edit',
                            ],

                            'constraints' => [
                                'id' => '[0-9]+',
                            ],
                        ],
                    ],

                    'delete' => [
                        'type' => 'Segment',

                        'options' => [
                            'route' => '/delete[/:id]',

                            'defaults' => [
                                'controller' => PurchaseItem\Controller\PurchaseItemController::class,
                                'action' => 'delete',
                            ],

                            'constraints' => [
                                'id' => '[0-9]+',
                            ],
                        ],
                    ],

                ],
            ],
            'inventory' => [
                'type' => 'Literal',
                'options' => [
                    'route' => '/inventory',
                    'defaults' => [
                        'controller' => Inventory\Controller\InventoryController::class,
                        'action' => 'index',
                    ],
                ],
                'may_terminate' => true,

                'child_routes' => [

                    'add' => [
                        'type' => 'Literal',
                        'options' => [
                            'route' => '/add',
                            'defaults' => [
                                'controller' => Inventory\Controller\InventoryController::class,
                                'action' => 'add',
                            ],
                        ],
                    ],

                    'edit' => [
                        'type' => 'Segment',
                        'options' => [
                            'route' => '/edit[/:id]',
                            'defaults' => [
                                'controller' => Inventory\Controller\InventoryController::class,
                                'action' => 'edit',
                            ],
                            'constraints' => [
                                'id' => '[0-9]+',
                            ],
                        ],
                    ],

                    'delete' => [
                        'type' => 'Segment',
                        'options' => [
                            'route' => '/delete[/:id]',
                            'defaults' => [
                                'controller' => Inventory\Controller\InventoryController::class,
                                'action' => 'delete',
                            ],
                            'constraints' => [
                                'id' => '[0-9]+',
                            ],
                        ],
                    ],
                ],
            ],
            'stock-in' => [
                'type' => 'Literal',
                'options' => [
                    'route' => '/stock-in',
                    'defaults' => [
                        'controller' => StockIn\Controller\StockInController::class,
                        'action' => 'index',
                    ],
                ],
                'may_terminate' => true,

                'child_routes' => [

                    'items' => [
                        'type' => 'Segment',
                        'options' => [
                            'route' => '/items[/:id]',
                            'defaults' => [
                                'controller' => StockIn\Controller\StockInController::class,
                                'action' => 'items',
                            ],
                            'constraints' => [
                                'id' => '[0-9]+',
                            ],
                        ],
                    ],

                    'receive' => [
                        'type' => 'Segment',
                        'options' => [
                            'route' => '/receive[/:id]',
                            'defaults' => [
                                'controller' => StockIn\Controller\StockInController::class,
                                'action' => 'receive',
                            ],
                            'constraints' => [
                                'id' => '[0-9]+',
                            ],
                        ],
                    ],

                ],
            ],
            'stock-out' => [
                'type' => 'Literal',

                'options' => [
                    'route' => '/stock-out',

                    'defaults' => [
                        'controller' => StockOut\Controller\StockOutController::class,
                        'action' => 'index',
                    ],
                ],

                'may_terminate' => true,

                'child_routes' => [

                    'remove' => [
                        'type' => 'Segment',

                        'options' => [
                            'route' => '/remove[/:id]',

                            'defaults' => [
                                'controller' => StockOut\Controller\StockOutController::class,
                                'action' => 'remove',
                            ],

                            'constraints' => [
                                'id' => '[0-9]+',
                            ],
                        ],
                    ],

                ],
            ],
            'login' => [
                'type' => 'Literal',

                'options' => [
                    'route' => '/login',

                    'defaults' => [
                        'controller' => User\Controller\UserController::class,
                        'action' => 'login',
                    ],
                ],

                'may_terminate' => true,
            ],

            'logout' => [
                'type' => 'Literal',

                'options' => [
                    'route' => '/logout',

                    'defaults' => [
                        'controller' => User\Controller\UserController::class,
                        'action' => 'logout',
                    ],
                ],

                'may_terminate' => true,
            ],

            'dashboard' => [
                'type' => 'Literal',

                'options' => [
                    'route' => '/dashboard',

                    'defaults' => [
                        'controller' => Dashboard\Controller\DashboardController::class,
                        'action' => 'index',
                    ],
                ],

                'may_terminate' => true,
            ],

'purchase-status' => [
    'type' => 'Literal',

    'options' => [
        'route' => '/purchase-status',

        'defaults' => [
            'controller' =>
                PurchaseStatus\Controller\PurchaseStatusController::class,

            'action' => 'index',
        ],
    ],

    'may_terminate' => true,

    'child_routes' => [

        'update' => [
            'type' => 'Segment',

            'options' => [
                'route' => '/update[/:id]',

                'defaults' => [
                    'controller' =>
                        PurchaseStatus\Controller\PurchaseStatusController::class,

                    'action' => 'update',
                ],

                'constraints' => [
                    'id' => '[0-9]+',
                ],
            ],
        ],

    ],
],
                'sales-dispatch' => ['type' => 'Segment','options' => ['route' => '/sales/dispatch[/:id]','defaults' => ['controller' => Sales\Controller\SalesController::class,'action' => 'dispatch'],'constraints' => ['id' => '[0-9]+',],],],'sales-delivered' => ['type' => 'Segment','options' => ['route' => '/sales/delivered[/:id]','defaults' => ['controller' => Sales\Controller\SalesController::class,'action' => 'delivered'],'constraints' => ['id' => '[0-9]+',],],],'sales' => [
                    'type' => 'Literal',

                    'options' => [
                        'route' => '/sales',

                        'defaults' => [
                            'controller' => Sales\Controller\SalesController::class,
                            'action' => 'index',
                        ],
                    ],

                    'may_terminate' => true,

                    'child_routes' => [

                        'create' => [
                            'type' => 'Literal',

                            'options' => [
                                'route' => '/create',

                                'defaults' => [
                                    'controller' => Sales\Controller\SalesController::class,
                                    'action' => 'create',
                                ],
                            ],
                        ],
                    ],
                ],
                     'sales-dispatch' => ['type' => 'Segment','options' => ['route' => '/sales/dispatch[/:id]','defaults' => ['controller' => Sales\Controller\SalesController::class,'action' => 'dispatch'],'constraints' => ['id' => '[0-9]+',],],],'sales-delivered' => ['type' => 'Segment','options' => ['route' => '/sales/delivered[/:id]','defaults' => ['controller' => Sales\Controller\SalesController::class,'action' => 'delivered'],'constraints' => ['id' => '[0-9]+',],],],'sales' => [
    'type' => 'Literal',

    'options' => [
        'route' => '/sales',

        'defaults' => [
            'controller' => Sales\Controller\SalesController::class,
            'action' => 'index',
        ],
    ],

    'may_terminate' => true,

    'child_routes' => [

        'create' => [
            'type' => 'Literal',

            'options' => [
                'route' => '/create',

                'defaults' => [
                    'controller' => Sales\Controller\SalesController::class,
                    'action' => 'create',
                ],
            ],
        ],
    ],
],
'sales-view' => [
    'type' => 'Segment',
    'options' => [
        'route' => '/sales/view[/:id]',
        'defaults' => [
            'controller' => Sales\Controller\SalesController::class,
            'action' => 'view',
        ],
        'constraints' => [
            'id' => '[0-9]+',
        ],
    ],
],
'customers' => [
    'type' => 'Literal',
    'options' => [
        'route' => '/customers',
        'defaults' => [
            'controller' => Customer\Controller\CustomerController::class,
            'action' => 'index',
        ],
    ],
],

'customer-create' => [
    'type' => 'Literal',
    'options' => [
        'route' => '/customers/create',
        'defaults' => [
            'controller' => Customer\Controller\CustomerController::class,
            'action' => 'create',
        ],
    ],
],

'customer-edit' => [
    'type' => 'Segment',
    'options' => [
        'route' => '/customers/edit[/:id]',
        'defaults' => [
            'controller' => Customer\Controller\CustomerController::class,
            'action' => 'edit',
        ],
        'constraints' => [
            'id' => '[0-9]+',
        ],
    ],
],

'customer-delete' => [
    'type' => 'Segment',
    'options' => [
        'route' => '/customers/delete[/:id]',
        'defaults' => [
            'controller' => Customer\Controller\CustomerController::class,
            'action' => 'delete',
        ],
        'constraints' => [
            'id' => '[0-9]+',
        ],
    ],
],
'reports' => [
    'type' => 'Literal',
    'options' => [
        'route' => '/reports',
        'defaults' => [
            'controller' => Report\Controller\ReportController::class,
            'action' => 'index',
        ],
    ],
],

'report-transactions' => [
    'type' => 'Literal',
    'options' => [
        'route' => '/reports/transactions',
        'defaults' => [
            'controller' => Report\Controller\ReportController::class,
            'action' => 'transactions',
        ],
    ],
],

'report-sales' => [
    'type' => 'Literal',
    'options' => [
        'route' => '/reports/sales',
        'defaults' => [
            'controller' => Report\Controller\ReportController::class,
            'action' => 'sales',
        ],
    ],
],

'report-purchases' => [
    'type' => 'Literal',
    'options' => [
        'route' => '/reports/purchases',
        'defaults' => [
            'controller' => Report\Controller\ReportController::class,
            'action' => 'purchases',
        ],
    ],
],
'report-stock' => [
    'type' => 'Literal',
    'options' => [
        'route' => '/reports/stock',
        'defaults' => [
            'controller' => Report\Controller\ReportController::class,
            'action' => 'stock',
        ],
    ],
],
'warehouses' => [
    'type' => 'Literal',

    'options' => [
        'route' => '/warehouses',

        'defaults' => [
            'controller' =>
                Warehouse\Controller\WarehouseController::class,

            'action' => 'index',
        ],
    ],
],

'warehouse-create' => [
    'type' => 'Literal',

    'options' => [
        'route' => '/warehouses/create',

        'defaults' => [
            'controller' =>
                Warehouse\Controller\WarehouseController::class,

            'action' => 'create',
        ],
    ],
],

'warehouse-edit' => [
    'type' => 'Segment',

    'options' => [
        'route' => '/warehouses/edit[/:id]',

        'defaults' => [
            'controller' =>
                Warehouse\Controller\WarehouseController::class,

            'action' => 'edit',
        ],

        'constraints' => [
            'id' => '[0-9]+',
        ],
    ],
],

'warehouse-delete' => [
    'type' => 'Segment',

    'options' => [
        'route' => '/warehouses/delete[/:id]',

        'defaults' => [
            'controller' =>
                Warehouse\Controller\WarehouseController::class,

            'action' => 'delete',
        ],

        'constraints' => [
            'id' => '[0-9]+',
        ],
    ],
],

'user-management' => [
    'type' => 'Literal',

    'options' => [
        'route' => '/user-management',

        'defaults' => [
            'controller' =>
                UserManagement\Controller\UserManagementController::class,

            'action' => 'index',
        ],
    ],
],

'user-management-create' => [
    'type' => 'Literal',

    'options' => [
        'route' => '/user-management/create',

        'defaults' => [
            'controller' =>
                UserManagement\Controller\UserManagementController::class,

            'action' => 'create',
        ],
    ],
],

'user-management-edit' => [
    'type' => 'Segment',

    'options' => [
        'route' => '/user-management/edit[/:id]',

        'defaults' => [
            'controller' =>
                UserManagement\Controller\UserManagementController::class,

            'action' => 'edit',
        ],

        'constraints' => [
            'id' => '[0-9]+',
        ],
    ],
],

'user-management-password' => [
    'type' => 'Segment',

    'options' => [
        'route' => '/user-management/password[/:id]',

        'defaults' => [
            'controller' =>
                UserManagement\Controller\UserManagementController::class,

            'action' => 'password',
        ],

        'constraints' => [
            'id' => '[0-9]+',
        ],
    ],
],

'user-management-delete' => [
    'type' => 'Segment',

    'options' => [
        'route' => '/user-management/delete[/:id]',

        'defaults' => [
            'controller' =>
                UserManagement\Controller\UserManagementController::class,

            'action' => 'delete',
        ],

        'constraints' => [
            'id' => '[0-9]+',
        ],
    ],
],

'inventory-report' => [
    'type' => 'Literal',

    'options' => [
        'route' => '/inventory-report',

        'defaults' => [
            'controller' =>
                InventoryReport\Controller\InventoryReportController::class,

            'action' => 'index',
        ],
    ],

    'may_terminate' => true,
],

'inventory-report-transactions' => [
    'type' => 'Literal',

    'options' => [
        'route' => '/inventory-report/transactions',

        'defaults' => [
            'controller' =>
                InventoryReport\Controller\InventoryReportController::class,

            'action' => 'transactions',
        ],
    ],

    'may_terminate' => true,
],

'inventory-report-movement' => [
    'type' => 'Literal',

    'options' => [
        'route' => '/inventory-report/movement',

        'defaults' => [
            'controller' =>
                InventoryReport\Controller\InventoryReportController::class,

            'action' => 'movement',
        ],
    ],

    'may_terminate' => true,
],

        'stock-transaction-history' => [
            'type' => 'Literal',

            'options' => [
                'route' => '/stock-transaction-history',

                'defaults' => [
                    'controller' =>
                        StockTransactionHistory\Controller\StockTransactionHistoryController::class,

                    'action' => 'index',
                ],
            ],

            'may_terminate' => true,
        ],

        'reorder-request' => [
        'type' => 'Literal',

        'options' => [
            'route' => '/reorder-request',

            'defaults' => [
                'controller' =>
                    ReorderRequest\Controller\ReorderRequestController::class,

                'action' => 'index',
            ],
        ],

        'may_terminate' => true,

        'child_routes' => [
            'create' => [
                'type' => 'Literal',

                'options' => [
                    'route' => '/create',

                    'defaults' => [
                        'controller' =>
                            ReorderRequest\Controller\ReorderRequestController::class,

                        'action' => 'create',
                    ],
                ],
            ],

            'status' => [
                'type' => 'Literal',

                'options' => [
                    'route' => '/status',

                    'defaults' => [
                        'controller' =>
                            ReorderRequest\Controller\ReorderRequestController::class,

                        'action' => 'status',
                    ],
                ],
            ],
        ],
    ],
    'reorder-management' => [
            'type' => 'Literal',

            'options' => [
                'route' => '/reorder-management',

                'defaults' => [
                    'controller' =>
                        ReorderManagement\Controller\ReorderManagementController::class,

                    'action' => 'index',
                ],
            ],

            'may_terminate' => true,
        ],

        'warehouse-inventory' => [
            'type' => 'Literal',

            'options' => [
                'route' => '/warehouse-inventory',

                'defaults' => [
                    'controller' =>
                        WarehouseInventory\Controller\WarehouseInventoryController::class,

                    'action' => 'index',
                ],
            ],

            'may_terminate' => true,
        ],

        'transfer-history' => [
            'type' => 'Literal',

            'options' => [
                'route' => '/transfer-history',

                'defaults' => [
                    'controller' =>
                        TransferHistory\Controller\TransferHistoryController::class,

                    'action' => 'index',
                ],
            ],

            'may_terminate' => true,
        ],

        'stock-transfer' => [
            'type' => 'Literal',

            'options' => [
                'route' => '/stock-transfer',

                'defaults' => [
                    'controller' =>
                        StockTransfer\Controller\StockTransferController::class,

                    'action' => 'index',
                ],
            ],

            'may_terminate' => true,

            'child_routes' => [
                'transfer' => [
                    'type' => 'Literal',

                    'options' => [
                        'route' => '/transfer',

                        'defaults' => [
                            'controller' =>
                                StockTransfer\Controller\StockTransferController::class,

                            'action' => 'transfer',
                        ],
                    ],
                ],
            ],
        ],

'stock-adjustment' => [
    'type' => 'Literal',
    'options' => [
        'route' => '/stock-adjustment',
        'defaults' => [
            'controller' =>
                StockAdjustment\Controller\StockAdjustmentController::class,
            'action' => 'index',
        ],
    ],
    'may_terminate' => true,

    'child_routes' => [
        'adjust' => [
            'type' => 'Literal',
            'options' => [
                'route' => '/adjust',
                'defaults' => [
                    'controller' =>
                        StockAdjustment\Controller\StockAdjustmentController::class,
                    'action' => 'adjust',
                ],
            ],
        ],
    ],
],



        ],
    ],


    /*
    |--------------------------------------------------------------------------
    | Views
    |--------------------------------------------------------------------------
    */

    'view_manager' => [

        'template_path_stack' => [
            __DIR__ . '/../view',
        ],

    ],

];









