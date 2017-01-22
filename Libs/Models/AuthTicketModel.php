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
 * AuthTicket Model
 */
class AuthTicketModel extends ModelBase {
    /** @var string */
    private $_authTicket ;
    /** @var string */
    private $_created ;
    /** @var string */
    private $_updated ;
    /** @var string */
    private $_expires ;
    
    /**
     * class constructor
     */
    public function __construct() {
        // Stub for now
    }

    /**
     * @return boolean
     */
    public function validateForAdd() {
        return 1 ;
    }

    /**
     * @return boolean
     */
    public function validateForUpdate() {
        return 1 ;
    }

    /**
     * Populate from form data
     */
    public function populateFromForm() {
        // Do nothing for now.
    }

    /**
     * Getter
     *
     * @return string
     */
    public function getAuthTicket() {
        return $this->_authTicket ;
    }

    /**
     * Setter
     *
     * @param string $value
     */
    public function setAuthTicket( $value ) {
        $this->_authTicket = $value ;
    }

    /**
     * Getter
     *
     * @return string
     */
    public function getCreated() {
        return $this->_created ;
    }

    /**
     * Setter
     *
     * @param string $value
     */
    public function setCreated( $value ) {
        $this->_created = $value ;
    }

    /**
     * Getter
     *
     * @return string
     */
    public function getUpdated() {
        return $this->_updated ;
    }

    /**
     * Setter
     *
     * @param string $value
     */
    public function setUpdated( $value ) {
        $this->_updated = $value ;
    }

    /**
     * Getter
     *
     * @return string
     */
    public function getExpires() {
        return $this->_expires ;
    }

    /**
     * Setter
     *
     * @param string $value
     */
    public function setExpires( $value ) {
        $this->_expires = $value ;
    }

}
