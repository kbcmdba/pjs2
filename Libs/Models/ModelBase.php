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
 * Model Base class
 */
abstract class ModelBase {
    /**#@+
     * @return boolean
     */
    /**
     * Row addition validator
     */
    abstract public function validateForAdd() ;
    /**
     * Row update validator
     */
    abstract public function validateForUpdate() ;

    /**
     * Validate model for delete
     *
     * @return boolean
     */
    public function validateForDelete() {
        return ( $this->validateId( $this->getId() ) ) ;
    }

    /**
     * Validate a numeric ID
     *
     * @param string
     */
    public function validateId( $id ) {
        return ( 1 === preg_match( '/^[1-9]([0-9]*)$/', $id ) ) ;
    }

    /**
     * Validate a date
     *
     * @param string
     */
    public function validateDate( $date ) {
        return ( 1 === preg_match( '/^20[123][0-9]-(0[1-9]|1[0-2])-(0[1-9]|[1-2][0-9]|3[01])$/', $date ) ) ;
    }

    /**
     * Validate a timestamp
     *
     * @param string
     */
    public function validateTimestamp( $timestamp ) {
        return ( 1 === preg_match( '/^20[123][0-9]-(0[1-9]|1[0-2])-(0[1-9]|[1-2][0-9]|3[01]) ([0-5][0-9][:]){2}[0-5][0-9]$/', $timestamp ) ) ;
    }
    /**#@-*/

    /**
     * Stub method
     */
    public function __construct() {
        // Do nothing for now
    }

}
