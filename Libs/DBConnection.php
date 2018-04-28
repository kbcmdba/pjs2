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
 * DBConnection
 */
class DBConnection
{

    /** DB connection Handle */
    private $_dbh ;
    /** Configuration data */
    private $_oConfig ;
    /** ConnectionType */
    private $_connectionClass ;
    /** When the database is created during construction, this is set to true, false otherwise */
    private $_createdDb ;

    /**
     * Class Constructor
     *
     * @param String  $connType
     * @param String  $dbHost
     * @param String  $dbName
     * @param String  $dbUser
     * @param String  $dbPass
     * @param Integer $dbPort
     * @param String  $connClass Must be 'mysql', 'mysqli', or 'PDO' for now.
     * @return void
     * @throws \Exception
     * @SuppressWarnings indentation
     * @SuppressWarnings cyclomaticComplexity
     */
    public function __construct(
        $connType = null,
        $dbHost = null,
        $dbName = null,
        $dbUser = null,
        $dbPass = null,
        $dbPort = null,
        $connClass = 'mysqli',
        $createDb = false
                               ) {
        $oConfig = new Config(
            $connType,
            $dbHost,
            $dbPort,
            $dbName,
            $dbUser,
            $dbPass
                             ) ;
        $this->_oConfig = $oConfig ;
        switch ($connClass) {
            case 'mysql':
                $this->_dbh = mysql_connect(
                    $oConfig->getDbHost()
                                           . ':'
                                           . $oConfig->getDbPort(),
                    $oConfig->getDbUser(),
                    $oConfig->getDbPass()
                                           ) ;
                if (! $this->_dbh) {
                    throw new \Exception('Error connecting to database server(' . $oConfig->getDbHost() . ')! : ' . mysql_error()) ;
                }
                $dbName = Tools::coalesce([ $oConfig->getDbName(), '' ]) ;
                if ($dbName !== '') {
                    if (! mysql_select_db($dbName, $this->_dbh)) {
                        throw new \Exception('Database does not exist: ', $dbName) ;
                    }
                }
                break ;
            case 'mysqli':
                $mysqli = mysqli_init() ;
                if (! $mysqli) {
                    throw new DaoException("Failed to allocate connection class!") ;
                }
                if (! $mysqli->options(MYSQLI_OPT_CONNECT_TIMEOUT, 2)) {
                    throw new DaoException('Failed setting connection timeout.') ;
                }
                $result = $mysqli->real_connect(
                    $oConfig->getDbHost(),
                    $oConfig->getDbUser(),
                    $oConfig->getDbPass(),
                    null,
                    $oConfig->getDbPort()
                                               ) ;
                if ((! $result) || ($mysqli->connect_errno)) {
                    throw new DaoException('Error connecting to database server(' . $oConfig->getDbHost() . ')! : ' . $mysqli->connect_error) ;
                }
                $this->_dbh = $mysqli ;
                if ($this->_dbh->connect_error) {
                    throw new DaoException('Error connecting to database server(' . $oConfig->getDbHost() . ')! : ' . $this->_dbh->connect_error) ;
                }
                $this->_dbh->query("SET @@SESSION.SQL_MODE = 'ALLOW_INVALID_DATES'") ;
                if (! $mysqli->select_db($oConfig->getDbName())) {
                    if ($createDb) {
                        $this->_createdDb = true ;
                        $this->_dbh->query("CREATE DATABASE IF NOT EXISTS " . $oConfig->getDbName()) ;
                        if (! $mysqli->select_db($oConfig->getDbName())) {
                            throw new DaoException("Database: {$oConfig->getDbName()} is missing. Please use resetDb.php to install the database.") ;
                        }
                    } else {
                        throw new DaoException("Database: {$oConfig->getDbName()} is missing. Please use resetDb.php to install the database.") ;
                    }
                }
                break ;
            case 'PDO':
                // May throw PDOException by itself.
                $this->_dbh = new \PDO(
                    $oConfig->get_dsn(),
                    $oConfig->getDbPass()
                                     ) ;
                if (! $this->_dbh) {
                    throw new DaoException('Error connecting to database server(' . $oConfig->getDbHost() . ')!') ;
                }
                break ;
            default:
                throw new DaoException('Unknown connection class: ' . $connClass) ;
        } // END OF switch ( $connClass )
        $this->_connectionClass = $connClass ;
    }

    /**
     * __toString
     *
     * @return string
     */
    public function __toString()
    {
        if (isset($this->_dbh)) {
            return $oConfig ;
        } else {
            return "Not connected." ;
        }
    }

    /**
     * Give back the database handle
     *
     * @return mixed
     * @throws \Exception
     */
    public function getConnection()
    {
        if ((! isset($this->_dbh)) || (! ($this->_dbh))) {
            throw new \Exception('Invalid connection!') ;
        } else {
            return $this->_dbh ;
        }
    }

    /**
     * Give back the connection class passed to the constructor.
     *
     * @return mixed
     */
    public function getConnectionClass()
    {
        return $this->_connectionClass ;
    }

    /**
     * @return boolean
     */
    public function getCreatedDb()
    {
        return $this->_createdDb ;
    }
}
