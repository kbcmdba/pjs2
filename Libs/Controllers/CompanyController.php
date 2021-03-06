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

class CompanyController extends ControllerBase
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
        $sql = "DROP TABLE IF EXISTS company";
        $this->_doDDL($sql);
    }

    public function createTable()
    {
        $sql = <<<SQL
CREATE TABLE IF NOT EXISTS company
     (
       id              INT UNSIGNED NOT NULL AUTO_INCREMENT
     , agencyCompanyId INT UNSIGNED NULL DEFAULT NULL
                       COMMENT 'When not null, point to agency company ID'
     , companyName     VARCHAR(100) NOT NULL DEFAULT ''
     , companyAddress1 VARCHAR(255) NOT NULL DEFAULT ''
     , companyAddress2 VARCHAR(255) NOT NULL DEFAULT ''
     , companyCity     VARCHAR(60) NOT NULL DEFAULT ''
     , companyState    CHAR(2) NOT NULL DEFAULT 'XX'
     , companyZip      VARCHAR(10) NOT NULL DEFAULT ''
     , companyPhone    VARCHAR(16) NOT NULL DEFAULT ''
     , companyUrl      VARCHAR(255) NOT NULL DEFAULT ''
     , created         TIMESTAMP NOT NULL DEFAULT 0
     , updated         TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
                       ON UPDATE CURRENT_TIMESTAMP
     , lastContacted   TIMESTAMP NULL DEFAULT NULL
     , PRIMARY KEY pk_companyId ( id )
     , FOREIGN KEY agencyCompanyIdFk( agencyCompanyId )
                       REFERENCES company( id )
                       ON UPDATE CASCADE ON DELETE SET NULL
     )
SQL;
        $this->_doDDL($sql);
    }

    public function dropTriggers()
    {
        $sqls = [
            "DROP TRIGGER IF EXISTS companyBeforeDeleteTrigger ;",
            "DROP TRIGGER IF EXISTS companyAfterDeleteTrigger ;",
            "DROP TRIGGER IF EXISTS companyBeforeUpdateTrigger ;",
            "DROP TRIGGER IF EXISTS companyAfterUpdateTrigger ;"
        ];
        foreach ($sqls as $sql) {
            $this->_doDDL($sql);
        }
    }

    public function createTriggers()
    {
        $sql = <<<SQL
CREATE TRIGGER companyBeforeDeleteTrigger
BEFORE DELETE
    ON company
   FOR EACH ROW
 BEGIN
       DELETE
         FROM note
        WHERE appliesToTable = 'company'
          AND appliesToId = OLD.id ;
   END
SQL;
        $this->_doDDL($sql);
        $sql = <<<SQL
CREATE TRIGGER companyBeforeUpdateTrigger
BEFORE UPDATE
    ON company
   FOR EACH ROW
 BEGIN
           IF OLD.id <> NEW.id
         THEN
              UPDATE note
                 SET note.appliesToId = NEW.id
               WHERE note.appliesToId = OLD.id
                 AND note.appliestoTable = 'company'
                   ;
       END IF ;
   END
SQL;
        $this->_doDDL($sql);
    }

    public function get($id)
    {
        $sql = <<<SQL
SELECT id
     , agencyCompanyId
     , companyName
     , companyAddress1
     , companyAddress2
     , companyCity
     , companyState
     , companyZip
     , companyPhone
     , companyUrl
     , created
     , updated
     , lastContacted
  FROM company
 WHERE id = ?
SQL;
        $stmt = $this->_dbh->prepare($sql);
        if ((! $stmt) || (! $stmt->bind_param('i', $id))) {
            throw new ControllerException('Failed to prepare SELECT statement. (' . $this->_dbh->error . ')');
        }
        if (! $stmt->execute()) {
            throw new ControllerException('Failed to execute SELECT statement. (' . $this->_dbh->error . ')');
        }
        if (! $stmt->bind_result($id, $agencyCompanyId, $companyName, $companyAddress1, $companyAddress2, $companyCity, $companyState, $companyZip, $companyPhone, $companyUrl, $created, $updated, $lastContacted)) {
            throw new ControllerException('Failed to bind to result: (' . $this->_dbh->error . ')');
        }
        if ($stmt->fetch()) {
            $model = new CompanyModel();
            $model->setId($id);
            $model->setAgencyCompanyId($agencyCompanyId);
            $model->setCompanyName($companyName);
            $model->setCompanyAddress1($companyAddress1);
            $model->setCompanyAddress2($companyAddress2);
            $model->setCompanyCity($companyCity);
            $model->setCompanyState($companyState);
            $model->setCompanyZip($companyZip);
            $model->setCompanyPhone($companyPhone);
            $model->setCompanyUrl($companyUrl);
            $model->setCreated($created);
            $model->setUpdated($updated);
            $model->setLastContacted($lastContacted);
        } else {
            $model = null;
        }
        return ($model);
    }

    public function getSome($whereClause = '1 = 1')
    {
        $sql = <<<SQL
SELECT id
     , agencyCompanyId
     , companyName
     , companyAddress1
     , companyAddress2
     , companyCity
     , companyState
     , companyZip
     , companyPhone
     , companyUrl
     , created
     , updated
     , lastContacted
  FROM company
 WHERE $whereClause
 ORDER
    BY companyName
SQL;
        $stmt = $this->_dbh->prepare($sql);
        if (! $stmt) {
            throw new ControllerException('Failed to prepare SELECT statement. (' . $this->_dbh->error . ')');
        }
        if (! $stmt->execute()) {
            throw new ControllerException('Failed to execute SELECT statement. (' . $this->_dbh->error . ')');
        }
        if (! $stmt->bind_result($id, $agencyCompanyId, $companyName, $companyAddress1, $companyAddress2, $companyCity, $companyState, $companyZip, $companyPhone, $companyUrl, $created, $updated, $lastContacted)) {
            throw new ControllerException('Failed to bind to result: (' . $this->_dbh->error . ')');
        }
        $models = [];
        while ($stmt->fetch()) {
            $model = new CompanyModel();
            $model->setId($id);
            $model->setAgencyCompanyId($agencyCompanyId);
            $model->setCompanyName($companyName);
            $model->setCompanyAddress1($companyAddress1);
            $model->setCompanyAddress2($companyAddress2);
            $model->setCompanyCity($companyCity);
            $model->setCompanyState($companyState);
            $model->setCompanyZip($companyZip);
            $model->setCompanyPhone($companyPhone);
            $model->setCompanyUrl($companyUrl);
            $model->setCreated($created);
            $model->setUpdated($updated);
            $model->setLastContacted($lastContacted);
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
     * @param CompanyModel $model
     * @see ControllerBase::add()
     */
    public function add($model)
    {
        if ($model->validateForAdd()) {
            try {
                $query = <<<SQL
INSERT company
     ( id
     , agencyCompanyId
     , companyName
     , companyAddress1
     , companyAddress2
     , companyCity
     , companyState
     , companyZip
     , companyPhone
     , companyUrl
     , created
     , updated
     , lastContacted
     )
VALUES ( NULL, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW(), NOW(), ? )
SQL;
                $agencyCompanyId = $model->getAgencyCompanyId();
                $companyName = $model->getCompanyName();
                $companyAddress1 = $model->getCompanyAddress1();
                $companyAddress2 = $model->getCompanyAddress2();
                $companyCity = $model->getCompanyCity();
                $companyState = $model->getCompanyState();
                $companyZip = $model->getCompanyZip();
                $companyPhone = $model->getCompanyPhone();
                $companyUrl = $model->getCompanyUrl();
                $created = $model->getCreated();
                $updated = $model->getUpdated();
                $lastContacted = $model->getLastContacted();
                $stmt = $this->_dbh->prepare($query);
                if (! $stmt) {
                    throw new ControllerException('Prepared statement failed for ' . $query);
                }
                if (! ($stmt->bind_param('isssssssss', $agencyCompanyId, $companyName, $companyAddress1, $companyAddress2, $companyCity, $companyState, $companyZip, $companyPhone, $companyUrl, $lastContacted))) {
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
     * @var CompanyModel $companyModel
     */
    public function update($companyModel)
    {
        if ($companyModel->validateForUpdate()) {
            try {
                $query = <<<SQL
UPDATE company
   SET agencyCompanyId = ?
     , companyName = ?
     , companyAddress1 = ?
     , companyAddress2 = ?
     , companyCity = ?
     , companyState = ?
     , companyZip = ?
     , companyPhone = ?
     , companyUrl = ?
     , lastContacted = ?
 WHERE id = ?
SQL;
                $id = $companyModel->getId();
                $agencyCompanyId = $companyModel->getAgencyCompanyId();
                $companyName = $companyModel->getCompanyName();
                $companyAddress1 = $companyModel->getCompanyAddress1();
                $companyAddress2 = $companyModel->getCompanyAddress2();
                $companyCity = $companyModel->getCompanyCity();
                $companyState = $companyModel->getCompanyState();
                $companyZip = $companyModel->getCompanyZip();
                $companyPhone = $companyModel->getCompanyPhone();
                $companyUrl = $companyModel->getCompanyUrl();
                $lastContacted = $companyModel->getLastContacted();
                $stmt = $this->_dbh->prepare($query);
                if (! $stmt) {
                    throw new ControllerException('Prepared statement failed for ' . $query);
                }
                if (! ($stmt->bind_param('isssssssssi', $agencyCompanyId, $companyName, $companyAddress1, $companyAddress2, $companyCity, $companyState, $companyZip, $companyPhone, $companyUrl, $lastContacted, $id))) {
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

    public function delete($companyModel)
    {
        $this->_deleteModelById("DELETE FROM company WHERE id = ?", $companyModel);
    }
}
