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
 * Controller Base
 */
abstract class ControllerBase
{

    /** @var mysqli */
    protected $_dbh ;

    /**
     * Class constructor
     *
     * @param string $readWriteMode "read", "write" or "admin"
     * @throws ControllerException
     */
    public function __construct($readWriteMode = 'write')
    {
        try {
            $dbc = new DBConnection($readWriteMode) ;
            $this->_dbh = $dbc->getConnection() ;
            $this->_dbh->autocommit(true) ;
        } catch (Exception $e) {
            throw new ControllerException('Problem connecting to database: ' . $this->_dbh->error) ;
        }
    }

    /**
     * Execute a DDL statement
     *
     * @param string $sql
     * @throws ControllerException
     */
    protected function _doDDL($sql)
    {
        try {
            if (! $this->_dbh->query($sql)) {
                throw new ControllerException($sql) ;
            }
        } catch (Exception $e) {
            throw new ControllerException("Failed to execute DDL: " . $this->_dbh->error) ;
        }
    }

    /**
     * Delete model by id.
     *
     * @param ModelBase $model
     * @throws ControllerException
     */
    protected function _deleteModelById($sql, $model)
    {
        if ($model->validateForDelete()) {
            $stmt = $this->_dbh->prepare($sql) ;
            if (!$stmt) {
                throw new ControllerException('Prepared statement failed for ' . $sql) ;
            }
            $id = $model->getId() ;
            if (! $stmt->bind_param('i', $id)) {
                throw new ControllerException('Binding parameters for prepared statement failed.') ;
            }
            if (!$stmt->execute()) {
                throw new ControllerException('Failed to execute DELETE statement. (' . $this->_dbh->error . ')') ;
            }
            /**
             * @SuppressWarnings checkAliases
             */
            if (!$stmt->close()) {
                throw new ControllerException('Something broke while trying to close the prepared statement.') ;
            }
            return ;
        } else {
            throw new ControllerException('Invalid data') ;
        }
    }

    /**
     * @param mixed $ts Unix seconds since epoch or null for "now"
     * @return string
     */
    public static function timestamp($ts = null)
    {
        if ($ts === null) {
            $ts = time() ;
        } elseif (! Tools::isNumeric($ts)) {
            throw new ControllerException('Invalid timestamp: ' . $ts) ;
        }
        return(date("Y-m-d H:i:s", $ts)) ;
    }

    /**
     * @param mixed $ts Unix seconds since epoch or null for "now"
     * @return string
     */
    public static function datestamp($ts = null)
    {
        if ($ts === null) {
            $ts = time() ;
        } elseif (! Tools::isNumeric($ts)) {
            throw new ControllerException('Invalid timestamp: ' . $ts) ;
        }
        return(date("Y-m-d", $ts)) ;
    }

    /**
     * @return string Equivalent to MySQL NOW() function
     */
    public static function now()
    {
        return self::timestamp(time()) ;
    }

    /**
     * @return string Equivalent to MySQL TODAY() function
     */
    public static function today()
    {
        return self::datestamp() ;
    }

    /**
     * @return string Equivalent to MySQL TOMORROW() function
     */
    public static function tomorrow()
    {
        return self::datestamp(time() + 86400) ;
    }

    /**
     * @return string Equivalent to MySQL YESTERDAY() function
     */
    public static function yesterday()
    {
        return self::datestamp(time() - 86400) ;
    }
}
