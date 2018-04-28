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

class ContactController extends ControllerBase
{

    /**
     * Class constructor
     *
     * @param string $readWriteMode "read", "write", or "admin"
     * @throws ControllerException
     */
    public function __construct($readWriteMode = 'write')
    {
        parent::__construct($readWriteMode) ;
    }

    /**
     * @see ControllerBase::dropTable()
     */
    public function dropTable()
    {
        $sql = "DROP TABLE IF EXISTS contact" ;
        $this->_doDDL($sql) ;
    }

    /**
     * @see ControllerBase::createTable()
     */
    public function createTable()
    {
        $sql = <<<SQL
CREATE TABLE contact
     (
       id                    INT UNSIGNED NOT NULL AUTO_INCREMENT
     , contactCompanyId      INT UNSIGNED NULL DEFAULT NULL
     , contactName           VARCHAR(100) NOT NULL DEFAULT ''
     , contactEmail          VARCHAR(100) NOT NULL DEFAULT ''
     , contactPhone          VARCHAR(25) NOT NULL DEFAULT ''
     , contactAlternatePhone VARCHAR(25) NOT NULL DEFAULT ''
     , created               TIMESTAMP NOT NULL DEFAULT 0
     , updated               TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
                             ON UPDATE CURRENT_TIMESTAMP
     , PRIMARY KEY pk_contactId ( id )
     , FOREIGN KEY fk_contactCompanyId ( contactCompanyId )
        REFERENCES company ( id )
                ON DELETE CASCADE
                ON UPDATE CASCADE
     )
SQL;
        $this->_doDDL($sql) ;
    }

    public function dropTriggers()
    {
        $sql = "DROP TRIGGER IF EXISTS contactAfterDeleteTrigger" ;
        $this->_doDDL($sql) ;
        $sql = "DROP TRIGGER IF EXISTS contactAfterUpdateTrigger" ;
        $this->_doDDL($sql) ;
    }

    public function createTriggers()
    {
        $sql = <<<SQL
CREATE TRIGGER contactAfterDeleteTrigger
 AFTER DELETE
    ON contact
   FOR EACH ROW
 BEGIN
       DELETE
         FROM note
        WHERE appliesToTable = 'contact'
          AND appliesToId = OLD.id ;
   END
SQL;
        $this->_doDDL($sql) ;
        $sql = <<<SQL
CREATE TRIGGER contactAfterUpdateTrigger
 AFTER UPDATE
    ON contact
   FOR EACH ROW
 BEGIN
         IF OLD.id <> NEW.id
       THEN
            UPDATE note
               SET note.appliesToId = NEW.id
             WHERE note.appliesToId = OLD.id
               AND note.appliestoTable = 'contact'
                 ;
        END IF ;
   END
SQL;
        $this->_doDDL($sql) ;
    }

    /**
     * @param integer $id
     * @see ControllerBase::get()
     */
    public function get($id)
    {
        $sql = <<<SQL
SELECT id
     , contactCompanyId
     , contactName
     , contactEmail
     , contactPhone
     , contactAlternatePhone
     , created
     , updated
  FROM contact
 WHERE id = ?
SQL;
        $stmt = $this->_dbh->prepare($sql) ;
        if ((! $stmt) || (! $stmt->bind_param('i', $id))) {
            throw new ControllerException('Failed to prepare SELECT statement. (' . $this->_dbh->error . ')') ;
        }
        if (! $stmt->execute()) {
            throw new ControllerException('Failed to execute SELECT statement. (' . $this->_dbh->error . ')') ;
        }
        if (! $stmt->bind_result(
            $id,
            $contactCompanyId,
            $contactName,
            $contactEmail,
            $contactPhone,
            $contactAlternatePhone,
            $created,
            $updated
                                 )) {
            throw new ControllerException('Failed to bind to result: (' . $this->_dbh->error . ')') ;
        }
        if ($stmt->fetch()) {
            $model = new ContactModel() ;
            $model->setId($id) ;
            $model->setContactCompanyId($contactCompanyId) ;
            $model->setContactName($contactName) ;
            $model->setContactEmail($contactEmail) ;
            $model->setContactPhone($contactPhone) ;
            $model->setContactAlternatePhone($contactAlternatePhone) ;
            $model->setCreated($created) ;
            $model->setUpdated($updated) ;
        } else {
            $model = null ;
        }
        return($model) ;
    }

    /**
     * @see ControllerBase::getSome()
     */
    public function getSome($whereClause = '1 = 1')
    {
        $sql = <<<SQL
SELECT id
     , contactCompanyId
     , contactName
     , contactEmail
     , contactPhone
     , contactAlternatePhone
     , created
     , updated
  FROM contact
 WHERE $whereClause
 ORDER
    BY contactName
SQL;
        $stmt = $this->_dbh->prepare($sql) ;
        if (! $stmt) {
            throw new ControllerException('Failed to prepare SELECT statement. (' . $this->_dbh->error . ')') ;
        }
        if (! $stmt->execute()) {
            throw new ControllerException('Failed to execute SELECT statement. (' . $this->_dbh->error . ')') ;
        }
        $stmt->bind_result(
            $id,
            $contactCompanyId,
            $contactName,
            $contactEmail,
            $contactPhone,
            $contactAlternatePhone,
            $created,
            $updated
                          ) ;
        $models = [] ;
        while ($stmt->fetch()) {
            $model = new ContactModel() ;
            $model->setId($id) ;
            $model->setContactCompanyId($contactCompanyId) ;
            $model->setContactName($contactName) ;
            $model->setContactEmail($contactEmail) ;
            $model->setContactPhone($contactPhone) ;
            $model->setContactAlternatePhone($contactAlternatePhone) ;
            $model->setCreated($created) ;
            $model->setUpdated($updated) ;
            $models[] = $model ;
        }
        return($models) ;
    }

    public function getAll()
    {
        return $this->getSome() ;
    }

    /**
     * @param ContactModel $model
     * @see ControllerBase::add()
     */
    public function add($model)
    {
        if ($model->validateForAdd()) {
            try {
                $query = <<<SQL
INSERT contact
     ( id
     , contactCompanyId
     , contactName
     , contactEmail
     , contactPhone
     , contactAlternatePhone
     , created
     , updated
     )
VALUES ( NULL, ?, ?, ?, ?, ?, NOW(), NOW() )
SQL;
                $id                    = $model->getId() ;
                $contactCompanyId      = $model->getContactCompanyId() ;
                $contactName           = $model->getContactName() ;
                $contactEmail          = $model->getContactEmail() ;
                $contactPhone          = $model->getContactPhone() ;
                $contactAlternatePhone = $model->getContactAlternatePhone() ;
                $stmt                  = $this->_dbh->prepare($query) ;
                if (! $stmt) {
                    throw new ControllerException('Prepared statement failed for ' . $query) ;
                }
                if (! ($stmt->bind_param(
                    'issss',
                    $contactCompanyId,
                    $contactName,
                    $contactEmail,
                    $contactPhone,
                    $contactAlternatePhone
                                          ))) {
                    throw new ControllerException('Binding parameters for prepared statement failed.') ;
                }
                if (! $stmt->execute()) {
                    throw new ControllerException('Failed to execute INSERT statement. ('
                                                 . $this->_dbh->error .
                                                 ')') ;
                }
                $newId = $stmt->insert_id ;
                /**
                 * @SuppressWarnings checkAliases
                 */
                if (! $stmt->close()) {
                    throw new ControllerException('Something broke while trying to close the prepared statement.') ;
                }
                return $newId ;
            } catch (Exception $e) {
                throw new ControllerException($e->getMessage()) ;
            }
        } else {
            throw new ControllerException("Invalid data.") ;
        }
    }

    /**
     * @param ContactModel $model
     * @see ControllerBase::update()
     */
    public function update($model)
    {
        if ($model->validateForUpdate()) {
            try {
                $query = <<<SQL
UPDATE contact
   SET contactCompanyId = ?
     , contactName = ?
     , contactEmail = ?
     , contactPhone = ?
     , contactAlternatePhone = ?
 WHERE id = ?
SQL;
                $id                    = $model->getId() ;
                $contactCompanyId      = $model->getContactCompanyId() ;
                $contactName           = $model->getContactName() ;
                $contactEmail          = $model->getContactEmail() ;
                $contactPhone          = $model->getContactPhone() ;
                $contactAlternatePhone = $model->getContactAlternatePhone() ;
                $stmt                = $this->_dbh->prepare($query) ;
                if (! $stmt) {
                    throw new ControllerException('Prepared statement failed for ' . $query) ;
                }
                if (! ($stmt->bind_param(
                    'issssi',
                    $contactCompanyId,
                    $contactName,
                    $contactEmail,
                    $contactPhone,
                    $contactAlternatePhone,
                    $id
                                          ))) {
                    throw new ControllerException('Binding parameters for prepared statement failed.') ;
                }
                if (!$stmt->execute()) {
                    throw new ControllerException('Failed to execute UPDATE statement. ('
                            . $this->_dbh->error .
                            ')') ;
                }
                /**
                 * @SuppressWarnings checkAliases
                 */
                if (!$stmt->close()) {
                    throw new ControllerException('Something broke while trying to close the prepared statement.') ;
                }
                return $id ;
            } catch (Exception $e) {
                throw new ControllerException($e->getMessage()) ;
            }
        } else {
            throw new ControllerException("Invalid data.") ;
        }
    }

    /**
     * @param ContactModel $model
     * @see ControllerBase::delete()
     */
    public function delete($model)
    {
        $this->_deleteModelById("DELETE FROM contact WHERE id = ?", $model) ;
    }
}
