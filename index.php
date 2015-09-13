<?php

require_once 'Libs/autoload.php' ;

// @todo Show application statuses in index page

$config = new Config() ;
$page = new PJSWebPage( $config->getTitle() ) ;
$page->displayPage() ;
