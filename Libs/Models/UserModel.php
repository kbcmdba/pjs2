<?php

/**
 * phpjobseeker
 *
 * Copyright (C) 2009, 2015, 2017, 2026 Kevin Benton - kbenton at bentonfam dot org
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
namespace com\kbcmdba\pjs2;

/**
 * User Model
 */
class UserModel extends ModelBase
{

    private $_id;

    private $_userName;

    private $_password;

    private $_role;

    private $_created;

    private $_updated;

    /**
     * class constructor
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Validate model for insert
     *
     * @return boolean
     */
    public function validateForAdd()
    {
        return (! Tools::isNullOrEmptyString($this->_userName))
            && (! Tools::isNullOrEmptyString($this->_password))
            && (! Tools::isNullOrEmptyString($this->_role));
    }

    /**
     * Validate model for update
     *
     * @return boolean
     */
    public function validateForUpdate()
    {
        return ($this->validateId($this->getId()))
            && (! Tools::isNullOrEmptyString($this->_userName))
            && (! Tools::isNullOrEmptyString($this->_role));
    }

    /**
     *
     * @return integer
     */
    public function getId()
    {
        return $this->_id;
    }

    /**
     *
     * @param integer $id
     */
    public function setId($id)
    {
        $this->_id = $id;
    }

    /**
     *
     * @return string
     */
    public function getUserName()
    {
        return $this->_userName;
    }

    /**
     *
     * @param string $userName
     */
    public function setUserName($userName)
    {
        $this->_userName = $userName;
    }

    /**
     *
     * @return string
     */
    public function getPassword()
    {
        return $this->_password;
    }

    /**
     *
     * @param string $password
     */
    public function setPassword($password)
    {
        $this->_password = $password;
    }

    /**
     *
     * @return string
     */
    public function getRole()
    {
        return $this->_role;
    }

    /**
     *
     * @param string $role
     */
    public function setRole($role)
    {
        $this->_role = $role;
    }

    /**
     *
     * @return string
     */
    public function getCreated()
    {
        return $this->_created;
    }

    /**
     *
     * @param string $created
     */
    public function setCreated($created)
    {
        $this->_created = $created;
    }

    /**
     *
     * @return string
     */
    public function getUpdated()
    {
        return $this->_updated;
    }

    /**
     *
     * @param string $updated
     */
    public function setUpdated($updated)
    {
        $this->_updated = $updated;
    }
}
