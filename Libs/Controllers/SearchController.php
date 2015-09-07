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

class SearchController extends ControllerBase {

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
        $sql = "DROP TABLE IF EXISTS search" ;
        $this->_doDDL( $sql ) ;
    }
    
    public function createTable() {
        $sql = <<<SQL
CREATE TABLE IF NOT EXISTS search
     (
       searchId              INT UNSIGNED NOT NULL AUTO_INCREMENT
     , engineName            VARCHAR(255) NOT NULL DEFAULT ''
     , searchName            VARCHAR(255) NOT NULL DEFAULT ''
     , url                   VARCHAR(4096) NOT NULL DEFAULT ''
     , created               TIMESTAMP NOT NULL DEFAULT 0
     , updated               TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
                             ON UPDATE CURRENT_TIMESTAMP
     , PRIMARY KEY pk_searchId ( searchId )
     )
SQL;
        $this->_doDDL( $sql ) ;
    }

    public function dropTriggers() {
        $sql = "DROP TRIGGER IF EXISTS searchAfterDeleteTrigger" ;
        $this->_doDDL( $sql ) ;
        $sql = "DROP TRIGGER IF EXISTS searchAfterUpdateTrigger" ;
        $this->_doDDL( $sql ) ;
    }

    public function createTriggers() {
        $sql = <<<SQL
CREATE TRIGGER searchAfterDeleteTrigger
 AFTER DELETE
    ON search
   FOR EACH ROW
 BEGIN
       DELETE
         FROM note
        WHERE appliesToTable = 'search'
          AND appliesToId = OLD.searchId ;
   END
SQL;
        $this->_doDDL( $sql ) ;
        $sql = <<<SQL
CREATE TRIGGER searchAfterUpdateTrigger
 AFTER UPDATE
    ON search
   FOR EACH ROW
 BEGIN
         IF OLD.searchId <> NEW.searchId
       THEN
            UPDATE note
               SET note.appliesToId = NEW.searchId
             WHERE note.appliesToId = OLD.searchId
               AND note.appliestoTable = 'search'
                 ;
        END IF ;
   END
SQL;
        $this->_doDDL( $sql ) ;
    }

    // @todo Implement SearchController::get( $id )
    public function get( $id ) {
        throw new ControllerException( "Not implemented." ) ;
    }

    // @todo Implement SearchController::getSome( $whereClause )
    public function getSome( $whereClause = '1 = 1') {
        throw new ControllerException( "Not implemented." ) ;
    }

    // @todo Implement SearchController::add( $model ) ;
    public function add( $model ) {
        throw new ControllerException( "Not implemented." ) ;
    }

    // @todo Implement SearchController::update( $model ) ;
    public function update( $model ) {
        throw new ControllerException( "Not implemented." ) ;
    }

    // @todo Implement SearchController::delete( $model ) ;
    public function delete( $model ) {
        throw new ControllerException( "Not implemented." ) ;
    }

}
