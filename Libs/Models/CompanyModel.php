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
 * Company Model
 */
class CompanyModel extends ModelBase
{
    private $_id ;
    private $_agencyCompanyId ;
    private $_companyName ;
    private $_companyAddress1 ;
    private $_companyAddress2 ;
    private $_companyCity ;
    private $_companyState ;
    private $_companyZip ;
    private $_companyPhone ;
    private $_companyUrl ;
    private $_created ;
    private $_updated ;

    /**
     * class constructor
     */
    public function __construct()
    {
        parent::__construct() ;
    }

    /**
     * Validate model for insert
     *
     * @return boolean
     */
    public function validateForAdd()
    {
        $agencyCompanyId    = $this->getAgencyCompanyId() ;
        $validAgencyId      =  (
            Tools::isNullOrEmptyString($agencyCompanyId)
                              ||  (
                                  (is_numeric($agencyCompanyId))
                                 && ($agencyCompanyId > 0)
                                  )
                               ) ;
        $lastContacted      = $this->getLastContacted() ;
        $validLastContacted =  (
            Tools::isNullOrEmptyString($lastContacted)
                              || $this->validateDate($lastContacted)
                              || $this->validateTimestamp($lastContacted)
                               ) ;
        $result =  (
            (Tools::isNullOrEmptyString($this->getId()))
                  && (! Tools::isNullOrEmptyString($this->getCompanyName()))
                  && $validAgencyId
                  && $validLastContacted
                   ) ;
        return $result ;
    }

    /**
     * Validate model for update
     *
     * @return boolean
     */
    public function validateForUpdate()
    {
        $agencyCompanyId    = $this->getAgencyCompanyId() ;
        $validAgencyId      =  (
            Tools::isNullOrEmptyString($agencyCompanyId)
                              ||  (
                                  (is_numeric($agencyCompanyId))
                                 && ($agencyCompanyId > 0)
                                  )
                               ) ;
        $lastContacted      = $this->getLastContacted() ;
        $validLastContacted =  (
            Tools::isNullOrEmptyString($lastContacted)
                              || $this->validateDate($lastContacted)
                              || $this->validateTimestamp($lastContacted)
                               ) ;
        $result =  (
            (! Tools::isNullOrEmptyString($this->getId()))
                  && (! Tools::isNullOrEmptyString($this->getCompanyName()))
                  && $validAgencyId
                  && $validLastContacted
                   ) ;
        return $result ;
    }

    public function populateFromForm()
    {
        $this->setId(Tools::param('id')) ;
        $this->setAgencyCompanyId(Tools::param('agencyCompanyId')) ;
        $this->setCompanyName(Tools::param('companyName')) ;
        $this->setCompanyAddress1(Tools::param('companyAddress1')) ;
        $this->setCompanyAddress2(Tools::param('companyAddress2')) ;
        $this->setCompanyCity(Tools::param('companyCity')) ;
        $this->setCompanyState(Tools::param('companyState')) ;
        $this->setCompanyZip(Tools::param('companyZip')) ;
        $this->setCompanyPhone(Tools::param('companyPhone')) ;
        $this->setCompanyUrl(Tools::param('companyUrl')) ;
        $this->setCreated(Tools::param('created')) ;
        $this->setUpdated(Tools::param('updated')) ;
        $this->setLastContacted(Tools::param('lastContacted')) ;
    }

    /**
     * @return integer
     */
    public function getId()
    {
        return $this->_id ;
    }

    /**
     * @param integer $id
     */
    public function setId($id)
    {
        $this->_id = $id ;
    }

    /**
     * @return integer
     */
    public function getAgencyCompanyId()
    {
        return $this->_agencyCompanyId ;
    }

    /**
     * @param integer $agencyCompanyId
     */
    public function setAgencyCompanyId($agencyCompanyId)
    {
        if (('' === $agencyCompanyId) || (0 === $agencyCompanyId)) {
            $agencyCompanyId = null ;
        }
        $this->_agencyCompanyId = $agencyCompanyId ;
    }

    /**
     * @return string
     */
    public function getCompanyName()
    {
        return $this->_companyName ;
    }

    /**
     * @param string $companyName
     */
    public function setCompanyName($companyName)
    {
        $this->_companyName = $companyName ;
    }

    /**
     * @return string
     */
    public function getCompanyAddress1()
    {
        return $this->_companyAddress1 ;
    }

    /**
     * @param string $companyAddress1
     */
    public function setCompanyAddress1($companyAddress1)
    {
        $this->_companyAddress1 = $companyAddress1 ;
    }

    /**
     * @return string
     */
    public function getCompanyAddress2()
    {
        return $this->_companyAddress2 ;
    }

    /**
     * @param string $companyAddress2
     */
    public function setCompanyAddress2($companyAddress2)
    {
        $this->_companyAddress2 = $companyAddress2 ;
    }

    /**
     * @return string
     */
    public function getCompanyCity()
    {
        return $this->_companyCity ;
    }

    /**
     * @param string $companyCity
     */
    public function setCompanyCity($companyCity)
    {
        $this->_companyCity = $companyCity ;
    }

    /**
     * @return string
     */
    public function getCompanyState()
    {
        return $this->_companyState ;
    }

    /**
     * @param string $companyState
     */
    public function setCompanyState($companyState)
    {
        $this->_companyState = $companyState ;
    }

    /**
     * @return integer
     */
    public function getCompanyZip()
    {
        return $this->_companyZip ;
    }

    /**
     * @param integer $companyZip
     */
    public function setCompanyZip($companyZip)
    {
        $this->_companyZip = $companyZip ;
    }

    /**
     * @return integer
     */
    public function getCompanyPhone()
    {
        return $this->_companyPhone ;
    }

    /**
     * @param integer $companyPhone
     */
    public function setCompanyPhone($companyPhone)
    {
        $this->_companyPhone = $companyPhone ;
    }

    /**
     * @return string
     */
    public function getCompanyUrl()
    {
        return $this->_companyUrl ;
    }

    /**
     * @param string $companyUrl
     */
    public function setCompanyUrl($companyUrl)
    {
        $this->_companyUrl = $companyUrl ;
    }

    /**
     * @return string
     */
    public function getCreated()
    {
        return $this->_created ;
    }

    /**
     * @param string $created
     */
    public function setCreated($created)
    {
        $this->_created = $created ;
    }

    /**
     * @return string
     */
    public function getUpdated()
    {
        return $this->_updated ;
    }

    /**
     * @param string $updated
     */
    public function setUpdated($updated)
    {
        $this->_updated = $updated ;
    }

    /**
     * @return string
     */
    public function getLastContacted()
    {
        return $this->_lastContacted ;
    }

    /**
     * @param string $lastContacted
     */
    public function setLastContacted($lastContacted)
    {
        $this->_lastContacted = ('' === $lastContacted) ? null : $lastContacted ;
    }
}
