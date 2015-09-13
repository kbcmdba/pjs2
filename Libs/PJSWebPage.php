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
 * Customized web page class
 */
class PJSWebPage extends WebPage {

    private $_userId ;
    private $_password ;
    private $_resetOk ;

    /**
     * class constructor
     *
     * @param string
     */
    public function __construct( $title = '' ) {
        parent::__construct( $title ) ;
        session_start() ;
        $config = new Config() ;
        $userId = $config->getUserId() ;
        $password = $config->getUserPassword() ;
        $this->_userId = $userId ;
        $this->_password = $password ;
        $this->_resetOk = $config->getResetOk() ;
        if ( isset( $_POST[ 'username' ] )
                && isset( $_POST[ 'password' ] )
        ) {
            $_SESSION[ 'username' ] = $_REQUEST[ 'username' ] ;
            $_SESSION[ 'password' ] = $_REQUEST[ 'password' ] ;
        }
        $header = <<<HTML
  <link rel="stylesheet" href="//code.jquery.com/ui/1.11.4/themes/smoothness/jquery-ui.css">
  <script src="//code.jquery.com/jquery-1.10.2.js"></script>
  <script src="//code.jquery.com/ui/1.11.4/jquery-ui.js"></script>
  <script>
    $( function() {
      $( ".datepicker" ).datepicker( { dateFormat: 'yy-mm-dd' } );
    } ) ;
  </script>
HTML;
        $this->setHead( $header ) ;
        $this->setMeta( array( "Cache-Control: no-cache, must-revalidate"
                             , "Expires: Sat, 26 Oct 2013 05:00:00 GMT"
                             , 'Last-Modified: ' . gmdate( 'D, d M Y H:i:s' ) . ' GMT'
                             ) ) ;
        $this->setStyles( '' ) ;
        $this->setTop( $this->_getTop() ) ;
        $this->setBottom( '<!-- EndOfPage -->' ) ;

        if ( ! isset( $_SESSION[ 'username' ] ) 
          || ! isset( $_SESSION[ 'password' ] )
          || ( $userId !== $_SESSION[ 'username' ] )
          || ( $password !== $_SESSION[ 'password' ] )
           ) {
            $body = <<<HTML
<form action="index.php" method="POST">
Login Page
  <table>
    <tr>
      <th>Login</th>
      <td><input type="text" name="username" /></td>
    </tr>
    <tr>
      <th>Password</th>
      <td><input type="password" name="password" /></td>
    </tr>
    <tr>
      <th colspan="2"><input type="submit" value="Log In" /></td>
    </tr>
  </table>
</form>
HTML;
            $this->setBody( $body ) ;
            $this->displayPage() ;
            exit ;
        }
    }

    /**
     * Get the "top" of the page
     *
     * @return string
     */
    private function _getTop() {
        $userId = $this->_userId ;
        $password = $this->_password ;
        $logout = '' ;
        if  ( isset( $_SESSION[ 'username' ] )
           && isset( $_SESSION[ 'password' ] )
           && ( $userId === $_SESSION[ 'username' ] )
           && ( $password === $_SESSION[ 'password' ] )
            ) {
            $logout  = '| <a href="logout.php">Log Out</a>' ;
        }
        $reset = ( $this->_resetOk ) ? "| <a href=\"resetDb.php\">Reset Database</a>" : "" ;
        $html = <<<HTML
<a href="index.php">Summary</a>
| <a href="applicationStatuses.php">Application Statuses</a>
| <a href="companies.php">Companies</a>
$reset
$logout
<p />
HTML;
        return $html ;
        // | <a href=""></a>
    }

}
