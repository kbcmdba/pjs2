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
var rowNumber = 1 ;
var reviewJobId = null ;

///////////////////////////////////////////////////////////////////////////////

/**
 * Open the job review panel with an iframe showing the job URL
 * and a control bar for updating status, next action, etc.
 *
 * @param {String} id   The job ID
 * @param {String} url  The job posting URL
 * @returns {Boolean}
 */
function reviewJob( id, url ) {
    reviewJobId = id ;
    var overlay = document.getElementById( 'reviewOverlay' ) ;
    var bar     = document.getElementById( 'reviewBar' ) ;
    var frame   = document.getElementById( 'reviewFrame' ) ;
    bar.innerHTML = '<span class="reviewTitle">Loading...</span>' ;
    frame.src     = url ;
    overlay.style.display = 'block' ;
    var data = 'id=' + encodeURIComponent( id ) ;
    doLoadAjaxJsonResultWithCallback( 'AJAXGetJobData.php', data, id, true, function( xhttp, targetId ) {
        var jsonObj = JSON.parse( xhttp.responseText ) ;
        if ( jsonObj.result !== 'OK' ) {
            bar.innerHTML = '<span class="reviewTitle">Error loading job data</span>'
                          + ' <button onclick="closeReviewPanel()">Close</button>' ;
            return ;
        }
        var job      = jsonObj.job ;
        var statuses = jsonObj.statuses ;
        var statusOpts = '' ;
        for ( var i = 0 ; i < statuses.length ; i++ ) {
            var sel = ( statuses[ i ].id == job.applicationStatusId ) ? ' selected="selected"' : '' ;
            statusOpts += '<option value="' + statuses[ i ].id + '"' + sel + '>'
                        + statuses[ i ].value + '</option>' ;
        }
        var html = '<span class="reviewTitle">' + escapeHtml( job.positionTitle ) + '</span>'
                 + '<span class="reviewCompany">' + escapeHtml( job.companyName ) + '</span>'
                 + ' <label>Status:</label>'
                 + '<select id="reviewStatus">' + statusOpts + '</select>'
                 + ' <label>Next Action:</label>'
                 + '<input type="text" id="reviewNextAction" value="' + escapeHtml( job.nextAction || '' ) + '" size="30" />'
                 + ' <label>Due:</label>'
                 + '<input type="text" id="reviewNextActionDue" value="' + escapeHtml( job.nextActionDue || '' ) + '" size="12" class="datepicker" />'
                 + ' <button onclick="saveReviewPanel()">Save</button>'
                 + ' <button onclick="closeReviewPanel()">Close</button>' ;
        bar.innerHTML = html ;
        $( "#reviewNextActionDue" ).datepicker( { dateFormat: 'yy-mm-dd' } ) ;
    } ) ;
    return false ;
}

/**
 * Save changes from the review panel control bar.
 *
 * @returns {Boolean}
 */
function saveReviewPanel() {
    if ( ! reviewJobId ) return false ;
    var statusId      = document.getElementById( 'reviewStatus' ).value ;
    var nextAction    = document.getElementById( 'reviewNextAction' ).value.trim() ;
    var nextActionDue = document.getElementById( 'reviewNextActionDue' ).value.trim() ;
    var uri  = "AJAXUpdateJobReview.php" ;
    var data = "id=" + reviewJobId
             + "&applicationStatusId=" + encodeURIComponent( statusId )
             + "&nextAction=" + encodeURIComponent( nextAction )
             + "&nextActionDue=" + encodeURIComponent( nextActionDue )
             ;
    doLoadAjaxJsonResultWithCallback( uri, data, reviewJobId, true, function( xhttp, targetId ) {
        var jsonObj = JSON.parse( xhttp.responseText ) ;
        if ( jsonObj.result === 'OK' || jsonObj.result > 0 ) {
            var row = document.getElementById( "ux" + targetId ) ;
            if ( row ) {
                row.innerHTML = jsonObj.row ;
            }
        }
    } ) ;
    return false ;
}

/**
 * Close the review panel.
 *
 * @returns {Boolean}
 */
function closeReviewPanel() {
    var overlay = document.getElementById( 'reviewOverlay' ) ;
    var frame   = document.getElementById( 'reviewFrame' ) ;
    overlay.style.display = 'none' ;
    frame.src = 'about:blank' ;
    reviewJobId = null ;
    return false ;
}

/**
 * Escape HTML entities for safe display.
 *
 * @param {String} str
 * @returns {String}
 */
// Close review panel on Escape key
document.addEventListener( 'keydown', function( e ) {
    if ( e.key === 'Escape' && reviewJobId !== null ) {
        closeReviewPanel() ;
    }
} ) ;

/**
 * Escape HTML entities for safe display.
 *
 * @param {String} str
 * @returns {String}
 */
function escapeHtml( str ) {
    if ( ! str ) return '' ;
    return str.replace( /&/g, '&amp;' )
              .replace( /</g, '&lt;' )
              .replace( />/g, '&gt;' )
              .replace( /"/g, '&quot;' ) ;
}

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
        $( "#lastStatusChange" + id ).datepicker( { dateFormat: 'yy-mm-dd' } ) ;
        $( "#nextActionDue" + id ).datepicker( { dateFormat: 'yy-mm-dd' } ) ;
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
        $( "#lastStatusChange" + id ).datepicker( { dateFormat: 'yy-mm-dd' } ) ;
        $( "#nextActionDue" + id ).datepicker( { dateFormat: 'yy-mm-dd' } ) ;
    } ) ; // END OF doLoadAjaxJsonResultWithCallback( ...
    return false ;
}

function updateJobSetNow( id ) {
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
        var now = new Date() ;
        var y  = now.getFullYear() ;
        var mo = ( '0' + ( now.getMonth() + 1 ) ).slice( -2 ) ;
        var d  = ( '0' + now.getDate() ).slice( -2 ) ;
        var h  = ( '0' + now.getHours() ).slice( -2 ) ;
        var mi = ( '0' + now.getMinutes() ).slice( -2 ) ;
        var s  = ( '0' + now.getSeconds() ).slice( -2 ) ;
        var nowStr = y + '-' + mo + '-' + d + ' ' + h + ':' + mi + ':' + s ;
        document.getElementById( 'lastStatusChange' + id ).value = nowStr ;
        $( "#lastStatusChange" + id ).datepicker( { dateFormat: 'yy-mm-dd' } ) ;
        $( "#nextActionDue" + id ).datepicker( { dateFormat: 'yy-mm-dd' } ) ;
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
    if  ( !  ( isDateTimeValid( nextActionDue, false )
            || isDateValid( nextActionDue, false )
             )
        ) {
        message += "Next Action Due must be valid.\n" ;
    }
    if  ( !  ( isDateValid( lastStatusChange, true )
            || isDateTimeValid( lastStatusChange, true )
             )
        ) {
        message += "Last Status Change must be valid.\n" ;
    }
    if  ( ( null === urgency )
       || ( '' === urgency )
        ) {
        message += "Urgency is required.\n" ;
    }
    if  ( ( null === positionTitle )
       || ( '' === positionTitle )
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
    var primaryContactId    = document.getElementById( "contactId" + rowId ).value.trim() ;
    var companyId           = document.getElementById( "companyId" + rowId ).value.trim() ;
    var applicationStatusId = document.getElementById( "applicationStatusId" + rowId ).value.trim() ;
    var lastStatusChange    = document.getElementById( "lastStatusChange" + rowId ).value.trim() ;
    var urgency             = document.getElementById( "urgency" + rowId ).value.trim() ;
    var nextActionDue       = document.getElementById( "nextActionDue" + rowId ).value.trim() ;
    var nextAction          = document.getElementById( "nextAction" + rowId ).value.trim() ;
    var positionTitle       = document.getElementById( "positionTitle" + rowId ).value.trim() ;
    var location            = document.getElementById( "location" + rowId ).value.trim() ;
    var url                 = document.getElementById( "url" + rowId ).value.trim() ;
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
             + "&nextActionDue=" + encodeURIComponent( nextActionDue )
             + "&nextAction=" + encodeURIComponent( nextAction )
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
    var primaryContactId    = document.getElementById( "contactId" + id ).value.trim() ;
    var companyId           = document.getElementById( "companyId" + id ).value.trim() ;
    var applicationStatusId = document.getElementById( "applicationStatusId" + id ).value.trim() ;
    var lastStatusChange    = document.getElementById( "lastStatusChange" + id ).value.trim() ;
    var urgency             = document.getElementById( "urgency" + id ).value.trim() ;
    var nextActionDue       = document.getElementById( "nextActionDue" + id ).value.trim() ;
    var nextAction          = document.getElementById( "nextAction" + id ).value.trim() ;
    var positionTitle       = document.getElementById( "positionTitle" + id ).value.trim() ;
    var location            = document.getElementById( "location" + id ).value.trim() ;
    var url                 = document.getElementById( "url" + id ).value.trim() ;
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
             + "&contactId=" + encodeURIComponent( primaryContactId )
             + "&companyId=" + encodeURIComponent( companyId )
             + "&applicationStatusId=" + encodeURIComponent( applicationStatusId )
             + "&lastStatusChange=" + encodeURIComponent( lastStatusChange )
             + "&urgency=" + encodeURIComponent( urgency )
             + "&nextActionDue=" + encodeURIComponent( nextActionDue )
             + "&nextAction=" + encodeURIComponent( nextAction )
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
