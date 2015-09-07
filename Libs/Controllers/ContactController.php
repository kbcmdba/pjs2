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

class ContactController extends ControllerBase {

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
        $sql = "DROP TABLE IF EXISTS contact" ;
        $this->_doDDL( $sql ) ;
    }
    
    public function createTable() {
        $sql = <<<SQL
CREATE TABLE contact
     (
       contactId             INT UNSIGNED NOT NULL AUTO_INCREMENT
     , contactCompanyId      INT UNSIGNED NOT NULL DEFAULT 0
     , contactName           VARCHAR(255)
     , contactEmail          VARCHAR(255)
     , contactPhone          INT UNSIGNED NOT NULL
     , contactAlternatePhone INT UNSIGNED NULL DEFAULT NULL
     , created               TIMESTAMP NOT NULL DEFAULT 0
     , updated               TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
                             ON UPDATE CURRENT_TIMESTAMP
     , PRIMARY KEY pk_contactId ( contactId )
     , FOREIGN KEY fk_contactCompanyId ( contactCompanyId )
        REFERENCES company ( companyId )
                ON DELETE CASCADE
                ON UPDATE CASCADE
     )
SQL;
        $this->_doDDL( $sql ) ;
    }

    public function dropTriggers() {
        $sql = "DROP TRIGGER IF EXISTS contactAfterDeleteTrigger" ;
        $this->_doDDL( $sql ) ;
        $sql = "DROP TRIGGER IF EXISTS contactAfterUpdateTrigger" ;
        $this->_doDDL( $sql ) ;
    }

    public function createTriggers() {
        $sql = <<<SQL
CREATE TRIGGER contactAfterDeleteTrigger
 AFTER DELETE
    ON contact
   FOR EACH ROW
 BEGIN
       DELETE
         FROM note
        WHERE appliesToTable = 'contact'
          AND appliesToId = OLD.contactId ;
   END
SQL;
        $this->_doDDL( $sql ) ;
        $sql = <<<SQL
CREATE TRIGGER contactAfterUpdateTrigger
 AFTER UPDATE
    ON contact
   FOR EACH ROW
 BEGIN
         IF OLD.contactId <> NEW.contactId
       THEN
            UPDATE note
               SET note.appliesToId = NEW.contactId
             WHERE note.appliesToId = OLD.contactId
               AND note.appliestoTable = 'contact'
                 ;
        END IF ;
   END
SQL;
        $this->_doDDL( $sql ) ;
    }

    // @todo Implement ContactController::get( $id )
    public function get( $id ) {
        throw new ControllerException( "Not implemented." ) ;
    }

    // @todo Implement ContactController::getSome( $whereClause )
    public function getSome( $whereClause = '1 = 1') {
        throw new ControllerException( "Not implemented." ) ;
    }

    // @todo Implement ContactController::add( $model ) ;
    public function add( $model ) {
        throw new ControllerException( "Not implemented." ) ;
    }

    // @todo Implement ContactController::update( $model ) ;
    public function update( $model ) {
        throw new ControllerException( "Not implemented." ) ;
    }

    // @todo Implement ContactController::delete( $model ) ;
    public function delete( $model ) {
        throw new ControllerException( "Not implemented." ) ;
    }

}
