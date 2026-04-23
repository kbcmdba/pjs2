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
     * $oConfig = new Config() ;
     *
     * Getting configuration data:
     * $dbh = mysql_connect( $oConfig->getDbHost() . ':' . $oConfig->getDbPort()
     * , $oConfig->getDbUser()
     * , $oConfig->getDbPass()
     * ) ;
     * $dbh->mysql_select_db( $oConfig->getDbName() ) ;
     */
    
    // This is sub-optimal but it works for now.
    
    /** @var integer */
    private $_authTimeoutSeconds = 3600;

    /**
     * #@+
     *
     * @var string
     */
    private $_dbHost = null;

    private $_dbPort = null;

    private $_dbUser = null;

    private $_dbPass = null;

    private $_dbName = null;

    private $_title = null;

    private $_timeZone = null;

    private $_userId = null;

    private $_userPassword = null;

    /**
     * #@-
     */
    
    /** @var boolean */
    private $_resetOk = false;

    private $_skipAuth = false;

    private $_apiKey = '';

    /**
     * Class Constructor
     *
     * @throws \Exception
     * @SuppressWarnings indentation
     */
    public function __construct()
    {
        $cfgValues = $this->_loadConfig();
        $this->_authTimeoutSeconds = $cfgValues['authTimeoutSeconds'];
        $this->_dbHost = $cfgValues['dbHost'];
        $this->_dbPort = $cfgValues['dbPort'];
        $this->_dbName = $cfgValues['dbName'];
        $this->_dbUser = $cfgValues['dbUser'];
        $this->_dbPass = $cfgValues['dbPass'];
        $this->_title = $cfgValues['title'];
        $this->_timeZone = $cfgValues['timeZone'];
        $this->_resetOk = $cfgValues['resetOk'];
        $this->_skipAuth = $cfgValues['skipAuth'];
        $this->_apiKey = $cfgValues['apiKey'];
        $this->_userId = $cfgValues['userId'];
        $this->_userPassword = $cfgValues['userPassword'];
        ini_set('date.timezone', $this->_timeZone);
    }

    /**
     * Load configuration from config.php (preferred) or config.xml (legacy).
     *
     * config.php is preferred because PHP files cannot be served raw by any
     * web server, preventing accidental exposure of credentials.
     *
     * @return array Configuration values
     * @throws \Exception on missing or invalid config
     */
    private function _loadConfig()
    {
        $defaults = [
            'resetOk' => 0,
            'authTimeoutSeconds' => 3600,
            'skipAuth' => 0,
            'apiKey' => ''
        ];
        $required = [
            'dbHost', 'dbPass', 'dbName', 'dbPort', 'dbUser',
            'timeZone', 'title', 'userId', 'userPassword'
        ];

        if (is_readable('config.php')) {
            return $this->_loadPhpConfig($defaults, $required);
        } elseif (is_readable('config.xml')) {
            return $this->_loadXmlConfig($defaults, $required);
        } else {
            throw new \Exception(
                "No configuration file found!\n"
                . "Copy config_sample.php to config.php and update with your settings."
            );
        }
    }

    /**
     * Load configuration from config.php (returns an array).
     *
     * @param array $defaults Default values for optional params
     * @param array $required List of required parameter names
     * @return array
     * @throws \Exception
     */
    private function _loadPhpConfig($defaults, $required)
    {
        $cfgValues = require 'config.php';
        if (! is_array($cfgValues)) {
            throw new \Exception("config.php must return an array!");
        }
        $cfgValues = array_merge($defaults, $cfgValues);
        $errors = '';
        foreach ($required as $key) {
            if (! isset($cfgValues[$key]) || $cfgValues[$key] === '') {
                $errors .= "Missing parameter: $key\n";
            }
        }
        if ($errors !== '') {
            throw new \Exception("\nConfiguration problem!\n\n" . $errors . "\n");
        }
        return $cfgValues;
    }

    /**
     * Load configuration from config.xml (legacy format).
     *
     * @param array $defaults Default values for optional params
     * @param array $required List of required parameter names
     * @return array
     * @throws \Exception
     */
    private function _loadXmlConfig($defaults, $required)
    {
        $xml = simplexml_load_file('config.xml');
        if (! $xml) {
            throw new \Exception("Invalid syntax in config.xml!");
        }
        $cfgValues = $defaults;
        $seen = [];
        foreach ($xml as $v) {
            $key = (string) $v['name'];
            if (isset($seen[$key])) {
                throw new \Exception("Multiply set parameter: $key\n");
            }
            $seen[$key] = true;
            $cfgValues[$key] = (string) $v;
        }
        $errors = '';
        foreach ($required as $key) {
            if (! isset($cfgValues[$key]) || $cfgValues[$key] === '') {
                $errors .= "Missing parameter: $key\n";
            }
        }
        if ($errors !== '') {
            throw new \Exception("\nConfiguration problem!\n\n" . $errors . "\n");
        }
        return $cfgValues;
    }

    /**
     * Another magic method...
     *
     * @return string
     */
    public function __toString()
    {
        return "Config::__toString not implemented";
    }

    /**
     * Getter
     *
     * @return integer
     */
    public function getAuthTimeoutSeconds()
    {
        return $this->_authTimeoutSeconds;
    }

    /**
     * Getter
     *
     * @return string
     */
    public function getDbHost()
    {
        return $this->_dbHost;
    }

    /**
     * Getter
     *
     * @return string
     */
    public function getDbPort()
    {
        return $this->_dbPort;
    }

    /**
     * Getter
     *
     * @return string
     */
    public function getDbUser()
    {
        return $this->_dbUser;
    }

    /**
     * Getter
     *
     * @return string
     */
    public function getDbPass()
    {
        return $this->_dbPass;
    }

    /**
     * Getter
     *
     * @return string
     */
    public function getDbName()
    {
        return $this->_dbName;
    }

    /**
     * Getter
     *
     * @return string
     */
    public function getTitle()
    {
        return $this->_title;
    }

    /**
     * Getter
     *
     * @return string
     */
    public function getTimeZone()
    {
        return $this->_timeZone;
    }

    /**
     * Getter
     *
     * @return boolean
     */
    public function getResetOk()
    {
        return $this->_resetOk;
    }

    /**
     * Getter
     *
     * @return boolean
     */
    public function getSkipAuth()
    {
        return $this->_skipAuth;
    }

    /**
     * Return the DSN for this connection
     *
     * @param
     *            string
     * @return string
     * @SuppressWarnings indentation
     */
    public function getDsn($dbType = 'mysql')
    {
        return $this->_dsn = $dbType . ':host=' . $oConfig->getDbHost() . ':' . $oConfig->getDbPort() . ';dbname=' . $oConfig->getDbName();
    }

    public function getUserId()
    {
        return $this->_userId;
    }

    public function getUserPassword()
    {
        return $this->_userPassword;
    }

    /**
     * Getter
     *
     * @return string
     */
    public function getApiKey()
    {
        return $this->_apiKey;
    }
}
