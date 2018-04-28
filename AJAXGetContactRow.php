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

require_once "Libs/autoload.php" ;

$auth = new Auth() ;
if (! $auth->isAuthorized()) {
    $auth->forbidden() ;
    exit(0) ; // Should never get here but just in case...
}
$result = "OK" ;
$id   = Tools::param('id') ;
$mode = Tools::param('mode') ;
$html = '' ;
$contactListView = new ContactListView('html', null) ;
if ('add' == $mode) {
    $contactModel = new ContactModel() ;
    $contactModel->setId($id) ;
    $html = $contactListView->displayContactRow($contactModel, $mode) ;
} else {
    $contactController = new ContactController() ;
    $contactModel = $contactController->get($id) ;
    $html = $contactListView->displayContactRow($contactModel, $mode) ;
}
$result = [ 'result' => $result, 'row' => $html ] ;
echo json_encode($result) . PHP_EOL ;
