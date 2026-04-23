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

var rowNumber = 1 ;

/**
 * Make sure that the passed value is valid for the proposed condition. If
 * isRequired is true, dateString must not be blank or null as well as being
 * a valid date string. If isRequired is false, dateString may be blank or null,
 * but when it's not, it must be a valid date string. A valid date string looks
 * like YYYY-MM-DD
 *
 * @param dateString {String}
 * @param isRequired {Boolean}
 * @returns {Boolean}
 */
function isDateValid( dateString, isRequired ) {
    var regex = /^\d\d\d\d-\d\d-\d\d$/ ;
    var retVal = true ;

    if ( ! isRequired ) {
        if ( ( null == dateString ) || ( '' == dateString ) ) {
            return true ;
        }
    }
    else {
        retVal = ( ( null !== dateString ) && ( '' !== dateString ) ) ;
    }
    retVal = ( retVal && ( null !== dateString.match( regex ) ) ) ;
    if ( retVal ) {
        var daysInMonths = [ 31, 28, 31, 30, 31, 30, 31, 31, 30, 31, 30, 31 ] ;
        var yr = parseInt( dateString.substring( 0, 4 ) ) ;
        var mo = parseInt( dateString.substring( 5, 7 ) ) ;
        var da = parseInt( dateString.substring( 8, 10 ) ) ;
        if ( ! ( yr % 4 ) && ( ( yr % 100 ) || ! ( yr % 400 ) ) ) {
                daysInMonths[ 1 ]++ ; // Leap day!
        }
        if  ( ( yr < 2000 ) || ( yr > 2038 )
           || ( mo < 1 ) || ( mo > 12 )
           || ( da < 1 ) || ( da > daysInMonths[ mo - 1 ] )
            ) {
            retVal = false ;
        }
    }
    return ( retVal ) ;
} 

/**
 * Make sure that the passed value is valid for the proposed condition. If
 * isRequired is true, dateTimeString must not be blank or null as well as being
 * a valid date and time string. If isRequired is false, dateTimeString may be
 * blank or null, but when it's not, it must be a valid date and time string. A
 * valid date and time string looks like 'YYYY-MM-DD hh:mm:ss'
 *
 * @param dateTimeString {String}
 * @param isRequired {Boolean}
 * @returns {Boolean}
 */
function isDateTimeValid( dateTimeString, isRequired ) {
    var regex = /^\d\d\d\d-\d\d-\d\d\s\d\d:\d\d:\d\d$/ ;
    var retVal = true ;
    if ( ! isRequired ) {
        if ( ( null == dateTimeString ) || ( '' == dateTimeString ) ) {
            return true ;
        }
    }
    else {
        retVal = ( ( null !== dateTimeString ) && ( '' !== dateTimeString ) ) ;
    }
    retVal = ( retVal && ( null !== dateTimeString.match( regex ) ) ) ;
    if ( retVal ) {
        var daysInMonths = [ 31, 28, 31, 30, 31, 30, 31, 31, 30, 31, 30, 31 ] ;
        var yr = parseInt( dateTimeString.substring( 0, 4 ) ) ;
        var mo = parseInt( dateTimeString.substring( 5, 7 ) ) ;
        var da = parseInt( dateTimeString.substring( 8, 10 ) ) ;
        var hr = parseInt( dateTimeString.substring( 11, 13 ) ) ;
        var mi = parseInt( dateTimeString.substring( 14, 16 ) ) ;
        var se = parseInt( dateTimeString.substring( 17, 19 ) ) ;
        if ( ( yr % 4 ) && ( ( yr % 400 ) || ! ( yr % 100 ) ) ) {
            daysInMonths[ 1 ]++ ; // Leap day!
        }
        if  ( ( yr < 2000 ) || ( yr > 2038 )
           || ( mo < 1 ) || ( mo > 12 )
           || ( da < 1 ) || ( da > daysInMonths[ mo - 1 ] )
           || ( hr < 0 ) || ( hr > 23 )
           || ( mi < 0 ) || ( mi > 59 )
           || ( se < 0 ) || ( se > 59 )
            ) {
            retVal = false ;
        }
    }
    return ( retVal ) ;
}

function isNumeric( n ) {
    return ! isNaN( parseFloat( n ) ) && isFinite( n ) ;
}

/**
 * Load the results of an AJAX call into the target ID
 *
 * @param uri        URI
 * @param data        Data in URL-encoded format
 * @param targetId    The response will be loaded here.
 * @param isAsync    Load the response asynchronously.
 * @param callback    A user-defined routine to handle the results.
 */
function getCsrfToken() {
    var meta = document.querySelector( 'meta[name="csrf-token"]' ) ;
    return meta ? meta.getAttribute( 'content' ) : '' ;
}

function doLoadAjaxJsonResultWithCallback( uri, data, targetId, isAsync, callback ) {
    var xhttp = new XMLHttpRequest() ;
    xhttp.onreadystatechange = function() {
        if ( xhttp.readyState == 4 && xhttp.status == 200 ) {
            callback( xhttp, targetId ) ;
        }
    } ;
    xhttp.open( "POST", uri, isAsync ) ;
    xhttp.setRequestHeader( "Content-type", "application/x-www-form-urlencoded" ) ;
    xhttp.setRequestHeader( "X-CSRF-Token", getCsrfToken() ) ;
    xhttp.send( data ) ;
}

///////////////////////////////////////////////////////////////////////////////

/**
 * Dynamically remove a row that was created.
 *
 * @param rowId
 * @returns {Boolean}
 */
function deleteRow( rowId ) {
    var row = document.getElementById( rowId ) ;
    row.parentNode.removeChild( row ) ;
    return false ;
}

///////////////////////////////////////////////////////////////////////////////

// Quick Add Company modal

var quickAddCompanyTargetSelectId = null ;

/**
 * Open the quick add company modal.
 * After saving, the new company will be added to the target select and selected.
 *
 * @param {String} selectId  The DOM id of the company select dropdown to update
 */
function openAddCompanyModal( selectId ) {
    quickAddCompanyTargetSelectId = selectId ;
    document.getElementById( 'quickCompanyName' ).value = '' ;
    document.getElementById( 'quickCompanyUrl' ).value = '' ;
    document.getElementById( 'quickCompanyError' ).style.display = 'none' ;
    document.getElementById( 'addCompanyOverlay' ).style.display = 'block' ;
    document.getElementById( 'quickCompanyName' ).focus() ;
}

/**
 * Close the quick add company modal.
 */
function closeAddCompanyModal() {
    document.getElementById( 'addCompanyOverlay' ).style.display = 'none' ;
    quickAddCompanyTargetSelectId = null ;
}

/**
 * Save the new company and update the target dropdown.
 */
function saveQuickAddCompany() {
    var name = document.getElementById( 'quickCompanyName' ).value.trim() ;
    var url  = document.getElementById( 'quickCompanyUrl' ).value.trim() ;
    var err  = document.getElementById( 'quickCompanyError' ) ;
    if ( name === '' ) {
        err.innerHTML = 'Company name is required.' ;
        err.style.display = 'block' ;
        return ;
    }
    var data = 'companyName=' + encodeURIComponent( name )
             + '&companyUrl=' + encodeURIComponent( url ) ;
    doLoadAjaxJsonResultWithCallback( 'AJAXQuickAddCompany.php', data, 'quickAdd', true, function( xhttp, targetId ) {
        var jsonObj = JSON.parse( xhttp.responseText ) ;
        if ( jsonObj.result === 'OK' ) {
            // Add the new company to the target select and select it
            var sel = document.getElementById( quickAddCompanyTargetSelectId ) ;
            if ( sel ) {
                var opt = document.createElement( 'option' ) ;
                opt.value = jsonObj.companyId ;
                opt.text = jsonObj.companyName ;
                opt.selected = true ;
                // Insert alphabetically
                var inserted = false ;
                for ( var i = 1 ; i < sel.options.length ; i++ ) {
                    if ( sel.options[ i ].text.toLowerCase() > jsonObj.companyName.toLowerCase() ) {
                        sel.add( opt, sel.options[ i ] ) ;
                        inserted = true ;
                        break ;
                    }
                }
                if ( ! inserted ) {
                    sel.add( opt ) ;
                }
            }
            // Also update any other company selects on the page
            var allSelects = document.querySelectorAll( 'select[id^="companyId"]' ) ;
            for ( var j = 0 ; j < allSelects.length ; j++ ) {
                if ( allSelects[ j ].id !== quickAddCompanyTargetSelectId ) {
                    var opt2 = document.createElement( 'option' ) ;
                    opt2.value = jsonObj.companyId ;
                    opt2.text = jsonObj.companyName ;
                    var inserted2 = false ;
                    for ( var k = 1 ; k < allSelects[ j ].options.length ; k++ ) {
                        if ( allSelects[ j ].options[ k ].text.toLowerCase() > jsonObj.companyName.toLowerCase() ) {
                            allSelects[ j ].add( opt2, allSelects[ j ].options[ k ] ) ;
                            inserted2 = true ;
                            break ;
                        }
                    }
                    if ( ! inserted2 ) {
                        allSelects[ j ].add( opt2 ) ;
                    }
                }
            }
            closeAddCompanyModal() ;
        } else {
            err.innerHTML = jsonObj.error || 'Failed to add company.' ;
            err.style.display = 'block' ;
        }
    } ) ;
}

/**
 * On page load, if the URL has a hash like #ux123, scroll to and highlight that row.
 * Used by globalSearch.php to deep-link into listing pages.
 */
window.addEventListener( 'DOMContentLoaded', function() {
    var hash = window.location.hash ;
    if ( ! hash ) return ;
    var id = hash.substring( 1 ) ;
    var el = document.getElementById( id ) ;
    if ( ! el ) return ;
    el.scrollIntoView( { behavior: 'smooth', block: 'center' } ) ;
    el.style.outline = '3px solid #4a4a8a' ;
    setTimeout( function() { el.style.outline = '' ; }, 3000 ) ;
} ) ;
