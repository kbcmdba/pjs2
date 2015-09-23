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
     , UNIQUE index valueIdx ( keywordValue )
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

    // @todo Implement KeywordController::get( $id )
    public function get( $id ) {
        throw new ControllerException( "Not implemented." ) ;
    }

    // @todo Implement KeywordController::getSome( $whereClause )
    public function getSome( $whereClause = '1 = 1') {
        throw new ControllerException( "Not implemented." ) ;
    }

    // @todo Implement KeywordController::add( $model ) ;
    public function add( $model ) {
        throw new ControllerException( "Not implemented." ) ;
    }

    // @todo Implement KeywordController::update( $model ) ;
    public function update( $model ) {
        throw new ControllerException( "Not implemented." ) ;
    }

    // @todo Implement KeywordController::delete( $model ) ;
    public function delete( $model ) {
        throw new ControllerException( "Not implemented." ) ;
    }

}
