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
 * Configuration for this tool set
 */
class Config
{

    /**
     * Configuration Class
     *
     * Requires formatted config.xml (see config_sample.xml)
     *
     * Usage Examples:
     *
     * Constructor:
     *   $oConfig = new Config() ;
     *
     * Getting configuration data:
     *   $dbh = mysql_connect( $oConfig->getDbHost() . ':' . $oConfig->getDbPort()
     *                       , $oConfig->getDbUser()
     *                       , $oConfig->getDbPass()
     *                       ) ;
     *   $dbh->mysql_select_db( $oConfig->getDbName() ) ;
     *
     */

    // This is sub-optimal but it works for now.

    /** @var integer */
    private $_authTimeoutSeconds = 3600 ;

    /**#@+
     * @var string
     */
    private $_dbHost       = null ;
    private $_dbPort       = null ;
    private $_dbUser       = null ;
    private $_dbPass       = null ;
    private $_dbName       = null ;
    private $_title        = null ;
    private $_timeZone     = null ;
    private $_userId       = null ;
    private $_userPassword = null ;
    /**#@-*/

    /** @var boolean */
    private $_resetOk  = false ;
    private $_skipAuth = false ;

    /**
     * Class Constructor
     *
     * @throws Exception
     * @SuppressWarnings indentation
     */
    public function __construct()
    {
        if (! is_readable('config.xml')) {
            throw new Exception("Unable to load configuration from config.xml!") ;
        }
        $xml = simplexml_load_file('config.xml') ;
        if (! $xml) {
            throw new Exception("Invalid syntax in config.xml!") ;
        }
        $errors = "" ;
        $cfgValues = [ 'resetOk'            => 0
                          , 'authTimeoutSeconds' => 3600
                          , 'skipAuth'           => 0
                          ] ;
        $paramList = [ 'authTimeoutSeconds' => [ 'isRequired' => 0, 'value' => 0 ]
                          , 'dbHost'             => [ 'isRequired' => 1, 'value' => 0 ]
                          , 'dbPass'             => [ 'isRequired' => 1, 'value' => 0 ]
                          , 'dbName'             => [ 'isRequired' => 1, 'value' => 0 ]
                          , 'dbPort'             => [ 'isRequired' => 1, 'value' => 0 ]
                          , 'dbUser'             => [ 'isRequired' => 1, 'value' => 0 ]
                          , 'resetOk'            => [ 'isRequired' => 0, 'value' => 0 ]
                          , 'skipAuth'           => [ 'isRequired' => 0, 'value' => 0 ]
                          , 'timeZone'           => [ 'isRequired' => 1, 'value' => 0 ]
                          , 'title'              => [ 'isRequired' => 1, 'value' => 0 ]
                          , 'userId'             => [ 'isRequired' => 1, 'value' => 0 ]
                          , 'userPassword'       => [ 'isRequired' => 1, 'value' => 0 ]
                          ] ;
        // verify that all the parameters are present and just once.
        foreach ($xml as $v) {
            $key = ( string ) $v[ 'name' ] ;
            if ((! isset($paramList[ $key ]))
               || ($paramList[ $key ][ 'value' ] != 0)) {
                $errors .= "Unset or multiply set name: " . $key . "\n" ;
            } else {
                $paramList[ $key ][ 'value' ] ++ ;
                $cfgValues[ $key ] = ( string ) $v ;
            }
        }
        foreach ($paramList as $key => $x) {
            if ((1 === $x[ 'isRequired' ]) && (0 === $x[ 'value' ])) {
                $errors .= "Missing parameter: " . $key . "\n" ;
            }
        }
        if ($errors !== '') {
            throw new Exception("\nConfiguration problem!\n\n" . $errors . "\n") ;
        }
        $this->_authTimeoutSeconds = $cfgValues[ 'authTimeoutSeconds' ] ;
        $this->_dbHost             = $cfgValues[ 'dbHost'       ] ;
        $this->_dbPort             = $cfgValues[ 'dbPort'       ] ;
        $this->_dbName             = $cfgValues[ 'dbName'       ] ;
        $this->_dbUser             = $cfgValues[ 'dbUser'       ] ;
        $this->_dbPass             = $cfgValues[ 'dbPass'       ] ;
        $this->_title              = $cfgValues[ 'title'        ] ;
        $this->_timeZone           = $cfgValues[ 'timeZone'     ] ;
        $this->_resetOk            = $cfgValues[ 'resetOk'      ] ;
        $this->_skipAuth           = $cfgValues[ 'skipAuth'           ] ;
        $this->_userId             = $cfgValues[ 'userId'       ] ;
        $this->_userPassword       = $cfgValues[ 'userPassword' ] ;
        ini_set('date.timezone', $this->_timeZone) ;
    }

    /**
     * Another magic method...
     *
     * @return string
     */
    public function __toString()
    {
        return "Config::__toString not implemented" ;
    }

    /**
     * Getter
     *
     * @return integer
     */
    public function getAuthTimeoutSeconds()
    {
        return $this->_authTimeoutSeconds ;
    }

    /**
     * Getter
     *
     * @return string
     */
    public function getDbHost()
    {
        return $this->_dbHost ;
    }

    /**
     * Getter
     *
     * @return string
     */
    public function getDbPort()
    {
        return $this->_dbPort ;
    }

    /**
     * Getter
     *
     * @return string
     */
    public function getDbUser()
    {
        return $this->_dbUser ;
    }

    /**
     * Getter
     *
     * @return string
     */
    public function getDbPass()
    {
        return $this->_dbPass ;
    }

    /**
     * Getter
     *
     * @return string
     */
    public function getDbName()
    {
        return $this->_dbName ;
    }

    /**
     * Getter
     *
     * @return string
     */
    public function getTitle()
    {
        return $this->_title ;
    }

    /**
     * Getter
     *
     * @return string
     */
    public function getTimeZone()
    {
        return $this->_timeZone ;
    }

    /**
     * Getter
     *
     * @return boolean
     */
    public function getResetOk()
    {
        return $this->_resetOk ;
    }

    /**
     * Getter
     *
     * @return boolean
     */
    public function getSkipAuth()
    {
        return $this->_skipAuth ;
    }

    /**
     * Return the DSN for this connection
     *
     * @param string
     * @return string
     * @SuppressWarnings indentation
     */
    public function getDsn($dbType = 'mysql')
    {
        return $this->_dsn = $dbType
                           . ':host='
                           . $oConfig->getDbHost()
                           . ':'
                           . $oConfig->getDbPort()
                           . ';dbname='
                           . $oConfig->getDbName()
                           ;
    }

    public function getUserId()
    {
        return $this->_userId ;
    }

    public function getUserPassword()
    {
        return $this->_userPassword ;
    }
}
