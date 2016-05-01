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
 * User Authorization Class
 * 
 * Note: This is *NOT* secure enough to use over a public internet connection.
 * At this point, it is a stub to be improved at a future time.
 */
class Auth {
    private $_userId ;
    private $_password ;

    public function __construct() {
        session_start() ;
        $config = new Config() ;
        $this->_userId = $config->getUserId() ;
        $this->_password = $config->getUserPassword() ;
        if  ( isset( $_POST[ 'auth_username' ] )
           && isset( $_POST[ 'auth_password' ] )
            ) {
            $_SESSION[ 'auth_username' ] = $_REQUEST[ 'auth_username' ] ;
            $_SESSION[ 'auth_password' ] = $_REQUEST[ 'auth_password' ] ;
        }
    }

    /**
     * Checks to make sure that the user's session is logged in.
     * 
     * @return boolean
     */
    public function isAuthorized() {
        return  ( isset( $_SESSION[ 'auth_username' ] )
               && isset( $_SESSION[ 'auth_password' ] )
               && ( $this->_userId === $_SESSION[ 'auth_username' ] )
               && ( $this->_password === $_SESSION[ 'auth_password' ] )
                ) ;
    }

    /**
     * Sends HTTP 403 error code, Forbidden error message and exits from the program.
     */
    public function forbidden() {
        header( "HTTP/1.0 403 Forbidden" ) ;
        echo "Forbidden" ;
        exit ;
    }

    /**
     * Get the login page string.
     */
    public function getLoginPage() {
        $body = <<<HTML
<form action="index.php" method="POST">
Login Page
  <table>
    <tr>
      <th>Login</th>
      <td><input type="text" name="auth_username" /></td>
    </tr>
    <tr>
      <th>Password</th>
      <td><input type="password" name="auth_password" /></td>
    </tr>
    <tr>
      <th colspan="2"><input type="submit" value="Log In" /></td>
    </tr>
  </table>
</form>
HTML;
        return( $body ) ;
    } // END OF function loginPage()

    /**
     * Destroy session variables that keep the user logged in.
     */
    public function doLogOut() {
        session_start() ;
        if ( isset( $_SESSION[ 'auth_username' ] ) ) {
            unset( $_SESSION[ 'auth_username' ] ) ;
        }
        if ( isset( $_SESSION[ 'auth_password' ] ) ) {
            unset( $_SESSION[ 'auth_password' ] ) ;
        }
    }

}
