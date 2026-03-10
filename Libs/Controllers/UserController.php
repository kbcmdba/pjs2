<?php

/**
 * phpjobseeker
 *
 * Copyright (C) 2009, 2015, 2017 Kevin Benton - kbenton at bentonfam dot org
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License along
 * with this program; if not, write to the Free Software Foundation, Inc.,
 * 51 Franklin Street, Fifth Floor, Boston, MA 02110-1301 USA.
 *
 */
namespace com\kbcmdba\pjs2;

class UserController extends ControllerBase
{

    /**
     * Class constructor
     *
     * @param string $readWriteMode
     *            "read", "write", or "admin"
     * @throws ControllerException
     */
    public function __construct($readWriteMode = 'write')
    {
        parent::__construct($readWriteMode);
    }

    public function dropTable()
    {
        $sql = "DROP TABLE IF EXISTS user";
        $this->_doDDL($sql);
    }

    public function createTable()
    {
        $sql = <<<SQL
CREATE TABLE IF NOT EXISTS user
     (
       id          INT UNSIGNED NOT NULL AUTO_INCREMENT
     , username    VARCHAR(255) NOT NULL
     , password    VARCHAR(255) NOT NULL
     , role        ENUM('admin', 'user', 'viewer') NOT NULL DEFAULT 'viewer'
     , created     TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
     , updated     TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
                   ON UPDATE CURRENT_TIMESTAMP
     , PRIMARY KEY pk_userId ( id )
     , UNIQUE KEY uk_username ( username )
     )
SQL;
        $this->_doDDL($sql);
    }

    /**
     * Pre-load the admin user from config.xml credentials.
     */
    public function preLoadData()
    {
        $config = new Config();
        $model = new UserModel();
        $model->setUserName($config->getUserId());
        $model->setPassword(password_hash($config->getUserPassword(), PASSWORD_DEFAULT));
        $model->setRole('admin');
        $this->add($model);
    }

    /**
     *
     * @param integer $id
     */
    public function get($id)
    {
        $sql = <<<SQL
SELECT id
     , username
     , password
     , role
     , created
     , updated
  FROM user
 WHERE id = ?
SQL;
        $stmt = $this->_dbh->prepare($sql);
        if ((! $stmt) || (! $stmt->bind_param('i', $id))) {
            throw new ControllerException('Failed to prepare SELECT statement. (' . $this->_dbh->error . ')');
        }
        if (! $stmt->execute()) {
            throw new ControllerException('Failed to execute SELECT statement. (' . $this->_dbh->error . ')');
        }
        $newId = $userName = $password = $role = $created = $updated = null;
        if (! $stmt->bind_result($newId, $userName, $password, $role, $created, $updated)) {
            throw new ControllerException('Failed to bind to result: (' . $this->_dbh->error . ')');
        }
        $model = null;
        if ($stmt->fetch()) {
            $model = new UserModel();
            $model->setId($newId);
            $model->setUserName($userName);
            $model->setPassword($password);
            $model->setRole($role);
            $model->setCreated($created);
            $model->setUpdated($updated);
        }
        return ($model);
    }

    /**
     * Get a user by username.
     *
     * @param string $username
     * @return UserModel|null
     * @throws ControllerException
     */
    public function getByUsername($username)
    {
        $sql = <<<SQL
SELECT id
     , username
     , password
     , role
     , created
     , updated
  FROM user
 WHERE username = ?
SQL;
        $stmt = $this->_dbh->prepare($sql);
        if ((! $stmt) || (! $stmt->bind_param('s', $username))) {
            throw new ControllerException('Failed to prepare SELECT statement. (' . $this->_dbh->error . ')');
        }
        if (! $stmt->execute()) {
            throw new ControllerException('Failed to execute SELECT statement. (' . $this->_dbh->error . ')');
        }
        $id = $userName = $password = $role = $created = $updated = null;
        if (! $stmt->bind_result($id, $userName, $password, $role, $created, $updated)) {
            throw new ControllerException('Failed to bind to result: (' . $this->_dbh->error . ')');
        }
        $model = null;
        if ($stmt->fetch()) {
            $model = new UserModel();
            $model->setId($id);
            $model->setUserName($userName);
            $model->setPassword($password);
            $model->setRole($role);
            $model->setCreated($created);
            $model->setUpdated($updated);
        }
        return ($model);
    }

    public function getSome()
    {
        $sql = <<<SQL
SELECT id
     , username
     , password
     , role
     , created
     , updated
  FROM user
 ORDER
    BY username
SQL;
        $stmt = $this->_dbh->prepare($sql);
        if (! $stmt) {
            throw new ControllerException('Failed to prepare SELECT statement. (' . $this->_dbh->error . ') from this SQL: ' . $sql);
        }
        if (! $stmt->execute()) {
            throw new ControllerException('Failed to execute SELECT statement. (' . $this->_dbh->error . ')');
        }
        $id = $userName = $password = $role = $created = $updated = null;
        $stmt->bind_result($id, $userName, $password, $role, $created, $updated);
        $models = [];
        while ($stmt->fetch()) {
            $model = new UserModel();
            $model->setId($id);
            $model->setUserName($userName);
            $model->setPassword($password);
            $model->setRole($role);
            $model->setCreated($created);
            $model->setUpdated($updated);
            $models[] = $model;
        }
        return ($models);
    }

    public function getAll()
    {
        return $this->getSome();
    }

    /**
     *
     * @param UserModel $model
     */
    public function add($model)
    {
        if ($model->validateForAdd()) {
            try {
                $query = <<<SQL
INSERT user
     ( id
     , username
     , password
     , role
     , created
     , updated
     )
VALUES ( NULL, ?, ?, ?, NOW(), NOW() )
SQL;
                $userName = $model->getUserName();
                $password = $model->getPassword();
                $role = $model->getRole();
                $stmt = $this->_dbh->prepare($query);
                if (! $stmt) {
                    throw new ControllerException('Prepared statement failed for ' . $query);
                }
                if (! ($stmt->bind_param('sss', $userName, $password, $role))) {
                    throw new ControllerException('Binding parameters for prepared statement failed.');
                }
                if (! $stmt->execute()) {
                    throw new ControllerException('Failed to execute INSERT statement. (' . $this->_dbh->error . ')');
                }
                $newId = $stmt->insert_id;
                /**
                 *
                 * @SuppressWarnings checkAliases
                 */
                if (! $stmt->close()) {
                    throw new ControllerException('Something broke while trying to close the prepared statement.');
                }
                return $newId;
            } catch (\Exception $e) {
                throw new ControllerException($e->getMessage());
            }
        } else {
            throw new ControllerException("Invalid data.");
        }
    }

    /**
     *
     * @param UserModel $model
     */
    public function update($model)
    {
        if ($model->validateForUpdate()) {
            try {
                $password = $model->getPassword();
                if (! Tools::isNullOrEmptyString($password)) {
                    $query = <<<SQL
UPDATE user
   SET username = ?
     , password = ?
     , role = ?
 WHERE id = ?
SQL;
                    $id = $model->getId();
                    $userName = $model->getUserName();
                    $role = $model->getRole();
                    $stmt = $this->_dbh->prepare($query);
                    if (! $stmt) {
                        throw new ControllerException('Prepared statement failed for ' . $query);
                    }
                    if (! ($stmt->bind_param('sssi', $userName, $password, $role, $id))) {
                        throw new ControllerException('Binding parameters for prepared statement failed.');
                    }
                } else {
                    $query = <<<SQL
UPDATE user
   SET username = ?
     , role = ?
 WHERE id = ?
SQL;
                    $id = $model->getId();
                    $userName = $model->getUserName();
                    $role = $model->getRole();
                    $stmt = $this->_dbh->prepare($query);
                    if (! $stmt) {
                        throw new ControllerException('Prepared statement failed for ' . $query);
                    }
                    if (! ($stmt->bind_param('ssi', $userName, $role, $id))) {
                        throw new ControllerException('Binding parameters for prepared statement failed.');
                    }
                }
                if (! $stmt->execute()) {
                    throw new ControllerException('Failed to execute UPDATE statement. (' . $this->_dbh->error . ')');
                }
                /**
                 *
                 * @SuppressWarnings checkAliases
                 */
                if (! $stmt->close()) {
                    throw new ControllerException('Something broke while trying to close the prepared statement.');
                }
                return $id;
            } catch (\Exception $e) {
                throw new ControllerException($e->getMessage());
            }
        } else {
            throw new ControllerException("Invalid data.");
        }
    }

    /**
     *
     * @param UserModel $model
     */
    public function delete($model)
    {
        $this->_deleteModelById("DELETE FROM user WHERE id = ?", $model);
    }
}
