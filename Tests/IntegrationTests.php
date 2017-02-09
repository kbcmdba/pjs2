<?php

/**
 * phpjobseeker
 *
 * Copyright (C) 2009, 2015, 2017 Kevin Benton - kbenton at bentonfam dot org
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
    private $_lookFor    = '' ;
    private $_retVal     = 0 ;

    /**
     * @var integer -1 = no testing, 0 = minimal testing, 1 = brief testing, 100 = full testing
     */
    private $_testMode   = 100 ;

    public function setUp() {
        $capabilities = DesiredCapabilities::firefox() ;
        $this->webDriver = RemoteWebDriver::create( 'http://localhost:4444/wd/hub', $capabilities ) ;
    }

    public function tearDown() {
       $this->webDriver->quit() ;
    }

    public function doWaitFor( $target, $timeout = 20, $interval = 250 ) {
        $lookFor = $target ;
        $ret = 0 ;
        $this->webDriver->wait($timeout, $interval)->until( function ( $webDriver ) use ( &$lookFor, &$ret ) {
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
        $lookFor = "<!-- EndOfPage --></body>\n</html>" ;
        $this->webDriver->wait( 60, 300 )->until( function ( $webDriver ) use ( &$lookFor ) {
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
            $this->markTestIncomplete( 'Left off here. ' . __FILE__ . ':' . __LINE__ ) ;
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

    public function checkSuHR() {
        if ( $this->_testMode < 0 ) {
            return ;
        }
        sleep( 15 ) ;
        $this->markTestIncomplete( 'Left off here. ' . __FILE__ . ':' . __LINE__ ) ;
    }

    public function doTestSummary1() {
        // FIXME Turn doTestSummary1 back on.
        return ;
        if ( $this->_testMode < 0 ) {
            return ;
        }
        $driver = $this->webDriver ;
        $this->doLoadFromHeader( 'Summary' ) ;
        $this->checkHeaderLoads() ;
        $this->checkSuHR() ;

        // @todo Implement IntegrationTests.php:doTestSummary2()
        sleep( 15 ) ;
        $this->markTestIncomplete( 'Left off here. ' . __FILE__ . ':' . __LINE__ ) ;
    }

    public function checkASHR() {
        if ( $this->_testMode < 0 ) {
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
        if ( $this->_testMode <= 1 ) {
            return ;
        }
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
        if ( $this->_testMode < 0 ) {
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
        $this->checkXpathText( '//th[7]', 'Last Contacted' ) ;
        $this->checkXpathText( '//th[8]', 'Created' ) ;
        $this->checkXpathText( '//tr[2]/th', 'Agency' ) ;
        $this->checkXpathText( '//tr[2]/th[2]', 'Address 2' ) ;
        $this->checkXpathText( '//tr[2]/th[3]', 'Phone' ) ;
        $this->checkXpathText( '//tr[2]/th[4]', 'URL' ) ;
        $this->checkXpathText( '//tr[2]/th[5]', 'Updated' ) ;
    }

    public function checkC1R( $id, $prefix1, $prefix2
                            , $company, $address1, $city, $state, $zip
                            , $agencyId, $address2, $phone, $url
                            , $lastContacted
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
        $this->checkXpathText( "/$prefix1/td[7]", $lastContacted ) ;
        $this->checkXpathPattern( "/$prefix1/td[8]", '/^\d\d\d\d-\d\d-\d\d \d\d:\d\d:\d\d$/' ) ; // created
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
        $this->doTypeAt( WebDriverBy::id( 'lastContactedix1'), '2017-01-01 00:00:00' ) ;
        if ( $this->_testMode <= 1 ) {
            return ;
        }
        $driver->findElement( WebDriverBy::id( 'SaveButtonix1' ) )->click() ;
        $this->doWaitFor( WebDriverBy::id( 'UpdateButton1' ) ) ;
        $this->doWaitFor( WebDriverBy::id( 'DeleteButton1' ) ) ;
        $this->checkC1HR() ;
        $this->checkC1R( 1, "/tr[@id='ux1-1']", "/tr[@id='ux1-2']"
                       , 'Company 1', '1 Any Street', 'City 1', 'S1', '11111-1111'
                       , 'None', '', '111-111-1111', 'http://testme1.com/'
                       , '2017-01-01 00:00:00'
                       ) ;
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
                , 'None', '', '111-111-1111', 'http://testme1.com/'
                , '2017-01-01 00:00:00'
                ) ;
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
                       , 'None', '', '111-111-1111', 'http://testme1.com/'
                       , '2017-01-01 00:00:00'
                       ) ;
        $driver->findElement( WebDriverBy::id( 'DeleteButton1' ) )->click() ;
        $this->doWaitFor( WebDriverBy::id( 'CancelButton1' ) ) ;
        $this->doLoadFromHeader( 'Companies' ) ;
        $this->checkHeaderLoads() ;
        $this->doWaitFor( WebDriverBy::id( 'UpdateButton1' ) ) ;
        $this->doWaitFor( WebDriverBy::id( 'DeleteButton1' ) ) ;
        $this->checkC1HR() ;
        $this->checkC1R( 1, "/tr[@id='ux1-1']", "/tr[@id='ux1-2']"
                       , 'Company 1', '1 Any Street', 'City 1', 'S1', '11111-1111'
                       , 'None', '', '111-111-1111', 'http://testme1.com/'
                       , '2017-01-01 00:00:00'
                       ) ;
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
                       , 'None', '', '111-111-1111', 'http://testme1.com/'
                       , '2017-01-01 00:00:00'
                       ) ;
        $this->checkC1HR() ;
        $this->checkC1R( 1, "/tr[@id='ux2-1']", "/tr[@id='ux2-2']"
                       , 'Company 2', 'c/o Me', 'City 2', 'S2', '22222-2222'
                       , 'Company 1', '2 Any Street', '222-222-2222', 'http://testme2.com/'
                       , ''
                       ) ;
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
                       , 'None', '', '111-111-1111', 'http://testme1.com/'
                       , '2017-01-01 00:00:00'
                       ) ;
        $this->checkC1R( 1, "/tr[@id='ux2-1']", "/tr[@id='ux2-2']"
                       , 'Company 2b', 'c/o Me 2', 'City 2b', '22', '22222-222B'
                       , 'None', '2B Any Street', '222-222-222B', 'http://www.testme2.com/'
                       , ''
                       ) ;
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
                       , 'None', '', '111-111-1111', 'http://testme1.com/'
                       , '2017-01-01 00:00:00'
                       ) ;
        $this->checkC1R( 1, "/tr[@id='ux2-1']", "/tr[@id='ux2-2']"
                       , 'Company 2c', 'c/o Me 3', 'City 2c', '32', '22222-222c'
                       , 'Company 1', '2c Any Street', '222-222-222c', 'http://www3.testme2.com/'
                       , ''
                       ) ;
        $driver->findElement( WebDriverBy::id( 'DeleteButton1' ) )->click() ;
        $this->doWaitFor( WebDriverBy::id( 'DeleteButton1' ) ) ;
        $this->doWaitFor( WebDriverBy::id( 'CancelButton1' ) ) ;
        $this->checkIdText( 'DeleteButton1', 'Confirm Delete' ) ;
        $this->checkIdText( 'CancelButton1', 'Cancel' ) ;
        $driver->findElement( WebDriverBy::id( 'DeleteButton1' ) )->click() ;
        $this->doLoadFromHeader( 'Companies' ) ;
        $this->checkHeaderLoads() ;
        $this->checkC1R( 2, "/tr[@id='ux2-1']", "/tr[@id='ux2-2']"
                       , 'Company 2c', 'c/o Me 3', 'City 2c', '32', '22222-222c'
                       , 'None', '2c Any Street', '222-222-222c', 'http://www3.testme2.com/'
                       , ''
                       ) ;
        $driver->findElement( WebDriverBy::id( 'AddButton' ) )->click() ;
        $this->doWaitFor( WebDriverBy::id( 'SaveButtonix1' ) ) ;
        $this->doWaitFor( WebDriverBy::id( 'CancelButtonix1' ) ) ;
        $this->checkIdText( 'SaveButtonix1', 'Save' ) ;
        $this->checkIdText( 'CancelButtonix1', 'Cancel' ) ;
        $this->doTypeAt( WebDriverBy::id( 'companyNameix1' ), 'Company 3' ) ;
        $this->doTypeAt( WebDriverBy::id( 'companyAddress1ix1' ), '3 Any Street' ) ;
        $this->doTypeAt( WebDriverBy::id( 'companyCityix1' ), 'City 3' ) ;
        $this->doTypeAt( WebDriverBy::id( 'companyStateix1' ), 'S3' ) ;
        $this->doTypeAt( WebDriverBy::id( 'companyZipix1' ), '33333-3333' ) ;
        $this->doTypeAt( WebDriverBy::id( 'companyAddress2ix1' ), '' ) ;
        $this->doTypeAt( WebDriverBy::id( 'companyPhoneix1' ), '333-333-3333' ) ;
        $this->doTypeAt( WebDriverBy::id( 'companyUrlix1' ), 'http://testme3.com/' ) ;
        $driver->findElement( WebDriverBy::id( 'SaveButtonix1' ) )->click() ;
        $this->checkHeaderLoads() ;
        $this->checkC1R( 2, "/tr[@id='ux2-1']", "/tr[@id='ux2-2']"
                       , 'Company 2c', 'c/o Me 3', 'City 2c', '32', '22222-222c'
                       , 'None', '2c Any Street', '222-222-222c', 'http://www3.testme2.com/'
                       , ''
                       ) ;
        $this->checkC1R( 3, "/tr[@id='ux3-1']", "/tr[@id='ux3-2']"
                       , 'Company 3', '3 Any Street', 'City 3', 'S3', '33333-3333'
                       , 'None', '', '333-333-3333', 'http://testme3.com/'
                       , ''
                       ) ;
    }

    public function checkC2HR() {
        if ( $this->_testMode < 0 ) {
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
        if ( $this->_testMode <= 1 ) {
            return ;
        }
        $driver->findElement( WebDriverBy::id( 'SaveButtonix1' ) )->click() ;
        $this->checkHeaderLoads() ;
        $this->checkC2HR() ;
        $this->doWaitFor( WebDriverBy::id( 'UpdateButton1' ) ) ;
        $this->doWaitFor( WebDriverBy::id( 'DeleteButton1' ) ) ;
        $this->checkC2R( 1, '', '---', 'John Doe', 'john.doe@example1.com', '999-555-1212', '999-555-1234' ) ;
        $driver->findElement( WebDriverBy::id( 'AddButton' ) )->click() ;
        $this->checkHeaderLoads() ;
        $this->checkC2HR() ;
        $this->doWaitFor( WebDriverBy::id( 'SaveButtonix2' ) ) ;
        $this->doWaitFor( WebDriverBy::id( 'CancelButtonix2' ) ) ;
        $this->doSelectOption( WebDriverBy::id( 'companyIdix2' ), 'Company 2c' ) ;
        $this->doTypeAt( WebDriverBy::id( 'nameix2' ), 'Jane Smith' ) ;
        $this->doTypeAt( WebDriverBy::id( 'emailix2' ), 'janesmith@example2.com' ) ;
        $this->doTypeAt( WebDriverBy::id( 'phoneix2' ), '999-000-1212' ) ;
        $this->doTypeAt( WebDriverBy::id( 'alternatePhoneix2' ), '999-000-1234' ) ;
        $driver->findElement( WebDriverBy::id( 'SaveButtonix2' ) )->click() ;
        $this->checkHeaderLoads() ;
        $this->checkC2HR() ;
        $this->doWaitFor( WebDriverBy::id( 'UpdateButton1' ) ) ;
        $this->doWaitFor( WebDriverBy::id( 'DeleteButton1' ) ) ;
        $this->doWaitFor( WebDriverBy::id( 'UpdateButton2' ) ) ;
        $this->doWaitFor( WebDriverBy::id( 'DeleteButton2' ) ) ;
        $this->checkC2R( 2, '', 'Company 2c', 'Jane Smith', 'janesmith@example2.com', '999-000-1212', '999-000-1234' ) ;
        $this->checkC2R( 1, '/tr[3]', '---', 'John Doe', 'john.doe@example1.com', '999-555-1212', '999-555-1234' ) ;
        $driver->findElement( WebDriverBy::id( 'UpdateButton1' ) )->click() ;
        $this->checkHeaderLoads() ;
        $this->checkC2HR() ;
        $this->doWaitFor( WebDriverBy::id( 'SaveButton1' ) ) ;
        $this->doWaitFor( WebDriverBy::id( 'CancelButton1' ) ) ;
        $this->checkIdText( 'SaveButton1', 'Save' ) ;
        $this->checkIdText( 'CancelButton1', 'Cancel' ) ;
        $driver->findElement( WebDriverBy::id( 'CancelButton1' ) )->click() ;
        $this->checkHeaderLoads() ;
        $this->checkC2HR() ;
        $this->doWaitFor( WebDriverBy::id( 'UpdateButton1' ) ) ;
        $this->doWaitFor( WebDriverBy::id( 'DeleteButton1' ) ) ;
        $driver->findElement( WebDriverBy::id( 'DeleteButton1' ) )->click() ;
        $this->checkHeaderLoads() ;
        $this->checkC2HR() ;
        $this->doWaitFor( WebDriverBy::id( 'DeleteButton1' ) ) ;
        $this->doWaitFor( WebDriverBy::id( 'CancelButton1' ) ) ;
        $this->checkIdText( 'DeleteButton1', 'Confirm Delete' ) ;
        $this->checkIdText( 'CancelButton1', 'Cancel' ) ;
        $driver->findElement( WebDriverBy::id( 'CancelButton1' ) )->click() ;
        $this->checkHeaderLoads() ;
        $this->checkC2HR() ;
        $this->doWaitFor( WebDriverBy::id( 'UpdateButton1' ) ) ;
        $this->doWaitFor( WebDriverBy::id( 'DeleteButton1' ) ) ;
        $driver->findElement( WebDriverBy::id( 'UpdateButton2' ) )->click() ;
        $this->checkHeaderLoads() ;
        $this->checkC2HR() ;
        $this->doWaitFor( WebDriverBy::id( 'SaveButton2' ) ) ;
        $this->doWaitFor( WebDriverBy::id( 'CancelButton2' ) ) ;
        $this->doSelectOption( WebDriverBy::id( 'companyId2' ), '---' ) ;
        $this->doTypeAt( WebDriverBy::id( 'name2' ), 'Jane Smithy' ) ;
        $this->doTypeAt( WebDriverBy::id( 'email2' ), 'janesmithy@example2.com' ) ;
        $this->doTypeAt( WebDriverBy::id( 'phone2' ), '999-000-1313' ) ;
        $this->doTypeAt( WebDriverBy::id( 'alternatePhone2' ), '999-000-2345' ) ;
        $driver->findElement( WebDriverBy::id( 'SaveButton2' ) )->click() ;
        $this->checkHeaderLoads() ;
        $this->checkC2HR() ;
        $this->doWaitFor( WebDriverBy::id( 'UpdateButton1' ) ) ;
        $this->doWaitFor( WebDriverBy::id( 'DeleteButton1' ) ) ;
        $this->doWaitFor( WebDriverBy::id( 'UpdateButton2' ) ) ;
        $this->doWaitFor( WebDriverBy::id( 'DeleteButton2' ) ) ;
        $this->checkC2R( 2, '', '---', 'Jane Smithy', 'janesmithy@example2.com', '999-000-1313', '999-000-2345' ) ;
        $this->checkC2R( 1, '/tr[3]', '---', 'John Doe', 'john.doe@example1.com', '999-555-1212', '999-555-1234' ) ;
        $driver->findElement( WebDriverBy::id( 'UpdateButton2' ) )->click() ;
        $this->checkHeaderLoads() ;
        $this->checkC2HR() ;
        $this->doWaitFor( WebDriverBy::id( 'SaveButton2' ) ) ;
        $this->doWaitFor( WebDriverBy::id( 'CancelButton2' ) ) ;
        $this->doSelectOption( WebDriverBy::id( 'companyId2' ), 'Company 2c' ) ;
        $driver->findElement( WebDriverBy::id( 'SaveButton2' ) )->click() ;
        $this->checkHeaderLoads() ;
        $this->checkC2HR() ;
        $this->doWaitFor( WebDriverBy::id( 'UpdateButton1' ) ) ;
        $this->doWaitFor( WebDriverBy::id( 'DeleteButton1' ) ) ;
        $this->doWaitFor( WebDriverBy::id( 'UpdateButton2' ) ) ;
        $this->doWaitFor( WebDriverBy::id( 'DeleteButton2' ) ) ;
        $this->checkC2R( 2, '', 'Company 2c', 'Jane Smithy', 'janesmithy@example2.com', '999-000-1313', '999-000-2345' ) ;
        $this->checkC2R( 1, '/tr[3]', '---', 'John Doe', 'john.doe@example1.com', '999-555-1212', '999-555-1234' ) ;
        $driver->findElement( WebDriverBy::id( 'DeleteButton2' ) )->click() ;
        $this->checkHeaderLoads() ;
        $this->checkC2HR() ;
        $this->doWaitFor( WebDriverBy::id( 'DeleteButton2' ) ) ;
        $this->doWaitFor( WebDriverBy::id( 'CancelButton2' ) ) ;
        $this->checkIdText( 'DeleteButton2', 'Confirm Delete' ) ;
        $this->checkIdText( 'CancelButton2', 'Cancel' ) ;
        $driver->findElement( WebDriverBy::id( 'DeleteButton2' ) )->click() ;
        $this->checkHeaderLoads() ;
        $this->checkC2HR() ;
        $this->doWaitFor( WebDriverBy::id( 'UpdateButton1' ) ) ;
        $this->doWaitFor( WebDriverBy::id( 'DeleteButton1' ) ) ;
        $this->checkC2R( 1, '', '---', 'John Doe', 'john.doe@example1.com', '999-555-1212', '999-555-1234' ) ;
        $this->checkNotPresent( WebDriverBy::id( 'UpdateButton2' ) ) ;
        $this->checkNotPresent( WebDriverBy::id( 'DeleteButton2' ) ) ;
        $this->doLoadFromHeader( 'Contacts' ) ;
        $this->checkHeaderLoads() ;
        $this->checkC2HR() ;
        $driver->findElement( WebDriverBy::id( 'AddButton' ) )->click() ;
        $this->doWaitFor( WebDriverBy::id( 'SaveButtonix1' ) ) ;
        $this->doWaitFor( WebDriverBy::id( 'CancelButtonix1' ) ) ;
        $this->doTypeAt( WebDriverBy::id( 'nameix1' ), 'Jane Smith' ) ;
        $this->doTypeAt( WebDriverBy::id( 'emailix1' ), 'jane.smith@example3.com' ) ;
        $this->doTypeAt( WebDriverBy::id( 'phoneix1' ), '333-333-3333' ) ;
        $this->doTypeAt( WebDriverBy::id( 'alternatePhoneix1' ), '333-333-1234' ) ;
        $driver->findElement( WebDriverBy::id( 'SaveButtonix1' ) )->click() ;
        $this->checkC2R( 3, '', '---', 'Jane Smith', 'jane.smith@example3.com', '333-333-3333', '333-333-1234' ) ;
        $this->checkC2R( 1, '/tr[2]', '---', 'John Doe', 'john.doe@example1.com', '999-555-1212', '999-555-1234' ) ;
    }

    public function checkJHR() {
        if ( $this->_testMode < 0 ) {
            return ;
        }
        $this->checkXpathText( '//button', 'Add Job' ) ;
        $this->checkXpathText( '//caption', 'Current Jobs' ) ;
        $this->checkXpathText( '//th', 'Actions' ) ;
        $this->checkXpathText( '//th[2]', 'Urgency' ) ;
        $this->checkXpathText( '//th[3]', 'Title' ) ;
        $this->checkXpathText( '//th[4]', 'Location' ) ;
        $this->checkXpathText( '//th[5]', 'Company' ) ;
        $this->checkXpathText( '//th[6]', 'Contact' ) ;
        $this->checkXpathText( '//th[7]', 'Status' ) ;
        $this->checkXpathText( '//th[8]', 'Next Action' ) ;
        $this->checkXpathText( '//th[9]', 'Next Action Due' ) ;
        $this->checkXpathText( '//th[10]', 'URL' ) ;
        $this->checkXpathText( '//th[11]', 'Last Status Change' ) ;
        $this->checkXpathText( '//th[12]', 'Created' ) ;
        $this->checkXpathText( '//th[13]', 'Updated' ) ;
    }

    public function checkJR( $id
                           , $prefix
                           , $primaryContact
                           , $company
                           , $applicationStatus
                           , $lastStatusChange
                           , $urgency
                           , $nextActionDue
                           , $nextAction
                           , $positionTitle
                           , $location
                           , $url ) {
        if ( $this->_testMode < 1 ) {
            return ;
        }
        $this->checkIdText( "UpdateButton$id", 'Update' ) ;
        $this->checkIdText( "DeleteButton$id", 'Delete' ) ;
        $this->checkXpathText( "/$prefix/td[2]", $urgency ) ;
        $this->checkXpathText( "/$prefix/td[3]", $positionTitle ) ;
        $this->checkXpathText( "/$prefix/td[4]", $location ) ;
        $this->checkXpathText( "/$prefix/td[5]", $company ) ;
        $this->checkXpathText( "/$prefix/td[6]", $primaryContact ) ;
        $this->checkXpathText( "/$prefix/td[7]", $applicationStatus ) ;
        $this->checkXpathText( "/$prefix/td[8]", $nextAction ) ;
        $this->checkXpathText( "/$prefix/td[9]", $nextActionDue . ' 00:00:00' ) ;
        $this->checkXpathText( "/$prefix/td[10]", $url ) ;
        $this->checkXpathText( "/$prefix/td[11]", $lastStatusChange . ' 00:00:00' ) ;
    }

    public function doTestJobs() {
        if ( $this->_testMode < 0 ) {
            return ;
        }
        $driver = $this->webDriver ;
        $this->doLoadFromHeader( 'Jobs' ) ;
        $this->checkHeaderLoads() ;
        $this->checkJHR() ;
        $driver->findElement( WebDriverBy::id( 'AddButton' ) )->click() ;
        $this->doWaitFor( WebDriverBy::id( 'SaveButtonix1' ) ) ;
        $this->doWaitFor( WebDriverBy::id( 'CancelButtonix1' ) ) ;
        if ( $this->_testMode <= 1 ) {
            return ;
        }
        $this->doSelectOption( WebDriverBy::id( 'urgencyix1' ), 'low' ) ;
        $this->doTypeAt( WebDriverBy::id( 'positionTitleix1' ), 'Janitor' ) ;
        $this->doTypeAt( WebDriverBy::id( 'locationix1' ), 'Hershey, PA' ) ;
        $this->doSelectOption( WebDriverBy::id( 'companyIdix1' ), 'Company 2c' ) ;
        $this->doSelectOption( WebDriverBy::id( 'contactIdix1' ), 'John Doe' ) ;
        $this->doSelectOption( WebDriverBy::id( 'applicationStatusIdix1' ), 'FOUND' ) ;
        $this->doTypeAt( WebDriverBy::id( 'nextActionix1' ), 'Do something' ) ;
        // @todo Compute dates for these records
        $this->doTypeAt( WebDriverBy::id( 'nextActionDueix1' ), '2017-08-01' ) ;
        $this->doTypeAt( WebDriverBy::id( 'urlix1' ), 'http://www.testme1.com/' ) ;
        $this->doTypeAt( WebDriverBy::id( 'lastStatusChangeix1' ), '2017-08-01' ) ;
        $driver->findElement( WebDriverBy::id( 'SaveButtonix1' ) )->click() ;
        $this->doWaitFor( WebDriverBy::id( 'UpdateButton1' ) ) ;
        $this->doWaitFor( WebDriverBy::id( 'DeleteButton1' ) ) ;
        $this->checkJR( 1, '', 'John Doe', 'Company 2c', 'FOUND', '2017-08-01', 'low', '2017-08-01', 'Do something', 'Janitor', 'Hershey, PA', 'http://www.testme1.com/' ) ;
        $driver->findElement( WebDriverBy::id( 'AddButton' ) )->click() ;
        $this->doWaitFor( WebDriverBy::id( 'SaveButtonix2' ) ) ;
        $this->doWaitFor( WebDriverBy::id( 'CancelButtonix2' ) ) ;
        $this->doSelectOption( WebDriverBy::id( 'urgencyix2' ), 'low' ) ;
        $this->doTypeAt( WebDriverBy::id( 'positionTitleix2' ), 'Floor Sweeper' ) ;
        $this->doTypeAt( WebDriverBy::id( 'locationix2' ), 'Hershey, PA' ) ;
        $this->doSelectOption( WebDriverBy::id( 'companyIdix2' ), 'Company 2c' ) ;
        $this->doSelectOption( WebDriverBy::id( 'contactIdix2' ), 'John Doe' ) ;
        $this->doSelectOption( WebDriverBy::id( 'applicationStatusIdix2' ), 'FOUND' ) ;
        $this->doTypeAt( WebDriverBy::id( 'nextActionix2' ), 'Do something else' ) ;
        // @todo Compute dates for these records
        $this->doTypeAt( WebDriverBy::id( 'nextActionDueix2' ), '2017-08-02' ) ;
        $this->doTypeAt( WebDriverBy::id( 'urlix2' ), 'http://www.testme2.com/' ) ;
        $this->doTypeAt( WebDriverBy::id( 'lastStatusChangeix2' ), '2017-08-01' ) ;
        $driver->findElement( WebDriverBy::id( 'SaveButtonix2' ) )->click() ;
        $this->doWaitFor( WebDriverBy::id( 'UpdateButton2' ) ) ;
        $this->doWaitFor( WebDriverBy::id( 'DeleteButton2' ) ) ;
        $this->checkJR( 2, '', 'John Doe', 'Company 2c', 'FOUND', '2017-08-01', 'low', '2017-08-02', 'Do something else', 'Floor Sweeper', 'Hershey, PA', 'http://www.testme2.com/' ) ;
        $this->checkJR( 1, '/tr[3]', 'John Doe', 'Company 2c', 'FOUND', '2017-08-01', 'low', '2017-08-01', 'Do something', 'Janitor', 'Hershey, PA', 'http://www.testme1.com/' ) ;
        $driver->findElement( WebDriverBy::id( 'AddButton' ) )->click() ;
        $this->doWaitFor( WebDriverBy::id( 'SaveButtonix3' ) ) ;
        $this->doWaitFor( WebDriverBy::id( 'CancelButtonix3' ) ) ;
        $driver->findElement( WebDriverBy::id( 'CancelButtonix3' ) )->click() ;
        $this->checkHeaderLoads() ;
        $this->checkNotPresent( WebDriverBy::id( 'SaveButtonix3' ) ) ;
        $this->checkNotPresent( WebDriverBy::id( 'CancelButtonix3' ) ) ;
        $driver->findElement( WebDriverBy::id( 'UpdateButton2' ) )->click() ;
        $this->doWaitFor( WebDriverBy::id( 'SaveButton2' ) ) ;
        $this->doWaitFor( WebDriverBy::id( 'CancelButton2' ) ) ;
        $driver->findElement( WebDriverBy::id( 'CancelButton2' ) )->click() ;
        $this->checkHeaderLoads() ;
        $this->checkNotPresent( WebDriverBy::id( 'SaveButton2' ) ) ;
        $this->checkNotPresent( WebDriverBy::id( 'CancelButton2' ) ) ;
        $driver->findElement( WebDriverBy::id( 'DeleteButton2' ) )->click() ;
        $this->doWaitFor( WebDriverBy::id( 'DeleteButton2' ) ) ;
        $this->doWaitFor( WebDriverBy::id( 'CancelButton2' ) ) ;
        $driver->findElement( WebDriverBy::id( 'CancelButton2' ) )->click() ;
        $this->checkHeaderLoads() ;
        $this->doWaitFor( WebDriverBy::id( 'UpdateButton2' ) ) ;
        $this->doWaitFor( WebDriverBy::id( 'DeleteButton2' ) ) ;
        $this->checkNotPresent( WebDriverBy::id( 'CancelButton2' ) ) ;
        $driver->findElement( WebDriverBy::id( 'UpdateButton2' ) )->click() ;
        $this->doSelectOption( WebDriverBy::id( 'urgency2' ), 'low' ) ;
        $this->doTypeAt( WebDriverBy::id( 'positionTitle2' ), 'Floor Sweeper 2' ) ;
        $this->doTypeAt( WebDriverBy::id( 'location2' ), 'Hershey, PX' ) ;
        $this->doSelectOption( WebDriverBy::id( 'companyId2' ), 'Company 3' ) ;
        $this->doSelectOption( WebDriverBy::id( 'contactId2' ), 'Jane Smith' ) ;
        $this->doSelectOption( WebDriverBy::id( 'applicationStatusId2' ), 'FOUND' ) ;
        $this->doTypeAt( WebDriverBy::id( 'nextAction2' ), 'Do something else entirely' ) ;
        // @todo Compute dates for these records
        $this->doTypeAt( WebDriverBy::id( 'nextActionDue2' ), '2017-08-03' ) ;
        $this->doTypeAt( WebDriverBy::id( 'url2' ), 'http://www.testme3.com/' ) ;
        $this->doTypeAt( WebDriverBy::id( 'lastStatusChange2' ), '2017-08-02' ) ;
        $driver->findElement( WebDriverBy::id( 'SaveButton2' ) )->click() ;
        $this->checkHeaderLoads() ;
        $this->doWaitFor( WebDriverBy::id( 'UpdateButton2' ) ) ;
        $this->doWaitFor( WebDriverBy::id( 'DeleteButton2' ) ) ;
        $this->doLoadFromHeader( 'Jobs' ) ;
        $this->checkHeaderLoads() ;
        $this->checkJHR() ;
        $this->checkJR( 2, '', 'Jane Smith', 'Company 3', 'FOUND', '2017-08-02', 'low', '2017-08-03', 'Do something else entirely', 'Floor Sweeper 2', 'Hershey, PX', 'http://www.testme3.com/' ) ;
        $this->checkJR( 1, '/tr[2]', 'John Doe', 'Company 2c', 'FOUND', '2017-08-01', 'low', '2017-08-01', 'Do something', 'Janitor', 'Hershey, PA', 'http://www.testme1.com/' ) ;
        $driver->findElement( WebDriverBy::id( 'DeleteButton2' ) )->click() ;
        $this->checkHeaderLoads() ;
        $this->doWaitFor( WebDriverBy::id( 'DeleteButton2' ) ) ;
        $this->doWaitFor( WebDriverBy::id( 'CancelButton2' ) ) ;
        $driver->findElement( WebDriverBy::id( 'DeleteButton2' ) )->click() ;
        $this->checkHeaderLoads() ;
        $this->checkNotPresent( WebDriverBy::id( 'DeleteButton2' ) ) ;
        $this->checkNotPresent( WebDriverBy::id( 'CancelButton2' ) ) ;
        $this->checkNotPresent( WebDriverBy::id( 'UpdateButton2' ) ) ;
        $this->checkJR( 1, '', 'John Doe', 'Company 2c', 'FOUND', '2017-08-01', 'low', '2017-08-01', 'Do something', 'Janitor', 'Hershey, PA', 'http://www.testme1.com/' ) ;
        $this->doLoadFromHeader( 'Jobs' ) ;
        $this->checkHeaderLoads() ;
        $this->checkJHR() ;
        $this->checkJR( 1, '', 'John Doe', 'Company 2c', 'FOUND', '2017-08-01', 'low', '2017-08-01', 'Do something', 'Janitor', 'Hershey, PA', 'http://www.testme1.com/' ) ;
    }

    public function checkKHR() {
        if ( $this->_testMode < 0 ) {
            return ;
        }
        sleep( 15 ) ;
        $this->markTestIncomplete( 'Left off here. ' . __FILE__ . ':' . __LINE__ ) ;
    }

    public function doTestKeywords() {
        return ;
        // @todo 60 Finish implementation of Tests/IntegrationTests.php:tdoTestKeywords.
        if ( $this->_testMode < 0 ) {
            return ;
        }
        $driver = $this->webDriver ;
        $this->doLoadFromHeader( 'Keywords' ) ;
        $this->checkHeaderLoads() ;
        $this->checkKHR() ;
        if ( $this->_testMode <= 1 ) {
            return ;
        }

        // @todo Implement IntegrationTests.php:doTestKeywords()
        sleep( 15 ) ;
        $this->markTestIncomplete( 'Left off here. ' . __FILE__ . ':' . __LINE__ ) ;
    }

    public function checkSeHR() {
        if ( $this->_testMode < 0 ) {
            return ;
        }
        $this->checkXpathText( '//button', 'Add Search' ) ;
        $this->checkXpathText( '//caption', 'Current Searches' ) ;
        $this->checkXpathText( '//th', 'Actions' ) ;
        $this->checkXpathText( '//th[2]', 'Engine' ) ;
        $this->checkXpathText( '//th[3]', 'Search Name' ) ;
        $this->checkXpathText( '//th[4]', 'Link' ) ;
        $this->checkXpathText( '//th[5]', 'Feed' ) ;
        $this->checkXpathText( '//th[6]', 'Feed Last Checked' ) ;
        $this->checkXpathText( '//th[7]', 'Created' ) ;
        $this->checkXpathText( '//th[8]', 'Updated' ) ;
    }

    public function checkSeR( $id
                            , $prefix
                            , $engineName
                            , $searchName
                            , $url
                            , $rssFeedUrl
                            , $rssLastChecked
                            ) {
        if ( $this->_testMode < 0 ) {
            return ;
        }
        if ( $this->_testMode < 1 ) {
            return ;
        }
        $this->checkIdText( "UpdateButton$id", 'Update' ) ;
        $this->checkIdText( "DeleteButton$id", 'Delete' ) ;
        $this->checkXpathText( "/$prefix/td[2]", $engineName ) ;
        $this->checkXpathText( "/$prefix/td[3]", $searchName ) ;
        $this->checkXpathText( "/$prefix/td[4]", $url ) ;
        $this->checkXpathText( "/$prefix/td[5]", $rssFeedUrl ) ;
        $this->checkXpathText( "/$prefix/td[6]", $rssLastChecked . ' 00:00:00' ) ;
        $this->checkXpathPattern( "/$prefix/td[7]", '/^\d\d\d\d-\d\d-\d\d \d\d:\d\d:\d\d$/' ) ;
        $this->checkXpathPattern( "/$prefix/td[8]", '/^\d\d\d\d-\d\d-\d\d \d\d:\d\d:\d\d$/' ) ;
    }

    public function doTestSearches() {
        if ( $this->_testMode < 0 ) {
            return ;
        }
        $driver = $this->webDriver ;
        $this->doLoadFromHeader( 'Searches' ) ;
        $this->checkHeaderLoads() ;
        $this->checkSeHR() ;
        $driver->findElement( WebDriverBy::id( 'AddButton' ) )->click() ;
        $this->doWaitFor( WebDriverBy::id( 'SaveButtonix1' ) ) ;
        $this->doWaitFor( WebDriverBy::id( 'CancelButtonix1' ) ) ;
        $this->doTypeAt( WebDriverBy::id( 'engineNameix1' ), 'LinkedIn' ) ;
        $this->doTypeAt( WebDriverBy::id( 'searchNameix1' ), 'LinkedIn General' ) ;
        $this->doTypeAt( WebDriverBY::id( 'urlix1' ), 'http://www.linkedin.com/' ) ;
        $this->doTypeAt( WebDriverBy::id( 'rssFeedUrlix1' ), 'http://www.linkedin.com' ) ;
        $this->doTypeAt( WebDriverBy::id( 'rssLastCheckedix1' ), '2017-01-01' ) ;
        $driver->findElement( WebDriverBy::id( 'SaveButtonix1' ) )->click() ;
        $this->checkHeaderLoads() ;
        $this->checkSeHR() ;
        $this->doWaitFor( WebDriverBy::id( 'UpdateButton1' ) ) ;
        $this->doWaitFor( WebDriverBy::id( 'DeleteButton1' ) ) ;
        $this->checkSeR( 1, '', 'LinkedIn', 'LinkedIn General'
                       , 'http://www.linkedin.com/', 'http://www.linkedin.com'
                       , '2017-01-01' ) ;
        $driver->findElement( WebDriverBy::id( 'AddButton' ) )->click() ;
        $this->doWaitFor( WebDriverBy::id( 'SaveButtonix2' ) ) ;
        $this->doWaitFor( WebDriverBy::id( 'CancelButtonix2' ) ) ;
        $this->doTypeAt( WebDriverBy::id( 'engineNameix2' ), 'Dice' ) ;
        $this->doTypeAt( WebDriverBy::id( 'searchNameix2' ), 'Dice General' ) ;
        $this->doTypeAt( WebDriverBy::id( 'urlix2' ), 'http://www.dice.com/' ) ;
        $this->doTypeAt( WebDriverBy::id( 'rssFeedUrlix2' ), 'http://www.dice.com' ) ;
        $this->doTypeAt( WebDriverBy::id( 'rssLastCheckedix2' ), '2017-02-02' ) ;
        $driver->findElement( WebDriverBy::id( 'SaveButtonix2' ) )->click() ;
        $this->checkHeaderLoads() ;
        $this->checkSeHR() ;
        $this->doWaitFor( WebDriverBy::id( 'UpdateButton2' ) ) ;
        $this->doWaitFor( WebDriverBy::id( 'DeleteButton2' ) ) ;
        $this->checkSeR( 2, '',  'Dice', 'Dice General'
                       , 'http://www.dice.com/', 'http://www.dice.com'
                       , '2017-02-02'
                       ) ;
        $this->checkSeR( 1, '/tr[3]', 'LinkedIn', 'LinkedIn General'
                       , 'http://www.linkedin.com/', 'http://www.linkedin.com'
                       , '2017-01-01'
                       ) ;
        $driver->findElement( WebDriverBy::id( 'AddButton' ) )->click() ;
        $this->doWaitFor( WebDriverBy::id( 'SaveButtonix3' ) ) ;
        $this->doWaitFor( WebDriverBy::id( 'CancelButtonix3' ) ) ;
        $driver->findElement( WebDriverBy::id( 'CancelButtonix3' ) )->click() ;
        $this->checkHeaderLoads() ;
        $this->checkNotPresent( WebDriverBy::id( 'SaveButtonix3' ) ) ;
        $this->checkNotPresent( WebDriverBy::id( 'CancelButtonix3' ) ) ;
        $driver->findElement( WebDriverBy::id( 'UpdateButton2' ) )->click() ;
        $this->checkHeaderLoads() ;
        $this->doWaitFor( WebDriverBy::id( 'SaveButton2' ) ) ;
        $this->doWaitFor( WebDriverBy::id( 'CancelButton2' ) ) ;
        $driver->findElement( WebDriverBy::id( 'CancelButton2' ) )->click() ;
        $this->checkHeaderLoads() ;
        $this->doWaitFor( WebDriverBy::id( 'UpdateButton2' ) ) ;
        $this->doWaitFor( WebDriverBy::id( 'DeleteButton2' ) ) ;
        $this->checkNotPresent( WebDriverBy::id( 'SaveButton2' ) ) ;
        $this->checkNotPresent( WebDriverBy::id( 'CancelButton2' ) ) ;
        $this->checkSeR( 2, '',  'Dice', 'Dice General'
                        , 'http://www.dice.com/', 'http://www.dice.com'
                        , '2017-02-02'
                        ) ;
        $this->checkSeR( 1, '/tr[3]', 'LinkedIn', 'LinkedIn General'
                       , 'http://www.linkedin.com/', 'http://www.linkedin.com'
                       , '2017-01-01'
                       ) ;
        $driver->findElement( WebDriverBy::id( 'DeleteButton2' ) )->click() ;
        $this->checkHeaderLoads() ;
        $this->doWaitFor( WebDriverBy::id( 'DeleteButton2' ) ) ;
        $this->doWaitFor( WebDriverBy::id( 'CancelButton2' ) ) ;
        $driver->findElement( WebDriverBy::id( 'CancelButton2' ) )->click() ;
        $this->checkHeaderLoads() ;
        $this->doWaitFor( WebDriverBy::id( 'UpdateButton2' ) ) ;
        $this->doWaitFor( WebDriverBy::id( 'DeleteButton2' ) ) ;
        $this->checkNotPresent( WebDriverBy::id( 'CancelButton2' ) ) ;
        $this->checkSeR( 2, '',  'Dice', 'Dice General'
                       , 'http://www.dice.com/', 'http://www.dice.com'
                       , '2017-02-02'
                       ) ;
        $this->checkSeR( 1, '/tr[3]', 'LinkedIn', 'LinkedIn General'
                       , 'http://www.linkedin.com/', 'http://www.linkedin.com'
                       , '2017-01-01'
                       ) ;
        $driver->findElement( WebDriverBy::id( 'UpdateButton2' ) )->click() ;
        $this->checkHeaderLoads() ;
        $this->doWaitFor( WebDriverBy::id( 'SaveButton2' ) ) ;
        $this->doWaitFor( WebDriverBy::id( 'CancelButton2' ) ) ;
        $this->doTypeAt( WebDriverBy::id( 'engineName2' ), 'Dicey' ) ;
        $this->doTypeAt( WebDriverBy::id( 'searchName2' ), 'Dicey General' ) ;
        $this->doTypeAt( WebDriverBy::id( 'url2' ), 'http://www.dicey.com/' ) ;
        $this->doTypeAt( WebDriverBy::id( 'rssFeedUrl2' ), 'http://www.dicey.com' ) ;
        $this->doTypeAt( WebDriverBy::id( 'rssLastChecked2' ), '2017-02-03' ) ;
        $driver->findElement( WebDriverBy::id( 'SaveButton2' ) )->click() ;
        $this->checkHeaderLoads() ;
        $this->doWaitFor( WebDriverBy::id( 'UpdateButton2' ) ) ;
        $this->doWaitFor( WebDriverBy::id( 'DeleteButton2' ) ) ;
        $this->doLoadFromHeader( 'Searches' ) ;
        $this->checkHeaderLoads() ;
        $this->doWaitFor( WebDriverBy::id( 'UpdateButton1' ) ) ;
        $this->doWaitFor( WebDriverBy::id( 'DeleteButton1' ) ) ;
        $this->checkSeHR() ;
        $this->checkSeR( 2, '',  'Dicey', 'Dicey General'
                       , 'http://www.dicey.com/', 'http://www.dicey.com'
                       , '2017-02-03'
                       ) ;
        $this->checkSeR( 1, '/tr[2]', 'LinkedIn', 'LinkedIn General'
                       , 'http://www.linkedin.com/', 'http://www.linkedin.com'
                       , '2017-01-01'
                       ) ;
        $driver->findElement( WebDriverBy::id( 'DeleteButton2' ) )->click() ;
        $this->checkHeaderLoads() ;
        $this->doWaitFor( WebDriverBy::id( 'DeleteButton2' ) ) ;
        $this->doWaitFor( WebDriverBy::id( 'CancelButton2' ) ) ;
        $driver->findElement( WebDriverBy::id( 'DeleteButton2' ) )->click() ;
        $this->checkHeaderLoads() ;
        $this->checkNotPresent( WebDriverBy::id( 'DeleteButton2' ) ) ;
        $this->checkNotPresent( WebDriverBy::id( 'UpdateButton2' ) ) ;
        $this->checkNotPresent( WebDriverBy::id( 'CancelButton2' ) ) ;
        $this->checkSeR( 1, '', 'LinkedIn', 'LinkedIn General'
                       , 'http://www.linkedin.com/', 'http://www.linkedin.com'
                       , '2017-01-01'
                       ) ;
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
        $this->markTestIncomplete( 'Left off here. ' . __FILE__ . ':' . __LINE__ ) ;
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

        $this->doTestSummary1() ;
        $this->doTestApplicationStatuses() ;
        $this->doTestCompanies() ;
        $this->doTestContacts() ;
        $this->doTestJobs() ;
        $this->doTestKeywords() ;
        $this->doTestSearches() ;
        $this->doTestSummary2() ;

    }

}
