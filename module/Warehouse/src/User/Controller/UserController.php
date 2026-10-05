<?php

namespace Warehouse\User\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use Zend\Session\Container;
use Warehouse\User\Model\UserTable;

class UserController extends AbstractActionController
{
    private $userTable;

    public function __construct(UserTable $userTable)
    {
        $this->userTable = $userTable;
    }

    public function loginAction()
    {
        $request = $this->getRequest();
        $error = null;

        if ($request->isPost()) {

            $username = trim(
                $request->getPost('Username', '')
            );

            $password = trim(
                $request->getPost('Password', '')
            );

            $user =
                $this->userTable
                    ->getUserByUsername($username);

            if ($user) {

                $storedPassword =
                    $user['PasswordHash'];

                /*
                 * New users use password_hash().
                 */
                $validPassword =
                    password_verify(
                        $password,
                        $storedPassword
                    );

                /*
                 * Temporary compatibility:
                 * existing admin may still have TEMP_PASSWORD.
                 */
                if (
                    !$validPassword &&
                    $password === $storedPassword
                ) {
                    $validPassword = true;
                }

                if ($validPassword) {

                    $session =
                        new Container('warehouse');

                    $session->userId =
                        $user['UserID'];

                    $session->username =
                        $user['Username'];

                    $session->roleId =
                        $user['RoleID'];

                    $session->loggedIn = true;

                    return $this->redirect()
                        ->toRoute('dashboard');
                }
            }

            $error =
                'Invalid username or password.';
        }

        return new ViewModel([
            'error' => $error
        ]);
    }

    public function logoutAction()
    {
        $session =
            new Container('warehouse');

        $session->getManager()->destroy();

        return $this->redirect()
            ->toRoute('login');
    }
}