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

namespace com\kbcmdba\pjs2 ;

/**
 * _User Model
 */
class UserModel extends ModelBase
{
    private $_userName ;
    private $_password ;
    private $_pSalt ;
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
               && (! Tools::isNullOrEmptyString(Tools::param('userName')))
               && (! Tools::isNullOrEmptyString(Tools::param('password')))
               && (! Tools::isNullOrEmptyString(Tools::param('pSalt')))
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
               && (Tools::isNumeric(Tools::param('id')))
               && (! Tools::isNullOrEmptyString(Tools::param('userName')))
               && (! Tools::isNullOrEmptyString(Tools::param('password')))
               && (! Tools::isNullOrEmptyString(Tools::param('pSalt')))
                ) ;
    }

    public function populateFromForm()
    {
        $this->setUserName(Tools::param('userName')) ;
        $this->setPassword(Tools::param('password')) ;
        $this->setPSalt(Tools::param('pSalt')) ;
        $this->setCreated(Tools::param('created')) ;
        $this->setUpdated(Tools::param('updated')) ;
    }

    /**
     * @return string
     */
    public function getUserName()
    {
        return $this->_userName ;
    }

    /**
     * @param string $userName
     */
    public function setUserName($userName)
    {
        $this->_userName = $userName ;
    }

    /**
     * @return string
     */
    public function getPassword()
    {
        return $this->_password ;
    }

    /**
     * @param string $password
     */
    public function setPassword($password)
    {
        $this->_password = $password ;
    }

    /**
     * @return string
     */
    public function getPSalt()
    {
        return $this->_pSalt ;
    }

    /**
     * @param string $pSalt
     */
    public function setPSalt($pSalt)
    {
        $this->_pSalt = $pSalt ;
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
