<?php

set_include_path( get_include_path()
                . PATH_SEPARATOR
                . '/home/kbenton/.config/composer'
                ) ;

use Facebook\WebDriver\Remote\DesiredCapabilities ;
use Facebook\WebDriver\Remote\RemoteWebDriver ;
use Facebook\WebDriver\WebDriverBy ;

require_once('vendor/autoload.php');

class IntegrationTests extends PHPUnit_Framework_TestCase {

    /**
     * @var \RemoteWebDriver
     */
    protected $webDriver ;
    private $_headerTags = array( 'Summary'
                                , "Application Statuses"
                                , "Companies"
                                , "Contacts"
                                , "Jobs"
                                , "Keywords"
                                , "Searches"
                                ) ;
    private $_userName   = 'pjs2_test' ;
    private $_password   = 'pjs2_test' ;
    protected $url = 'http://127.0.0.1/pjs2/' ;

    public function setUp() {
        $capabilities = DesiredCapabilities::firefox() ;
        $this->webDriver = RemoteWebDriver::create( 'http://localhost:4444/wd/hub', $capabilities ) ;
        $this->webDriver->manage()->timeouts()->implicitlyWait = 10 ;
    }

    public function tearDown() {
       $this->webDriver->quit() ;
    }

    public function doWaitFor( $target, $timeout = 60, $interval = 250 ) {
        global $lookFor ;
        global $ret ;
        $lookFor = $target ;
        $this->webDriver->wait($timeout, $interval)->until( function ( $webDriver ) {
            global $lookFor ;
            global $ret ;
            $ret = ( 1 === count( $this->webDriver->findElements( $lookFor ) ) ) ;
            return( $ret ) ;
        } ) ;
        $this->assertTrue( $ret ) ;
        return( $ret ) ;
    }

    public function doTypeAt( $target , $value ) {
        $element = $this->webDriver->findElement( $target )->click() ;
        $element->clear() ;
        $this->webDriver->getKeyboard()->sendKeys( $value ) ;
    }

    public function doLoadFromHeader( $tag ) {
        $this->assertTrue( $this->doWaitFor( WebDriverBy::linkText( $tag ) ) ) ;
        $element = $this->webDriver->findElement( WebDriverBy::linkText( $tag ) ) ;
        $element->click() ;
        $this->checkHeaderLoads() ;
    }

    public function checkFooterLoads() {
        global $lookFor ;
        $lookFor = "<!-- EndOfPage --></body>\n</html>" ;
        $this->webDriver->wait( 15, 300 )->until( function ( $webDriver ) {
            global $lookFor ;
            return strpos( $webDriver->getPageSource(), $lookFor ) !== null ;
        }) ;
        $this->assertNotNull( strpos( $this->webDriver->getPageSource(), $lookFor ) ) ;
    }

    public function checkHeaderLoads() {
        $this->checkFooterLoads() ;
        foreach ( $this->_headerTags as $tag ) {
            $element = $this->webDriver->findElement( WebDriverBy::linkText( $tag ) ) ;
            $this->assertNotNull( $element ) ;
        }
    }

    public function checkIdText( $locator, $text ) {
        $element = $this->webDriver->findElement( WebDriverBy::id( $locator ) ) ;
        $this->assertEquals( $text, $element->getText() ) ;
    }

    public function checkIdValue( $locator, $value ) {
        $element = $this->webDriver->findElement( WebDriverBy::id( $locator ) ) ;
        $this->assertEquals( $value, $element->getAttribute( "value" ) ) ;
    }

    public function checkXpathText( $locator, $text ) {
        $element = $this->webDriver->findElement( WebDriverBy::xpath( $locator ) ) ;
        $this->assertEquals( $text, $element->getText() ) ;
    }

    public function checkXpathPattern( $locator, $pattern ) {
        $element = $this->webDriver->findElement( WebDriverBy::xpath( $locator ) ) ;
        $this->assertEquals( 1, preg_match( $pattern, $element->getText() ) ) ;
    }

    public function checkCssText( $locator, $text ) {
        $element = $this->webDriver->findElement( WebDriverBy::cssSelector( $locator ) ) ;
        $this->assertEquals( $text, $element->getText() ) ;
    }

    public function checkNotPresent( $locator ) {
        $this->assertEquals( 0, $this->webDriver->findElements( $locator ) ) ;
    }

    public function doLogOutLogIn() {
        $username = null ;
        $password = null ;
        $driver = $this->webDriver ;
        $driver->get( $this->url . "logout.php" ) ;
        $driver->wait(15, 300)->until(function ($webDriver) {
            return $webDriver->getCurrentURL() === $this->url . 'index.php' ;
        } ) ;
        $indexURL = $this->url . 'index.php' ;
        $currentURL = $this->webDriver->getCurrentURL() ;
        $this->assertEquals( $indexURL, $currentURL ) ;
        if ( $indexURL !== $currentURL ) {
            $this->markTestSkipped( 'Logout page failed.' ) ;
            return ;
        }
        $this->assertEquals( 'PHP Job Seeker 2', $this->webDriver->getTitle() ) ;
        $this->checkHeaderLoads() ;
        try {
            $username = $this->webDriver->findElement( WebDriverBy::name( 'auth_username' ) ) ;
            $password = $this->webDriver->findElement( WebDriverBy::name( 'auth_password' ) ) ;
        }
        catch( NoSuchElementException $ex ) {
            $this->markTestIncomplete( 'This part of the test has not been written.' ) ;
            return ;
        }
        $this->assertNotNull( $username ) ;
        $this->assertNotNull( $password ) ;
        if ( ( null === $username ) || ( null === $password ) ) {
            $this->markTestSkipped( 'Unable to test login screen - fields missing.' ) ;
            return false ;
        }
        $username->sendKeys( $this->_userName ) ;
        $password->sendKeys( $this->_password )->submit() ;
        $url = $this->webDriver->getCurrentURL() ;
        $this->assertEquals( ( $this->url . "index.php" ), $url ) ;
        $this->assertEquals( true, $this->doWaitFor( WebDriverBy::linkText( 'Log Out' ) ) ) ;
    }

    public function doResetDb() {
        $this->doLoadFromHeader( 'Reset Database' ) ;
        $resetElements = array( 'Dropping Triggers: SearchController'
                              , 'Dropping Triggers: KeywordController'
                              , 'Dropping Triggers: JobController'
                              , 'Dropping Triggers: ContactController'
                              , 'Dropping Triggers: CompanyController'
                              , 'Dropping Triggers: ApplicationStatusController'
                              , 'Dropping Tables: JobKeywordMapController'
                              , 'Dropping Tables: SearchController'
                              , 'Dropping Tables: NoteController'
                              , 'Dropping Tables: KeywordController'
                              , 'Dropping Tables: JobController'
                              , 'Dropping Tables: ContactController'
                              , 'Dropping Tables: CompanyController'
                              , 'Dropping Tables: ApplicationStatusSummaryController'
                              , 'Dropping Tables: ApplicationStatusController'
                              , 'Dropping Tables: AuthTicketController'
                              , 'Dropping Tables: VersionController'
                              , 'Creating Tables: VersionController'
                              , 'Creating Tables: AuthTicketController'
                              , 'Creating Tables: ApplicationStatusController'
                              , 'Creating Tables: ApplicationStatusSummaryController'
                              , 'Creating Tables: CompanyController'
                              , 'Creating Tables: ContactController'
                              , 'Creating Tables: JobController'
                              , 'Creating Tables: KeywordController'
                              , 'Creating Tables: NoteController'
                              , 'Creating Tables: SearchController'
                              , 'Creating Tables: JobKeywordMapController'
                              , 'Creating Triggers: ApplicationStatusController'
                              , 'Creating Triggers: CompanyController'
                              , 'Creating Triggers: ContactController'
                              , 'Creating Triggers: JobController'
                              , 'Creating Triggers: KeywordController'
                              , 'Creating Triggers: SearchController'
                              , 'Pre-populating tables: VersionController'
                              , 'Pre-populating tables: ApplicationStatusController'
        ) ;

        $cnt = count( $resetElements ) ;
        $this->doWaitFor( WebDriverBy::xpath( "//ul[2]/li[$cnt]" ) ) ;
        for( $i=1 ; $i <= $cnt ; $i++ ) {
            $this->checkXpathText( "//ul[2]/li[$i]", $resetElements[ $i - 1 ] ) ;
        }
        $this->checkXpathText( '//p[2]', 'Done.' ) ;
    }

    /**
     * FIXME Implement this
     */
    public function doTestSummary1() {
        $this->markTestIncomplete( 'Left off here.' ) ;
    }

    /**
     * FIXME Implement this
     */
    public function doTestApplicationStatuses() {
        $this->doLoadFromHeader( 'Application Statuses' ) ;
        $this->checkHeaderLoads() ;
        $this->checkXpathText( '//button', 'Add Application Status' ) ;
        $this->checkXpathText( '//caption', 'Current Application Statuses' ) ;
        $this->checkXpathText( '//th', 'Actions' ) ;
        $this->checkXpathText( '//th[2]', 'Value' ) ;
        $this->checkXpathText( '//th[3]', 'Style' ) ;
        $this->checkXpathText( '//th[4]', 'Is Active' ) ;
        $this->checkXpathText( '//th[5]', 'Sort Key' ) ;
        $this->checkXpathText( '//th[6]', 'Created' ) ;
        $this->checkXpathText( '//th[7]', 'Updated' ) ;
        $this->checkIdText( 'UpdateButton1', 'Edit' ) ;
        $this->checkIdText( 'DeleteButton1', 'Delete' ) ;
        $this->checkXpathText( '//td[2]', 'FOUND' ) ;
        $this->checkXpathText( '//td[3]', 'background-color: lightgreen; color: blue;' ) ;
        $this->checkXpathText( '//td[4]', 'Yes' ) ;
        $this->checkXpathText( '//td[5]', '10' ) ;
        $this->checkXpathPattern( '//td[6]', '/^\d\d\d\d-\d\d-\d\d \d\d:\d\d:\d\d$/' ) ;
        $this->checkXpathPattern( '//td[7]', '/^\d\d\d\d-\d\d-\d\d \d\d:\d\d:\d\d$/' ) ;
        $this->checkIdText( 'UpdateButton2', 'Edit' ) ;
        $this->checkIdText( 'DeleteButton2', 'Delete' ) ;
        $this->checkXpathText( '//tr[2]/td[2]', 'CONTACTED' ) ;
        $this->checkXpathText( '//tr[2]/td[3]', 'background-color: orange; color: blue;' ) ;
        $this->checkXpathText( '//tr[2]/td[4]', 'Yes' ) ;
        $this->checkXpathText( '//tr[2]/td[5]', '20' ) ;
        $this->checkXpathPattern( '//tr[2]/td[6]', '/^\d\d\d\d-\d\d-\d\d \d\d:\d\d:\d\d$/' ) ;
        $this->checkXpathPattern( '//tr[2]/td[7]', '/^\d\d\d\d-\d\d-\d\d \d\d:\d\d:\d\d$/' ) ;
        $this->checkIdText( 'UpdateButton3', 'Edit' ) ;
        $this->checkIdText( 'DeleteButton3', 'Delete' ) ;
        $this->checkXpathText( '//tr[3]/td[2]', 'APPLIED' ) ;
        $this->checkXpathText( '//tr[3]/td[3]', 'background-color: yellow; color: blue;' ) ;
        $this->checkXpathText( '//tr[3]/td[4]', 'Yes' ) ;
        $this->checkXpathText( '//tr[3]/td[5]', '30' ) ;
        $this->checkXpathPattern( '//tr[3]/td[6]', '/^\d\d\d\d-\d\d-\d\d \d\d:\d\d:\d\d$/' ) ;
        $this->checkXpathPattern( '//tr[3]/td[7]', '/^\d\d\d\d-\d\d-\d\d \d\d:\d\d:\d\d$/' ) ;
        $this->checkIdText( 'UpdateButton4', 'Edit' ) ;
        $this->checkIdText( 'DeleteButton4', 'Delete' ) ;
        $this->checkXpathText( '//tr[4]/td[2]', 'INTERVIEWING' ) ;
        $this->checkXpathText( '//tr[4]/td[3]', 'background-color: white; color: red;' ) ;
        $this->checkXpathText( '//tr[4]/td[4]', 'Yes' ) ;
        $this->checkXpathText( '//tr[4]/td[5]', '40' ) ;
        $this->checkXpathPattern( '//tr[4]/td[6]', '/^\d\d\d\d-\d\d-\d\d \d\d:\d\d:\d\d$/' ) ;
        $this->checkXpathPattern( '//tr[4]/td[7]', '/^\d\d\d\d-\d\d-\d\d \d\d:\d\d:\d\d$/' ) ;
        $this->checkIdText( 'UpdateButton5', 'Edit' ) ;
        $this->checkIdText( 'DeleteButton5', 'Delete' ) ;
        $this->checkXpathText( '//tr[5]/td[2]', 'FOLLOWUP' ) ;
        $this->checkXpathText( '//tr[5]/td[3]', 'background-color: yellow; color: black;' ) ;
        $this->checkXpathText( '//tr[5]/td[4]', 'Yes' ) ;
        $this->checkXpathText( '//tr[5]/td[5]', '50' ) ;
        $this->checkXpathPattern( '//tr[5]/td[6]', '/^\d\d\d\d-\d\d-\d\d \d\d:\d\d:\d\d$/' ) ;
        $this->checkXpathPattern( '//tr[5]/td[7]', '/^\d\d\d\d-\d\d-\d\d \d\d:\d\d:\d\d$/' ) ;
        $this->checkIdText( 'UpdateButton6', 'Edit' ) ;
        $this->checkIdText( 'DeleteButton6', 'Delete' ) ;
        $this->checkXpathText( '//tr[6]/td[2]', 'CHASING' ) ;
        $this->checkXpathText( '//tr[6]/td[3]', 'background-color: red; color: black;' ) ;
        $this->checkXpathText( '//tr[6]/td[4]', 'Yes' ) ;
        $this->checkXpathText( '//tr[6]/td[5]', '60' ) ;
        $this->checkXpathPattern( '//tr[6]/td[6]', '/^\d\d\d\d-\d\d-\d\d \d\d:\d\d:\d\d$/' ) ;
        $this->checkXpathPattern( '//tr[6]/td[7]', '/^\d\d\d\d-\d\d-\d\d \d\d:\d\d:\d\d$/' ) ;
        $this->checkIdText( 'UpdateButton7', 'Edit' ) ;
        $this->checkIdText( 'DeleteButton7', 'Delete' ) ;
        $this->checkXpathText( '//tr[7]/td[2]', 'NETWORKING' ) ;
        $this->checkXpathText( '//tr[7]/td[3]', 'background-color: cyan; color: black;' ) ;
        $this->checkXpathText( '//tr[7]/td[4]', 'Yes' ) ;
        $this->checkXpathText( '//tr[7]/td[5]', '70' ) ;
        $this->checkXpathPattern( '//tr[7]/td[6]', '/^\d\d\d\d-\d\d-\d\d \d\d:\d\d:\d\d$/' ) ;
        $this->checkXpathPattern( '//tr[7]/td[7]', '/^\d\d\d\d-\d\d-\d\d \d\d:\d\d:\d\d$/' ) ;
        $this->checkIdText( 'UpdateButton8', 'Edit' ) ;
        $this->checkIdText( 'DeleteButton8', 'Delete' ) ;
        $this->checkXpathText( '//tr[8]/td[2]', 'UNAVAILABLE' ) ;
        $this->checkXpathText( '//tr[8]/td[3]', 'background-color: black; color: white;' ) ;
        $this->checkXpathText( '//tr[8]/td[4]', 'No' ) ;
        $this->checkXpathText( '//tr[8]/td[5]', '999' ) ;
        $this->checkXpathPattern( '//tr[8]/td[6]', '/^\d\d\d\d-\d\d-\d\d \d\d:\d\d:\d\d$/' ) ;
        $this->checkXpathPattern( '//tr[8]/td[7]', '/^\d\d\d\d-\d\d-\d\d \d\d:\d\d:\d\d$/' ) ;
        $this->checkIdText( 'UpdateButton9', 'Edit' ) ;
        $this->checkIdText( 'DeleteButton9', 'Delete' ) ;
        $this->checkXpathText( '//tr[9]/td[2]', 'INVALID' ) ;
        $this->checkXpathText( '//tr[9]/td[3]', 'background-color: black; color: white;' ) ;
        $this->checkXpathText( '//tr[9]/td[4]', 'No' ) ;
        $this->checkXpathText( '//tr[9]/td[5]', '999' ) ;
        $this->checkXpathPattern( '//tr[9]/td[6]', '/^\d\d\d\d-\d\d-\d\d \d\d:\d\d:\d\d$/' ) ;
        $this->checkXpathPattern( '//tr[9]/td[7]', '/^\d\d\d\d-\d\d-\d\d \d\d:\d\d:\d\d$/' ) ;
        $this->checkIdText( 'UpdateButton10', 'Edit' ) ;
        $this->checkIdText( 'DeleteButton10', 'Delete' ) ;
        $this->checkXpathText( '//tr[10]/td[2]', 'DUPLICATE' ) ;
        $this->checkXpathText( '//tr[10]/td[3]', 'background-color: black; color: white;' ) ;
        $this->checkXpathText( '//tr[10]/td[4]', 'No' ) ;
        $this->checkXpathText( '//tr[10]/td[5]', '999' ) ;
        $this->checkXpathPattern( '//tr[10]/td[6]', '/^\d\d\d\d-\d\d-\d\d \d\d:\d\d:\d\d$/' ) ;
        $this->checkXpathPattern( '//tr[10]/td[7]', '/^\d\d\d\d-\d\d-\d\d \d\d:\d\d:\d\d$/' ) ;
        $this->checkIdText( 'UpdateButton11', 'Edit' ) ;
        $this->checkIdText( 'DeleteButton11', 'Delete' ) ;
        $this->checkXpathText( '//tr[11]/td[2]', 'CLOSED' ) ;
        $this->checkXpathText( '//tr[11]/td[3]', 'background-color: black; color: white;' ) ;
        $this->checkXpathText( '//tr[11]/td[4]', 'No' ) ;
        $this->checkXpathText( '//tr[11]/td[5]', '999' ) ;
        $this->checkXpathPattern( '//tr[11]/td[6]', '/^\d\d\d\d-\d\d-\d\d \d\d:\d\d:\d\d$/' ) ;
        $this->checkXpathPattern( '//tr[11]/td[7]', '/^\d\d\d\d-\d\d-\d\d \d\d:\d\d:\d\d$/' ) ;
        // Add Verify fill
        // Add Verify fill Cancel 2nd add
        // Populate add 1, save, verify
        // Verify Page
        // Reload Page
        // Verify Page
        // Update new row, cancel
        // Delete new row, cancel
        // Update new row, save, verify
        // Delete new row, verify, cancel, verify

        sleep( 5 ) ;
        $this->markTestIncomplete( 'Left off here.' ) ;
    }

    /**
     * FIXME Implement this
     */
    public function doTestCompanies() {
        $this->markTestIncomplete( 'Left off here.' ) ;
    }

    /**
     * FIXME Implement this
     */
    public function doTestContacts() {
        $this->markTestIncomplete( 'Left off here.' ) ;
    }

    /**
     * FIXME Implement this
     */
    public function doTestJobs() {
        $this->markTestIncomplete( 'Left off here.' ) ;
    }

    /**
     * FIXME Implement this
     */
    public function doTestKeywords() {
        $this->markTestIncomplete( 'Left off here.' ) ;
    }

    /**
     * FIXME Implement this
     */
    public function doTestSearches() {
        $this->markTestIncomplete( 'Left off here.' ) ;
    }

    /**
     * FIXME Implement this
     */
    public function doTestSummary2() {
        $this->markTestIncomplete( 'Left off here.' ) ;
    }

    /**
     * @group minimal
     */
    public function testWebsiteLoads() {
        $driver = $this->webDriver ;
        $url    = $this->url ;
        $driver->get( $this->url ) ;
        $this->checkHeaderLoads() ;
        $this->doLogOutLogIn() ;
        $this->checkHeaderLoads() ;
    }

    /*
     * @group minimal
     * @group doReset
     */
    public function testResetDatabase() {
        $driver = $this->webDriver ;
        $url    = $this->url ;
        $driver->get( $this->url ) ;
        $this->checkHeaderLoads() ;
        $this->doLogOutLogIn() ;
        $this->checkHeaderLoads() ;
        $this->doResetDb() ;
        // Log back in since the database was reset and my session is gone.
        $this->doLogOutLogIn() ;
        $this->checkHeaderLoads() ;
    }

    /**
     * @group full
     * @group skipReset
     */
    public function testWebSite() {
        $this->doLogOutLogIn() ;
        $driver = $this->webDriver ;
        $url    = $this->url ;
        $driver->get( $this->url ) ;
        $this->checkHeaderLoads() ;
        // Check that all the pages in the header load properly
        foreach ( $this->_headerTags as $headerTag ) {
            $this->doLoadFromHeader( $headerTag ) ;
        }

//         $this->doTestSummary1() ;
        $this->doTestApplicationStatuses() ;
//         $this->doTestCompanies() ;
//         $this->doTestContacts() ;
//         $this->doTestJobs() ;
//         $this->doTestKeywords() ;
//         $this->doTestSearches() ;
//         $this->doTestSummary2() ;

    }

}
