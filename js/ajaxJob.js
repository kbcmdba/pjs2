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
function addJob() {
    var table  = document.getElementById( "jobs" ) ;
    var row    = table.insertRow( 1 ) ;
    row.id     = "ix" + rowNumber ;
    var data   = 'id=' + rowNumber + '&mode=add' ;
    doLoadAjaxJsonResultWithCallback( 'AJAXGetJobRow.php'
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
function updateJob( id ) {
    var row  = document.getElementById( 'ux' + id ) ;
    var data = 'id=' + id + '&mode=update' ;
    doLoadAjaxJsonResultWithCallback( 'AJAXGetJobRow.php'
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
function deleteJob( id ) {
    var row  = document.getElementById( 'ux' + id ) ;
    var data = 'id=' + id + '&mode=delete' ;
    doLoadAjaxJsonResultWithCallback( 'AJAXGetJobRow.php'
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
function ajaxValidateJob( primaryContactId
                        , companyId
                        , applicationStatusId
                        , lastStatusChange
                        , urgency
                        , nextActionDue
                        , nextAction
                        , positionTitle
                        , location
                        , url
                        ) {
    var message = '' ;
    if  ( ( null !== primaryContactId )
       && ( '' !== primaryContactId )
       && ( ! isNumeric( primaryContactId ) )
        ) {
        message += "Primary Contact must be valid.\n" ;
    }
    if  ( ( null !== companyId )
       && ( '' !== companyId )
       && ( ! isNumeric( companyId ) )
        ) {
        message += "Company must be valid.\n" ;
    }
    if  ( ( null !== applicationStatusId )
       && ( '' !== applicationStatusId )
       && ( ! isNumeric( applicationStatusId ) )
        ) {
        message += "Application Status must be valid.\n" ;
    }
    if  ( ( null !== lastStatusChange )
       && ( '' !== lastStatusChange )
       // @todo validate lastStatusChange date
        ) {
        message += "Last Status Change must be valid.\n" ;
    }
    if  ( ( null !== urgency )
       && ( '' !== urgency )
        ) {
        message += "Urgency is required.\n" ;
    }
    if  ( ( null !== nextAction )
       && ( '' !== nextAction )
        ) {
        message += "Next action is required.\n" ;
    }
    if  ( ( null !== nextActionDue )
       && ( '' !== nextActionDue )
       // @todo Validate nextActionDue date
        ) {
        message += "nextActionDue must be valid.\n" ;
    }
    if  ( ( null !== positionTitle )
       && ( '' !== positionTitle )
        ) {
        message += "Position title is required.\n" ;
    }
    // location is not validated
    // url is not validated
    return message ;
}

/**
 * Revert the displayed row back to the updatable / deletable row.
 *
 * @param id
 * @returns {Boolean}
 */
function cancelUpdateJobRow( id ) {
    var rowId   = 'ux' + id ;
    var uri     = "AJAXGetJobRow.php" ;
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
 * Save the Job row displayed and display a replacement row that can be
 * updated or deleted.
 *
 * @param id
 * @returns {Boolean}
 */
function saveAddJob( id ) {
    var rowId               = 'ix' + id ;
    var primaryContactId    = document.getElementById( "primaryContactId" + rowId ).value ;
    var companyId           = document.getElementById( "companyId" + rowId ).value ;
    var applicationStatusId = document.getElementById( "applicationStatusId" + rowId ).value ;
    var lastStatusChange    = document.getElementById( "lastStatusChange" + rowId ).value ;
    var urgency             = document.getElementById( "urgency" + rowId ).value ;
    var nextActionDue       = document.getElementById( "nextActionDue" + rowId ).value ;
    var nextAction          = document.getElementById( "nextAction" + rowId ).value ;
    var positionTitle       = document.getElementById( "positionTitle" + rowId ).value ;
    var location            = document.getElementById( "location" + rowId ).value ;
    var url                 = document.getElementById( "url" + rowId ).value ;
    var msg                 = ajaxValidateJob( primaryContactId
                                             , companyId
                                             , applicationStatusId
                                             , lastStatusChange
                                             , urgency
                                             , nextActionDue
                                             , nextAction
                                             , positionTitle
                                             , location
                                             , url
                                             ) ;
    if ( '' !== msg ) {
        alert( msg ) ;
        return false ;
    }
    var uri  = "AJAXAddJob.php" ;
    var data = "primaryContactId=" + encodeURIComponent( primaryContactId )
             + "&companyId=" + encodeURIComponent( companyId )
             + "&applicationStatusId=" + encodeURIComponent( applicationStatusId )
             + "&lastStatusChange=" + encodeURIComponent( lastStatusChange )
             + "&urgency=" + encodeURIComponent( urgency )
             + "&nextActionDue" + encodeURIComponent( nextActionDue )
             + "&nextAction" + encodeURIComponent( nextAction )
             + "&positionTitle=" + encodeURIComponent( positionTitle )
             + "&location=" + encodeURIComponent( location )
             + "&url=" + encodeURIComponent( url )
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
function saveUpdateJob( id ) {
    var rowId               = 'ux' + id ;
    var primaryContactId    = document.getElementById( "primaryContactId" + rowId ).value ;
    var companyId           = document.getElementById( "companyId" + rowId ).value ;
    var applicationStatusId = document.getElementById( "applicationStatusId" + rowId ).value ;
    var lastStatusChange    = document.getElementById( "lastStatusChange" + rowId ).value ;
    var urgency             = document.getElementById( "urgency" + rowId ).value ;
    var nextActionDue       = document.getElementById( "nextActionDue" + rowId ).value ;
    var nextAction          = document.getElementById( "nextAction" + rowId ).value ;
    var positionTitle       = document.getElementById( "positionTitle" + rowId ).value ;
    var location            = document.getElementById( "location" + rowId ).value ;
    var url                 = document.getElementById( "url" + rowId ).value ;
    var msg                 = ajaxValidateJob( primaryContactId
                                             , companyId
                                             , applicationStatusId
                                             , lastStatusChange
                                             , urgency
                                             , nextActionDue
                                             , nextAction
                                             , positionTitle
                                             , location
                                             , url
                                             ) ;
    if ( '' !== msg ) {
        alert( msg ) ;
        return false ;
    }
    var uri  = "AJAXUpdateJob.php" ;
    var data = "id=" + id
             + "&primaryContactId=" + encodeURIComponent( primaryContactId )
             + "&companyId=" + encodeURIComponent( companyId )
             + "&applicationStatusId=" + encodeURIComponent( applicationStatusId )
             + "&lastStatusChange=" + encodeURIComponent( lastStatusChange )
             + "&urgency=" + encodeURIComponent( urgency )
             + "&nextActionDue" + encodeURIComponent( nextActionDue )
             + "&nextAction" + encodeURIComponent( nextAction )
             + "&positionTitle=" + encodeURIComponent( positionTitle )
             + "&location=" + encodeURIComponent( location )
             + "&url=" + encodeURIComponent( url )
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
function doDeleteJob( id ) {
    var uri     = "AJAXDeleteJob.php" ;
    var data    = "id=" + encodeURIComponent( id ) ;
    var isAsync = true ;
    doLoadAjaxJsonResultWithCallback( uri, data, id, isAsync, function( xhttp, targetId ) {
        var jsonObj = JSON.parse( xhttp.responseText ) ;
        if ( "OK" == jsonObj.result ) {
            deleteRow( "ux" + id ) ;
        }
        else {
            var uri2 = "AJAXGetJob.php" ;
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
