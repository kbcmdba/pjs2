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

require_once 'Libs/autoload.php' ;

$config = new Config() ;
$page = new PJSWebPage($config->getTitle() . " - Searches") ;
$body = "<h2>Searches</h2>\n" ;
$searchController = new SearchController('read') ;
$searchModelList = $searchController->getAll() ;
$searchListView = new SearchListView('html', $searchModelList) ;
$body .= $searchListView->getView() ;
$page->setBody($body) ;
$page->displayPage() ;
