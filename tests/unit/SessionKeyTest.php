<?php

use PHPUnit\Framework\TestCase;

/**
 * ezstarrating_data.session_key holds a SHA-256 hash of the session id, never the session id itself. Runs without
 * the kernel: eZPersistentObject is stood in for when it is missing.
 */
class SessionKeyTest extends TestCase
{
    public static function setUpBeforeClass(): void
    {
        if ( !class_exists( 'eZPersistentObject', false ) )
            eval( 'class eZPersistentObject {}' );
        require_once dirname( __DIR__, 2 ) . '/classes/ezsrratingdataobject.php';
    }

    public function testTheSessionIdIsStoredAsItsSha256Hash(): void
    {
        $sessionId = 'k3v9p1q8r2s7t4u6w0x5y1z3a8b2c7d4';
        $key = ezsrRatingDataObject::sessionKey( $sessionId );
        $this->assertSame( hash( 'sha256', $sessionId ), $key );
        $this->assertStringNotContainsString( $sessionId, $key );
    }

    public function testEverySessionIdLengthFitsTheColumn(): void
    {
        foreach ( array( 22, 32, 48, 128, 256 ) as $length )
            $this->assertMatchesRegularExpression( '/^[0-9a-f]{64}$/', ezsrRatingDataObject::sessionKey( str_repeat( 'a', $length ) ) );
    }

    public function testNoSessionStaysEmpty(): void
    {
        $this->assertSame( '', ezsrRatingDataObject::sessionKey( '' ) );
        $this->assertSame( '', ezsrRatingDataObject::sessionKey( null ) );
    }

    public function testCreateDoesNotStoreTheRawSessionId(): void
    {
        $source = (string)file_get_contents( dirname( __DIR__, 2 ) . '/classes/ezsrratingdataobject.php' );
        $this->assertStringNotContainsString( "\$row['session_key'] = \$http->sessionID()", $source );
        $this->assertStringContainsString( "self::sessionKey( \$http->sessionID() )", $source );
    }

    public function testEverySchemaGivesTheColumn64Characters(): void
    {
        $root = dirname( __DIR__, 2 );
        $schemas = array(
            'sql/mysql/mysql.sql' => '/`session_key` varchar\(64\) NOT NULL/',
            'sql/sqlite/sqlite.sql' => '/`session_key` varchar\(64\) NOT NULL/',
            'sql/postgresql/postgresql.sql' => '/session_key character varying\(64\) NOT NULL/',
            'sql/oracle/oracle.sql' => '/session_key varchar2\(64\) NOT NULL/',
            'share/db_schema.dba' => "/'session_key' =>\\s*array \\(\\s*'length' => 64,/",
        );
        foreach ( $schemas as $file => $pattern )
            $this->assertMatchesRegularExpression( $pattern, (string)file_get_contents( "$root/$file" ), $file );
    }
}
