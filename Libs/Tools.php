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
namespace com\kbcmdba\pjs2;

/**
 * A series of static methods for re-use.
 */
class Tools
{

    /**
     * Class Constructor - never intended to be used.
     *
     * @throws \Exception
     */
    public function __construct()
    {
        throw new \Exception("Improper use of Tools class");
    }

    /**
     * Return the value from $_REQUEST[ $key ] if available or an empty string.
     *
     * @param String $key
     * @return String
     */
    public static function param($key)
    {
        return (isset($key) && (isset($_REQUEST[$key]))) ? trim($_REQUEST[$key]) : '';
    }

    /**
     * Return the value from $_POST[ $key ] if available or an empty string.
     *
     * @param String $key
     * @return String
     */
    public static function post($key)
    {
        return (isset($key) && (isset($_POST[$key]))) ? $_POST[$key] : '';
    }

    /**
     * Display a table cell but put a non-blank space in it if it's empty or
     * null.
     * Typically, this helps get around empty boxes without lines in
     * browsers that don't properly support styles to make this happen.
     *
     * @param string $x
     * @return boolean
     */
    public static function nonBlankCell($x)
    {
        return (! isset($x) || ($x === '')) ? "&nbsp;" : $x;
    }

    /**
     * Return true when the value passed is either NULL or an empty string ('')
     *
     * @param mixed $x
     * @return boolean
     */
    public static function isNullOrEmptyString($x)
    {
        return ((null === $x) || ('' === $x));
    }

    /**
     * Return true when the value passed is a number
     *
     * @param boolean $x
     */
    public static function isNumeric($x)
    {
        return (isset($x) && preg_match('/^(-|)[0-9]+$/', $x));
    }

    /**
     * Return the MySQL format timestamp value for the given time()
     * value.
     * If epochTime is null, return the current date and time.
     *
     * @param int $epochTime
     *            Seconds since January 1, 1970 at midnight
     * @return string
     */
    public static function currentTimestamp($epochTime = null)
    {
        if (null === $epochTime) {
            $epochTime = time();
        }
        return date('Y-m-d H:i:s', $epochTime);
    }
}
