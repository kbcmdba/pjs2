<?php

require_once "Libs/autoload.php" ;

$config = new Config() ;
$webPage = new PJSWebPage( $config->getTitle() . ' - Delete Application Status') ;
$act = Tools::Param( 'act' ) ;
if ( "Delete Application Status" === $act ) {
    $asm = new ApplicationStatusModel() ;
    $asm->populateFromForm() ;
    if ( ! $asm->validateForDelete() ) {
        $view = new ApplicationStatusFormView( 'Delete Application Status', $asm ) ;
        $body = "<h2>Invalid data</h2>\n" . $view->getForm() ;
    }
    else {
        $asc = new ApplicationStatusController() ;
        $newId = $asc->delete( $asm ) ;
        $body = "Deleted application status # " . $asm->getId() . "<br />\n";
    }
}
else {
    $asc = new ApplicationStatusController() ;
    $asm = $asc->get( Tools::param( 'id' ) ) ;
    $view = new ApplicationStatusFormView( 'Delete Application Status', $asm ) ;
    $body = $view->getForm() ;
}
$webPage->setBody( $body ) ;
$webPage->displayPage() ;

