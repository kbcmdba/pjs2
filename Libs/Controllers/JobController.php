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
       id                    INT UNSIGNED NOT NULL AUTO_INCREMENT
     , primaryContactId      INT UNSIGNED NULL DEFAULT NULL
     , companyId             INT UNSIGNED NULL DEFAULT NULL
     , applicationStatusId   INT UNSIGNED NULL DEFAULT NULL
     , isActiveSummary       BOOLEAN NOT NULL DEFAULT false
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
     , PRIMARY KEY pk_jobId ( id )
     , FOREIGN KEY fk_primaryContactId ( primaryContactId )
        REFERENCES contact ( id )
                ON DELETE CASCADE
                ON UPDATE CASCADE
     , FOREIGN KEY fk_companyId ( companyId )
        REFERENCES company ( id )
                ON DELETE CASCADE
                ON UPDATE CASCADE
     , FOREIGN KEY fk_job_applicationStatusId ( applicationStatusId )
        REFERENCES applicationStatus ( id )
                ON DELETE CASCADE
                ON UPDATE CASCADE
     )

SQL;
        $this->_doDDL( $sql ) ;
    }

    public function dropTriggers() {
        $sql = "DROP TRIGGER IF EXISTS jobBeforeInsertTrigger" ;
        $this->_doDDL( $sql ) ;
        $sql = "DROP TRIGGER IF EXISTS jobAfterUpdateTrigger" ;
        $this->_doDDL( $sql ) ;
        $sql = "DROP TRIGGER IF EXISTS jobAfterDeleteTrigger" ;
        $this->_doDDL( $sql ) ;
    }

    public function createTriggers() {
        $sql = <<<SQL
CREATE TRIGGER jobBeforeInsertTrigger
BEFORE INSERT
    ON job
   FOR EACH ROW
 BEGIN
       DECLARE newIsActive BOOLEAN ;
       DECLARE newApplicationStatusId INT UNSIGNED DEFAULT NEW.applicationStatusId ;

       SELECT isActive INTO newIsActive
         FROM applicationStatus
        WHERE id = newApplicationStatusId
            ;
          SET NEW.isActiveSummary = newIsActive
            ;
       UPDATE applicationStatusSummary
           AS jss
          SET jss.statusCount = jss.statusCount + 1
        WHERE jss.id = NEW.applicationStatusId
            ;
   END

SQL;
        $this->_doDDL( $sql ) ;
        $sql = <<<SQL
CREATE TRIGGER jobAfterUpdateTrigger
BEFORE UPDATE
    ON job
   FOR EACH ROW
 BEGIN
         IF OLD.applicationStatusId <> NEW.applicationStatusId
       THEN
              IF 0 = NEW.lastStatusChange
            THEN
                 SET NEW.lastStatusChange = NOW() ;
             END IF
               ;
            UPDATE applicationStatusSummary
                AS jss
               SET jss.statusCount = jss.statusCount + 1
             WHERE jss.id = NEW.applicationStatusId
                 ;
            UPDATE applicationStatusSummary
                AS jss
               SET jss.statusCount = jss.statusCount - 1
             WHERE jss.id = OLD.applicationStatusId
                 ;
        END IF ;
         IF OLD.id <> NEW.id
       THEN
            UPDATE note
               SET note.appliesToId = NEW.id
             WHERE note.appliesToId = OLD.id
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
          AND note.appliesToId = OLD.id ;
   END

SQL;
        $this->_doDDL( $sql ) ;
    }

    /**
     * Get up to one matching model
     *
     * @param int $id
     * @throws ControllerException
     * @return NULL|JobModel
     */
    public function get( $id ) {
        $sql = <<<SQL
SELECT id
     , primaryContactId
     , companyId
     , applicationStatusId
     , lastStatusChange
     , urgency
     , created
     , updated
     , nextActionDue
     , nextAction
     , positionTitle
     , location
     , url
  FROM job
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
                                 , $primaryContactId
                                 , $companyId
                                 , $applicationStatusId
                                 , $lastStatusChange
                                 , $urgency
                                 , $created
                                 , $updated
                                 , $nextActionDue
                                 , $nextAction
                                 , $positionTitle
                                 , $location
                                 , $url
                                 ) ) {
            throw new ControllerException( 'Failed to bind to result: (' . $this->_dbh->error . ')' ) ;
        }
        if ( $stmt->fetch() ) {
            $model = new JobModel() ;
            $model->setId( $id ) ;
            $model->setPrimaryContactId( $primaryContactId ) ;
            $model->setCompanyId( $companyId ) ;
            $model->setApplicationStatusId( $applicationStatusId ) ;
            $model->setLastStatusChange( $lastStatusChange ) ;
            $model->setUrgency( $urgency ) ;
            $model->setCreated( $created ) ;
            $model->setUpdated( $updated ) ;
            $model->setNextActionDue( $nextActionDue ) ;
            $model->setNextAction( $nextAction ) ;
            $model->setPositionTitle( $positionTitle ) ;
            $model->setLocation( $location ) ;
            $model->setUrl( $url ) ;
        }
        else {
            $model = null ;
        }
        return( $model ) ;
    }

    /**
     * Get models matching the where clause
     *
     * @param string $whereClause
     * @throws ControllerException
     * @return JobModel[]
     */
    public function getSome( $whereClause = '1 = 1') {
        $sql = <<<SQL
SELECT id
     , primaryContactId
     , companyId
     , applicationStatusId
     , lastStatusChange
     , urgency
     , created
     , updated
     , nextActionDue
     , nextAction
     , positionTitle
     , location
     , url
  FROM job
 WHERE $whereClause
 ORDER
    BY nextActionDue DESC

SQL;
        $stmt = $this->_dbh->prepare( $sql ) ;
        if ( ! $stmt ) {
            throw new ControllerException( 'Failed to prepare SELECT statement. (' . $this->_dbh->error . ') ' . $sql ) ;
        }
        if ( ! $stmt->execute() ) {
            throw new ControllerException( 'Failed to execute SELECT statement. (' . $this->_dbh->error . ')' ) ;
        }
        $stmt->bind_result( $id
                          , $primaryContactId
                          , $companyId
                          , $applicationStatusId
                          , $lastStatusChange
                          , $urgency
                          , $created
                          , $updated
                          , $nextActionDue
                          , $nextAction
                          , $positionTitle
                          , $location
                          , $url
                          ) ;
        $models = array() ;
        while ( $stmt->fetch() ) {
            $model = new JobModel() ;
            $model->setId( $id ) ;
            $model->setPrimaryContactId( $primaryContactId ) ;
            $model->setCompanyId( $companyId ) ;
            $model->setApplicationStatusId( $applicationStatusId ) ;
            $model->setLastStatusChange( $lastStatusChange ) ;
            $model->setUrgency( $urgency ) ;
            $model->setCreated( $created ) ;
            $model->setUpdated( $updated ) ;
            $model->setNextActionDue( $nextActionDue ) ;
            $model->setNextAction( $nextAction ) ;
            $model->setPositionTitle( $positionTitle ) ;
            $model->setLocation( $location ) ;
            $model->setUrl( $url ) ;
            $models[] = $model ;
        }
        return( $models ) ;
    }

    /**
     * Get all models from the table
     *
     * @throws ControllerException
     * @return JobModel[]
     */
    public function getAll() {
        return $this->getSome() ;
    }

    /**
     * Count the number of rows matching the where clause in this table.
     * 
     * @param string $whereClause
     * @throws ControllerException
     * @return int
     */
    public function countSome( $whereClause = '1 = 1') {
        $sql = "SELECT COUNT( id ) FROM job WHERE $whereClause" ;
        $stmt = $this->_dbh->prepare( $sql ) ;
        if ( ! $stmt ) {
            throw new ControllerException( 'Failed to prepare SELECT statement. (' . $this->_dbh->error . ') ' . $sql ) ;
        }
        if ( ! $stmt->execute() ) {
            throw new ControllerException( 'Failed to execute SELECT statement. (' . $this->_dbh->error . ')' ) ;
        }
        $stmt->bind_result( $count ) ;
        $stmt->fetch() ;
        return $count ;
    }

    /**
     * Count the number of rows in the table.
     *
     * @throws ControllerException
     * @return int
     */
    public function countAll() {
        return $this->countSome() ;
    }

    /**
     * @param JobModel $model
     */
    public function add( $model ) {
        if ( $model->validateForAdd() ) {
            try {
                $query = <<<SQL
INSERT job
     ( id
     , primaryContactId
     , companyId
     , applicationStatusId
     , lastStatusChange
     , urgency
     , created
     , updated
     , nextActionDue
     , nextAction
     , positionTitle
     , location
     , url
     )
VALUES ( NULL, ?, ?, ?, ?, ?, NOW(), NOW(), ?, ?, ?, ?, ? )

SQL;
                $primaryContactId    = $model->getPrimaryContactId() ;
                $companyId           = $model->getCompanyId() ;
                $applicationStatusId = $model->getApplicationStatusId() ;
                $lastStatusChange    = $model->getLastStatusChange() ;
                $urgency             = $model->getUrgency() ;
                $nextActionDue       = $model->getNextActionDue() ;
                $nextAction          = $model->getNextAction() ;
                $positionTitle       = $model->getPositionTitle() ;
                $location            = $model->getLocation() ;
                $url                 = $model->getUrl() ;
                $stmt                = $this->_dbh->prepare( $query ) ;
                if ( ! $stmt ) {
                    throw new ControllerException( 'Prepared statement failed for ' . $query . ' - ' . $this->_dbh->error ) ;
                }
                if ( ! ( $stmt->bind_param( 'iiisssssss'
                                          , $primaryContactId
                                          , $companyId
                                          , $applicationStatusId
                                          , $lastStatusChange
                                          , $urgency
                                          , $nextActionDue
                                          , $nextAction
                                          , $positionTitle
                                          , $location
                                          , $url
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
     * @param JobModel $model
     * @see ControllerBase::update()
     */
    public function update( $model ) {
            if ( $model->validateForUpdate() ) {
            try {
                $query = <<<SQL
UPDATE job
   SET primaryContactId = ?
     , companyId = ?
     , applicationStatusId = ?
     , lastStatusChange = ?
     , urgency = ?
     , nextActionDue = ?
     , nextAction = ?
     , positionTitle = ?
     , location = ?
     , url = ?
 WHERE id = ?

SQL;
                $id                  = $model->getId() ;
                $primaryContactId    = $model->getPrimaryContactId() ;
                $companyId           = $model->getCompanyId() ;
                $applicationStatusId = $model->getApplicationStatusId() ;
                $lastStatusChange    = $model->getLastStatusChange() ;
                $urgency             = $model->getUrgency() ;
                $nextActionDue       = $model->getNextActionDue() ;
                $nextAction          = $model->getNextAction() ;
                $positionTitle       = $model->getPositionTitle() ;
                $location            = $model->getLocation() ;
                $url                 = $model->getUrl() ;
                $stmt                = $this->_dbh->prepare( $query ) ;
                if ( ! $stmt ) {
                    throw new ControllerException( 'Prepared statement failed for ' . $query ) ;
                }
                if ( ! ( $stmt->bind_param( 'iiisssssssi'
                                          , $primaryContactId
                                          , $companyId
                                          , $applicationStatusId
                                          , $lastStatusChange
                                          , $urgency
                                          , $nextActionDue
                                          , $nextAction
                                          , $positionTitle
                                          , $location
                                          , $url
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

    public function delete( $model ) {
        $this->_deleteModelById( "DELETE FROM job WHERE id = ?", $model ) ;
    }

}
