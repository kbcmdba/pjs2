<?php

require_once 'Libs/autoload.php' ;

$config = new Config() ;
$page = new PJSWebPage( $config->getTitle() . " - Searches" ) ;
$body = "<h2>Searches</h2>\n<div>Not yet written.<div>" ;
$page->setBody( $body ) ;
$page->displayPage() ;
