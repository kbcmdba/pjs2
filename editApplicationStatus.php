<?php

require_once "Libs/autoload.php" ;

$config = new Config() ;
$webPage = new PJSWebPage( $config->getTitle() . ' - Edit Application Status') ;
$act = Tools::Param( 'act' ) ;
if ( "Edit Application Status" === $act ) {
    $asm = new ApplicationStatusModel() ;
    $asm->populateFromForm() ;
    if ( ! $asm->validateForUpdate() ) {
        $view = new ApplicationStatusFormView( 'Edit Application Status', $asm ) ;
        $body = "<h2>Invalid data</h2>\n" . $view->getForm() ;
    }
    else {
        $asc = new ApplicationStatusController() ;
        $newId = $asc->update( $asm ) ;
        if ( $newId > 0 ) {
            $body = "Edited application status: " . $asm->getStatusValue() . " as # " . $newId . "<br />\n";
        }
    }
}
else {
    $asc = new ApplicationStatusController() ;
    $asm = $asc->get( Tools::param( 'id' ) ) ;
    $view = new ApplicationStatusFormView( 'Edit Application Status', $asm ) ;
    $body = $view->getForm() ;
}
$webPage->setBody( $body ) ;
$webPage->displayPage() ;

