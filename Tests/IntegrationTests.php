<?php

set_include_path( get_include_path()
                . PATH_SEPARATOR
                . $ENV[ 'HOME' ] . '/.config/composer'
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
                                ) ;
    private $_userName   = '' ;
    private $_password   = '' ;
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
        $this->webDriver->wait($timeout, $interval)->until( function ($webDriver) {
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

    public function checkCssText( $locator, $text ) {
        $element = $this->webDriver->findElement( WebDriverBy::cssSelector( $locator ) ) ;
        $this->assertEquals( $text, $element->getText() ) ;
    }

    public function checkNotPresent( $locator ) {
        $this->assertEquals( 0, $this->webDriver->findElements( $locator ) ) ;
    }

    /**
     * FIXME Implement this
     */
    public function doLogOutLogIn(){
        $this->markTestIncomplete( 'Left off here.' ) ;
    }

    /**
     * FIXME Implement this
     */
    public function doResetDb(){
        $this->markTestIncomplete( 'Left off here.' ) ;
    }

    /**
     * @group minimal
     */
    public function testWebsiteLoads() {
        $url = $this->url ;
        $driver->get( $this->url ) ;
        $this->checkHeaderLoads() ;
        $this->doLogOutLogIn() ;
        $this->checkHeaderLoads() ;
    }

     * @group minimal
     */
    public function testResetDatabase() {
        $url = $this->url ;
        $driver->get( $this->url ) ;
        $this->checkHeaderLoads() ;
        $this->doLogOutLogIn() ;
        $this->checkHeaderLoads() ;
        $this->markTestIncomplete( 'Left off here.' ) ;
    }

    /**
     * @group full
     * @group skipReset
     */
    public function testWebSite() {
        $this->doLogOutLogIn() ;
        $driver = $this->webDriver ;
        $url = $this->url ;
        $driver->get( $this->url ) ;
        $this->checkHeaderLoads() ;
        // Check that all the pages in the header load properly
        foreach ( $this->_headerTags as $headerTag ) {
            $this->doLoadFromHeader( $headerTag ) ;
        }
    }

}
