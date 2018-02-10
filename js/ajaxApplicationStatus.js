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

// This is local to this file.
var rowNumber = 1 ;

///////////////////////////////////////////////////////////////////////////////

/**
 * Add an application status row for user input.
 *
 * @returns {Boolean}
 */
function addApplicationStatus() {
    var table  = document.getElementById( "applicationStatus" ) ;
    var row    = table.insertRow( 1 ) ;
    row.id     = "ix" + rowNumber ;
    var data   = 'id=' + rowNumber + '&mode=add' ;
    doLoadAjaxJsonResultWithCallback( 'AJAXGetApplicationStatusRow.php'
                                    , data
                                    , 'ix'
                                    + rowNumber
                                    , true
                                    , function ( xhttp, id ) {
        var jsonObj = JSON.parse( xhttp.responseText ) ;
        row.innerHTML = jsonObj.row ;
    } ) ; // END OF doLoadAjaxJsonResultWithCallback( ...
    rowNumber ++ ;
    return false ;
}

/**
 * Display the Application Status row to be updated based on the id provided.
 * 
 * @param id
 * @returns {Boolean}
 */
function updateApplicationStatus( id ) {
    var row  = document.getElementById( 'ux' + id ) ;
    var data = 'id=' + id + '&mode=update' ;
    doLoadAjaxJsonResultWithCallback( 'AJAXGetApplicationStatusRow.php'
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
 * Display the Application Status row to be removed based on the id provided.
 * 
 * @param id
 * @returns {Boolean}
 */
function deleteApplicationStatus( id ) {
    var row  = document.getElementById( 'ux' + id ) ;
    var data = 'id=' + id + '&mode=delete' ;
    doLoadAjaxJsonResultWithCallback( 'AJAXGetApplicationStatusRow.php'
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
 * Validate an Application Status row prior to submission. When there are errors, the
 * errors are reported back in the return string. Otherwise, the empty string
 * is returned.
 *
 * @param displayValue
 * @param style
 * @param isActive
 * @param amount
 * @param tipAmount
 * @param checkNumber
 * @returns {String}
 */
function ajaxValidateApplicationStatus( displayValue, style, isActive, sortKey ) {
    var message = '' ;
    if ( ( null == displayValue ) || ( '' == displayValue ) ) {
        message += "Value cannot be blank.\n" ;
    }
    if ( ( null == style ) || ( '' == style ) ) {
        message += "Style cannot be blank.\n" ;
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
function cancelUpdateApplicationStatusRow( id ) {
    var rowId   = 'ux' + id ;
    var uri     = "AJAXGetApplicationStatusRow.php" ;
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
 * Save the ApplicationStatus row displayed and display a replacement row that can be
 * updated or deleted.
 *
 * @param id
 * @returns {Boolean}
 */
function saveAddApplicationStatus( id ) {
    var rowId       = 'ix' + id ;
    var statusValue = document.getElementById( "statusValue" + rowId ).value ;
    var style       = document.getElementById( "style" + rowId ).value ;
    var isActive    = document.getElementById( "isActive" + rowId ).checked ;
    var sortKey     = document.getElementById( "sortKey" + rowId ).value ;
    var msg         = ajaxValidateApplicationStatus( statusValue
                                                   , style
                                                   , isActive
                                                   , sortKey
                                                   ) ;
    if ( '' !== msg ) {
        alert( msg ) ;
        return false ;
    }
    var uri     = "AJAXAddApplicationStatus.php" ;
    var data    = "statusValue=" + encodeURIComponent( statusValue )
                + "&style=" + encodeURIComponent( style )
                + "&isActive=" + ( isActive ? '1' : '0' )
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
function saveUpdateApplicationStatus( id ) {
    var rowId        = 'ux' + id ;
    var statusValue = document.getElementById( "statusValue" + id ).value ;
    var style       = document.getElementById( "style" + id ).value ;
    var isActive    = document.getElementById( "isActive" + id ).checked ;
    var sortKey     = document.getElementById( "sortKey" + id ).value ;
    var msg         = ajaxValidateApplicationStatus( statusValue
                                                   , style
                                                   , isActive
                                                   , sortKey
                                                   ) ;
    if ( '' !== msg ) {
        alert( msg ) ;
        return false ;
    }
    var uri     = "AJAXUpdateApplicationStatus.php" ;
    var data    = "id=" + id
                + "&statusValue=" + encodeURIComponent( statusValue )
                + "&style=" + encodeURIComponent( style )
                + "&isActive=" + ( isActive ? '1' : '0' )
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
function doDeleteApplicationStatus( id ) {
    var uri     = "AJAXDeleteApplicationStatus.php" ;
    var data    = "id=" + encodeURIComponent( id ) ;
    var isAsync = true ;
    doLoadAjaxJsonResultWithCallback( uri, data, id, isAsync, function( xhttp, targetId ) {
        var jsonObj = JSON.parse( xhttp.responseText ) ;
        if ( "OK" == jsonObj.result ) {
            deleteRow( "ux" + id ) ;
        }
        else {
            var uri2 = "AJAXGetApplicationStatus.php" ;
            var jsonObj2 = JSON.parse( xhttp.responseText ) ;
            var result = jsonObj2.result ;
            var data2 = "id=" + id + "&warning=" + result ;
            doLoadAjaxJsonResultWithCallback( uri2, data2, id, isAsync, function( xhttp2, targetId2 ) {
                var jsonObj = JSON.parse( xhttp.responseText ) ;
                var row       = document.getElementById( "ux" + id ) ;
                if ( "OK" == jsonObj.result ) {
                    row.innerHTML = jsonObj.row ;
                }
                else {
                    row.innerHTML = "Undefined result!" ;
                }
            } ) ;  // END OF doAjaxJsonResultWithCallback( 2 )
        }
    } ) ; // END OF doAjaxJsonResultWithCallback( 1 )
    return false ;
}
