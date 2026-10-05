<?php

namespace Warehouse\UserManagement\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use Warehouse\UserManagement\Model\UserManagementTable;

class UserManagementController extends AbstractActionController
{
    private $userManagementTable;

    public function __construct(
        UserManagementTable $userManagementTable
    ) {
        $this->userManagementTable =
            $userManagementTable;
    }

    public function indexAction()
    {
        return new ViewModel([
            'users' =>
                $this->userManagementTable->fetchAll()
        ]);
    }

    public function createAction()
    {
        $request = $this->getRequest();
        $error = null;

        if ($request->isPost()) {

            $username = trim(
                $request->getPost(
                    'Username',
                    ''
                )
            );

            $password = trim(
                $request->getPost(
                    'Password',
                    ''
                )
            );

            $roleId = (int) $request->getPost(
                'RoleID',
                0
            );

            if ($username === '') {

                $error =
                    'Username is required.';

            } elseif ($password === '') {

                $error =
                    'Password is required.';

            } elseif (strlen($password) < 6) {

                $error =
                    'Password must be at least 6 characters.';

            } elseif (!in_array($roleId, [1, 2, 3])) {

                $error =
                    'Please select a valid role.';

            } elseif (
                $this->userManagementTable
                    ->usernameExists($username)
            ) {

                $error =
                    'Username already exists.';

            } else {

                try {

                    $this->userManagementTable
                        ->saveUser(
                            $username,
                            $password,
                            $roleId
                        );

                    return $this->redirect()
                        ->toRoute('user-management');

                } catch (\Exception $e) {

                    $error = $e->getMessage();
                }
            }
        }

        return new ViewModel([
            'error' => $error
        ]);
    }

    public function editAction()
    {
        $id = (int) $this->params()
            ->fromRoute('id', 0);

        $user =
            $this->userManagementTable
                ->getUser($id);

        if (!$user) {

            return $this->redirect()
                ->toRoute('user-management');
        }

        $request = $this->getRequest();
        $error = null;

        if ($request->isPost()) {

            $username = trim(
                $request->getPost(
                    'Username',
                    ''
                )
            );

            $roleId = (int) $request->getPost(
                'RoleID',
                0
            );

            $isActive = (int) $request->getPost(
                'IsActive',
                1
            );

            if ($username === '') {

                $error =
                    'Username is required.';

            } elseif (!in_array($roleId, [1, 2, 3])) {

                $error =
                    'Please select a valid role.';

            } elseif (
                $this->userManagementTable
                    ->usernameExists(
                        $username,
                        $id
                    )
            ) {

                $error =
                    'Username already exists.';

            } else {

                try {

                    $this->userManagementTable
                        ->updateUser(
                            $id,
                            $username,
                            $roleId,
                            $isActive
                        );

                    return $this->redirect()
                        ->toRoute('user-management');

                } catch (\Exception $e) {

                    $error = $e->getMessage();
                }
            }

            $user['Username'] = $username;
            $user['RoleID'] = $roleId;
            $user['IsActive'] = $isActive;
        }

        return new ViewModel([
            'user'  => $user,
            'error' => $error
        ]);
    }

    public function passwordAction()
    {
        $id = (int) $this->params()
            ->fromRoute('id', 0);

        $user =
            $this->userManagementTable
                ->getUser($id);

        if (!$user) {

            return $this->redirect()
                ->toRoute('user-management');
        }

        $request = $this->getRequest();
        $error = null;

        if ($request->isPost()) {

            $password = trim(
                $request->getPost(
                    'Password',
                    ''
                )
            );

            if ($password === '') {

                $error =
                    'Password is required.';

            } elseif (strlen($password) < 6) {

                $error =
                    'Password must be at least 6 characters.';

            } else {

                try {

                    $this->userManagementTable
                        ->updatePassword(
                            $id,
                            $password
                        );

                    return $this->redirect()
                        ->toRoute('user-management');

                } catch (\Exception $e) {

                    $error = $e->getMessage();
                }
            }
        }

        return new ViewModel([
            'user'  => $user,
            'error' => $error
        ]);
    }

    public function deleteAction()
    {
        $id = (int) $this->params()
            ->fromRoute('id', 0);

        if ($id > 0) {

            $this->userManagementTable
                ->deleteUser($id);
        }

        return $this->redirect()
            ->toRoute('user-management');
    }
}