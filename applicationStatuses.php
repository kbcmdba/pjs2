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

$config = new Config() ;
$page = new PJSWebPage( $config->getTitle() . ' - Application Statuses' ) ;

$body = "<h2>Application Statuses</h2>" ;
$asc = new ApplicationStatusController( 'read' ) ;
$asmList = $asc->getAll() ;
$asv = new ApplicationStatusListView( 'html', $asmList ) ;
$body .= $asv->getView() ;

$page->setBody( $body ) ;
$page->displayPage() ;
