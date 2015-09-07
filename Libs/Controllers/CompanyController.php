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

class CompanyController extends ControllerBase {

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
        $sql = "DROP TABLE IF EXISTS company" ;
        $this->_doDDL( $sql ) ;
    }
    
    public function createTable() {
        $sql = <<<SQL
CREATE TABLE IF NOT EXISTS company
     (
       companyId             INT UNSIGNED NOT NULL AUTO_INCREMENT
     , isAnAgency            BOOLEAN NOT NULL DEFAULT 0
     , agencyCompanyId       INT UNSIGNED NULL DEFAULT NULL
                             COMMENT 'When isAnAgency is false, point to agency company ID'
     , companyName           VARCHAR(100) NOT NULL DEFAULT ''
     , companyAddress1       VARCHAR(255) NOT NULL DEFAULT ''
     , companyAddress2       VARCHAR(255) NOT NULL DEFAULT ''
     , companyCity           VARCHAR(60) NOT NULL DEFAULT ''
     , companyState          CHAR(2) NOT NULL DEFAULT 'XX'
     , companyZip            INT(5) UNSIGNED NULL DEFAULT NULL
     , companyPhone          INT UNSIGNED NULL DEFAULT NULL
     , created               TIMESTAMP NOT NULL DEFAULT 0
     , updated               TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
                             ON UPDATE CURRENT_TIMESTAMP
     , PRIMARY KEY pk_companyId ( companyId )
     , FOREIGN KEY fk_agencyCompanyId ( agencyCompanyId )
        REFERENCES company ( companyId )
                ON DELETE CASCADE
                ON UPDATE CASCADE
     
     )
SQL;
        $this->_doDDL( $sql ) ;
    }

    public function dropTriggers() {
        $sql = "DROP TRIGGER IF EXISTS companyAfterDeleteTrigger" ;
        $this->_doDDL( $sql ) ;
        $sql = "DROP TRIGGER IF EXISTS companyAfterUpdateTrigger" ;
        $this->_doDDL( $sql ) ;
    }
    
    public function createTriggers() {
       $sql = <<<SQL
CREATE TRIGGER companyAfterDeleteTrigger
 AFTER DELETE
    ON company
   FOR EACH ROW
 BEGIN
       DELETE
         FROM note
        WHERE appliesToTable = 'company'
          AND appliesToId = OLD.companyId ;
   END
SQL;
        $this->_doDDL( $sql ) ;
        $sql = <<<SQL
CREATE TRIGGER companyAfterUpdateTrigger
 AFTER UPDATE
    ON company
   FOR EACH ROW
 BEGIN
             IF OLD.companyId <> NEW.companyId
           THEN
            UPDATE note
               SET note.appliesToId = NEW.companyId
             WHERE note.appliesToId = OLD.companyId
               AND note.appliestoTable = 'company'
                 ;
      END IF ;
   END
SQL;
        $this->_doDDL( $sql ) ;
    }

    // @todo Implement CompanyController::get( $id )
    public function get( $id ) {
        throw new ControllerException( "Not implemented." ) ;
    }

    // @todo Implement CompanyController::getSome( $whereClause )
    public function getSome( $whereClause = '1 = 1') {
        throw new ControllerException( "Not implemented." ) ;
    }

    // @todo Implement CompanyController::add( $model ) ;
    public function add( $model ) {
        throw new ControllerException( "Not implemented." ) ;
    }

    // @todo Implement CompanyController::update( $model ) ;
    public function update( $model ) {
        throw new ControllerException( "Not implemented." ) ;
    }

    // @todo Implement CompanyController::delete( $model ) ;
    public function delete( $model ) {
        throw new ControllerException( "Not implemented." ) ;
    }

}
