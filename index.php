<?php

require_once 'Libs/autoload.php' ;

$config = new Config() ;
$page = new PJSWebPage( $config->getTitle() ) ;
$page->displayPage() ;
