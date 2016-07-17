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

class ApplicationStatusSummaryController extends ControllerBase {

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
        $sql = "DROP TABLE IF EXISTS applicationStatusSummary" ;
        $this->_doDDL( $sql ) ;
    }
    
    public function createTable() {
        $sql = <<<SQL
CREATE TABLE applicationStatusSummary
     (
       id          INT UNSIGNED NOT NULL COMMENT 'No auto_increment: foreign key'
     , statusCount INT UNSIGNED NOT NULL DEFAULT 0
     , created     TIMESTAMP NOT NULL DEFAULT 0
     , updated     TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
                   ON UPDATE CURRENT_TIMESTAMP
     , PRIMARY KEY pk_applicationStatusId ( id )
     , FOREIGN KEY fk_applicationStatusId ( id )
        REFERENCES applicationStatus ( id )
                ON DELETE CASCADE
                ON UPDATE CASCADE
     )
SQL;
        $this->_doDDL( $sql ) ;
    }

    // @todo Implement ApplicationStatusSummaryController::get( $id ) ;
    public function get( $id ) {
        throw new ControllerException( "Not implemented." ) ;
    }

    // @todo Implement ApplicationStatusSummaryController::getSome( $whereClause ) ;
    public function getSome( $whereClause = '1 = 1') {
        throw new ControllerException( "Not implemented." ) ;
    }

    public function getAll() {
        return $this->getSome() ;
    }

    // @todo Implement ApplicationStatusSummaryController::add( $model ) ;
    public function add( $model ) {
        throw new ControllerException( "Not implemented." ) ;
    }

    // @todo Implement ApplicationStatusSummaryController::update( $model ) ;
    public function update( $model ) {
        throw new ControllerException( "Not implemented." ) ;
    }

    // @todo Implement ApplicationStatusSummaryController::delete( $model ) ;
    public function delete( $model ) {
        throw new ControllerException( "Not implemented." ) ;
    }

}