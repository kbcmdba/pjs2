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
 * Authorization class for AJAX tools
 */
 class AjaxAuth {

    private $_password ;
    private $_userId ;

    public function __construct() {
        session_start() ;
        $config = new Config() ;
        $userId = $config->getUserId() ;
        $password = $config->getUserPassword() ;
        $this->_userId = $userId ;
        $this->_password = $password ;
        // Authentication must happen outside the AJAX call.
        // Don't accept POST variables here.
    }

    /**
     * When a user is already logged in to the tool, return true, false otherwise.
     * 
     * @return boolean
     */
    public function isAuthorized() {
        return  ( isset( $_SESSION[ 'username' ] )
               && isset( $_SESSION[ 'password' ] )
               && ( $this->_userId === $_SESSION[ 'username' ] )
               && ( $this->_password === $_SESSION[ 'password' ] )
                ) ;
    }

}
