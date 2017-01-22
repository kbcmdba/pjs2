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

class KeywordController extends ControllerBase {

    /**
     * Class constructor
     *
     * @param string $readWriteMode "read", "write", or "admin"
     * @throws ControllerException
     */
    public function __construct( $readWriteMode = 'write' ) {
        parent::__construct( $readWriteMode ) ;
    }
    
    public function dropTable() {
        $sql = "DROP TABLE IF EXISTS keyword" ;
        $this->_doDDL( $sql ) ;
    }
    
    public function createTable() {
        $sql = <<<SQL
CREATE TABLE IF NOT EXISTS keyword
     (
       id           INT UNSIGNED NOT NULL AUTO_INCREMENT
     , keywordValue VARCHAR(255) NOT NULL
     , sortKey      SMALLINT(3) NOT NULL DEFAULT 0
     , created      TIMESTAMP NOT NULL DEFAULT 0
     , updated      TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
                    ON UPDATE CURRENT_TIMESTAMP
     , PRIMARY KEY pk_keywordId ( id )
     , UNIQUE INDEX valueIdx ( keywordValue )
     )
SQL;
        $this->_doDDL( $sql ) ;
    }

    public function dropTriggers() {
        
    }

    public function createTriggers() {
        $sql = <<<SQL
CREATE TRIGGER keywordAfterDeleteTrigger
 AFTER DELETE
    ON keyword
   FOR EACH ROW
 BEGIN
       DELETE
         FROM note
        WHERE appliesToTable = 'keyword'
          AND appliesToId = OLD.id ;
   END
SQL;
        $this->_doDDL( $sql ) ;
        $sql = <<<SQL
CREATE TRIGGER keywordAfterUpdateTrigger
 AFTER UPDATE
    ON keyword
   FOR EACH ROW
 BEGIN
         IF OLD.id <> NEW.id
       THEN
            UPDATE note
               SET note.appliesToId = NEW.id
             WHERE note.appliesToId = OLD.id
               AND note.appliestoTable = 'keyword'
                 ;
        END IF ;
   END
SQL;
        $this->_doDDL( $sql ) ;
    }

    /**
     * @param integer $id
     * @see ControllerBase::get()
     */
    public function get( $id ) {
        $sql = <<<SQL
SELECT id
     , keywordValue
     , sortKey
     , created
     , updated
  FROM keyword
 WHERE id = ?
SQL;
        $stmt = $this->_dbh->prepare( $sql ) ;
        if ( ( ! $stmt ) || ( ! $stmt->bind_param( 'i', $id ) ) ) {
            throw new ControllerException( 'Failed to prepare SELECT statement. (' . $this->_dbh->error . ')' ) ;
        }
        if ( ! $stmt->execute() ) {
            throw new ControllerException( 'Failed to execute SELECT statement. (' . $this->_dbh->error . ')' ) ;
        }
        if ( ! $stmt->bind_result( $id
                                 , $keywordValue
                                 , $sortKey
                                 , $created
                                 , $updated
                                 ) ) {
            throw new ControllerException( 'Failed to bind to result: (' . $this->_dbh->error . ')' ) ;
        }
        if ( $stmt->fetch() ) {
            $model = new KeywordModel() ;
            $model->setId( $id ) ;
            $model->setSortKey( $sortKey ) ;
            $model->setCreated( $created ) ;
            $model->setUpdated( $updated ) ;
        }
        else {
            $model = null ;
        }
        return( $model ) ;
    }

    /**
     * @param string $whereClause
     * @see ControllerBase::getSome()
     */
    public function getSome( $whereClause = '1 = 1') {
        $sql = <<<SQL
SELECT id
     , keywordValue
     , sortKey
     , created
     , updated
  FROM keyword
 WHERE $whereClause
 ORDER
    BY sortKey
SQL;
        $stmt = $this->_dbh->prepare( $sql ) ;
        if ( ! $stmt ) {
            throw new ControllerException( 'Failed to prepare SELECT statement. (' . $this->_dbh->error . ')' ) ;
        }
        if ( ! $stmt->execute() ) {
            throw new ControllerException( 'Failed to execute SELECT statement. (' . $this->_dbh->error . ')' ) ;
        }
        $stmt->bind_result( $id
                          , $keywordValue
                          , $sortKey
                          , $created
                          , $updated
                          ) ;
        $models = array() ;
        while ( $stmt->fetch() ) {
            $model = new KeywordModel() ;
            $model->setId( $id ) ;
            $model->setKeywordValue( $keywordValue ) ;
            $model->setSortKey( $sortKey ) ;
            $model->setCreated( $created ) ;
            $model->setUpdated( $updated ) ;
            $models[] = $model ;
        }
        return( $models ) ;
    }

    public function getAll() {
        return $this->getSome() ;
    }

    /**
     * @param KeywordModel $model
     * @see ControllerBase::add()
     */
    public function add( $model ) {
        if ( $model->validateForAdd() ) {
            try {
                $query = <<<SQL
INSERT keyword
     ( id
     , keywordValue
     , sortKey
     , created
     , updated
     )
VALUES ( NULL, ?, ?, NOW(), NOW() )
SQL;
                $id           = $model->getId() ;
                $keywordValue = $model->getKeywordValue() ;
                $sortKey      = $model->getSortKey() ;
                $stmt         = $this->_dbh->prepare( $query ) ;
                if ( ! $stmt ) {
                    throw new ControllerException( 'Prepared statement failed for ' . $query ) ;
                }
                if ( ! ( $stmt->bind_param( 'si'
                                          , $keywordValue
                                          , $sortKey
                                          ) ) ) {
                    throw new ControllerException( 'Binding parameters for prepared statement failed.' ) ;
                }
                if ( ! $stmt->execute() ) {
                    throw new ControllerException( 'Failed to execute INSERT statement. ('
                                                 . $this->_dbh->error .
                                                 ')' ) ;
                }
                $newId = $stmt->insert_id ;
                /**
                 * @SuppressWarnings checkAliases
                 */
                if ( ! $stmt->close() ) {
                    throw new ControllerException( 'Something broke while trying to close the prepared statement.' ) ;
                }
                return $newId ;
            }
            catch ( Exception $e ) {
                throw new ControllerException( $e->getMessage() ) ;
            }
        }
        else {
            throw new ControllerException( "Invalid data." ) ;
        }
    }

    /**
     * @param KeywordModel $model
     * @see ControllerBase::update()
     */
    public function update( $model ) {
        if ( $model->validateForUpdate() ) {
            try {
                $query = <<<SQL
UPDATE keyword
   SET keywordValue = ?
     , sortKey = ?
 WHERE id = ?
SQL;
                $id           = $model->getId() ;
                $keywordValue = $model->getKeywordValue() ;
                $sortKey      = $model->getSortKey() ;
                $stmt         = $this->_dbh->prepare( $query ) ;
                if ( ! $stmt ) {
                    throw new ControllerException( 'Prepared statement failed for ' . $query ) ;
                }
                if ( ! ( $stmt->bind_param( 'sii'
                                          , $keywordValue
                                          , $sortKey
                                          , $id
                                          ) ) ) {
                    throw new ControllerException( 'Binding parameters for prepared statement failed.' ) ;
                }
                if ( !$stmt->execute() ) {
                    throw new ControllerException( 'Failed to execute UPDATE statement. ('
                            . $this->_dbh->error .
                            ')' ) ;
                }
                /**
                 * @SuppressWarnings checkAliases
                 */
                if ( !$stmt->close() ) {
                    throw new ControllerException( 'Something broke while trying to close the prepared statement.' ) ;
                }
                return $id ;
            }
            catch ( Exception $e ) {
                throw new ControllerException( $e->getMessage() ) ;
            }
        }
        else {
            throw new ControllerException( "Invalid data." ) ;
        }
    }

    /**
     * @param KeywordModel $model
     * @see ControllerBase::delete()
     */
    public function delete( $model ) {
        $this->_deleteModelById( "DELETE FROM keyword WHERE id = ?", $model ) ;
    }

}
