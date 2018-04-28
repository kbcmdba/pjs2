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
$id             = Tools::param('id') ;
$engineName     = Tools::param('engineName') ;
$searchName     = Tools::param('searchName') ;
$url            = Tools::param('url') ;
$rssFeedUrl     = Tools::param('rssFeedUrl') ;
$rssLastChecked = Tools::param('rssLastChecked') ;
$rowId          = Tools::param('rowId') ;
$result         = 'OK' ;
$searchId       = '' ;
$searchListView = new SearchListView('html', null) ;
try {
    $searchController = new SearchController() ;
    $searchModel      = $searchController->get($id) ;
    $searchModel->setEngineName($engineName) ;
    $searchModel->setSearchName($searchName) ;
    $searchModel->setUrl($url) ;
    $searchModel->setRssFeedUrl($rssFeedUrl) ;
    $searchModel->setRssLastChecked($rssLastChecked) ;
    $result = $searchController->update($searchModel) ;

    if (! ($result > 0)) {
        throw new ControllerException("Update failed.") ;
    }
    $searchId = $result ;
    $row = $searchListView->displaySearchRow($searchModel, 'list') ;
    $result = 'OK' ;
} catch (ControllerException $e) {
    $result = 'FAILED' ;
    $row = $searchListView->displaySearchRow(
        $searchModel,
        'update',
        'Update Search record failed. '
                                            . $e->getMessage()
                                            ) ;
}

$result = [ 'result' => $result, 'row' => $row, 'id' => $searchId ] ;
echo json_encode($result) . PHP_EOL ;
