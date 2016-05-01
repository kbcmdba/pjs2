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

require_once "Libs/autoload.php" ;

$auth = new AjaxAuth() ;
if ( ! $auth->isAuthorized() ) {
    $auth->forbidden() ;
}

// Sample call ajaxSaveApplicationStatusRow.php
//                 ?id=1
//                 &statusValue=FOUND
//                 &style=background-color%3A%20lightgreen%3B%20color%3A%20blue%3B
//                 &isActive=1
//                 &sortKey=12

$result = array( 'result' => 'Not implemented.' ) ;
print json_encode( $result ) ;
