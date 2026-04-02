<?php

/**
 * phpjobseeker
 *
 * Copyright (C) 2009, 2015, 2017, 2026 Kevin Benton - kbenton at bentonfam dot org
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

class SearchStatusController extends ControllerBase
{

    public function __construct($readWriteMode = 'write')
    {
        parent::__construct($readWriteMode);
    }

    public function dropTable()
    {
        $sql = "DROP TABLE IF EXISTS searchStatus";
        $this->_doDDL($sql);
    }

    public function createTable()
    {
        $sql = <<<SQL
CREATE TABLE IF NOT EXISTS searchStatus
     (
       id          INT UNSIGNED NOT NULL AUTO_INCREMENT
     , statusValue VARCHAR(50) NOT NULL
     , isActive    TINYINT(1) NOT NULL DEFAULT 1
     , sortKey     SMALLINT UNSIGNED NOT NULL DEFAULT 100
     , style       VARCHAR(4096) NOT NULL DEFAULT ''
     , created     TIMESTAMP NOT NULL DEFAULT 0
     , updated     TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
                   ON UPDATE CURRENT_TIMESTAMP
     , PRIMARY KEY pk_searchStatusId ( id )
     )
SQL;
        $this->_doDDL($sql);
    }

    public function dropTriggers()
    {}

    public function createTriggers()
    {}

    public function get($id)
    {
        $sql = <<<SQL
SELECT id
     , statusValue
     , isActive
     , sortKey
     , style
     , created
     , updated
  FROM searchStatus
 WHERE id = ?
SQL;
        $stmt = $this->_dbh->prepare($sql);
        if ((! $stmt) || (! $stmt->bind_param('i', $id))) {
            throw new ControllerException('Failed to prepare SELECT statement. (' . $this->_dbh->error . ')');
        }
        if (! $stmt->execute()) {
            throw new ControllerException('Failed to execute SELECT statement. (' . $this->_dbh->error . ')');
        }
        if (! $stmt->bind_result($id, $statusValue, $isActive, $sortKey, $style, $created, $updated)) {
            throw new ControllerException('Failed to bind to result: (' . $this->_dbh->error . ')');
        }
        if ($stmt->fetch()) {
            $model = new SearchStatusModel();
            $model->setId($id);
            $model->setStatusValue($statusValue);
            $model->setIsActive($isActive);
            $model->setSortKey($sortKey);
            $model->setStyle($style);
            $model->setCreated($created);
            $model->setUpdated($updated);
        } else {
            $model = null;
        }
        return ($model);
    }

    public function getSome()
    {
        $sql = <<<SQL
SELECT id
     , statusValue
     , isActive
     , sortKey
     , style
     , created
     , updated
  FROM searchStatus
 ORDER
    BY sortKey
SQL;
        $stmt = $this->_dbh->prepare($sql);
        if (! $stmt) {
            throw new ControllerException('Failed to prepare SELECT statement. (' . $this->_dbh->error . ')');
        }
        if (! $stmt->execute()) {
            throw new ControllerException('Failed to execute SELECT statement. (' . $this->_dbh->error . ')');
        }
        $stmt->bind_result($id, $statusValue, $isActive, $sortKey, $style, $created, $updated);
        $models = [];
        while ($stmt->fetch()) {
            $model = new SearchStatusModel();
            $model->setId($id);
            $model->setStatusValue($statusValue);
            $model->setIsActive($isActive);
            $model->setSortKey($sortKey);
            $model->setStyle($style);
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

    public function add($model)
    {
        if ($model->validateForAdd()) {
            try {
                $query = <<<SQL
INSERT searchStatus
     ( id, statusValue, isActive, sortKey, style, created, updated )
VALUES ( NULL, ?, ?, ?, ?, NOW(), NOW() )
SQL;
                $statusValue = $model->getStatusValue();
                $isActive = $model->getIsActive();
                $sortKey = $model->getSortKey();
                $style = $model->getStyle();
                $stmt = $this->_dbh->prepare($query);
                if (! $stmt) {
                    throw new ControllerException('Prepared statement failed for ' . $query);
                }
                if (! ($stmt->bind_param('siis', $statusValue, $isActive, $sortKey, $style))) {
                    throw new ControllerException('Binding parameters for prepared statement failed.');
                }
                if (! $stmt->execute()) {
                    throw new ControllerException('Failed to execute INSERT statement. (' . $this->_dbh->error . ')');
                }
                $newId = $stmt->insert_id;
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

    public function update($model)
    {
        if ($model->validateForUpdate()) {
            try {
                $query = <<<SQL
UPDATE searchStatus
   SET statusValue = ?
     , isActive    = ?
     , sortKey     = ?
     , style       = ?
 WHERE id          = ?
SQL;
                $id = $model->getId();
                $statusValue = $model->getStatusValue();
                $isActive = $model->getIsActive();
                $sortKey = $model->getSortKey();
                $style = $model->getStyle();
                $stmt = $this->_dbh->prepare($query);
                if (! $stmt) {
                    throw new ControllerException('Prepared statement failed for ' . $query);
                }
                if (! ($stmt->bind_param('siisi', $statusValue, $isActive, $sortKey, $style, $id))) {
                    throw new ControllerException('Binding parameters for prepared statement failed.');
                }
                if (! $stmt->execute()) {
                    throw new ControllerException('Failed to execute UPDATE statement. (' . $this->_dbh->error . ')');
                }
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

    public function delete($model)
    {
        $this->_deleteModelById("DELETE FROM searchStatus WHERE id = ?", $model);
    }
}
