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

class SearchController extends ControllerBase
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
        $sql = "DROP TABLE IF EXISTS search" ;
        $this->_doDDL($sql) ;
    }
    
    public function createTable()
    {
        $sql = <<<SQL
CREATE TABLE IF NOT EXISTS search
     (
       id             INT UNSIGNED NOT NULL AUTO_INCREMENT
     , engineName     VARCHAR(255) NOT NULL DEFAULT ''
     , searchName     VARCHAR(255) NOT NULL DEFAULT ''
     , url            VARCHAR(4096) NOT NULL DEFAULT ''
     , rssFeedUrl     VARCHAR(4096) NOT NULL DEFAULT ''
     , rssLastChecked TIMESTAMP NULL DEFAULT NULL
     , created        TIMESTAMP NOT NULL DEFAULT 0
     , updated        TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
                      ON UPDATE CURRENT_TIMESTAMP
     , PRIMARY KEY pk_searchId ( id )
     )
SQL;
        $this->_doDDL($sql) ;
    }

    public function dropTriggers()
    {
        $sql = "DROP TRIGGER IF EXISTS searchAfterDeleteTrigger" ;
        $this->_doDDL($sql) ;
        $sql = "DROP TRIGGER IF EXISTS searchAfterUpdateTrigger" ;
        $this->_doDDL($sql) ;
    }

    public function createTriggers()
    {
        $sql = <<<SQL
CREATE TRIGGER searchAfterDeleteTrigger
 AFTER DELETE
    ON search
   FOR EACH ROW
 BEGIN
       DELETE
         FROM note
        WHERE appliesToTable = 'search'
          AND appliesToId = OLD.id ;
   END
SQL;
        $this->_doDDL($sql) ;
        $sql = <<<SQL
CREATE TRIGGER searchAfterUpdateTrigger
 AFTER UPDATE
    ON search
   FOR EACH ROW
 BEGIN
         IF OLD.id <> NEW.id
       THEN
            UPDATE note
               SET note.appliesToId = NEW.id
             WHERE note.appliesToId = OLD.id
               AND note.appliestoTable = 'search'
                 ;
        END IF ;
   END
SQL;
        $this->_doDDL($sql) ;
    }

    public function get($id)
    {
        $sql = <<<SQL
SELECT id
     , engineName
     , searchName
     , url
     , rssFeedUrl
     , rssLastChecked
     , created
     , updated
  FROM search
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
            $engineName,
            $searchName,
            $url,
            $rssFeedUrl,
            $rssLastChecked,
            $created,
            $updated
                                 )) {
            throw new ControllerException('Failed to bind to result: (' . $this->_dbh->error . ')') ;
        }
        if ($stmt->fetch()) {
            $model = new SearchModel() ;
            $model->setId($id) ;
            $model->setEngineName($engineName) ;
            $model->setSearchName($searchName) ;
            $model->setUrl($url) ;
            $model->setRssFeedUrl($rssFeedUrl) ;
            $model->setRssLastChecked($rssLastChecked) ;
            $model->setCreated($created) ;
            $model->setUpdated($updated) ;
        } else {
            $model = null ;
        }
        return($model) ;
    }

    public function getSome($whereClause = '1 = 1')
    {
        $sql = <<<SQL
SELECT id
     , engineName
     , searchName
     , url
     , rssFeedUrl
     , rssLastChecked
     , created
     , updated
  FROM search
 WHERE $whereClause
 ORDER
    BY searchName
     , engineName
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
            $engineName,
            $searchName,
            $url,
            $rssFeedUrl,
            $rssLastChecked,
            $created,
            $updated
                          ) ;
        $models = [] ;
        while ($stmt->fetch()) {
            $model = new SearchModel() ;
            $model->setId($id) ;
            $model->setEngineName($engineName) ;
            $model->setSearchName($searchName) ;
            $model->setUrl($url) ;
            $model->setRssFeedUrl($rssFeedUrl) ;
            $model->setRssLastChecked($rssLastChecked) ;
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
     * Count the number of matching searches
     *
     * @param string $whereClause
     * @throws ControllerException
     * @return int
     */
    public function countSome($whereClause = '1 = 1')
    {
        $sql = <<<SQL
SELECT COUNT( id )
  FROM search
 WHERE $whereClause
SQL;
        $stmt = $this->_dbh->prepare($sql) ;
        if (! $stmt) {
            throw new ControllerException('Failed to prepare SELECT statement. (' . $this->_dbh->error . ')') ;
        }
        if (! $stmt->execute()) {
            throw new ControllerException('Failed to execute SELECT statement. (' . $this->_dbh->error . ')') ;
        }
        $stmt->bind_result($count) ;
        $stmt->fetch() ;
        return($count) ;
    }

    /**
     * Count the number rows in the table
     *
     * @throws ControllerException
     * @return int
     */
    public function countAll()
    {
        return $this->countSome() ;
    }

    /**
     * @param SearchModel $model
     * @see ControllerBase::add()
     */
    public function add($model)
    {
        if ($model->validateForAdd()) {
            try {
                $query = <<<SQL
INSERT search
     ( id
     , engineName
     , searchName
     , url
     , rssFeedUrl
     , rssLastChecked
     , created
     , updated
     )
VALUES ( ?, ?, ?, ?, ?, ?, NOW(), NOW() )
SQL;
                $id             = $model->getId() ;
                $engineName     = $model->getEngineName() ;
                $searchName     = $model->getSearchName() ;
                $url            = $model->getUrl() ;
                $rssFeedUrl     = $model->getRssFeedUrl() ;
                $rssLastChecked = $model->getRssLastChecked() ;
                $stmt           = $this->_dbh->prepare($query) ;
                if (! $stmt) {
                    throw new ControllerException('Prepared statement failed for ' . $query) ;
                }
                if (! ($stmt->bind_param(
                    'isssss',
                    $id,
                    $engineName,
                    $searchName,
                    $url,
                    $rssFeedUrl,
                    $rssLastChecked
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
     * @param SearchModel $model
     * @see ControllerBase::update()
     */
    public function update($model)
    {
        if ($model->validateForUpdate()) {
            try {
                $query = <<<SQL
UPDATE search
   SET engineName     = ?
     , searchName     = ?
     , url            = ?
     , rssFeedUrl     = ?
     , rssLastChecked = ?
 WHERE id             = ?
SQL;
                $id             = $model->getId() ;
                $engineName     = $model->getEngineName() ;
                $searchName     = $model->getSearchName() ;
                $url            = $model->getUrl() ;
                $rssFeedUrl     = $model->getRssFeedUrl() ;
                $rssLastChecked = $model->getRssLastChecked() ;
                $stmt           = $this->_dbh->prepare($query) ;
                if (! $stmt) {
                    throw new ControllerException('Prepared statement failed for ' . $query) ;
                }
                if (! ($stmt->bind_param(
                    'sssssi',
                    $engineName,
                    $searchName,
                    $url,
                    $rssFeedUrl,
                    $rssLastChecked,
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

    public function delete($model)
    {
        $this->_deleteModelById("DELETE FROM search WHERE id = ?", $model) ;
    }
}
