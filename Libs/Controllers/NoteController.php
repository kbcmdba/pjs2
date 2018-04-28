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

class NoteController extends ControllerBase
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
    
    public function dropTable()
    {
        $sql = "DROP TABLE IF EXISTS note" ;
        $this->_doDDL($sql) ;
    }
    
    public function createTable()
    {
        $sql = <<<SQL
CREATE TABLE IF NOT EXISTS note
     (
       id                INT UNSIGNED NOT NULL AUTO_INCREMENT
     , appliesToTable        ENUM( 'job', 'company', 'contact', 'keyword', 'search' ) NOT NULL
     , appliesToId           INT UNSIGNED NOT NULL
     , created               TIMESTAMP NOT NULL DEFAULT 0
     , updated               TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
                             ON UPDATE CURRENT_TIMESTAMP
     , noteText              TEXT NOT NULL
     , PRIMARY KEY pk_noteId ( id )
     , INDEX appliesTo ( appliesToTable, appliesToId, created )
     )
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
     , appliesToTable
     , appliesToId
     , created
     , updated
     , noteText
  FROM note
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
            $appliesToTable,
            $appliesToId,
            $created,
            $updated,
            $noteText
                                 )) {
            throw new ControllerException('Failed to bind to result: (' . $this->_dbh->error . ')') ;
        }
        if ($stmt->fetch()) {
            $model = new NoteModel() ;
            $model->setId($id) ;
            $model->setAppliesToTable($appliesToTable) ;
            $model->setAppliesToId($appliesToId) ;
            $model->setCreated($created) ;
            $model->setUpdated($updated) ;
            $model->setNoteText($noteText) ;
        } else {
            $model = null ;
        }
        return($model) ;
    }

    /**
     * @param string $whereClause
     * @see ControllerBase::getSome()
     */
    public function getSome($whereClause = '1 = 1')
    {
        $sql = <<<SQL
SELECT id
     , appliesToTable
     , appliesToId
     , created
     , updated
     , noteText
  FROM note
 WHERE $whereClause
 ORDER
    BY updated
SQL;
        $stmt = $this->_dbh->prepare($sql) ;
        if (! $stmt) {
            throw new ControllerException('Failed to prepare SELECT statement. (' . $this->_dbh->error . ') from this SQL: ' . $sql) ;
        }
        if (! $stmt->execute()) {
            throw new ControllerException('Failed to execute SELECT statement. (' . $this->_dbh->error . ')') ;
        }
        $stmt->bind_result(
            $id,
            $appliesToTable,
            $appliesToId,
            $created,
            $updated,
            $noteText
                          ) ;
        $models = [] ;
        while ($stmt->fetch()) {
            $model = new NoteModel() ;
            $model->setId($id) ;
            $model->setAppliesToTable($appliesToTable) ;
            $model->setAppliesToId($appliesToId) ;
            $model->setCreated($created) ;
            $model->setUpdated($updated) ;
            $model->setNoteText($noteText) ;
            $models[] = $model ;
        }
        return($models) ;
    }

    public function getAll()
    {
        return $this->getSome() ;
    }

    /**
     * @param NoteModel $model
     * @see ControllerBase::add()
     */
    public function add($model)
    {
        if ($model->validateForAdd()) {
            try {
                $query = <<<SQL
INSERT note
     ( id
     , appliesToTable
     , appliesToId
     , created
     , updated
     , noteText
     )
VALUES ( NULL, ?, ?, NOW(), NOW(), ? )
SQL;
                $id             = $model->getId() ;
                $appliesToTable = $model->getAppliesToTable() ;
                $appliesToId    = $model->getAppliesToId() ;
                $noteText       = $model->getNoteText() ;
                $stmt           = $this->_dbh->prepare($query) ;
                if (! $stmt) {
                    throw new ControllerException('Prepared statement failed for ' . $query) ;
                }
                if (! ($stmt->bind_param(
                    'sis',
                    $appliesToTable,
                    $appliesToId,
                    $noteText
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
     * @param NoteModel $model
     * @see ControllerBase::update()
     */
    public function update($model)
    {
        if ($model->validateForUpdate()) {
            try {
                $query = <<<SQL
UPDATE note
   SET appliesToTable = ?
     , appliesToId = ?
     , noteText = ?
 WHERE id = ?
SQL;
                $id             = $model->getId() ;
                $appliesToTable = $model->getAppliesToTable() ;
                $appliesToId    = $model->getAppliesToId() ;
                $noteText       = htmlspecialchars($model->getNoteText()) ;
                $stmt           = $this->_dbh->prepare($query) ;
                if (! $stmt) {
                    throw new ControllerException('Prepared statement failed for ' . $query) ;
                }
                if (! ($stmt->bind_param(
                    'sisi',
                    $appliesToTable,
                    $appliesToId,
                    $noteText,
                    $id
                                          ))) {
                    throw new ControllerException('Binding parameters for prepared statement failed.') ;
                }
                if (! $stmt->execute()) {
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
     * @param NoteModel $model
     * @see ControllerBase::delete()
     */
    public function delete($model)
    {
        $this->_deleteModelById("DELETE FROM note WHERE id = ?", $model) ;
    }
}
