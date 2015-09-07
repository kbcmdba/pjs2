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

/**
 * ApplicationStatusSummary Model
 */
class ApplicationStatusSummaryModel extends ModelBase {

    private $_applicationStatusId ;
    private $_statusCount ;
    private $_created ;
    private $_updated ;

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
     * @todo Implement ApplicationStatusSummaryModel::validateForAdd()
     */
    public function validateForAdd() {
        return 0 ;
    }

    /**
     * Validate model for update
     *
     * @return boolean
     * @todo Implement ApplicationStatusSummaryModel::validateForUpdate()
     */
    public function validateForUpdate() {
        return 0 ;
    }

    public function populateFromForm() {
        $this->setApplicationStatusId( Tools::param( 'applicationStatusId' ) ) ;
        $this->setStatusCount( Tools::param( 'statusCount' ) ) ;
        $this->setCreated( Tools::param( 'created' ) ) ;
        $this->setUpdated( Tools::param( 'updated' ) ) ;
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
     * @return integer
     */
    public function getStatusCount() {
        return $this->_statusCount ;
    }

    /**
     * @param integer $statusCount
     */
    public function setStatusCount( $statusCount ) {
        $this->_statusCount = $statusCount ;
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

}
