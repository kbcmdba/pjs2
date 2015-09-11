<?php

/**
 * phpjobseeker
 *
 * Copyright (C) 2009, 2015 Kevin Benton - kbenton at bentonfam dot org
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
       applicationStatusId   INT UNSIGNED NOT NULL AUTO_INCREMENT
     , statusValue           VARCHAR(50) NOT NULL
     , isActive              BOOLEAN NOT NULL DEFAULT 1
     , sortKey               SMALLINT(3) UNSIGNED NOT NULL DEFAULT 100
     , style                 VARCHAR(4096) NOT NULL DEFAULT ''
     , created               TIMESTAMP NOT NULL DEFAULT 0
     , updated               TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
                             ON UPDATE CURRENT_TIMESTAMP
     , PRIMARY KEY pk_applicationStatusId ( applicationStatusId )
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
            ( applicationStatusId
            , statusCount
            , created
            , updated
            )
       VALUES
            ( NEW.applicationStatusId
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
     ( applicationStatusId
     , isActive
     , statusValue
     , sortKey
     , created
     , updated
     )
VALUES (  1, 1, 'FOUND'        , 10  , NOW(), NOW() )
     , (  2, 1, 'CONTACTED'    , 20  , NOW(), NOW() )
     , (  3, 1, 'APPLIED'      , 30  , NOW(), NOW() )
     , (  4, 1, 'INTERVIEWING' , 40  , NOW(), NOW() )
     , (  5, 1, 'FOLLOWUP'     , 50  , NOW(), NOW() )
     , (  6, 1, 'CHASING'      , 60  , NOW(), NOW() )
     , (  7, 1, 'NETWORKING'   , 70  , NOW(), NOW() )
     , (  8, 0, 'UNAVAILABLE'  , 999 , NOW(), NOW() )
     , (  9, 0, 'INVALID'      , 999 , NOW(), NOW() )
     , ( 10, 0, 'DUPLICATE'    , 999 , NOW(), NOW() )
     , ( 11, 0, 'CLOSED'       , 999 , NOW(), NOW() )
SQL;
        $this->_doDDL( $sql ) ;
    }

    // @todo Implement ApplicationStatusController::get( $id ) ;
    public function get( $id ) {
        throw new ControllerException( "Not implemented." ) ;
    }

    // @todo Implement ApplicationStatusController::getSome( $whereClause ) ;
    public function getSome( $whereClause = '1 = 1' ) {
        $sql = <<<SQL
SELECT applicationStatusId
     , statusValue
     , isActive
     , sortKey
     , style
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
        $stmt->bind_result( $applicationStatusId
                          , $statusValue
                          , $isActive
                          , $sortKey
                          , $style
                          , $created
                          , $updated
                          ) ;
        $models = array() ;
        while ( $stmt->fetch() ) {
            $model = new ApplicationStatusModel() ;
            $model->setApplicationStatusId( $applicationStatusId ) ;
            $model->setStatusValue( $statusValue ) ;
            $model->setIsActive( $isActive ) ;
            $model->setSortKey( $sortKey ) ;
            $model->setStyle( $style ) ;
            $model->setCreated( $created ) ;
            $model->setUpdated( $updated ) ;
            $models[] = $model ;
        }
        return( $models ) ;
    }

    // @todo Implement ApplicationStatusController::add( $model ) ;
    public function add( $model ) {
        throw new ControllerException( "Not implemented." ) ;
    }

    // @todo Implement ApplicationStatusController::update( $model ) ;
    public function update( $model ) {
        throw new ControllerException( "Not implemented." ) ;
    }

    // @todo Implement ApplicationStatusController::delete( $model ) ;
    public function delete( $model ) {
        throw new ControllerException( "Not implemented." ) ;
    }
    
}