/**
 * phpjobseeker
 *
 * Copyright (C) 2009, 2015, 2017, 2026 Kevin Benton - kbenton at bentonfam dot org
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

// This is local to this file.
var keywordRowNumber = 1 ;

///////////////////////////////////////////////////////////////////////////////

/**
 * Attach jQuery UI autocomplete to a keyword input field, sourcing
 * suggestions from existing keywords via AJAX.
 *
 * @param {String} inputId  The DOM id of the input element.
 */
function attachKeywordAutocomplete( inputId ) {
    $.getJSON( 'AJAXGetKeywordList.php', function( keywords ) {
        $( "#" + inputId ).autocomplete( { source: keywords, minLength: 1 } ) ;
    } ) ;
}

/**
 * Add a keyword row for user input.
 *
 * @returns {Boolean}
 */
function addKeyword() {
    var table  = document.getElementById( "keywords" ) ;
    var row    = table.insertRow( 1 ) ;
    row.id     = "ix" + keywordRowNumber ;
    var data   = 'id=' + keywordRowNumber + '&mode=add' ;
    doLoadAjaxJsonResultWithCallback( 'AJAXGetKeywordRow.php'
                                    , data
                                    , 'ix'
                                    + keywordRowNumber
                                    , true
                                    , function ( xhttp, id ) {
        var jsonObj = JSON.parse( xhttp.responseText ) ;
        row.innerHTML = jsonObj.row ;
        attachKeywordAutocomplete( "keywordValue" + id ) ;
    } ) ; // END OF doLoadAjaxJsonResultWithCallback( ...
    keywordRowNumber ++ ;
    return false ;
}

/**
 * Display the Keyword row to be updated based on the id provided.
 *
 * @param id
 * @returns {Boolean}
 */
function updateKeyword( id ) {
    var row  = document.getElementById( 'ux' + id ) ;
    var data = 'id=' + id + '&mode=update' ;
    doLoadAjaxJsonResultWithCallback( 'AJAXGetKeywordRow.php'
                                    , data
                                    , 'ux'
                                    + id
                                    , true
                                    , function ( xhttp, targetId ) {
        var jsonObj = JSON.parse( xhttp.responseText ) ;
        row.innerHTML = jsonObj.row ;
        attachKeywordAutocomplete( "keywordValue" + id ) ;
    } ) ; // END OF doLoadAjaxJsonResultWithCallback( ...
    return false ;
}

/**
 * Display the Keyword row to be removed based on the id provided.
 *
 * @param id
 * @returns {Boolean}
 */
function deleteKeyword( id ) {
    var row  = document.getElementById( 'ux' + id ) ;
    var data = 'id=' + id + '&mode=delete' ;
    doLoadAjaxJsonResultWithCallback( 'AJAXGetKeywordRow.php'
                                    , data
                                    , 'ux'
                                    + id
                                    , true
                                    , function ( xhttp, targetId ) {
        var jsonObj = JSON.parse( xhttp.responseText ) ;
        row.innerHTML = jsonObj.row ;
    } ) ; // END OF doLoadAjaxJsonResultWithCallback( ...
    return false ;
}

/**
 * Validate a Keyword row prior to submission.
 *
 * @param keywordValue
 * @param sortKey
 * @returns {String}
 */
function ajaxValidateKeyword( keywordValue, sortKey ) {
    var message = '' ;
    if ( ( null == keywordValue ) || ( '' == keywordValue ) ) {
        message += "Keyword value cannot be blank.\n" ;
    }
    if ( ( null == sortKey ) || ( '' == sortKey ) || ( ! isNumeric( sortKey ) ) ) {
        message += "Sort Key must be numeric.\n" ;
    }
    return message ;
}

/**
 * Revert the displayed row back to the updatable / deletable row.
 *
 * @param id
 * @returns {Boolean}
 */
function cancelUpdateKeywordRow( id ) {
    var rowId   = 'ux' + id ;
    var uri     = "AJAXGetKeywordRow.php" ;
    var data    = "id=" + encodeURIComponent( id ) + '&mode=list' ;
    var isAsync = true ;
    doLoadAjaxJsonResultWithCallback( uri, data, id, isAsync, function( xhttp, targetId ) {
        var jsonObj   = JSON.parse( xhttp.responseText ) ;
        var row       = document.getElementById( rowId ) ;
        row.innerHTML = jsonObj.row ;
    } ) ; // END OF doLoadAjaxJsonResultWithCallback( ...
    return false ;
}

/**
 * Save the Keyword row displayed and display a replacement row that can be
 * updated or deleted.
 *
 * @param id
 * @returns {Boolean}
 */
function saveAddKeyword( id ) {
    var rowId        = 'ix' + id ;
    var keywordValue = document.getElementById( "keywordValue" + rowId ).value ;
    var sortKey      = document.getElementById( "sortKey" + rowId ).value ;
    var msg          = ajaxValidateKeyword( keywordValue, sortKey ) ;
    if ( '' !== msg ) {
        alert( msg ) ;
        return false ;
    }
    var uri     = "AJAXAddKeyword.php" ;
    var data    = "value=" + encodeURIComponent( keywordValue )
                + "&sortKey=" + encodeURIComponent( sortKey )
                + "&rowId=" + encodeURIComponent( rowId )
                + "&rowStyle=add"
                ;
    var isAsync = true ;
    doLoadAjaxJsonResultWithCallback( uri, data, id, isAsync, function( xhttp, targetId ) {
        var jsonObj   = JSON.parse( xhttp.responseText ) ;
        var row       = document.getElementById( "ix" + targetId ) ;
        row.id        = "ux" + jsonObj.newId ;
        row.innerHTML = jsonObj.row ;
    } ) ; // END OF doLoadAjaxJsonResultWithCallback( ...
    return false ;
}

/**
 * Actually save the changes to the row and redisplay it.
 *
 * @param id
 * @returns {Boolean}
 */
function saveUpdateKeyword( id ) {
    var rowId        = 'ux' + id ;
    var keywordValue = document.getElementById( "keywordValue" + id ).value ;
    var sortKey      = document.getElementById( "sortKey" + id ).value ;
    var msg          = ajaxValidateKeyword( keywordValue, sortKey ) ;
    if ( '' !== msg ) {
        alert( msg ) ;
        return false ;
    }
    var uri     = "AJAXUpdateKeyword.php" ;
    var data    = "id=" + id
                + "&value=" + encodeURIComponent( keywordValue )
                + "&sortKey=" + encodeURIComponent( sortKey )
                ;
    var isAsync = true ;
    doLoadAjaxJsonResultWithCallback( uri, data, id, isAsync, function( xhttp, targetId ) {
        var jsonObj   = JSON.parse( xhttp.responseText ) ;
        var row       = document.getElementById( "ux" + targetId ) ;
        row.innerHTML = jsonObj.row ;
    } ) ; // END OF doLoadAjaxJsonResultWithCallback( ...
    return false ;
}

/**
 * Actually remove the displayed row from the database and the user's screen.
 *
 * @param id
 * @returns {Boolean}
 */
function doDeleteKeyword( id ) {
    var uri     = "AJAXDeleteKeyword.php" ;
    var data    = "id=" + encodeURIComponent( id ) ;
    var isAsync = true ;
    doLoadAjaxJsonResultWithCallback( uri, data, id, isAsync, function( xhttp, targetId ) {
        var jsonObj = JSON.parse( xhttp.responseText ) ;
        if ( "OK" == jsonObj.result ) {
            deleteRow( "ux" + id ) ;
        }
        else {
            var uri2 = "AJAXGetKeywordRow.php" ;
            var data2 = "id=" + id + "&mode=list" ;
            doLoadAjaxJsonResultWithCallback( uri2, data2, id, isAsync, function( xhttp2, targetId2 ) {
                var jsonObj2 = JSON.parse( xhttp2.responseText ) ;
                var row      = document.getElementById( "ux" + id ) ;
                if ( "OK" == jsonObj2.result ) {
                    row.innerHTML = jsonObj2.row ;
                }
                else {
                    row.innerHTML = "Undefined result!" ;
                }
            } ) ;  // END OF doAjaxJsonResultWithCallback( 2 )
        }
    } ) ; // END OF doAjaxJsonResultWithCallback( 1 )
    return false ;
}
