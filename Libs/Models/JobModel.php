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

/**
 * Job Model
 */
class JobModel extends ModelBase {

    private $_id ;
    private $_primaryContactId ;
    private $_companyId ;
    private $_applicationStatusId ;
    private $_lastStatusChange ;
    private $_urgency ;
    private $_created ;
    private $_updated ;
    private $_nextActionDue ;
    private $_nextAction ;
    private $_positionTitle ;
    private $_location ;
    private $_url ;

    /**
     * class constructor
     */
    public function __construct() {
        parent::__construct() ;
    }

    /**
     * Validate model for insert
     *
     * @return boolean
     */
    public function validateForAdd() {
        return  ( (   Tools::isNullOrEmptyString( Tools::param( 'id' ) ) )
               && ( ! Tools::isNullOrEmptyString( Tools::param( 'contactId' ) ) )
               && ( ! Tools::isNullOrEmptyString( Tools::param( 'companyId' ) ) )
               && ( ! Tools::isNullOrEmptyString( Tools::param( 'applicationStatusId' ) ) )
               && ( ! Tools::isNullOrEmptyString( Tools::param( 'urgency' ) ) )
               && ( ! Tools::isNullOrEmptyString( Tools::param( 'nextActionDue' ) ) )
               && ( ! Tools::isNullOrEmptyString( Tools::param( 'nextAction' ) ) )
               && ( ! Tools::isNullOrEmptyString( Tools::param( 'positionTitle' ) ) )
               && ( ! Tools::isNullOrEmptyString( Tools::param( 'location' ) ) )
                ) ;
    }

    /**
     * Validate model for update
     *
     * @return boolean
     */
    public function validateForUpdate() {
        return  ( ( ! Tools::isNullOrEmptyString( Tools::param( 'id' ) ) )
               && ( ! Tools::isNullOrEmptyString( Tools::param( 'contactId' ) ) )
               && ( ! Tools::isNullOrEmptyString( Tools::param( 'companyId' ) ) )
               && ( ! Tools::isNullOrEmptyString( Tools::param( 'applicationStatusId' ) ) )
               && ( ! Tools::isNullOrEmptyString( Tools::param( 'urgency' ) ) )
               && ( ! Tools::isNullOrEmptyString( Tools::param( 'nextActionDue' ) ) )
               && ( ! Tools::isNullOrEmptyString( Tools::param( 'nextAction' ) ) )
               && ( ! Tools::isNullOrEmptyString( Tools::param( 'positionTitle' ) ) )
               && ( ! Tools::isNullOrEmptyString( Tools::param( 'location' ) ) )
                ) ;
    }

    public function populateFromForm() {
        $this->setId( Tools::param( 'id' ) ) ;
        $this->setPrimaryContactId( Tools::param( 'contactId' ) ) ;
        $this->setCompanyId( Tools::param( 'companyId' ) ) ;
        $this->setApplicationStatusId( Tools::param( 'applicationStatusId' ) ) ;
        $this->setLastStatusChange( Tools::param( 'lastStatusChange' ) ) ;
        $this->setUrgency( Tools::param( 'urgency' ) ) ;
        $this->setCreated( Tools::param( 'created' ) ) ;
        $this->setUpdated( Tools::param( 'updated' ) ) ;
        $this->setNextActionDue( Tools::param( 'nextActionDue' ) ) ;
        $this->setNextAction( Tools::param( 'nextAction' ) ) ;
        $this->setPositionTitle( Tools::param( 'positionTitle' ) ) ;
        $this->setLocation( Tools::param( 'location' ) ) ;
        $this->setUrl( Tools::param( 'url' ) ) ;
    }

    /**
     * @return integer
     */
    public function getId() {
        return $this->_id ;
    }

    /**
     * @param integer $id
     */
    public function setId( $id ) {
        $this->_id = $id ;
    }

    /**
     * @return integer
     */
    public function getPrimaryContactId() {
        return $this->_primaryContactId ;
    }

    /**
     * @param integer $primaryContactId
     */
    public function setPrimaryContactId( $primaryContactId ) {
        $this->_primaryContactId = $primaryContactId ;
    }

    /**
     * @return integer
     */
    public function getCompanyId() {
        return $this->_companyId ;
    }

    /**
     * @param integer $companyId
     */
    public function setCompanyId( $companyId ) {
        $this->_companyId = $companyId ;
    }

    /**
     * @return integer
     */
    public function getApplicationStatusId() {
        return $this->_applicationStatusId ;
    }

    /**
     * @param integer $applicationStatusId
     */
    public function setApplicationStatusId( $applicationStatusId ) {
        $this->_applicationStatusId = $applicationStatusId ;
    }

    /**
     * @return string
     */
    public function getLastStatusChange() {
        if ( "0000-00-00 00:00:00" === $this->_lastStatusChange ) {
            return "" ;
        }
        else {
            return $this->_lastStatusChange ;
        }
    }

    /**
     * @param string $lastStatusChange
     */
    public function setLastStatusChange( $lastStatusChange ) {
        $this->_lastStatusChange = $lastStatusChange ;
    }

    /**
     * @return string
     */
    public function getUrgency() {
        return $this->_urgency ;
    }

    /**
     * @param string $urgency
     */
    public function setUrgency( $urgency ) {
        $this->_urgency = $urgency ;
    }

    /**
     * @return string
     */
    public function getCreated() {
        return $this->_created ;
    }

    /**
     * @param string $created
     */
    public function setCreated( $created ) {
        $this->_created = $created ;
    }

    /**
     * @return string
     */
    public function getUpdated() {
        return $this->_updated ;
    }

    /**
     * @param string $updated
     */
    public function setUpdated( $updated ) {
        $this->_updated = $updated ;
    }


    /**
     * @return string
     */
    public function getNextActionDue() {
        return $this->_nextActionDue ;
    }

    /**
     * @param string $nextActionDue
     */
    public function setNextActionDue( $nextActionDue ) {
        $this->_nextActionDue = $nextActionDue ;
    }

    /**
     * @return string
     */
    public function getNextAction() {
        return $this->_nextAction ;
    }

    /**
     * @param string $nextAction
     */
    public function setNextAction( $nextAction ) {
        $this->_nextAction = $nextAction ;
    }

    /**
     * @return string
     */
    public function getPositionTitle() {
        return $this->_positionTitle ;
    }

    /**
     * @param string $positionTitle
     */
    public function setPositionTitle( $positionTitle ) {
        $this->_positionTitle = $positionTitle ;
    }

    /**
     * @return string
     */
    public function getLocation() {
        return $this->_location ;
    }

    /**
     * @param string $location
     */
    public function setLocation( $location ) {
        $this->_location = $location ;
    }

    /**
     * @return string
     */
    public function getUrl() {
        return $this->_url ;
    }

    /**
     * @param string $url
     */
    public function setUrl( $url ) {
        $this->_url = $url ;
    }

}
