<?php

require_once 'Libs/autoload.php' ;

$config = new Config() ;
$page = new PJSWebPage( $config->getTitle() . " - Keywords" ) ;
$body = "<h2>Keywords</h2>\n<div>Not yet written.<div>" ;
$page->setBody( $body ) ;
$page->displayPage() ;
