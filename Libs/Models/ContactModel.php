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
 * Contact Model
 */
class ContactModel extends ModelBase
{
    private $_id ;
    private $_contactCompanyId ;
    private $_contactName ;
    private $_contactEmail ;
    private $_contactPhone ;
    private $_contactAlternatePhone ;
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
        return  ((Tools::isNullOrEmptyString(Tools::param('id')))
               && (! Tools::isNullOrEmptyString(Tools::param('contactName')))
               && (! Tools::isNullOrEmptyString(Tools::param('contactEmail')))
               && (! Tools::isNullOrEmptyString(Tools::param('contactPhone')))
                ) ;
    }

    /**
     * Validate model for update
     *
     * @return boolean
     */
    public function validateForUpdate()
    {
        return  ((! Tools::isNullOrEmptyString(Tools::param('id')))
               && (! Tools::isNullOrEmptyString(Tools::param('contactName')))
               && (! Tools::isNullOrEmptyString(Tools::param('contactEmail')))
               && (! Tools::isNullOrEmptyString(Tools::param('contactPhone')))
                ) ;
    }

    public function populateFromForm()
    {
        $this->setId(Tools::param('id')) ;
        $this->setContactCompanyId(Tools::param('companyId')) ;
        $this->setContactName(Tools::param('contactName')) ;
        $this->setContactEmail(Tools::param('contactEmail')) ;
        $this->setContactPhone(Tools::param('contactPhone')) ;
        $this->setContactAlternatePhone(Tools::param('contactAlternatePhone')) ;
        $this->setCreated(Tools::param('created')) ;
        $this->setUpdated(Tools::param('updated')) ;
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
    public function getContactCompanyId()
    {
        return $this->_contactCompanyId ;
    }

    /**
     * @param integer $contactCompanyId
     */
    public function setContactCompanyId($contactCompanyId)
    {
        if ('' === $contactCompanyId) {
            $contactCompanyId = null ;
        }
        $this->_contactCompanyId = $contactCompanyId ;
    }

    /**
     * @return string
     */
    public function getContactName()
    {
        return $this->_contactName ;
    }

    /**
     * @param string $contactName
     */
    public function setContactName($contactName)
    {
        $this->_contactName = $contactName ;
    }

    /**
     * @return string
     */
    public function getContactEmail()
    {
        return $this->_contactEmail ;
    }

    /**
     * @param string $contactEmail
     */
    public function setContactEmail($contactEmail)
    {
        $this->_contactEmail = $contactEmail ;
    }

    /**
     * @return string
     */
    public function getContactPhone()
    {
        return $this->_contactPhone ;
    }

    /**
     * @param string $contactPhone
     */
    public function setContactPhone($contactPhone)
    {
        $this->_contactPhone = $contactPhone ;
    }

    /**
     * @return string
     */
    public function getContactAlternatePhone()
    {
        return $this->_contactAlternatePhone ;
    }

    /**
     * @param string $contactAlternatePhone
     */
    public function setContactAlternatePhone($contactAlternatePhone)
    {
        $this->_contactAlternatePhone = $contactAlternatePhone ;
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
}
