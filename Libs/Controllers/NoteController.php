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

class NoteController extends ControllerBase {

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
        $sql = "DROP TABLE IF EXISTS note" ;
        $this->_doDDL( $sql ) ;
    }
    
    public function createTable() {
        $sql = <<<SQL
CREATE TABLE IF NOT EXISTS note
     (
       noteId                INT UNSIGNED NOT NULL AUTO_INCREMENT
     , appliesToTable        ENUM( 'job', 'company', 'contact', 'keyword', 'search' ) NOT NULL
     , appliesToId           INT UNSIGNED NOT NULL
     , created               TIMESTAMP NOT NULL DEFAULT 0
     , updated               TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
                             ON UPDATE CURRENT_TIMESTAMP
     , noteText              TEXT NOT NULL
     , PRIMARY KEY pk_noteId ( noteId )
     , INDEX appliesTo ( appliesToTable, appliesToId, created )
     )
SQL;
        $this->_doDDL( $sql ) ;
    }

    // @todo Implement NoteController::get( $id )
    public function get( $id ) {
        throw new ControllerException( "Not implemented." ) ;
    }

    // @todo Implement NoteController::getSome( $whereClause )
    public function getSome( $whereClause = '1 = 1') {
        throw new ControllerException( "Not implemented." ) ;
    }

    // @todo Implement NoteController::add( $model ) ;
    public function add( $model ) {
        throw new ControllerException( "Not implemented." ) ;
    }

    // @todo Implement NoteController::update( $model ) ;
    public function update( $model ) {
        throw new ControllerException( "Not implemented." ) ;
    }

    // @todo Implement NoteController::delete( $model ) ;
    public function delete( $model ) {
        throw new ControllerException( "Not implemented." ) ;
    }

}
