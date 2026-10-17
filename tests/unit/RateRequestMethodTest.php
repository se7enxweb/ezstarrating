<?php

use PHPUnit\Framework\TestCase;

/**
 * ezstarrating::rate changes data and answers POST requests only, unless site.ini [eZStarRating] AllowGetRating is
 * enabled. Runs without the kernel: the two kernel classes the check touches are stood in for when they are missing.
 */
class RateRequestMethodTest extends TestCase
{
    /** @var string|null */
    private $method;

    public static function setUpBeforeClass(): void
    {
        if ( !class_exists( 'ezjscServerFunctions', false ) )
            eval( 'class ezjscServerFunctions {}' );
        if ( !class_exists( 'eZINI', false ) )
            eval( 'class eZINI { public static $values = array(); public static function instance() { return new self(); }
                public function hasVariable( $g, $n ) { return isset( self::$values[$g][$n] ); }
                public function variable( $g, $n ) { return self::$values[$g][$n] ?? false; } }' );
        require_once dirname( __DIR__, 2 ) . '/classes/ezsrserverfunctions.php';
    }

    protected function setUp(): void
    {
        if ( !property_exists( 'eZINI', 'values' ) )
            $this->markTestSkipped( 'runs only without the kernel' );
        $this->method = $_SERVER['REQUEST_METHOD'] ?? null;
        eZINI::$values = array();
    }

    protected function tearDown(): void
    {
        if ( $this->method === null )
            unset( $_SERVER['REQUEST_METHOD'] );
        else
            $_SERVER['REQUEST_METHOD'] = $this->method;
    }

    public function testPostIsAllowed(): void
    {
        $_SERVER['REQUEST_METHOD'] = 'POST';
        $this->assertTrue( ezsrServerFunctions::requestMethodAllowed() );
    }

    public function testGetIsRefusedByDefault(): void
    {
        $_SERVER['REQUEST_METHOD'] = 'GET';
        $this->assertFalse( ezsrServerFunctions::requestMethodAllowed() );
        unset( $_SERVER['REQUEST_METHOD'] );
        $this->assertFalse( ezsrServerFunctions::requestMethodAllowed() );
    }

    public function testRateThrowsOnGetBeforeLookingAtTheArguments(): void
    {
        $_SERVER['REQUEST_METHOD'] = 'GET';
        $this->expectException( InvalidArgumentException::class );
        $this->expectExceptionMessage( 'POST' );
        ezsrServerFunctions::rate( array( 1, 1, 5 ) );
    }

    public function testGetIsAllowedWhenTheSettingIsEnabled(): void
    {
        $_SERVER['REQUEST_METHOD'] = 'GET';
        eZINI::$values = array( 'eZStarRating' => array( 'AllowGetRating' => 'enabled' ) );
        $this->assertTrue( ezsrServerFunctions::requestMethodAllowed() );
    }

    public function testTheSettingShipsDisabled(): void
    {
        $ini = (string)file_get_contents( dirname( __DIR__, 2 ) . '/settings/site.ini.append.php' );
        $this->assertMatchesRegularExpression( '/\[eZStarRating\][^\[]*\nAllowGetRating=disabled\n/', $ini );
    }

    public function testTheShippedScriptPosts(): void
    {
        $js = (string)file_get_contents( dirname( __DIR__, 2 ) . '/design/standard/javascript/ezstarrating_jquery.js' );
        // jQuery.ez() with a post object (here {}) posts, and sends the form token
        $this->assertMatchesRegularExpression( "/jQuery\\.ez\\(\\s*'ezstarrating::rate::'[^;]*,\\s*\\{\\}\\s*,/", $js );
    }
}
