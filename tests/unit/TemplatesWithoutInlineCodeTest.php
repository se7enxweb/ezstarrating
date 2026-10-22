<?php

use PHPUnit\Framework\TestCase;

/**
 * The templates carry no inline script, no event handler attribute and no javascript: link, so a page that shows a
 * rating runs under a Content Security Policy without 'unsafe-inline'. The stars are links to "#" handled by
 * design/standard/javascript/ezstarrating.js (plain JavaScript, no jQuery), which posts to the list's data-ezsr-call.
 */
class TemplatesWithoutInlineCodeTest extends TestCase
{
    /** @return string[] every template of the extension */
    private function templates(): array
    {
        $root = dirname( __DIR__, 2 ) . '/design';
        $files = array();
        $it = new RecursiveIteratorIterator( new RecursiveDirectoryIterator( $root, FilesystemIterator::SKIP_DOTS ) );
        foreach ( $it as $file )
            if ( substr( $file->getFilename(), -4 ) === '.tpl' )
                $files[] = $file->getPathname();
        sort( $files );
        return $files;
    }

    public function testNoInlineScriptHandlerOrJavascriptLink(): void
    {
        $found = array();
        foreach ( $this->templates() as $file )
        {
            $source = file_get_contents( $file );
            if ( preg_match( '/<script\b(?![^>]*\bsrc=)(?![^>]*type="application\/(?:ld\+)?json")[^>]*>/i', $source ) )
                $found[] = basename( $file ) . ': inline <script>';
            if ( preg_match( '/<[a-z][^>]*\son[a-z]+\s*=/i', $source ) )
                $found[] = basename( $file ) . ': event handler attribute';
            if ( preg_match( '/=\s*["\']?\s*javascript:/i', $source ) )
                $found[] = basename( $file ) . ': javascript: link';
        }
        $this->assertSame( array(), $found );
    }

    public function testStarsAreLinksToTheScriptOnlyForWhoMayRate(): void
    {
        $source = file_get_contents( dirname( __DIR__, 2 ) . '/design/standard/templates/content/datatype/view/ezsrrating.tpl' );
        $this->assertStringContainsString( '<a{if $ezsr_can_rate} href="#"{/if}', $source );
        $this->assertStringContainsString( "{if \$ezsr_can_rate}", $source );
        $this->assertStringContainsString( "data-ezsr-call={'ezjscore/call'|ezurl}", $source );
        $this->assertStringContainsString( "{ezscript_require( array( 'ezstarrating.js' ) )}", $source );
        $this->assertStringNotContainsString( 'ezjsc::jquery', $source );
        $script = file_get_contents( dirname( __DIR__, 2 ) . '/design/standard/javascript/ezstarrating.js' );
        $this->assertStringContainsString( "a[href=\"#\"]", $script );
    }
}
