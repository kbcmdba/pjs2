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

require_once "Libs/autoload.php" ;

$auth = new Auth() ;
if (! $auth->isAuthorized()) {
    $auth->forbidden() ;
    exit(0) ; // Should never get here but just in case...
}
$result         = 'OK' ;
$engineName     = Tools::param('engineName') ;
$searchName     = Tools::param('searchName') ;
$url            = Tools::param('url') ;
$rssFeedUrl     = Tools::param('rssFeedUrl') ;
$rssLastChecked = Tools::param('rssLastChecked') ;
$rowId          = Tools::param('rowId') ;
$newSearchModel = null ;
try {
    $searchModel = new SearchModel() ;
    $searchModel->setEngineName($engineName) ;
    $searchModel->setSearchName($searchName) ;
    $searchModel->setUrl($url) ;
    $searchModel->setRssFeedUrl($rssFeedUrl) ;
    $searchModel->setRssLastChecked($rssLastChecked) ;

    $searchController = new SearchController() ;
    $searchId         = $searchController->add($searchModel) ;

    if (! ($searchId >= 1)) {
        throw new ControllerException("Add failed.") ;
    }
    $newSearchModel = $searchController->get($searchId) ;
    $searchRowView = new SearchListView('html', null) ;
    $row = $searchRowView->displaySearchRow($newSearchModel, 'list') ;
} catch (ControllerException $e) {
    $searchRowView = new SearchListView('html', null) ;
    $searchModel->setId($rowId) ;
    $row = $searchRowView->displaySearchRow(
        $searchModel,
        'add',
        'Add Search record failed. '
                                           . $e->getMessage()
                                           ) ;
    $result = 'FAILED' ;
}

$result = [ 'result' => $result, 'row' => $row, 'newId' => $searchId ] ;
echo json_encode($result) . PHP_EOL ;
