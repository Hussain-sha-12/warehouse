<?php

namespace Warehouse\UserManagement\Model;

use Zend\Db\Adapter\Adapter;

class UserManagementTable
{
    private $adapter;

    public function __construct(Adapter $adapter)
    {
        $this->adapter = $adapter;
    }

    public function fetchAll()
    {
        $sql = "
            SELECT
                u.UserID,
                u.Username,
                u.RoleID,
                CASE
                    WHEN u.RoleID = 1 THEN 'Admin'
                    WHEN u.RoleID = 2 THEN 'Manager'
                    WHEN u.RoleID = 3 THEN 'Employee'
                    ELSE 'Unknown'
                END AS RoleName,
                u.IsActive,
                u.CreatedDate
            FROM Users u
            ORDER BY u.UserID DESC
        ";

        return $this->adapter->query(
            $sql,
            Adapter::QUERY_MODE_EXECUTE
        );
    }

    public function getUser($id)
    {
        $sql = "
            SELECT
                UserID,
                Username,
                RoleID,
                IsActive,
                CreatedDate
            FROM Users
            WHERE UserID = ?
        ";

        $statement = $this->adapter->query(
            $sql,
            Adapter::QUERY_MODE_PREPARE
        );

        return $statement->execute([$id])->current();
    }

    public function usernameExists($username, $excludeId = 0)
    {
        $sql = "
            SELECT COUNT(*) AS Total
            FROM Users
            WHERE Username = ?
              AND UserID <> ?
        ";

        $statement = $this->adapter->query(
            $sql,
            Adapter::QUERY_MODE_PREPARE
        );

        $result = $statement->execute([
            $username,
            $excludeId
        ])->current();

        return (int) $result['Total'] > 0;
    }

    public function saveUser(
        $username,
        $password,
        $roleId
    ) {
        $passwordHash = password_hash(
            $password,
            PASSWORD_DEFAULT
        );

        $sql = "
            INSERT INTO Users
            (
                Username,
                PasswordHash,
                RoleID,
                IsActive,
                CreatedDate
            )
            VALUES
            (
                ?,
                ?,
                ?,
                1,
                GETDATE()
            )
        ";

        $statement = $this->adapter->query(
            $sql,
            Adapter::QUERY_MODE_PREPARE
        );

        return $statement->execute([
            $username,
            $passwordHash,
            $roleId
        ]);
    }

    public function updateUser(
        $id,
        $username,
        $roleId,
        $isActive
    ) {
        $sql = "
            UPDATE Users
            SET
                Username = ?,
                RoleID = ?,
                IsActive = ?
            WHERE UserID = ?
        ";

        $statement = $this->adapter->query(
            $sql,
            Adapter::QUERY_MODE_PREPARE
        );

        return $statement->execute([
            $username,
            $roleId,
            $isActive,
            $id
        ]);
    }

    public function updatePassword(
        $id,
        $password
    ) {
        $passwordHash = password_hash(
            $password,
            PASSWORD_DEFAULT
        );

        $sql = "
            UPDATE Users
            SET PasswordHash = ?
            WHERE UserID = ?
        ";

        $statement = $this->adapter->query(
            $sql,
            Adapter::QUERY_MODE_PREPARE
        );

        return $statement->execute([
            $passwordHash,
            $id
        ]);
    }

    public function deleteUser($id)
    {
        $sql = "
            UPDATE Users
            SET IsActive = 0
            WHERE UserID = ?
        ";

        $statement = $this->adapter->query(
            $sql,
            Adapter::QUERY_MODE_PREPARE
        );

        return $statement->execute([$id]);
    }
}