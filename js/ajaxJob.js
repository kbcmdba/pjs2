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
var reviewQueue = [] ;
var reviewQueueIndex = -1 ;
var reviewAdvancedViaSaveNext = false ;
var reviewActiveStatusIds = [] ;

///////////////////////////////////////////////////////////////////////////////

/**
 * Open the job review panel with an iframe showing the job URL
 * and a control bar for updating status, next action, etc.
 *
 * @param {String} id   The job ID
 * @param {String} url  The job posting URL
 * @returns {Boolean}
 */
/**
 * Build the review queue from active job rows on the page.
 * Each entry is { id: jobId, url: jobUrl }.
 */
function buildReviewQueue() {
    reviewQueue = [] ;
    // Get all job rows from the table
    var rows = document.querySelectorAll( 'tr[id^="ux"]' ) ;
    for ( var i = 0 ; i < rows.length ; i++ ) {
        var row = rows[ i ] ;
        var jobId = row.id.replace( 'ux', '' ) ;
        // Find the review link in this row
        var links = row.querySelectorAll( 'a[onclick*="reviewJob"]' ) ;
        if ( links.length > 0 ) {
            var onclick = links[ 0 ].getAttribute( 'onclick' ) ;
            var match = onclick.match( /reviewJob\(\s*'([^']+)'\s*,\s*'([^']+)'\s*\)/ ) ;
            if ( match ) {
                var statusId = parseInt( links[ 0 ].getAttribute( 'data-status-id' ) ) || 0 ;
                reviewQueue.push( { id: match[ 1 ], url: match[ 2 ], statusId: statusId } ) ;
            }
        }
    }
}

/**
 * Get count of remaining active jobs after current position in the queue.
 *
 * @returns {Number}
 */
function reviewQueueRemaining() {
    if ( reviewQueueIndex < 0 || reviewQueueIndex >= reviewQueue.length - 1 ) return 0 ;
    return reviewQueue.length - reviewQueueIndex - 1 ;
}

/**
 * Count active (non-closed) jobs remaining in the queue after current position.
 * Falls back to total remaining if active status IDs haven't been loaded yet.
 *
 * @returns {Number}
 */
/**
 * Get count of active (non-closed) jobs remaining after current position,
 * using the applicationStatusId stored in each queue entry.
 *
 * @returns {Number}
 */
function reviewQueueActiveRemaining() {
    if ( reviewActiveStatusIds.length === 0 ) return reviewQueueRemaining() ;
    var count = 0 ;
    for ( var i = reviewQueueIndex + 1 ; i < reviewQueue.length ; i++ ) {
        if ( reviewActiveStatusIds.indexOf( reviewQueue[ i ].statusId ) >= 0 ) {
            count++ ;
        }
    }
    return count ;
}

/**
 * Advance to the next job in the review queue.
 *
 * @returns {Boolean}
 */
function reviewNext() {
    // Save current first
    saveReviewPanel() ;
    if ( reviewQueueIndex < reviewQueue.length - 1 ) {
        reviewAdvancedViaSaveNext = true ;
        reviewQueueIndex++ ;
        var next = reviewQueue[ reviewQueueIndex ] ;
        reviewJob( next.id, next.url ) ;
    }
    return false ;
}

/**
 * Go back to the previous job in the review queue.
 *
 * @returns {Boolean}
 */
/**
 * Skip to the next job without saving changes.
 *
 * @returns {Boolean}
 */
function reviewSkip() {
    if ( reviewQueueIndex < reviewQueue.length - 1 ) {
        reviewAdvancedViaSaveNext = false ;
        reviewQueueIndex++ ;
        var next = reviewQueue[ reviewQueueIndex ] ;
        reviewJob( next.id, next.url ) ;
    }
    return false ;
}

function reviewPrev() {
    if ( reviewAdvancedViaSaveNext && reviewQueueIndex > 0 ) {
        reviewAdvancedViaSaveNext = false ;
        reviewQueueIndex-- ;
        var prev = reviewQueue[ reviewQueueIndex ] ;
        reviewJob( prev.id, prev.url ) ;
    }
    return false ;
}

function reviewJob( id, url ) {
    reviewJobId = id ;
    // Build queue on first open, find our position
    if ( reviewQueue.length === 0 ) {
        buildReviewQueue() ;
    }
    for ( var i = 0 ; i < reviewQueue.length ; i++ ) {
        if ( reviewQueue[ i ].id == id ) {
            reviewQueueIndex = i ;
            break ;
        }
    }
    var overlay = document.getElementById( 'reviewOverlay' ) ;
    var bar     = document.getElementById( 'reviewBar' ) ;
    var frame   = document.getElementById( 'reviewFrame' ) ;
    var nav     = document.getElementById( 'navBar' ) ;
    var navHeight = nav ? nav.offsetHeight : 40 ;
    overlay.style.top = navHeight + 'px' ;
    overlay.style.height = 'calc(100% - ' + navHeight + 'px)' ;
    bar.innerHTML = '<span class="reviewTitle">Loading...</span>' ;
    overlay.style.display = 'block' ;
    // Try loading the URL; show fallback if blocked
    frame.onload = function() {
        try {
            // Accessing contentDocument will throw if cross-origin blocked
            var doc = frame.contentDocument || frame.contentWindow.document ;
            if ( ! doc || doc.URL === 'about:blank' ) {
                throw new Error( 'blocked' ) ;
            }
        } catch ( e ) {
            // Site blocked framing — show fallback
            frame.style.display = 'none' ;
            var fallback = document.getElementById( 'reviewFallback' ) ;
            if ( ! fallback ) {
                fallback = document.createElement( 'div' ) ;
                fallback.id = 'reviewFallback' ;
                overlay.appendChild( fallback ) ;
            }
            fallback.style.display = 'flex' ;
            fallback.innerHTML = '<div style="text-align: center;">'
                + '<p style="font-size: 1.2em; margin-bottom: 16px;">This site cannot be embedded.</p>'
                + '<a href="' + url + '" target="_blank"><button style="font-size: 1em; padding: 10px 24px;">Open in New Tab</button></a>'
                + '</div>' ;
        }
    } ;
    frame.style.display = '' ;
    var fallback = document.getElementById( 'reviewFallback' ) ;
    if ( fallback ) fallback.style.display = 'none' ;
    frame.src = url ;
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
        reviewActiveStatusIds = [] ;
        var statusOpts = '' ;
        var currentStyle = '' ;
        for ( var i = 0 ; i < statuses.length ; i++ ) {
            if ( statuses[ i ].isActive ) {
                reviewActiveStatusIds.push( parseInt( statuses[ i ].id ) ) ;
            }
            var sel = ( statuses[ i ].id == job.applicationStatusId ) ? ' selected="selected"' : '' ;
            var optStyle = statuses[ i ].style || '' ;
            if ( sel ) currentStyle = optStyle ;
            statusOpts += '<option value="' + statuses[ i ].id + '" style="' + escapeHtml( optStyle ) + '"'
                        + ' data-style="' + escapeHtml( optStyle ) + '"' + sel + '>'
                        + statuses[ i ].value + '</option>' ;
        }
        var html = '<span class="reviewTitle">' + escapeHtml( job.positionTitle ) + '</span>'
                 + '<span class="reviewCompany">' + escapeHtml( job.companyName ) + '</span>'
                 + ' <label>Status:</label>'
                 + '<select id="reviewStatus" style="' + escapeHtml( currentStyle ) + '" onchange="updateReviewStatusStyle(this)">' + statusOpts + '</select>'
                 + ' <label>Next Action:</label>'
                 + '<input type="text" id="reviewNextAction" value="' + escapeHtml( job.nextAction || '' ) + '" size="30" />'
                 + ' <label>Due:</label>'
                 + '<input type="text" id="reviewNextActionDue" value="' + escapeHtml( job.nextActionDue || '' ) + '" size="12" class="datepicker" />'
                 + ' <button onclick="saveReviewPanel()">Save</button>'
                 + ' <button onclick="closeReviewPanel()">Close</button>' ;
        if ( reviewAdvancedViaSaveNext ) {
            html += ' <button onclick="reviewPrev()">Go Back</button>' ;
        }
        var remaining = reviewQueueActiveRemaining() ;
        if ( remaining > 0 ) {
            html += ' <button onclick="reviewSkip()">Next (' + remaining + ')</button>' ;
            html += ' <button onclick="reviewNext()">Save &amp; Next (' + remaining + ')</button>' ;
        }
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
            showToast( 'Saved', 'success' ) ;
        } else {
            showToast( 'Save failed: ' + ( jsonObj.error || 'Unknown error' ), 'error' ) ;
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
    frame.style.display = '' ;
    var fallback = document.getElementById( 'reviewFallback' ) ;
    if ( fallback ) fallback.style.display = 'none' ;
    reviewJobId = null ;
    reviewAdvancedViaSaveNext = false ;
    return false ;
}

/**
 * Escape HTML entities for safe display.
 *
 * @param {String} str
 * @returns {String}
 */
/**
 * Show a temporary toast message.
 *
 * @param {String} message  Text to display
 * @param {String} type     'success' or 'error'
 * @param {Number} duration Milliseconds before auto-hide (default 3000)
 */
var toastTimer = null ;
function showToast( message, type, duration ) {
    var toast = document.getElementById( 'reviewToast' ) ;
    toast.innerHTML = message + ' <span onclick="hideToast()" style="cursor: pointer; margin-left: 12px; font-size: 1.1em;">&times;</span>' ;
    toast.className = type || 'success' ;
    toast.style.display = 'block' ;
    if ( toastTimer ) clearTimeout( toastTimer ) ;
    toastTimer = setTimeout( function() {
        toast.style.display = 'none' ;
    }, duration || 10000 ) ;
}
function hideToast() {
    var toast = document.getElementById( 'reviewToast' ) ;
    toast.style.display = 'none' ;
    if ( toastTimer ) clearTimeout( toastTimer ) ;
}

/**
 * Update the review status dropdown style when selection changes.
 *
 * @param {HTMLSelectElement} sel
 */
function updateReviewStatusStyle( sel ) {
    var opt = sel.options[ sel.selectedIndex ] ;
    sel.setAttribute( 'style', opt.getAttribute( 'data-style' ) || '' ) ;
}

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
 * Toggle visibility of closed (inactive) job rows.
 */
function toggleClosedJobs() {
    var table  = document.getElementById( 'jobs' ) ;
    var button = document.getElementById( 'ToggleClosedButton' ) ;
    if ( table.classList.contains( 'hide-closed' ) ) {
        table.classList.remove( 'hide-closed' ) ;
        button.innerHTML = 'Hide Closed' ;
    } else {
        table.classList.add( 'hide-closed' ) ;
        button.innerHTML = 'Show Closed' ;
    }
}

/**
 * Check if a URL is already in use by another job.
 * Shows a warning next to the URL field if a duplicate is found.
 *
 * @param {String} inputId   The DOM id of the URL input field
 * @param {String} excludeId Job ID to exclude from the check (for updates)
 */
function checkDuplicateUrl( inputId, excludeId ) {
    var urlInput = document.getElementById( inputId ) ;
    var url = urlInput.value.trim() ;
    // Remove any existing warning
    var existingWarn = document.getElementById( inputId + '_dupwarn' ) ;
    if ( existingWarn ) existingWarn.parentNode.removeChild( existingWarn ) ;
    if ( url === '' ) return ;
    var data = 'url=' + encodeURIComponent( url ) ;
    if ( excludeId ) {
        data += '&excludeId=' + encodeURIComponent( excludeId ) ;
    }
    doLoadAjaxJsonResultWithCallback( 'AJAXCheckDuplicateUrl.php', data, inputId, true, function( xhttp, targetId ) {
        var jsonObj = JSON.parse( xhttp.responseText ) ;
        if ( jsonObj.isDuplicate ) {
            var warn = document.createElement( 'div' ) ;
            warn.id = targetId + '_dupwarn' ;
            warn.style.color = 'red' ;
            warn.style.fontWeight = 'bold' ;
            warn.style.fontSize = '0.9em' ;
            var desc = escapeHtml( jsonObj.positionTitle ) ;
            if ( jsonObj.companyName ) {
                desc += ' at ' + escapeHtml( jsonObj.companyName ) ;
            }
            warn.innerHTML = 'Duplicate URL! <a href="#" onclick="updateJob(\'' + jsonObj.jobId + '\'); '
                           + 'var r = document.getElementById(\'ux' + jsonObj.jobId + '\'); '
                           + 'if (r) r.scrollIntoView({behavior:\'smooth\',block:\'center\'}); '
                           + 'return false;" style="color: red;">'
                           + 'Edit job #' + jsonObj.jobId + ' (' + desc + ')</a>' ;
            urlInput.parentNode.appendChild( warn ) ;
            // Auto-set status to DUPLICATE on the current row
            var statusId = targetId.replace( 'url', 'applicationStatusId' ) ;
            var statusSelect = document.getElementById( statusId ) ;
            if ( statusSelect ) {
                for ( var i = 0 ; i < statusSelect.options.length ; i++ ) {
                    if ( statusSelect.options[ i ].text === 'DUPLICATE' ) {
                        statusSelect.selectedIndex = i ;
                        break ;
                    }
                }
            }
        }
    } ) ;
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
