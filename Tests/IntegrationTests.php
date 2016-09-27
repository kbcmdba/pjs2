<?php

/**
 * phpjobseeker
 *
 * Copyright (C) 2009, 2015 Kevin Benton - kbenton at bentonfam dot org
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

    function doToggleCheckBox( $locator ) {
        $this->webDriver->findElement( $locator )->click() ;
    }

    function doSelectOption( $location, $displayedValue ) {
        $select   = $this->webDriver->findElement( $location ) ;
        $options  = $select->findElements( WebDriverBy::tagName( 'option' ) ) ;
        $wasFound = 0 ;
        foreach ( $options as $option ) {
            if ( $displayedValue === $option->getText() ) {
                $option->click() ;
                $wasFound = 1 ;
            }
        }
        $this->assertEquals( 1, $wasFound ) ;
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
        $this->webDriver->wait( 30, 300 )->until( function ( $webDriver ) {
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
        $this->assertEquals( 0, count( $this->webDriver->findElements( $locator ) ) ) ;
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

    public function checkASHR() {
        $this->checkXpathText( '//button', 'Add Application Status' ) ;
        $this->checkXpathText( '//caption', 'Current Application Statuses' ) ;
        $this->checkXpathText( '//th', 'Actions' ) ;
        $this->checkXpathText( '//th[2]', 'Value' ) ;
        $this->checkXpathText( '//th[3]', 'Style' ) ;
        $this->checkXpathText( '//th[4]', 'Is Active' ) ;
        $this->checkXpathText( '//th[5]', 'Sort Key' ) ;
        $this->checkXpathText( '//th[6]', 'Created' ) ;
        $this->checkXpathText( '//th[7]', 'Updated' ) ;
    }

    public function checkASR( $id, $prefix, $label, $css, $isActive, $sortKey ) {
        $this->checkIdText( "UpdateButton$id", 'Edit' ) ;
        $this->checkIdText( "DeleteButton$id", 'Delete' ) ;
        $this->checkXpathText( "/$prefix/td[2]", $label ) ;
        $this->checkXpathText( "/$prefix/td[3]", $css ) ;
        $this->checkXpathText( "/$prefix/td[4]", $isActive ) ;
        $this->checkXpathText( "/$prefix/td[5]", $sortKey ) ;
        $this->checkXpathPattern( "/$prefix/td[6]", '/^\d\d\d\d-\d\d-\d\d \d\d:\d\d:\d\d$/' ) ;
        $this->checkXpathPattern( "/$prefix/td[7]", '/^\d\d\d\d-\d\d-\d\d \d\d:\d\d:\d\d$/' ) ;
    }

    public function doTestApplicationStatuses() {
        $driver = $this->webDriver ;
        $this->doLoadFromHeader( 'Application Statuses' ) ;
        $this->checkHeaderLoads() ;
        $this->checkASHR() ;
        $this->checkASR( 1, '', 'FOUND', 'background-color: lightgreen; color: blue;', 'Yes', '10' ) ;
        $this->checkASR( 2, '/tr[2]', 'CONTACTED', 'background-color: orange; color: blue;', 'Yes', '20' ) ;
        $this->checkASR( 3, '/tr[3]', 'APPLIED', 'background-color: yellow; color: blue;', 'Yes', '30' ) ;
        $this->checkASR( 4, '/tr[4]', 'INTERVIEWING', 'background-color: white; color: red;', 'Yes', '40' ) ;
        $this->checkASR( 5, '/tr[5]', 'FOLLOWUP', 'background-color: yellow; color: black;', 'Yes', '50' ) ;
        $this->checkASR( 6, '/tr[6]', 'CHASING', 'background-color: red; color: black;', 'Yes', '60' ) ;
        $this->checkASR( 7, '/tr[7]', 'NETWORKING', 'background-color: cyan; color: black;', 'Yes', '70' ) ;
        $this->checkASR( 8, '/tr[8]', 'UNAVAILABLE', 'background-color: black; color: white;', 'No', '999' ) ;
        $this->checkASR( 9, '/tr[9]', 'INVALID', 'background-color: black; color: white;', 'No', '999' ) ;
        $this->checkASR( 10, '/tr[10]', 'DUPLICATE', 'background-color: black; color: white;', 'No', '999' ) ;
        $this->checkASR( 11, '/tr[11]', 'CLOSED', 'background-color: black; color: white;', 'No', '999' ) ;
        $driver->findElement( WebDriverBy::xpath( '//button' ) )->click() ;
        $this->doWaitFor( WebDriverBy::id( 'SaveButtonix1' ) ) ;
        $this->doWaitFor( WebDriverBy::id( 'CancelButtonix1' ) ) ;
        $driver->findElement( WebDriverBy::xpath( '//button' ) )->click() ;
        $this->doWaitFor( WebDriverBy::id( 'SaveButtonix2' ) ) ;
        $this->doWaitFor( WebDriverBy::id( 'CancelButtonix2' ) ) ;
        $driver->findElement( WebDriverBy::id( 'CancelButtonix2' ) )->click() ;
        $this->checkHeaderLoads() ;
        $this->checkNotPresent( WebDriverBy::id( 'SaveButtonix2' ) ) ;
        $this->checkNotPresent( WebDriverBy::id( 'CancelButtonix2' ) ) ;
        // We could verify the entire table, but that's a little silly.
        $this->doTypeAt( WebDriverBy::id( 'statusValueix1' ), 'FOO' ) ;
        $this->doTypeAt( WebDriverBy::id( 'styleix1' ), 'background-color: white; color: black;' ) ;
        $this->doToggleCheckBox( WebDriverBy::id( 'isActiveix1' ) ) ;
        $this->doTypeAt( WebDriverBy::id( 'sortKeyix1' ), '5' ) ;
        $driver->findElement( WebDriverBy::id( 'SaveButtonix1' ) )->click() ;
        $this->checkASHR() ;
        $this->checkASR( 12, '', 'FOO', 'background-color: white; color: black;', 'Yes', '5' ) ;
        $this->checkASR( 1, '/tr[2]', 'FOUND', 'background-color: lightgreen; color: blue;', 'Yes', '10' ) ;
        $this->checkASR( 2, '/tr[3]', 'CONTACTED', 'background-color: orange; color: blue;', 'Yes', '20' ) ;
        $this->checkASR( 3, '/tr[4]', 'APPLIED', 'background-color: yellow; color: blue;', 'Yes', '30' ) ;
        $this->checkASR( 4, '/tr[5]', 'INTERVIEWING', 'background-color: white; color: red;', 'Yes', '40' ) ;
        $this->checkASR( 5, '/tr[6]', 'FOLLOWUP', 'background-color: yellow; color: black;', 'Yes', '50' ) ;
        $this->checkASR( 6, '/tr[7]', 'CHASING', 'background-color: red; color: black;', 'Yes', '60' ) ;
        $this->checkASR( 7, '/tr[8]', 'NETWORKING', 'background-color: cyan; color: black;', 'Yes', '70' ) ;
        $this->checkASR( 8, '/tr[9]', 'UNAVAILABLE', 'background-color: black; color: white;', 'No', '999' ) ;
        $this->checkASR( 9, '/tr[10]', 'INVALID', 'background-color: black; color: white;', 'No', '999' ) ;
        $this->checkASR( 10, '/tr[11]', 'DUPLICATE', 'background-color: black; color: white;', 'No', '999' ) ;
        $this->checkASR( 11, '/tr[12]', 'CLOSED', 'background-color: black; color: white;', 'No', '999' ) ;
        $this->doLoadFromHeader( 'Application Statuses' ) ;
        $this->checkHeaderLoads() ;
        $this->checkASHR() ;
        $this->checkASR( 12, '', 'FOO', 'background-color: white; color: black;', 'Yes', '5' ) ;
        $this->checkASR( 1, '/tr[2]', 'FOUND', 'background-color: lightgreen; color: blue;', 'Yes', '10' ) ;
        $this->checkASR( 2, '/tr[3]', 'CONTACTED', 'background-color: orange; color: blue;', 'Yes', '20' ) ;
        $this->checkASR( 3, '/tr[4]', 'APPLIED', 'background-color: yellow; color: blue;', 'Yes', '30' ) ;
        $this->checkASR( 4, '/tr[5]', 'INTERVIEWING', 'background-color: white; color: red;', 'Yes', '40' ) ;
        $this->checkASR( 5, '/tr[6]', 'FOLLOWUP', 'background-color: yellow; color: black;', 'Yes', '50' ) ;
        $this->checkASR( 6, '/tr[7]', 'CHASING', 'background-color: red; color: black;', 'Yes', '60' ) ;
        $this->checkASR( 7, '/tr[8]', 'NETWORKING', 'background-color: cyan; color: black;', 'Yes', '70' ) ;
        $this->checkASR( 8, '/tr[9]', 'UNAVAILABLE', 'background-color: black; color: white;', 'No', '999' ) ;
        $this->checkASR( 9, '/tr[10]', 'INVALID', 'background-color: black; color: white;', 'No', '999' ) ;
        $this->checkASR( 10, '/tr[11]', 'DUPLICATE', 'background-color: black; color: white;', 'No', '999' ) ;
        $this->checkASR( 11, '/tr[12]', 'CLOSED', 'background-color: black; color: white;', 'No', '999' ) ;
        // Update new row, cancel
        // Delete new row, cancel
        // Update new row, save, verify
        // Delete new row, verify, cancel, verify

        sleep( 10 ) ;
        $this->markTestIncomplete( 'Left off here.' ) ;
    }

    public function checkC1HR() {
        $this->checkXpathText( '//button', 'Add Company' ) ;
        $this->checkXpathText( '//caption', 'Current Companies' ) ;
        $this->checkXpathText( '//th', 'Actions' ) ;
        $this->checkXpathText( '//th[2]', 'Company' ) ;
        $this->checkXpathText( '//th[3]', 'Address1' ) ;
        $this->checkXpathText( '//th[4]', 'City' ) ;
        $this->checkXpathText( '//th[5]', 'State' ) ;
        $this->checkXpathText( '//th[6]', 'Zip' ) ;
        $this->checkXpathText( '//th[7]', 'Created' ) ;
        $this->checkXpathText( '//tr[2]/th[2]', 'Agency Of' ) ;
        $this->checkXpathText( '//tr[2]/th[3]', 'Address 2' ) ;
        $this->checkXpathText( '//tr[2]/th[4]', 'Phone' ) ;
        $this->checkXpathText( '//tr[2]/th[5]', 'URL' ) ;
        $this->checkXpathText( '//tr[2]/th[6]', 'Updated' ) ;
    }

    public function checkC1R( $id, $prefix1, $prefix2, $company, $city, $state, $zip
                                                    , $agencyId, $address2, $phone, $url ) {
        $this->checkIdText( "UpdateButton$id", 'Edit' ) ;
        $this->checkIdText( "DeleteButton$id", 'Delete' ) ;
        $this->checkXpathText( "/$prefix1/td[2]", $company ) ;
        $this->checkXpathText( "/$prefix1/td[3]", $city ) ;
        $this->checkXpathText( "/$prefix1/td[4]", $state ) ;
        $this->checkXpathText( "/$prefix1/td[5]", $zip ) ;
        $this->checkXpathPattern( "/$prefix1/td[7]", '/^\d\d\d\d-\d\d-\d\d \d\d:\d\d:\d\d$/' ) ; // created
        $this->checkXpathText( "/$prefix2/td[2]", $agencyId ) ;
        $this->checkXpathText( "/$prefix2/td[3]", $address2 ) ;
        $this->checkXpathText( "/$prefix2/td[4]", $phone ) ;
        $this->checkXpathText( "/$prefix2/td[5]/a", $url ) ;
        $this->checkXpathPattern( "/$prefix2/td[6]", '/^\d\d\d\d-\d\d-\d\d \d\d:\d\d:\d\d$/' ) ; // updated
    }

    /**
     * FIXME Implement this
     */
    public function doTestCompanies() {
        $driver = $this->webDriver ;
        $this->doLoadFromHeader( 'Companies' ) ;
        $this->checkHeaderLoads() ;
        $this->checkC1HR() ;

        sleep( 15 ) ;
        $this->markTestIncomplete( 'Left off here.' ) ;
    }

    /**
     * FIXME Implement this
     */
    public function doTestContacts() {
        $driver = $this->webDriver ;
        $this->doLoadFromHeader( 'Contacts' ) ;
        $this->checkHeaderLoads() ;
        $this->checkC2HR() ;

        sleep( 15 ) ;
        $this->markTestIncomplete( 'Left off here.' ) ;
    }

    /**
     * FIXME Implement this
     */
    public function doTestJobs() {
        $driver = $this->webDriver ;
        $this->doLoadFromHeader( 'Jobs' ) ;
        $this->checkHeaderLoads() ;
        $this->checkJHR() ;

        sleep( 15 ) ;
        $this->markTestIncomplete( 'Left off here.' ) ;
    }

    /**
     * FIXME Implement this
     */
    public function doTestKeywords() {
        $driver = $this->webDriver ;
        $this->doLoadFromHeader( 'Keywords' ) ;
        $this->checkHeaderLoads() ;
        $this->checkC1HR() ;

        sleep( 15 ) ;
        $this->markTestIncomplete( 'Left off here.' ) ;
    }

    /**
     * FIXME Implement this
     */
    public function doTestSearches() {
        $driver = $this->webDriver ;
        $this->doLoadFromHeader( 'Searches' ) ;
        $this->checkHeaderLoads() ;
        $this->checkSeHR() ;

        sleep( 15 ) ;
        $this->markTestIncomplete( 'Left off here.' ) ;
    }

    /**
     * FIXME Implement this
     */
    public function doTestSummary2() {
        $driver = $this->webDriver ;
        $this->doLoadFromHeader( 'Summary' ) ;
        $this->checkHeaderLoads() ;
        $this->checkSuHR() ;

        sleep( 15 ) ;
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
