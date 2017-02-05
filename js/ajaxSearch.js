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
function addSearch() {
    var table  = document.getElementById( "search" ) ;
    var row    = table.insertRow( 1 ) ;
    row.id     = "ix" + rowNumber ;
    var data   = 'id=' + rowNumber + '&mode=add' ;
    doLoadAjaxJsonResultWithCallback( 'AJAXGetSearchRow.php'
                                    , data
                                    , 'ix'
                                    + rowNumber
                                    , true
                                    , function ( xhttp, id ) {
        var jsonObj = JSON.parse( xhttp.responseText ) ;
        row.innerHTML = jsonObj.row ;
        $( "#rssLastChecked" + id ).datepicker( { dateFormat: 'yy-mm-dd' } ) ;
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
function updateSearch( id ) {
    var row  = document.getElementById( 'ux' + id ) ;
    var data = 'id=' + id + '&mode=update' ;
    doLoadAjaxJsonResultWithCallback( 'AJAXGetSearchRow.php'
                                    , data
                                    , 'ux'
                                    + id
                                    , true
                                    , function ( xhttp, targetId ) {
        var jsonObj = JSON.parse( xhttp.responseText ) ;
        row.innerHTML = jsonObj.row ;
        $( "#rssLastChecked" + id ).datepicker( { dateFormat: 'yy-mm-dd' } ) ;
    } ) ; // END OF doLoadAjaxJsonResultWithCallback( ...
    return false ;
}

/**
 * Display the Application Status row to be removed based on the id provided.
 * 
 * @param id
 * @returns {Boolean}
 */
function deleteSearch( id ) {
    var row  = document.getElementById( 'ux' + id ) ;
    var data = 'id=' + id + '&mode=delete' ;
    doLoadAjaxJsonResultWithCallback( 'AJAXGetSearchRow.php'
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
 * @param primaryContactId
 * @param companyId
 * @param applicationStatusId
 * @param lastStatusChange
 * @param urgency
 * @param nextActionDue
 * @param nextAction
 * @param positionTitle
 * @param location
 * @param url
 * @returns {String}
 */
function ajaxValidateSearch( engineName
                           , searchName
                           , url
                           , rssFeedUrl
                           , rssLastChecked
                        ) {
    var message = '' ;
    if  ( ( null === engineName )
       || ( '' === engineName )
        ) {
        message += "Engine Name must not be empty.\n" ;
    }
    if  ( ( null === searchName )
       || ( '' === searchName )
        ) {
        message += "Search Name must not be empty.\n" ;
    }
    if  ( ( null === url )
       || ( '' === url )
        ) {
        message += "Search Name must not be empty.\n" ;
    }
    // rssFeedUrl not validated
    // rssLastChecked not validated
    return message ;
}

/**
 * Revert the displayed row back to the updatable / deletable row.
 *
 * @param id
 * @returns {Boolean}
 */
function cancelUpdateSearch( id ) {
    var rowId   = 'ux' + id ;
    var uri     = "AJAXGetSearchRow.php" ;
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
 * Save the Search row displayed and display a replacement row that can be
 * updated or deleted.
 *
 * @param id
 * @returns {Boolean}
 */
function doAddSearch( id ) {
    var rowId          = 'ix' + id ;
    var engineName     = document.getElementById( "engineName" + rowId ).value ;
    var searchName     = document.getElementById( "searchName" + rowId ).value ;
    var url            = document.getElementById( "url" + rowId ).value ;
    var rssFeedUrl     = document.getElementById( "rssFeedUrl" + rowId ).value ;
    var rssLastChecked = document.getElementById( "rssLastChecked" + rowId ).value ;
    var msg            = ajaxValidateSearch( engineName
                                           , searchName
                                           , url
                                           , rssFeedUrl
                                           , rssLastChecked
                                           ) ;
    if ( '' !== msg ) {
        alert( msg ) ;
        return false ;
    }
    var uri  = "AJAXAddSearch.php" ;
    var data = "engineName=" + encodeURIComponent( engineName )
             + "&searchName=" + encodeURIComponent( searchName )
             + "&url=" + encodeURIComponent( url )
             + "&rssFeedUrl=" + encodeURIComponent( rssFeedUrl )
             + "&rssLastChecked=" + encodeURIComponent( rssLastChecked )
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
function doUpdateSearch( id ) {
    var rowId          = 'ux' + id ;
    var engineName     = document.getElementById( "engineName" + id ).value ;
    var searchName     = document.getElementById( "searchName" + id ).value ;
    var url            = document.getElementById( "url" + id ).value ;
    var rssFeedUrl     = document.getElementById( "rssFeedUrl" + id ).value ;
    var rssLastChecked = document.getElementById( "rssLastChecked" + id ).value ;
    var msg            = ajaxValidateSearch( engineName
                                           , searchName
                                           , url
                                           , rssFeedUrl
                                           , rssLastChecked
                                           ) ;
    if ( '' !== msg ) {
        alert( msg ) ;
        return false ;
    }
    var uri  = "AJAXUpdateSearch.php" ;
    var data = "id=" + id
             + "&engineName=" + encodeURIComponent( engineName )
             + "&searchName=" + encodeURIComponent( searchName )
             + "&url=" + encodeURIComponent( url )
             + "&rssFeedUrl=" + encodeURIComponent( rssFeedUrl )
             + "&rssLastChecked=" + encodeURIComponent( rssLastChecked )
             + "&rowId=" + encodeURIComponent( rowId )
             + "&rowStyle=update"
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
function doDeleteSearch( id ) {
    var uri     = "AJAXDeleteSearch.php" ;
    var data    = "id=" + encodeURIComponent( id ) ;
    var isAsync = true ;
    doLoadAjaxJsonResultWithCallback( uri, data, id, isAsync, function( xhttp, targetId ) {
        var jsonObj = JSON.parse( xhttp.responseText ) ;
        if ( "OK" == jsonObj.result ) {
            deleteRow( "ux" + id ) ;
        }
        else {
            var uri2 = "AJAXGetSearch.php" ;
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
