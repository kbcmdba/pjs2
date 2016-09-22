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

    private $_auth ;
    private $_resetOk ;

    /**
     * class constructor
     *
     * @param string
     */
    public function __construct( $title = '', $skipAuth = false ) {
        parent::__construct( $title ) ;
        $this->_skipAuth = $skipAuth ;
        if ( ! $skipAuth ) {
            $auth = new Auth() ;
            $this->_auth = $auth ;
        }
        $config = new Config() ;
        $this->_resetOk = $config->getResetOk() ;
        $header = <<<HTML
  <link rel="stylesheet" href="css/main.css" />
  <link rel="stylesheet" href="//code.jquery.com/ui/1.11.4/themes/smoothness/jquery-ui.css" />
  <script src="//code.jquery.com/jquery-1.10.2.js"></script>
  <script src="//code.jquery.com/ui/1.11.4/jquery-ui.js"></script>
  <script>
    $( function() {
      $( ".datepicker" ).datepicker( { dateFormat: 'yy-mm-dd' } );
    } ) ;
  </script>
  <script src="js/main.js"></script>
HTML;
        $this->setHead( $header ) ;
        $this->setMeta( array( "Cache-Control: no-cache, must-revalidate"
                             , "Expires: Sat, 26 Oct 2013 05:00:00 GMT"
                             , 'Last-Modified: ' . gmdate( 'D, d M Y H:i:s' ) . ' GMT'
                             ) ) ;
        $this->setStyles( '' ) ;
        $this->setTop( $this->_getTop() ) ;
        $this->setBottom( '<!-- EndOfPage -->' ) ;

        if ( ( ! $skipAuth ) && ( ! $auth->isAuthorized() ) ) {
            $this->setBody( $auth->getLoginPage() ) ;
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
        $logout = '' ;
        if ( ( ! $this->_skipAuth ) && ( $this->_auth->isAuthorized() ) ) {
            $logout  = '  <li><a href="logout.php">Log Out</a></li>' ;
        }
        $reset = ( $this->_resetOk ) ? "  <li><a href=\"resetDb.php\">Reset Database</a></li>" : "" ;
        $html = <<<HTML
<ul id="navBar">
  <li><a href="index.php">Summary</a></li>
  <li><a href="applicationStatuses.php">Application Statuses</a></li>
  <li><a href="companies.php">Companies</a></li>
  <li><a href="contacts.php">Contacts</a></li>
  <li><a href="jobs.php">Jobs</a></li>
  <li><a href="keywords.php">Keywords</a></li>
  <li><a href="searches.php">Searches</a></li>
  <ul style="float: right; list-style-type: none;">
$reset
$logout
  </ul>
</ul>
<p />
HTML;
        return $html ;
        // | <a href=""></a>
    }

}
