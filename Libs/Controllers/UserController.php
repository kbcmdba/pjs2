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
namespace com\kbcmdba\pjs2\Libs\Controllers;

use com\kbcmdba\pjs2\Libs\Exceptions\ControllerException;
use com\kbcmdba\pjs2\Libs\Models\UserModel;

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
     , username    TEXT NOT NULL
     , password    TEXT NOT NULL
     , psalt       TEXT NOT NULL
     , created     TIMESTAMP NOT NULL DEFAULT 0
     , updated     TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
                   ON UPDATE CURRENT_TIMESTAMP
     , PRIMARY KEY pk_userId ( id )
     , INDEX appliesTo ( appliesToTable, appliesToId, created )
     )
SQL;
        $this->_doDDL($sql);
    }

    /**
     *
     * @param integer $id
     * @see ControllerBase::get()
     */
    public function get($id)
    {
        $sql = <<<SQL
SELECT id
     , username
     , password
     , psalt
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
        $newId = $userName = $password = $pSalt = $created = $updated = null;
        if (! $stmt->bind_result($newId, $userName, $password, $pSalt, $created, $updated)) {
            throw new ControllerException('Failed to bind to result: (' . $this->_dbh->error . ')');
        }
        $model = null;
        if ($stmt->fetch()) {
            $model = new UserModel();
            $model->setId($newId);
            $model->setUserName($userName);
            $model->setPassword($password);
            $model->setPSalt($pSalt);
            $model->setCreated($created);
            $model->setUpdated($updated);
        }
        return ($model);
    }

    /**
     *
     * @param string $whereClause
     * @see ControllerBase::getSome()
     */
    public function getSome($whereClause = '1 = 1')
    {
        $sql = <<<SQL
SELECT id
     , username
     , password
     , psalt
     , created
     , updated
  FROM user
 WHERE $whereClause
 ORDER
    BY updated
SQL;
        $stmt = $this->_dbh->prepare($sql);
        if (! $stmt) {
            throw new ControllerException('Failed to prepare SELECT statement. (' . $this->_dbh->error . ') from this SQL: ' . $sql);
        }
        if (! $stmt->execute()) {
            throw new ControllerException('Failed to execute SELECT statement. (' . $this->_dbh->error . ')');
        }
        $id = $userName = $password = $psalt = $created = $updated = null;
        $stmt->bind_result($id, $userName, $password, $psalt, $created, $updated);
        $models = [];
        while ($stmt->fetch()) {
            $model = new UserModel();
            $model->setId($id);
            $model->setUserName($userName);
            $model->setPassword($password);
            $model->setPSalt($psalt);
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
     * @see ControllerBase::add()
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
     , psalt
     , created
     , updated
     )
VALUES ( NULL, ?, ?, ?, NOW(), NOW() )
SQL;
                $userName = $model->getUserName();
                $password = $model->getPassword();
                $pSalt = $model->getPSalt();
                $stmt = $this->_dbh->prepare($query);
                if (! $stmt) {
                    throw new ControllerException('Prepared statement failed for ' . $query);
                }
                if (! ($stmt->bind_param('sss', $userName, $password, $pSalt))) {
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
     * @see ControllerBase::update()
     */
    public function update($model)
    {
        if ($model->validateForUpdate()) {
            try {
                $query = <<<SQL
UPDATE user
   SET username = ?
     , password = ?
     , psalt = ?
 WHERE id = ?
SQL;
                $id = $model->getId();
                $userName = $model->getuUserName();
                $password = $model->getPassword();
                $pSalt = $model->getPSalt();
                $stmt = $this->_dbh->prepare($query);
                if (! $stmt) {
                    throw new ControllerException('Prepared statement failed for ' . $query);
                }
                if (! ($stmt->bind_param('sssi', $userName, $password, $pSalt, $id))) {
                    throw new ControllerException('Binding parameters for prepared statement failed.');
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
     * @see ControllerBase::delete()
     */
    public function delete($model)
    {
        $this->_deleteModelById("DELETE FROM user WHERE id = ?", $model);
    }
}
