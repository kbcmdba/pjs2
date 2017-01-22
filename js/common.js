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

var rowNumber = 1 ;

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
function doLoadAjaxJsonResultWithCallback( uri, data, targetId, isAsync, callback ) {
    var xhttp = new XMLHttpRequest() ;
    xhttp.onreadystatechange = function() {
        if ( xhttp.readyState == 4 && xhttp.status == 200 ) {
            callback( xhttp, targetId ) ;
        }
    } ;
    xhttp.open( "POST", uri, isAsync ) ;
    xhttp.setRequestHeader( "Content-type", "application/x-www-form-urlencoded" ) ;
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
