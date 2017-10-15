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

class ApplicationStatusController extends ControllerBase {

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
        $sql = "DROP TABLE IF EXISTS applicationStatus" ;
        $this->_doDDL( $sql ) ;
    }

    public function createTable() {
        $sql = <<<SQL
CREATE TABLE IF NOT EXISTS applicationStatus
     (
       id           INT UNSIGNED NOT NULL AUTO_INCREMENT
     , statusValue  VARCHAR(50) NOT NULL
     , isActive     BOOLEAN NOT NULL DEFAULT 1
     , sortKey      SMALLINT(3) UNSIGNED NOT NULL DEFAULT 100
     , style        VARCHAR(4096) NOT NULL DEFAULT ''
     , summaryCount INT UNSIGNED NOT NULL DEFAULT 0
     , created      TIMESTAMP NOT NULL DEFAULT 0
     , updated      TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
                    ON UPDATE CURRENT_TIMESTAMP
     , PRIMARY KEY  pk_applicationStatusId ( id )
     )
SQL;
        $this->_doDDL( $sql ) ;
    }

    public function dropTriggers() {
        $sql = "DROP TRIGGER IF EXISTS applicationStatusAfterInsertTrigger" ;
        $this->_doDDL( $sql ) ;
    }

    public function createTriggers() {
        $sql = <<<SQL
CREATE TRIGGER applicationStatusAfterInsertTrigger
 AFTER INSERT
    ON applicationStatus
   FOR EACH ROW
 BEGIN
       INSERT applicationStatusSummary
            ( id
            , statusCount
            , created
            , updated
            )
       VALUES
            ( NEW.id
            , 0
            , NOW()
            , NOW()
            ) ;
   END
SQL;
        $this->_doDDL( $sql ) ;
    }

    public function preLoadData() {
        $sql = <<<SQL
INSERT applicationStatus
     ( id
     , isActive
     , statusValue
     , sortKey
     , style
     , summaryCount
     , created
     , updated
     )
VALUES (  1, 1, 'FOUND'       , 10  , 'background-color: lightgreen; color: blue;', 0, NOW(), NOW() )
     , (  2, 1, 'CONTACTED'   , 20  , 'background-color: orange; color: blue;'    , 0, NOW(), NOW() )
     , (  3, 1, 'APPLIED'     , 30  , 'background-color: yellow; color: blue;'    , 0, NOW(), NOW() )
     , (  4, 1, 'INTERVIEWING', 40  , 'background-color: white; color: red;'      , 0, NOW(), NOW() )
     , (  5, 1, 'FOLLOWUP'    , 50  , 'background-color: yellow; color: black;'   , 0, NOW(), NOW() )
     , (  6, 1, 'CHASING'     , 60  , 'background-color: red; color: black;'      , 0, NOW(), NOW() )
     , (  7, 1, 'NETWORKING'  , 70  , 'background-color: cyan; color: black;'     , 0, NOW(), NOW() )
     , (  8, 0, 'UNAVAILABLE' , 999 , 'background-color: black; color: white;'    , 0, NOW(), NOW() )
     , (  9, 0, 'INVALID'     , 999 , 'background-color: black; color: white;'    , 0, NOW(), NOW() )
     , ( 10, 0, 'DUPLICATE'   , 999 , 'background-color: black; color: white;'    , 0, NOW(), NOW() )
     , ( 11, 0, 'CLOSED'      , 999 , 'background-color: black; color: white;'    , 0, NOW(), NOW() )
SQL;
        $this->_doDDL( $sql ) ;
    }

    public function get( $id ) {
        $sql = <<<SQL
SELECT id
     , statusValue
     , isActive
     , sortKey
     , style
     , summaryCount
     , created
     , updated
  FROM applicationStatus
 WHERE id = ?
 ORDER
    BY sortKey
SQL;
        $stmt = $this->_dbh->prepare( $sql ) ;
        if ( ! $stmt ) {
            throw new ControllerException( 'Failed to prepare SELECT statement. (' . $this->_dbh->error . ')' ) ;
        }
        $stmt->bind_param( 'i', $id ) ;
        if ( ! $stmt->execute() ) {
            throw new ControllerException( 'Failed to execute SELECT statement. (' . $this->_dbh->error . ')' ) ;
        }
        if ( ! $stmt->bind_result( $id
                                 , $statusValue
                                 , $isActive
                                 , $sortKey
                                 , $style
                                 , $summaryCount
                                 , $created
                                 , $updated
                                 ) ) {
            throw new ControllerException( 'Failed to bind to result: (' . $this->_dbh->error . ')' ) ;
        }
        $model = new ApplicationStatusModel() ;
        if ( $stmt->fetch() ) {
            $model->setId( $id ) ;
            $model->setStatusValue( $statusValue ) ;
            $model->setIsActive( $isActive ) ;
            $model->setSortKey( $sortKey ) ;
            $model->setStyle( $style ) ;
            $model->setSummaryCount( $summaryCount ) ;
            $model->setCreated( $created ) ;
            $model->setUpdated( $updated ) ;
            return( $model ) ;
        }
        else {
            return( null ) ;
        }
    }

    public function getSome( $whereClause = '1 = 1' ) {
        $sql = <<<SQL
SELECT id
     , statusValue
     , isActive
     , sortKey
     , style
     , summaryCount
     , created
     , updated
  FROM applicationStatus
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
                          , $statusValue
                          , $isActive
                          , $sortKey
                          , $style
                          , $summaryCount
                          , $created
                          , $updated
                          ) ;
        $models = array() ;
        while ( $stmt->fetch() ) {
            $model = new ApplicationStatusModel() ;
            $model->setId( $id ) ;
            $model->setStatusValue( $statusValue ) ;
            $model->setIsActive( $isActive ) ;
            $model->setSortKey( $sortKey ) ;
            $model->setStyle( $style ) ;
            $model->setSummaryCount( $summaryCount ) ;
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
     * Update an application status model
     *
     * @param ApplicationStatusModel $model
     * @see ControllerBase::update()
     */
    public function add( $model ) {
            if ( $model->validateForAdd() ) {
            try {
                $query = <<<SQL
INSERT applicationStatus
     ( id
     , statusValue
     , isActive
     , sortKey
     , style
     , summaryCount
     , created
     , updated
     )
VALUES ( ?, ?, ?, ?, ?, 0, NOW(), NOW() )
SQL;
                $id           = $model->getId() ;
                $statusValue  = $model->getStatusValue() ;
                $isActive     = $model->getIsActive() ;
                $sortKey      = $model->getSortKey() ;
                $style        = $model->getStyle() ;
                $summaryCount = $model->getSummaryCount() ;
                $stmt = $this->_dbh->prepare( $query ) ;
                if ( ! $stmt ) {
                    throw new ControllerException( 'Prepared statement failed for ' . $query ) ;
                }
                if ( ! ( $stmt->bind_param( 'isiisi'
                                          , $id
                                          , $statusValue
                                          , $isActive
                                          , $sortKey
                                          , $style
                                          , $summaryCount
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
     * Update an application status model
     *
     * @param ApplicationStatusModel $model
     * @see ControllerBase::update()
     */
    public function update( $model ) {
            if ( $model->validateForUpdate() ) {
            try {
                $query = <<<SQL
UPDATE applicationStatus
   SET statusValue = ?
     , isActive = ?
     , sortKey = ?
     , style = ?
     , summaryCount = ?
 WHERE id = ?
SQL;
                $id           = $model->getId() ;
                $statusValue  = $model->getStatusValue() ;
                $isActive     = $model->getIsActive() ;
                $sortKey      = $model->getSortKey() ;
                $style        = $model->getStyle() ;
                $summaryCount = $model->getSummaryCount() ;
                $stmt = $this->_dbh->prepare( $query ) ;
                if ( ! $stmt ) {
                    throw new ControllerException( 'Prepared statement failed for ' . $query ) ;
                }
                if ( ! ( $stmt->bind_param( 'siisii'
                                          , $statusValue
                                          , $isActive
                                          , $sortKey
                                          , $style
                                          , $summaryCount
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
     * Delete an application status
     *
     * @param ExpenseModel $applicationStatusModel
     * @throws ControllerException
     */
    public function delete( $applicationStatusModel ) {
        $this->_deleteModelById( "DELETE FROM applicationStatus WHERE id = ?", $applicationStatusModel ) ;
    }
    
}