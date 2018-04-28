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

namespace com\kbcmdba\pjs2 ;

class JobKeywordMapController extends ControllerBase
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
        $sql = "DROP TABLE IF EXISTS jobKeywordMap" ;
        $this->_doDDL($sql) ;
    }
    
    public function createTable()
    {
        $sql = <<<SQL
CREATE TABLE IF NOT EXISTS jobKeywordMap
     (
       id        INT UNSIGNED NOT NULL AUTO_INCREMENT
     , jobId     INT UNSIGNED NOT NULL
     , keywordId INT UNSIGNED NOT NULL
     , sortKey   SMALLINT(3) NOT NULL DEFAULT 0
     , created   TIMESTAMP NOT NULL DEFAULT 0
     , updated   TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
                 ON UPDATE CURRENT_TIMESTAMP
     , PRIMARY KEY pk_jobKeywordMapId ( id )
     , FOREIGN KEY fk_jobIdx ( jobId )
        REFERENCES job ( id )
                ON UPDATE CASCADE
                ON DELETE CASCADE
     , FOREIGN KEY fk_keywordIdx ( keywordId )
        REFERENCES keyword ( id )
                ON UPDATE CASCADE
                ON DELETE CASCADE
     , UNIQUE KEY uk_jobKeyword ( jobId, keywordId )
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
     , jobId
     , keywordId
     , sortKey
     , created
     , updated
  FROM jobKeywordMap
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
            $jobId,
            $keywordId,
            $sortKey,
            $created,
            $updated
                                 )) {
            throw new ControllerException('Failed to bind to result: (' . $this->_dbh->error . ')') ;
        }
        if ($stmt->fetch()) {
            $model = new JobKeywordMapModel() ;
            $model->setId($id) ;
            $model->setJobId($jobId) ;
            $model->setKeywordId($keywordId) ;
            $model->setSortKey($sortKey) ;
            $model->setCreated($created) ;
            $model->setUpdated($updated) ;
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
     , jobId
     , keywordId
     , sortKey
     , created
     , updated
  FROM jobKeywordMap
 WHERE $whereClause
 ORDER
    BY sortKey
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
            $jobId,
            $keywordId,
            $sortKey,
            $created,
            $updated
                          ) ;
        $models = [] ;
        while ($stmt->fetch()) {
            $model = new JobKeywordMapModel() ;
            $model->setId($id) ;
            $model->setJobId($jobId) ;
            $model->setKeywordId($keywordId) ;
            $model->setSortKey($sortKey) ;
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
     * @param JobKeywordMapModel $model
     * @see ControllerBase::add()
     */
    public function add($model)
    {
        if ($model->validateForAdd()) {
            try {
                $query = <<<SQL
INSERT jobKeywordMap
     ( id
     , jobId
     , keywordId
     , sortKey
     , created
     , updated
     )
VALUES ( NULL, ?, ?, ?, NOW(), NOW() )
SQL;
                $jobId     = $model->getJobId() ;
                $keywordId = $model->getKeywordId() ;
                $sortKey   = $model->getSortKey() ;
                $stmt      = $this->_dbh->prepare($query) ;
                if (! $stmt) {
                    throw new ControllerException('Prepared statement failed for ' . $query) ;
                }
                if (! ($stmt->bind_param(
                    'iii',
                    $jobId,
                    $keywordId,
                    $sortKey
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
            } catch (\Exception $e) {
                throw new ControllerException($e->getMessage()) ;
            }
        } else {
            throw new ControllerException("Invalid data.") ;
        }
    }

    /**
     * @param JobKeywordMapModel $model
     * @see ControllerBase::update()
     */
    public function update($model)
    {
        if ($model->validateForUpdate()) {
            try {
                $query = <<<SQL
UPDATE jobKeywordMap
   SET jobId = ?
     , keywordId = ?
     , sortKey = ?
 WHERE id = ?
SQL;
                $id        = $model->getId() ;
                $jobId     = $model->getJobId() ;
                $keywordId = $model->getKeywordId() ;
                $sortKey   = $model->getSortKey() ;
                $stmt      = $this->_dbh->prepare($query) ;
                if (! $stmt) {
                    throw new ControllerException('Prepared statement failed for ' . $query) ;
                }
                if (! ($stmt->bind_param(
                    'iiii',
                    $jobId,
                    $keywordId,
                    $sortKey,
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
            } catch (\Exception $e) {
                throw new ControllerException($e->getMessage()) ;
            }
        } else {
            throw new ControllerException("Invalid data.") ;
        }
    }

    /**
     * @param JobKeywordMapModel $model
     * @see ControllerBase::delete()
     */
    public function delete($model)
    {
        $this->_deleteModelById("DELETE FROM jobKeywordMap WHERE id = ?", $model) ;
    }
}
