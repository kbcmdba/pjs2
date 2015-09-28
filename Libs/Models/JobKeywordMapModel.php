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
 * JobKeyword Model
 */
class JobKeywordMapModel extends ModelBase {

    private $_id ;
    private $_jobId ;
    private $_keywordId ;
    private $_sortKey ;
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
     */
    public function validateForAdd() {
        return  ( (   Tools::isNullOrEmptyString( Tools::param( 'id' ) ) )
               && ( ! Tools::isNullOrEmptyString( Tools::param( 'jobId' ) ) )
               && (   Tools::isNumeric( Tools::param( 'jobId' ) ) )
               && ( ! Tools::isNullOrEmptyString( Tools::param( 'keywordId' ) ) )
               && (   Tools::isNumeric( Tools::param( 'keywordId' ) ) )
               && ( ! Tools::isNullOrEmptyString( Tools::param( 'sortKey' ) ) )
               && (   Tools::isNumeric( Tools::param( 'sortKey' ) ) )
                ) ;
    }

    /**
     * Validate model for update
     *
     * @return boolean
     */
    public function validateForUpdate() {
        return  ( ( ! Tools::isNullOrEmptyString( Tools::param( 'id' ) ) )
               && (   Tools::isNumeric( Tools::param( 'id' ) ) )
               && ( ! Tools::isNullOrEmptyString( Tools::param( 'jobId' ) ) )
               && (   Tools::isNumeric( Tools::param( 'jobId' ) ) )
               && ( ! Tools::isNullOrEmptyString( Tools::param( 'keywordId' ) ) )
               && (   Tools::isNumeric( Tools::param( 'keywordId' ) ) )
               && ( ! Tools::isNullOrEmptyString( Tools::param( 'sortKey' ) ) )
               && (   Tools::isNumeric( Tools::param( 'sortKey' ) ) )
                ) ;
    }

    public function populateFromForm() {
        $this->setId( Tools::param( 'id' ) ) ;
        $this->setJobId( Tools::param( 'jobId' ) ) ;
        $this->setKeywordId( Tools::param( 'keywordId' ) ) ;
        $this->setSortKey( Tools::param( 'sortKey' ) ) ;
        $this->setCreated( Tools::param( 'created' ) ) ;
        $this->setUpdated( Tools::param( 'updated' ) ) ;
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
    public function getJobId() {
        return $this->_jobId ;
    }

    /**
     * @param integer $jobId
     */
    public function setJobId( $jobId ) {
        $this->_jobId = $jobId ;
    }

    /**
     * @return integer
     */
    public function getKeywordId() {
        return $this->_keywordId ;
    }

    /**
     * @param integer $keywordId
     */
    public function setKeywordId( $keywordId ) {
        $this->_keywordId = $keywordId ;
    }

    /**
     * @return integer
     */
    public function getSortKey() {
        return $this->_sortKey ;
    }

    /**
     * @param integer $sortKey
     */
    public function setSortKey( $sortKey ) {
        $this->_sortKey = $sortKey ;
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
