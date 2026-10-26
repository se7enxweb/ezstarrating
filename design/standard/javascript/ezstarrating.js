/**
 * eZ Star Rating: the rating of content/datatype/view/ezsrrating.tpl, in plain JavaScript (no jQuery needed).
 *
 * The stars of a rating the user may give are links to "#" (ul.ezsr-star-rating, each link's id
 * ezsr_<attribute id>_<version>_<rating>). A click rates: the list stops taking clicks, and the rating is posted as
 * ezjscore's jQuery.ez() posted it (ezsrrating.tpl loaded ezstarrating_jquery.js before): a POST to the call address
 * of the list's data-ezsr-call (with a slash after it) with ezjscServer_function_arguments=
 * ezstarrating::rate::<attribute id>::<version>::<rating> and the form token (ezxform_token), the same headers.
 * The answer updates the stars, the average and the count, and shows the thank you or the "already rated" message.
 * ezstarrating_jquery.js stays for a template of its own that still loads it with jQuery.
 *
 * @copyright Copyright CJW Network and Exponential Foundation. All rights reserved.
 * @license GNU General Public License v2.0 (or any later version)
 * @package eZ Starating extension for eZ Publish
 */
(function( window, document )
{
    function ready( fn )
    {
        if ( document.readyState === 'loading' )
            document.addEventListener( 'DOMContentLoaded', fn );
        else
            window.setTimeout( fn );
    }

    function each( selector, fn, root )
    {
        Array.prototype.forEach.call( ( root || document ).querySelectorAll( selector ), fn );
    }

    ready( function()
    {
        each( 'ul.ezsr-star-rating', function( node ){
            if ( !node.classList.contains('ezsr-star-rating-disabled') )
                   node.classList.add('ezsr-star-rating-enabled');
        });
        each( 'ul.ezsr-star-rating-enabled li a', function( link ){ link.addEventListener( 'click', _rate ); });
        // the stars are links to "#": after a vote (their handler is removed) a click stays on the page
        document.addEventListener( 'click', function( e ){
            var link = e.target && e.target.closest ? e.target.closest( 'ul.ezsr-star-rating li a[href="#"]' ) : null;
            if ( link )
                e.preventDefault();
        });
    });

    function _rate( e )
    {
        // as a jQuery handler that returns false: no default action, and the click goes no further
        e.preventDefault();
        e.stopPropagation();
        var args = this.getAttribute('id').split('_'),
            list = document.getElementById( 'ezsr_rating_' + args[1] );
        if ( list )
        {
            list.classList.remove('ezsr-star-rating-enabled');
            each( 'li a', function( link ){ link.removeEventListener( 'click', _rate ); }, list );
        }
        _post( list, 'ezstarrating::rate::' + args[1] + '::' + args[2] + '::' + args[3], _callBack );
    }

    /** jQuery.ez( callArgs, {}, callBack ): a POST with the call and the form token; callBack gets the parsed answer */
    function _post( list, callArgs, callBack )
    {
        var withCall = document.querySelector( 'ul.ezsr-star-rating[data-ezsr-call]' ),
            url = ( list && list.getAttribute('data-ezsr-call') ) || ( withCall && withCall.getAttribute('data-ezsr-call') ) ||
                  ( window.Exp && window.Exp.config ? window.Exp.config.call.replace( /\/$/, '' ) : '/ezjscore/call' ),
            token = '', tokenNode = document.getElementById('ezxform_token_js'), xhr = new XMLHttpRequest();
        if ( tokenNode )
            token = tokenNode.getAttribute('title');
        xhr.open( 'POST', url + '/', true );
        xhr.setRequestHeader( 'Content-Type', 'application/x-www-form-urlencoded; charset=UTF-8' );
        xhr.setRequestHeader( 'Accept', 'application/json, text/javascript, */*; q=0.01' );
        xhr.setRequestHeader( 'X-Requested-With', 'XMLHttpRequest' );
        xhr.onload = function()
        {
            var data;
            if ( ( xhr.status < 200 || xhr.status >= 300 ) && xhr.status !== 304 )
                return;
            try { data = JSON.parse( xhr.responseText ); } catch ( err ) { return; }
            callBack( data );
        };
        xhr.send( ( 'ezjscServer_function_arguments=' + encodeURIComponent( callArgs ) + '&ezxform_token=' + encodeURIComponent( token == null ? '' : token ) ).replace( /%20/g, '+' ) );
    }

    function _removeClass( id, name )
    {
        var el = document.getElementById( id );
        if ( el )
            el.classList.remove( name );
    }

    function _text( id, value )
    {
        var el = document.getElementById( id );
        // as jQuery's .text(): undefined changes nothing
        if ( el && value !== undefined )
            el.textContent = value;
    }

    function _callBack( data )
    {
        if ( data && data.content )
        {
            if ( data.content.rated )
            {
                if ( data.content.already_rated )
                    _removeClass( 'ezsr_changed_rating_' + data.content.id, 'hide' );
                else
                    _removeClass( 'ezsr_just_rated_' + data.content.id, 'hide' );
                var percent = document.getElementById( 'ezsr_rating_percent_' + data.content.id );
                if ( percent )
                    percent.style.width = (( data.content.stats.rounded_average / 5 ) * 100 ) + '%';
                _text( 'ezsr_average_' + data.content.id, data.content.stats.rating_average );
                _text( 'ezsr_total_' + data.content.id, data.content.stats.rating_count );
            }
            else if ( data.content.already_rated )
                _removeClass( 'ezsr_has_rated_' + data.content.id, 'hide' );
        }
        else
        {
            // This shouldn't happen as we have already checked access in the template..
            // Unless this is inside a aggressive cache-block of course.
            // ezjscore puts the error in error_text next to content (which is empty then)
            if ( data && data.error_text )
                window.alert( data.error_text );
        }
    }
})( window, document );
