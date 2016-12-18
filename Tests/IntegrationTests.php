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
    protected $url       = 'http://127.0.0.1/pjs2/' ;
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
    /**
     * @var integer -1 = no testing, 0 = minimal testing, 1 = brief testing, 100 = full testing
     */
    private $_testMode   = 0 ;

    public function setUp() {
        $capabilities = DesiredCapabilities::firefox() ;
        $this->webDriver = RemoteWebDriver::create( 'http://localhost:4444/wd/hub', $capabilities ) ;
//        $this->webDriver->manage()->timeouts()->implicitlyWait( 60 ) ;
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
        if ( $this->_testMode < 0 ) {
            return ;
        }
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
        if ( $this->_testMode < 1 ) {
            return ;
        }
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
     * FIXME Implement Tests/IntegrationTests.php:doTestSummary1()
     */
    public function doTestSummary1() {
        $this->markTestIncomplete( 'Left off here.' ) ;
    }

    public function checkASHR() {
        if ( $this->_testMode < 1 ) {
            return ;
        }
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
        if ( $this->_testMode < 1 ) {
            return ;
        }
        $this->checkIdText( "UpdateButton$id", 'Update' ) ;
        $this->checkIdText( "DeleteButton$id", 'Delete' ) ;
        $this->checkXpathText( "/$prefix/td[2]", $label ) ;
        $this->checkXpathText( "/$prefix/td[3]", $css ) ;
        $this->checkXpathText( "/$prefix/td[4]", $isActive ) ;
        $this->checkXpathText( "/$prefix/td[5]", $sortKey ) ;
        $this->checkXpathPattern( "/$prefix/td[6]", '/^\d\d\d\d-\d\d-\d\d \d\d:\d\d:\d\d$/' ) ;
        $this->checkXpathPattern( "/$prefix/td[7]", '/^\d\d\d\d-\d\d-\d\d \d\d:\d\d:\d\d$/' ) ;
    }

    public function doTestApplicationStatuses() {
        if ( $this->_testMode < 0 ) {
            return ;
        }
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
        $driver->findElement( WebDriverBy::id( 'UpdateButton12' ) )->click() ;
        $this->checkHeaderLoads() ;
        $this->doWaitFor( WebDriverBy::id( 'SaveButton12' ) ) ;
        $this->doWaitFor( WebDriverBy::id( 'CancelButton12' ) ) ;
        $this->checkIdValue( 'statusValue12', 'FOO' ) ;
        $this->checkIdValue( 'style12', 'background-color: white; color: black;' ) ;
        $this->assertEquals( true, $driver->findElement( WebDriverBy::id( 'isActive12' ) )->isSelected() ) ;
        $this->checkIdValue( 'sortKey12', '5' ) ;
        $driver->findElement( WebDriverBy::id( 'CancelButton12' ) )->click() ;
        $this->checkHeaderLoads() ;
        $this->doWaitFor( WebDriverBy::id( 'UpdateButton12' ) ) ;
        $this->doWaitFor( WebDriverBy::id( 'DeleteButton12' ) ) ;
        $driver->findElement( WebDriverBy::id( 'DeleteButton12' ) )->click() ;
        $this->checkHeaderLoads() ;
        $this->doWaitFor( WebDriverBy::id( 'DeleteButton12' ) ) ;
        $this->doWaitFor( WebDriverBy::id( 'CancelButton12' ) ) ;
        $this->checkIdText( 'DeleteButton12', 'Confirm Delete' ) ;
        $this->checkIdText( 'CancelButton12', 'Cancel' ) ;
        $driver->findElement( WebDriverBy::id( 'CancelButton12' ) )->click() ;
        $this->checkHeaderLoads() ;
        $this->doWaitFor( WebDriverBy::id( 'UpdateButton12' ) ) ;
        $this->doWaitFor( WebDriverBy::id( 'DeleteButton12' ) ) ;
        $driver->findElement( WebDriverBy::id( 'UpdateButton12' ) )->click() ;
        $this->checkHeaderLoads() ;
        $this->doWaitFor( WebDriverBy::id( 'SaveButton12' ) ) ;
        $this->doWaitFor( WebDriverBy::id( 'CancelButton12' ) ) ;
        $this->doTypeAt( WebDriverBy::id( 'statusValue12' ), 'FOO1' ) ;
        $this->doTypeAt( WebDriverBy::id( 'style12' ), 'background-color: silver; color: black;' ) ;
        $this->doToggleCheckBox( WebDriverBy::id( 'isActive12' ) ) ;
        $this->doTypeAt( WebDriverBy::id( 'sortKey12' ), '15' ) ;
        $driver->findElement( WebDriverBy::id( 'SaveButton12' ) )->click() ;
        $this->checkHeaderLoads() ;
        $this->checkASHR() ;
        $this->checkASR( 12, '', 'FOO1', 'background-color: silver; color: black;', 'No', '15' ) ;
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
        $driver->findElement( WebDriverBy::id( 'DeleteButton12' ) )->click() ;
        $this->checkHeaderLoads() ;
        $this->doWaitFor( WebDriverBy::id( 'DeleteButton12' ) ) ;
        $this->doWaitFor( WebDriverBy::id( 'CancelButton12' ) ) ;
        $this->checkIdText( 'DeleteButton12', 'Confirm Delete' ) ;
        $this->checkIdText( 'CancelButton12', 'Cancel' ) ;
        $this->checkXpathText( "//tr[@id='ux12']/td[2]", 'FOO1' ) ;
        $this->checkXpathText( "//tr[@id='ux12']/td[3]", 'background-color: silver; color: black;' ) ;
        $this->checkXpathText( "//tr[@id='ux12']/td[4]", 'No' ) ;
        $this->checkXpathText( "//tr[@id='ux12']/td[5]", '15' ) ;
        $driver->findElement( WebDriverBy::id( 'DeleteButton12' ) )->click() ;
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
    }

    public function checkC1HR() {
        if ( $this->_testMode < 1 ) {
            return ;
        }
        $this->checkXpathText( '//button', 'Add Company' ) ;
        $this->checkXpathText( '//caption', 'Current Companies' ) ;
        $this->checkXpathText( '//th', 'Actions' ) ;
        $this->checkXpathText( '//th[2]', 'Company' ) ;
        $this->checkXpathText( '//th[3]', 'Address 1' ) ;
        $this->checkXpathText( '//th[4]', 'City' ) ;
        $this->checkXpathText( '//th[5]', 'State' ) ;
        $this->checkXpathText( '//th[6]', 'Zip' ) ;
        $this->checkXpathText( '//th[7]', 'Created' ) ;
        $this->checkXpathText( '//tr[2]/th', 'Agency' ) ;
        $this->checkXpathText( '//tr[2]/th[2]', 'Address 2' ) ;
        $this->checkXpathText( '//tr[2]/th[3]', 'Phone' ) ;
        $this->checkXpathText( '//tr[2]/th[4]', 'URL' ) ;
        $this->checkXpathText( '//tr[2]/th[5]', 'Updated' ) ;
    }

    public function checkC1R( $id, $prefix1, $prefix2
                            , $company, $address1, $city, $state, $zip
                            , $agencyId, $address2, $phone, $url
                            ) {
        if ( $this->_testMode < 1 ) {
            return ;
        }
        $this->checkIdText( "UpdateButton$id", 'Update' ) ;
        $this->checkIdText( "DeleteButton$id", 'Delete' ) ;
        $this->checkXpathText( "/$prefix1/td[2]", $company ) ;
        $this->checkXpathText( "/$prefix1/td[3]", $address1 ) ;
        $this->checkXpathText( "/$prefix1/td[4]", $city ) ;
        $this->checkXpathText( "/$prefix1/td[5]", $state ) ;
        $this->checkXpathText( "/$prefix1/td[6]", $zip ) ;
        $this->checkXpathPattern( "/$prefix1/td[7]", '/^\d\d\d\d-\d\d-\d\d \d\d:\d\d:\d\d$/' ) ; // created
        $this->checkXpathText( "/$prefix2/td", $agencyId ) ;
        $this->checkXpathText( "/$prefix2/td[2]", $address2 ) ;
        $this->checkXpathText( "/$prefix2/td[3]", $phone ) ;
        $this->checkXpathText( "/$prefix2/td[4]/a", $url ) ;
        $this->checkXpathPattern( "/$prefix2/td[5]", '/^\d\d\d\d-\d\d-\d\d \d\d:\d\d:\d\d$/' ) ; // updated
    }

    public function doTestCompanies() {
        if ( $this->_testMode < 0 ) {
            return ;
        }
        $driver = $this->webDriver ;
        $this->doLoadFromHeader( 'Companies' ) ;
        $this->checkHeaderLoads() ;
        $this->checkC1HR() ;
        $driver->findElement( WebDriverBy::id( 'AddButton' ) )->click() ;
        $this->doWaitFor( WebDriverBy::id( 'SaveButtonix1' ) ) ;
        $this->doWaitFor( WebDriverBy::id( 'CancelButtonix1' ) ) ;
        $this->checkIdText( 'SaveButtonix1', 'Save' ) ;
        $this->checkIdText( 'CancelButtonix1', 'Cancel' ) ;
        $this->doTypeAt( WebDriverBy::id( 'companyNameix1' ), 'Company 1' ) ;
        $this->doTypeAt( WebDriverBy::id( 'companyAddress1ix1' ), '1 Any Street' ) ;
        $this->doTypeAt( WebDriverBy::id( 'companyCityix1' ), 'City 1' ) ;
        $this->doTypeAt( WebDriverBy::id( 'companyStateix1' ), 'S1' ) ;
        $this->doTypeAt( WebDriverBy::id( 'companyZipix1' ), '11111-1111' ) ;
        $this->doTypeAt( WebDriverBy::id( 'companyAddress2ix1' ), '' ) ;
        $this->doTypeAt( WebDriverBy::id( 'companyPhoneix1' ), '111-111-1111' ) ;
        $this->doTypeAt( WebDriverBy::id( 'companyUrlix1' ), 'http://testme1.com/' ) ;
        $driver->findElement( WebDriverBy::id( 'SaveButtonix1' ) )->click() ;
        $this->doWaitFor( WebDriverBy::id( 'UpdateButton1' ) ) ;
        $this->doWaitFor( WebDriverBy::id( 'DeleteButton1' ) ) ;
        $this->checkC1HR() ;
        $this->checkC1R( 1, "/tr[@id='ux1-1']", "/tr[@id='ux1-2']"
                       , 'Company 1', '1 Any Street', 'City 1', 'S1', '11111-1111'
                       , 'None', '', '111-111-1111', 'http://testme1.com/' ) ;
        $driver->findElement( WebDriverBy::id( 'UpdateButton1' ) )->click() ;
        $this->checkHeaderLoads() ;
        $this->checkC1HR() ;
        $this->doWaitFor( WebDriverBy::id( 'SaveButton1' ) ) ;
        $this->doWaitFor( WebDriverBy::id( 'CancelButton1' ) ) ;
        $this->checkIdText( 'SaveButton1', 'Save' ) ;
        $this->checkIdText( 'CancelButton1', 'Cancel' ) ;
        $driver->findElement( WebDriverBy::id( 'CancelButton1' ) )->click() ;
        $this->doWaitFor( WebDriverBy::id( 'UpdateButton1' ) ) ;
        $this->doWaitFor( WebDriverBy::id( 'DeleteButton1' ) ) ;
        $this->checkHeaderLoads() ;
        $this->checkC1HR() ;
        $this->checkC1R( 1, "/tr[@id='ux1-1']", "/tr[@id='ux1-2']"
                , 'Company 1', '1 Any Street', 'City 1', 'S1', '11111-1111'
                , 'None', '', '111-111-1111', 'http://testme1.com/' ) ;
        $driver->findElement( WebDriverBy::id( 'DeleteButton1' ) )->click() ;
        $this->checkHeaderLoads() ;
        $this->checkC1HR() ;
        $this->doWaitFor( WebDriverBy::id( 'DeleteButton1' ) ) ;
        $this->doWaitFor( WebDriverBy::id( 'CancelButton1' ) ) ;
        $this->checkIdText( 'DeleteButton1', 'Confirm Delete' ) ;
        $this->checkIdText( 'CancelButton1', 'Cancel' ) ;
        $driver->findElement( WebDriverBy::id( 'CancelButton1' ) )->click() ;
        $this->doWaitFor( WebDriverBy::id( 'UpdateButton1' ) ) ;
        $this->doWaitFor( WebDriverBy::id( 'DeleteButton1' ) ) ;
        $this->checkHeaderLoads() ;
        $this->checkC1HR() ;
        $this->checkC1R( 1, "/tr[@id='ux1-1']", "/tr[@id='ux1-2']"
                , 'Company 1', '1 Any Street', 'City 1', 'S1', '11111-1111'
                , 'None', '', '111-111-1111', 'http://testme1.com/' ) ;
        $driver->findElement( WebDriverBy::id( 'DeleteButton1' ) )->click() ;
        $this->doWaitFor( WebDriverBy::id( 'CancelButton1' ) ) ;
        $this->doLoadFromHeader( 'Companies' ) ;
        $this->checkHeaderLoads() ;
        $this->doWaitFor( WebDriverBy::id( 'UpdateButton1' ) ) ;
        $this->doWaitFor( WebDriverBy::id( 'DeleteButton1' ) ) ;
        $this->checkC1HR() ;
        $this->checkC1R( 1, "/tr[@id='ux1-1']", "/tr[@id='ux1-2']"
                , 'Company 1', '1 Any Street', 'City 1', 'S1', '11111-1111'
                , 'None', '', '111-111-1111', 'http://testme1.com/' ) ;
        $driver->findElement( WebDriverBy::id( 'AddButton' ) )->click() ;
        $this->doWaitFor( WebDriverBy::id( 'SaveButtonix1' ) ) ;
        $this->doWaitFor( WebDriverBy::id( 'CancelButtonix1' ) ) ;
        $driver->findElement( WebDriverBy::id( 'AddButton' ) )->click() ;
        $this->doWaitFor( WebDriverBy::id( 'SaveButtonix2' ) ) ;
        $this->doWaitFor( WebDriverBy::id( 'CancelButtonix2' ) ) ;
        $driver->findElement( WebDriverBy::id( 'CancelButtonix1' ) )->click() ;
        $this->doSelectOption( WebDriverBy::id( 'agencyCompanyIdix2' ), 'Company 1' ) ;
        $this->doTypeAt( WebDriverBy::id( 'companyNameix2' ), 'Company 2' ) ;
        $this->doTypeAt( WebDriverBy::id( 'companyAddress1ix2' ), 'c/o Me' ) ;
        $this->doTypeAt( WebDriverBy::id( 'companyCityix2' ), 'City 2' ) ;
        $this->doTypeAt( WebDriverBy::id( 'companyStateix2' ), 'S2' ) ;
        $this->doTypeAt( WebDriverBy::id( 'companyZipix2' ), '22222-2222' ) ;
        $this->doTypeAt( WebDriverBy::id( 'companyAddress2ix2' ), '2 Any Street' ) ;
        $this->doTypeAt( WebDriverBy::id( 'companyPhoneix2' ), '222-222-2222' ) ;
        $this->doTypeAt( WebDriverBy::id( 'companyUrlix2' ), 'http://testme2.com/' ) ;
        $driver->findElement( WebDriverBy::id( 'SaveButtonix2' ) )->click() ;
        $this->doWaitFor( WebDriverBy::id( 'UpdateButton2' ) ) ;
        $this->doWaitFor( WebDriverBy::id( 'DeleteButton2' ) ) ;
        $this->assertEquals( 0, count( $driver->findElements( WebDriverBy::id( 'SaveButtonIx1' ) ) ) ) ;
        $this->assertEquals( 0, count( $driver->findElements( WebDriverBy::id( 'CancelButtonIx1' ) ) ) ) ;
        $this->checkC1HR() ;
        $this->checkC1R( 1, "/tr[@id='ux1-1']", "/tr[@id='ux1-2']"
                , 'Company 1', '1 Any Street', 'City 1', 'S1', '11111-1111'
                , 'None', '', '111-111-1111', 'http://testme1.com/' ) ;
        $this->checkC1HR() ;
        $this->checkC1R( 1, "/tr[@id='ux2-1']", "/tr[@id='ux2-2']"
                , 'Company 2', 'c/o Me', 'City 2', 'S2', '22222-2222'
                , 'Company 1', '2 Any Street', '222-222-2222', 'http://testme2.com/' ) ;
        $driver->findElement( WebDriverBy::id( 'UpdateButton2' ) )->click() ;
        $this->doWaitFor( WebDriverBy::id( 'SaveButton2' ) ) ;
        $this->doWaitFor( WebDriverBy::id( 'CancelButton2' ) ) ;
        $this->checkIdText( 'SaveButton2', 'Save' ) ;
        $this->checkIdText( 'CancelButton2', 'Cancel' ) ;
        $this->doSelectOption( WebDriverBy::id( 'agencyCompanyId2' ), '---' ) ;
        $this->doTypeAt( WebDriverBy::id( 'companyName2' ), 'Company 2b' ) ;
        $this->doTypeAt( WebDriverBy::id( 'companyAddress12' ), 'c/o Me 2' ) ;
        $this->doTypeAt( WebDriverBy::id( 'companyCity2' ), 'City 2b' ) ;
        $this->doTypeAt( WebDriverBy::id( 'companyState2' ), '22' ) ;
        $this->doTypeAt( WebDriverBy::id( 'companyZip2' ), '22222-222B' ) ;
        $this->doTypeAt( WebDriverBy::id( 'companyAddress22' ), '2B Any Street' ) ;
        $this->doTypeAt( WebDriverBy::id( 'companyPhone2' ), '222-222-222B' ) ;
        $this->doTypeAt( WebDriverBy::id( 'companyUrl2' ), 'http://www.testme2.com/' ) ;
        $driver->findElement( WebDriverBy::id( 'SaveButton2' ) )->click() ;
        $this->doWaitFor( WebDriverBy::id( 'UpdateButton2' ) ) ;
        $this->doWaitFor( WebDriverBy::id( 'DeleteButton2' ) ) ;
        $this->assertEquals( 0, count( $driver->findElements( WebDriverBy::id( 'SaveButtonIx1' ) ) ) ) ;
        $this->assertEquals( 0, count( $driver->findElements( WebDriverBy::id( 'CancelButtonIx1' ) ) ) ) ;
        $this->checkC1HR() ;
        $this->checkC1R( 1, "/tr[@id='ux1-1']", "/tr[@id='ux1-2']"
                , 'Company 1', '1 Any Street', 'City 1', 'S1', '11111-1111'
                , 'None', '', '111-111-1111', 'http://testme1.com/' ) ;
        $this->checkC1HR() ;
        $this->checkC1R( 1, "/tr[@id='ux2-1']", "/tr[@id='ux2-2']"
                , 'Company 2b', 'c/o Me 2', 'City 2b', '22', '22222-222B'
                , 'None', '2B Any Street', '222-222-222B', 'http://www.testme2.com/' ) ;
        $driver->findElement( WebDriverBy::id( 'UpdateButton2' ) )->click() ;
        $this->doWaitFor( WebDriverBy::id( 'SaveButton2' ) ) ;
        $this->doWaitFor( WebDriverBy::id( 'CancelButton2' ) ) ;
        $this->checkIdText( 'SaveButton2', 'Save' ) ;
        $this->checkIdText( 'CancelButton2', 'Cancel' ) ;
        $this->doSelectOption( WebDriverBy::id( 'agencyCompanyId2' ), 'Company 1' ) ;
        $this->doTypeAt( WebDriverBy::id( 'companyName2' ), 'Company 2c' ) ;
        $this->doTypeAt( WebDriverBy::id( 'companyAddress12' ), 'c/o Me 3' ) ;
        $this->doTypeAt( WebDriverBy::id( 'companyCity2' ), 'City 2c' ) ;
        $this->doTypeAt( WebDriverBy::id( 'companyState2' ), '32' ) ;
        $this->doTypeAt( WebDriverBy::id( 'companyZip2' ), '22222-222c' ) ;
        $this->doTypeAt( WebDriverBy::id( 'companyAddress22' ), '2c Any Street' ) ;
        $this->doTypeAt( WebDriverBy::id( 'companyPhone2' ), '222-222-222c' ) ;
        $this->doTypeAt( WebDriverBy::id( 'companyUrl2' ), 'http://www3.testme2.com/' ) ;
        $driver->findElement( WebDriverBy::id( 'SaveButton2' ) )->click() ;
        $this->doWaitFor( WebDriverBy::id( 'UpdateButton2' ) ) ;
        $this->doWaitFor( WebDriverBy::id( 'DeleteButton2' ) ) ;
        $this->assertEquals( 0, count( $driver->findElements( WebDriverBy::id( 'SaveButtonIx1' ) ) ) ) ;
        $this->assertEquals( 0, count( $driver->findElements( WebDriverBy::id( 'CancelButtonIx1' ) ) ) ) ;
        $this->checkC1HR() ;
        $this->checkC1R( 1, "/tr[@id='ux1-1']", "/tr[@id='ux1-2']"
                , 'Company 1', '1 Any Street', 'City 1', 'S1', '11111-1111'
                , 'None', '', '111-111-1111', 'http://testme1.com/' ) ;
        $this->checkC1HR() ;
        $this->checkC1R( 1, "/tr[@id='ux2-1']", "/tr[@id='ux2-2']"
                , 'Company 2c', 'c/o Me 3', 'City 2c', '32', '22222-222c'
                , 'Company 1', '2c Any Street', '222-222-222c', 'http://www3.testme2.com/' ) ;
        $driver->findElement( WebDriverBy::id( 'DeleteButton1' ) )->click() ;
        $this->doWaitFor( WebDriverBy::id( 'DeleteButton1' ) ) ;
        $this->doWaitFor( WebDriverBy::id( 'CancelButton1' ) ) ;
        $this->checkIdText( 'DeleteButton1', 'Confirm Delete' ) ;
        $this->checkIdText( 'CancelButton1', 'Cancel' ) ;
        $driver->findElement( WebDriverBy::id( 'DeleteButton1' ) )->click() ;
        // FIXME Tests/IntegrationTests.php:doTestCompanies() - look for "Undefined Result" in output
        $this->doLoadFromHeader( 'Companies' ) ;
        $this->checkHeaderLoads() ;
        $this->checkC1R( 2, "/tr[@id='ux2-1']", "/tr[@id='ux2-2']"
                       , 'Company 2c', 'c/o Me 3', 'City 2c', '32', '22222-222c'
                       , 'None', '2c Any Street', '222-222-222c', 'http://www3.testme2.com/' ) ;
    }

    public function checkC2HR() {
        if ( $this->_testMode < 1 ) {
            return ;
        }
        $this->checkXpathText( '//button', 'Add Contact' ) ;
        $this->checkXpathText( '//caption', 'Current Contacts' ) ;
        $this->checkXpathText( '//th', 'Actions' ) ;
        $this->checkXpathText( '//th[2]', 'Company' ) ;
        $this->checkXpathText( '//th[3]', 'Name' ) ;
        $this->checkXpathText( '//th[4]', 'Email' ) ;
        $this->checkXpathText( '//th[5]', 'Phone' ) ;
        $this->checkXpathText( '//th[6]', 'Alternate Phone' ) ;
        $this->checkXpathText( '//th[7]', 'Created' ) ;
        $this->checkXpathText( '//th[8]', 'Updated' ) ;
    }

    public function checkC2R( $id
                            , $prefix
                            , $contactCompany
                            , $contactName
                            , $contactEmail
                            , $contactPhone
                            , $contactAlternatePhone
                            ) {
        if ( $this->_testMode < 1 ) {
            return ;
        }
        $this->checkIdText( "UpdateButton$id", 'Update' ) ;
        $this->checkIdText( "DeleteButton$id", 'Delete' ) ;
        $this->checkXpathText( "/$prefix/td[2]", $contactCompany ) ;
        $this->checkXpathText( "/$prefix/td[3]", $contactName ) ;
        $this->checkXpathText( "/$prefix/td[4]", $contactEmail ) ;
        $this->checkXpathText( "/$prefix/td[5]", $contactPhone ) ;
        $this->checkXpathText( "/$prefix/td[6]", $contactAlternatePhone ) ;
        $this->checkXpathPattern( "/$prefix/td[7]", '/^\d\d\d\d-\d\d-\d\d \d\d:\d\d:\d\d$/' ) ;
        $this->checkXpathPattern( "/$prefix/td[8]", '/^\d\d\d\d-\d\d-\d\d \d\d:\d\d:\d\d$/' ) ;
    }

    public function doTestContacts() {
        if ( $this->_testMode < 0 ) {
            return ;
        }
        $driver = $this->webDriver ;
        $this->doLoadFromHeader( 'Contacts' ) ;
        $this->checkHeaderLoads() ;
        $this->checkC2HR() ;
        $driver->findElement( WebDriverBy::id( 'AddButton' ) )->click() ;
        $this->doWaitFor( WebDriverBy::id( 'SaveButtonix1' ) ) ;
        $this->doWaitFor( WebDriverBy::id( 'CancelButtonix1' ) ) ;
        $this->doTypeAt( WebDriverBy::id( 'nameix1' ), 'John Doe' ) ;
        $this->doTypeAt( WebDriverBy::id( 'emailix1' ), 'john.doe@example1.com' ) ;
        $this->doTypeAt( WebDriverBy::id( 'phoneix1' ), '999-555-1212' ) ;
        $this->doTypeAt( WebDriverBy::id( 'alternatePhoneix1' ), '999-555-1234' ) ;
        $driver->findElement( WebDriverBy::id( 'SaveButtonix1' ) )->click() ;
        $this->checkHeaderLoads() ;
        $this->checkC2HR() ;
        $this->checkC2R( 1, '', '---', 'John Doe', 'john.doe@example1.com', '999-555-1212', '999-555-1234' ) ;
        $driver->findElement( WebDriverBy::id( 'AddButton' ) )->click() ;
        $this->doWaitFor( WebDriverBy::id( 'SaveButtonix2' ) ) ;
        $this->doWaitFor( WebDriverBy::id( 'CancelButtonix2' ) ) ;
        sleep(60) ;
        $this->doSelectOption( 'companyIdix2', '2' ) ;
        $this->doTypeAt( WebDriverBy::id( 'nameix2' ), 'Jane Smith' ) ;
        $this->doTypeAt( WebDriverBy::id( 'emailix2' ), 'janesmith@example2.com' ) ;
        $this->doTypeAt( WebDriverBy::id( 'phoneix2' ), '999-000-1212' ) ;
        $this->doTypeAt( WebDriverBy::id( 'alternatePhoneix2' ), '999-000-1234' ) ;
        $driver->findElement( WebDriverBy::id( 'SaveButtonix2' ) )->click() ;
        $this->checkC2HR() ;
        $this->checkC2R( 1, '/tr[2]', '---', 'John Doe', 'john.doe@example1.com', '999-555-1212', '999-555-1234' ) ;
        $this->checkC2R( 2, '', 'Company 2c', 'Jane Smith', 'janesmith@example2.com', '999-000-1212', '999-000-1234' ) ;

        // @todo Implement Tests/IntegrationTests.php:doTestContacts
        sleep( 15 ) ;
        $this->markTestIncomplete( 'Left off here.' ) ;
    }

    public function doTestJobs() {
        if ( $this->_testMode < 0 ) {
            return ;
        }
        $driver = $this->webDriver ;
        $this->doLoadFromHeader( 'Jobs' ) ;
        $this->checkHeaderLoads() ;
        $this->checkJHR() ;

        // @todo Implement Tests/IntegrationTests.php:doTestJobs()
        sleep( 15 ) ;
        $this->markTestIncomplete( 'Left off here.' ) ;
    }

    public function doTestKeywords() {
        if ( $this->_testMode < 0 ) {
            return ;
        }
        $driver = $this->webDriver ;
        $this->doLoadFromHeader( 'Keywords' ) ;
        $this->checkHeaderLoads() ;
        $this->checkC1HR() ;

        // @todo Implement IntegrationTests.php:doTestKeywords()
        sleep( 15 ) ;
        $this->markTestIncomplete( 'Left off here.' ) ;
    }

    public function doTestSearches() {
        if ( $this->_testMode < 0 ) {
            return ;
        }
        $driver = $this->webDriver ;
        $this->doLoadFromHeader( 'Searches' ) ;
        $this->checkHeaderLoads() ;
        $this->checkSeHR() ;

        // @todo Implement IntegrationTests.php:doTestSearches()
        sleep( 15 ) ;
        $this->markTestIncomplete( 'Left off here.' ) ;
    }

    public function doTestSummary2() {
        if ( $this->_testMode < 0 ) {
            return ;
        }
        $driver = $this->webDriver ;
        $this->doLoadFromHeader( 'Summary' ) ;
        $this->checkHeaderLoads() ;
        $this->checkSuHR() ;

        // @todo Implement IntegrationTests.php:doTestSummary2()
        sleep( 15 ) ;
        $this->markTestIncomplete( 'Left off here.' ) ;
    }

    /**
     * @group minimal
     * @group doReset
     * @group skipReset
     * @group full
     */
    public function testTestMode() {
        if ( $this->_testMode < 100 ) {
            $this->markTestIncomplete( '_testMode is less than full value' ) ;
        }
        else {
            $this->assertTrue( true, 'Test mode at full value.' ) ;
        }
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
        if ( $this->_testMode >= 0 ) {
            // Check that all the pages in the header load properly
            foreach ( $this->_headerTags as $headerTag ) {
                $this->doLoadFromHeader( $headerTag ) ;
            }
        }

        // FIXME Finish implementation of Tests/IntegrationTests.php:testWebSite sub-routines.
//         $this->doTestSummary1() ;
        $this->doTestApplicationStatuses() ;
        $this->doTestCompanies() ;
        $this->doTestContacts() ;
        $this->doTestJobs() ;
        $this->doTestKeywords() ;
        $this->doTestSearches() ;
        $this->doTestSummary2() ;

    }

}
