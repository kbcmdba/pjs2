<?php

require_once "Libs/autoload.php" ;

$auth = new Auth() ;
if ( ! $auth->isAuthorized() ) {
    $auth->forbidden() ;
    exit( 0 ) ; // Should never get here but just in case...
}
$receivedOn   = Tools::post( 'receivedOn' ) ;
$receivedFrom = Tools::post( 'receivedFrom' ) ;
$receivedFor  = Tools::post( 'receivedFor' ) ;
$amount       = Tools::post( 'amount' ) ;
$tipAmount    = Tools::post( 'tipAmount' ) ;
$checkNumber  = Tools::post( 'checkNumber' ) ;

$result       = 'OK' ;
$incomeId     = '' ;
$newIncomeModel = null ;
try {
    $incomeModel = new IncomeModel() ;
    $incomeModel->setReceivedOn( $receivedOn ) ;
    $incomeModel->setReceivedFrom( $receivedFrom ) ;
    $incomeModel->setReceivedFor( $receivedFor ) ;
    $incomeModel->setAmount( $amount ) ;
    $incomeModel->setTipAmount( $tipAmount ) ;
    $incomeModel->setCheckNumber( $checkNumber ) ;
    
    $incomeController = new IncomeController() ;
    $incomeId = $incomeController->add( $incomeModel ) ;

    if ( ! ( $incomeId > 0 ) ) {
        throw new ControllerException( "Add failed." ) ;
    }
    $newIncomeModel = $incomeController->get( $incomeId ) ;
    $incomeRowView = new IncomeListView() ;
    $row = $incomeRowView->displayIncomeRow( $newIncomeModel, 'list' ) ;
}
catch ( ControllerException $e ) {
    $incomeRowView = new IncomeListView() ;
    $row = $incomeRowView->displayIncomeRow( $newIncomeModel
                                           , 'list'
                                           , 'Add Income record failed. '
                                           . $e->getMessage()
                                           ) ;
}

$result = array( 'result' => $result, 'row' => $row, 'newId' => $incomeId ) ;
echo json_encode( $result ) . PHP_EOL ;
