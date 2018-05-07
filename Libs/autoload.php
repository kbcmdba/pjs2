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

spl_autoload_register( function ($className)
{
    $className = str_replace('com\\kbcmdba\\pjs2\\', '', $className);
    switch (true) {
        case (preg_match('/^ControllerBase$/', $className)
            || preg_match('/Controller(|Interface)$/', $className)
             ):
            $reqFile = 'Libs/Controllers/' . $className . '.php' ;
            break ;
        case (preg_match('/Exception$/', $className)):
            $reqFile = 'Libs/Exceptions/' . $className . '.php' ;
            break ;
        case (preg_match('/^ModelBase$/', $className)
            || preg_match('/Model(|Interface)$/', $className)
             ):
            $reqFile = 'Libs/Models/' . $className . '.php' ;
            break ;
        case (preg_match('/ViewBase$/', $className)
            || preg_match('/View(|Interface)$/', $className)
             ):
            $reqFile = 'Libs/Views/' . $className . '.php' ;
            break ;
        case (preg_match('/^TL(Methods|)_/', $className)):
            $reqFile = 'Tests/TestLibs/' . $className . '.php' ;
            break ;
        case (preg_match('/^Framework(Methods|)/', $className)):
            $className = str_replace('_', '/', $className) ;
            $reqFile = 'Tests/' . $className . '.php' ;
            break ;
        default:
            $reqFile = 'Libs/' . $className . '.php' ;
            break ;
    } // END OF switch ( true )
    if (! is_file($reqFile)) {
        echo 'Class file ' . $reqFile . ' does not exist.' ;
        exit(1) ;
    }
    try {
        require_once $reqFile ;
    } catch (\Exception $e) {
        echo 'class_file was ' . $className . ', req_file=' . $reqFile . "\n" ;
        echo $e->getMessage() . "\n" ;
        exit(1) ;
    }
});
