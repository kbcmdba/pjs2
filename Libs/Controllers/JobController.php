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

class JobController extends ControllerBase {

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
        $sql = "DROP TABLE IF EXISTS job" ;
        $this->_doDDL( $sql ) ;
    }
    
    public function createTable() {
        $sql = <<<SQL
CREATE TABLE IF NOT EXISTS job
     (
       jobId                 INT UNSIGNED NOT NULL AUTO_INCREMENT
     , primaryContactId      INT UNSIGNED NULL DEFAULT NULL
     , companyId             INT UNSIGNED NULL DEFAULT NULL
     , applicationStatusId   INT UNSIGNED NOT NULL
     , lastStatusChange      TIMESTAMP NOT NULL DEFAULT 0
     , urgency               ENUM( 'high', 'medium', 'low' ) NOT NULL DEFAULT 'low'
     , created               TIMESTAMP NOT NULL DEFAULT 0
     , updated               TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
                             ON UPDATE CURRENT_TIMESTAMP
     , nextActionDue         TIMESTAMP NOT NULL DEFAULT 0
     , nextAction            VARCHAR(255) NOT NULL DEFAULT ''
     , positionTitle         VARCHAR(255) NOT NULL DEFAULT ''
     , location              VARCHAR(255) NOT NULL DEFAULT ''
     , url                   VARCHAR(4096) NOT NULL DEFAULT ''
     , PRIMARY KEY pk_jobId ( jobId )
     , FOREIGN KEY fk_primaryContactId ( primaryContactId )
        REFERENCES contact ( contactId )
                ON DELETE CASCADE
                ON UPDATE CASCADE
     , FOREIGN KEY fk_companyId ( companyId )
        REFERENCES company ( id )
                ON DELETE CASCADE
                ON UPDATE CASCADE
     , FOREIGN KEY fk_applicationStatusId ( applicationStatusId )
        REFERENCES applicationStatus ( id )
                ON DELETE CASCADE
                ON UPDATE CASCADE
     )
SQL;
        $this->_doDDL( $sql ) ;
    }

    public function dropTriggers() {
        $sql = "DROP TRIGGER IF EXISTS jobAfterInsertTrigger" ;
        $this->_doDDL( $sql ) ;
        $sql = "DROP TRIGGER IF EXISTS jobAfterUpdateTrigger" ;
        $this->_doDDL( $sql ) ;
        $sql = "DROP TRIGGER IF EXISTS jobAfterDeleteTrigger" ;
        $this->_doDDL( $sql ) ;
    }

    public function createTriggers() {
        $sql = <<<SQL
CREATE TRIGGER jobAfterInsertTrigger
 AFTER INSERT
    ON job
   FOR EACH ROW
 BEGIN
       UPDATE applicationStatusSummary
           AS jss
          SET jss.statusCount = jss.statusCount + 1
        WHERE jss.id = NEW.applicationStatusId ;
   END
SQL;
        $this->_doDDL( $sql ) ;
        $sql = <<<SQL
CREATE TRIGGER jobAfterUpdateTrigger
 AFTER UPDATE
    ON job
   FOR EACH ROW
 BEGIN
         IF OLD.applicationStatusId <> NEW.applicationStatusId
       THEN
            UPDATE applicationStatusSummary
                AS jss
               SET jss.statusCount = jss.statusCount + 1
             WHERE jss.id = NEW.applicationStatusId ;
            UPDATE applicationStatusSummary
                AS jss
               SET jss.statusCount = jss.statusCount + 1
             WHERE jss.id = OLD.applicationStatusId ;
        END IF ;
         IF OLD.jobId <> NEW.jobId
       THEN
            UPDATE note
               SET note.appliesToId = NEW.jobId
             WHERE note.appliesToId = OLD.jobId
               AND note.appliestoTable = 'job'
                 ;
      END IF ;
   END
SQL;
        $this->_doDDL( $sql ) ;
        $sql = <<<SQL
CREATE TRIGGER jobAfterDeleteTrigger
 AFTER DELETE
    ON job
   FOR EACH ROW
 BEGIN
       UPDATE applicationStatusSummary
           AS jss
          SET jss.statusCount = jss.statusCount - 1
        WHERE jss.id = OLD.applicationStatusId ;

       DELETE
         FROM note
        WHERE note.appliesToTable = 'job'
          AND note.appliesToId = OLD.jobId ;
   END
SQL;
        $this->_doDDL( $sql ) ;
    }

    // @todo Implement JobController::get( $id )
    public function get( $id ) {
        throw new ControllerException( "Not implemented." ) ;
    }

    // @todo Implement JobController::getSome( $whereClause )
    public function getSome( $whereClause = '1 = 1') {
        throw new ControllerException( "Not implemented." ) ;
    }

    // @todo Implement JobController::add( $model ) ;
    public function add( $model ) {
        throw new ControllerException( "Not implemented." ) ;
    }

    // @todo Implement JobController::update( $model ) ;
    public function update( $model ) {
        throw new ControllerException( "Not implemented." ) ;
    }

    // @todo Implement JobController::delete( $model ) ;
    public function delete( $model ) {
        throw new ControllerException( "Not implemented." ) ;
    }

}
