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
 * Add a company row for user input.
 *
 * @returns {Boolean}
 */
function addCompany() {
    var table  = document.getElementById( "companies" ) ;
    var row2   = table.insertRow( 2 ) ;
    var row1   = table.insertRow( 2 ) ;
    row1.id    = "ix" + rowNumber + "-1" ;
    row2.id    = "ix" + rowNumber + "-2" ;
    var data   = 'id=' + rowNumber + '&mode=add&rowStyle=add' ;
    doLoadAjaxJsonResultWithCallback( 'AJAXGetCompanyRow.php'
                                    , data
                                    , 'ix'
                                    + rowNumber
                                    , true
                                    , function ( xhttp, id ) {
        var jsonObj = JSON.parse( xhttp.responseText ) ;
        row1.innerHTML = jsonObj.rows[ 0 ] ;
        row2.innerHTML = jsonObj.rows[ 1 ] ;
        $( "#lastContactedix" + id ).datepicker( { dateFormat: 'yy-mm-dd' } ) ;
    } ) ; // END OF doLoadAjaxJsonResultWithCallback( ...
    rowNumber ++ ;
    return false ;
}

/**
 * Display the Company row to be updated based on the id provided.
 * 
 * @param id
 * @returns {Boolean}
 */
function updateCompany( id ) {
    var row1  = document.getElementById( 'ux' + id + '-1' ) ;
    var row2  = document.getElementById( 'ux' + id + '-2' ) ;
    var data = 'id=' + id + '&mode=update&rowStyle=add' ;
    doLoadAjaxJsonResultWithCallback( 'AJAXGetCompanyRow.php'
                                    , data
                                    , 'ux'
                                    + id
                                    , true
                                    , function ( xhttp, targetId ) {
        var jsonObj = JSON.parse( xhttp.responseText ) ;
        row1.innerHTML = jsonObj.rows[ 0 ] ;
        row2.innerHTML = jsonObj.rows[ 1 ] ;
        $( "#lastContacted" + id ).datepicker( { dateFormat: 'yy-mm-dd' } ) ;
    } ) ; // END OF doLoadAjaxJsonResultWithCallback( ...
    return false ;
}

/**
 * Display the Company row to be removed based on the id provided.
 * 
 * @param id
 * @returns {Boolean}
 */
function deleteCompany( id ) {
    var row1  = document.getElementById( 'ux' + id + '-1' ) ;
    var row2  = document.getElementById( 'ux' + id + '-2' ) ;
    var data = 'id=' + id + '&mode=delete&rowStyle=add' ;
    doLoadAjaxJsonResultWithCallback( 'AJAXGetCompanyRow.php'
                                    , data
                                    , 'ux'
                                    + id
                                    , true
                                    , function ( xhttp, targetId ) {
        var jsonObj = JSON.parse( xhttp.responseText ) ;
        row1.innerHTML = jsonObj.rows[ 0 ] ;
        row2.innerHTML = jsonObj.rows[ 1 ] ;
    } ) ; // END OF doLoadAjaxJsonResultWithCallback( ...
    return false ;
}

/**
 * Validate a Company row prior to submission. When there are errors, the errors
 * are reported back in the return string. Otherwise, the empty string is
 * returned.
 *
 * @param companyName
 * @param agency
 * @param addr1
 * @param addr2
 * @param city
 * @param state
 * @param zip
 * @param phone
 * @param url
 * @returns {String}
 */
function ajaxValidateCompany( companyName, agency, addr1, addr2, city, state, zip, phone, url, lastContacted ) {
    var message = '' ;
    if ( ( null == companyName ) || ( '' == companyName ) ) {
        message += "Company Name cannot be blank.\n" ;
    }
    if  ( ! isDateValid( lastContacted, false ) &&  ! isDateTimeValid( lastContacted, false ) ) {
        message += "Last Contacted must be valid.\n" ;
    }
    return message ;
}

/**
 * Revert the displayed row back to the updatable / deletable row.
 *
 * @param id
 * @returns {Boolean}
 */
function cancelUpdateCompanyRow( id ) {
    var uri     = "AJAXGetCompanyRow.php" ;
    var data    = "id=" + encodeURIComponent( id ) + '&mode=list' ;
    var isAsync = true ;
    doLoadAjaxJsonResultWithCallback( uri, data, id, isAsync, function( xhttp, targetId ) {
        var jsonObj    = JSON.parse( xhttp.responseText ) ;
        var row1       = document.getElementById( "ux" + id + "-1" ) ;
        var row2       = document.getElementById( "ux" + id + "-2" ) ;
        row1.innerHTML = jsonObj.rows[ 0 ] ;
        row2.innerHTML = jsonObj.rows[ 1 ] ;
    } ) ; // END OF doLoadAjaxJsonResultWithCallback( ...
    return false ;
}

/**
 * Save the Company row displayed and display a replacement row that can be
 * updated or deleted.
 *
 * @param id
 * @returns {Boolean}
 */
function saveAddCompany( id ) {
    var agencyCompanyId = document.getElementById( "agencyCompanyIdix" + id ).value ;
    var companyName     = document.getElementById( "companyNameix" + id ).value ;
    var companyAddress1 = document.getElementById( "companyAddress1ix" + id ).value ;
    var companyAddress2 = document.getElementById( "companyAddress2ix" + id ).value ;
    var companyCity     = document.getElementById( "companyCityix" + id ).value ;
    var companyState    = document.getElementById( "companyStateix" + id ).value ;
    var companyZip      = document.getElementById( "companyZipix" + id ).value ;
    var companyPhone    = document.getElementById( "companyPhoneix" + id ).value ;
    var companyUrl      = document.getElementById( "companyUrlix" + id ).value ;
    var lastContacted   = document.getElementById( "lastContactedix" + id ).value ;
    var msg             = ajaxValidateCompany( companyName
                                             , agencyCompanyId
                                             , companyAddress1
                                             , companyAddress1
                                             , companyCity
                                             , companyState
                                             , companyZip
                                             , companyPhone
                                             , companyUrl
                                             , lastContacted
                                             ) ;
    if ( '' !== msg ) {
        alert( msg ) ;
        return false ;
    }
    var uri     = "AJAXAddCompany.php" ;
    var data    = "companyName=" + encodeURIComponent( companyName )
                + "&agencyCompanyId=" + encodeURIComponent( agencyCompanyId )
                + "&companyAddress1=" + encodeURIComponent( companyAddress1 )
                + "&companyAddress2=" + encodeURIComponent( companyAddress2 )
                + "&companyCity=" + encodeURIComponent( companyCity )
                + "&companyState=" + encodeURIComponent( companyState )
                + "&companyZip=" + encodeURIComponent( companyZip )
                + "&companyPhone=" + encodeURIComponent( companyPhone )
                + "&companyUrl=" + encodeURIComponent( companyUrl )
                + "&lastContacted=" + encodeURIComponent( lastContacted )
                + "&rowStyle=add"
                ;
    var isAsync = true ;
    doLoadAjaxJsonResultWithCallback( uri, data, id, isAsync, function( xhttp, targetId ) {
        var jsonObj    = JSON.parse( xhttp.responseText ) ;
        var row1       = document.getElementById( "ix" + id + "-1" ) ;
        var row2       = document.getElementById( "ix" + id + "-2" ) ;
        row1.id        = "ux" + jsonObj.newId + "-1" ;
        row2.id        = "ux" + jsonObj.newId + "-2" ;
        row1.innerHTML = jsonObj.rows[ 0 ] ;
        row2.innerHTML = jsonObj.rows[ 1 ] ;
    } ) ; // END OF doLoadAjaxJsonResultWithCallback( ...
    return false ;
}

/**
 * Update the lastContacted column to read the current date/timestamp.
 *
 * @param id
 * @returns {Boolean}
 */
function doUpdateLastContacted( id ) {
    var rowId           = 'ux' + id ;
    var uri     = "AJAXUpdateCompanyLastContacted.php" ;
    var data    = "id=" + id + "&rowStyle=add" ;
    var isAsync = true ;
    doLoadAjaxJsonResultWithCallback( uri, data, id, isAsync, function( xhttp, targetId ) {
        var jsonObj   = JSON.parse( xhttp.responseText ) ;
        var row1      = document.getElementById( "ux" + targetId + "-1" ) ;
        var row2      = document.getElementById( "ux" + targetId + "-2" ) ;
        row1.innerHTML = jsonObj.rows[ 0 ] ;
        row2.innerHTML = jsonObj.rows[ 1 ] ;
    } ) ; // END OF doLoadAjaxJsonResultWithCallback( ...
    return false ;
}

/**
 * Actually save the changes to the row and redisplay it.
 *
 * @param id
 * @returns {Boolean}
 */
function saveUpdateCompany( id ) {
    var rowId           = 'ux' + id ;
    var agencyCompanyId = document.getElementById( "agencyCompanyId" + id ).value ;
    var companyName     = document.getElementById( "companyName" + id ).value ;
    var companyAddress1 = document.getElementById( "companyAddress1" + id ).value ;
    var companyAddress2 = document.getElementById( "companyAddress2" + id ).value ;
    var companyCity     = document.getElementById( "companyCity" + id ).value ;
    var companyState    = document.getElementById( "companyState" + id ).value ;
    var companyZip      = document.getElementById( "companyZip" + id ).value ;
    var companyPhone    = document.getElementById( "companyPhone" + id ).value ;
    var companyUrl      = document.getElementById( "companyUrl" + id ).value ;
    var lastContacted   = document.getElementById( "lastContacted" + id ).value ;
    var msg             = ajaxValidateCompany( companyName
                                             , agencyCompanyId
                                             , companyAddress1
                                             , companyAddress1
                                             , companyCity
                                             , companyState
                                             , companyZip
                                             , companyPhone
                                             , companyUrl
                                             , lastContacted
                                             ) ;
    if ( '' !== msg ) {
        alert( msg ) ;
        return false ;
    }
    var uri     = "AJAXUpdateCompany.php" ;
    var data    = "id=" + id
                + "&companyName=" + encodeURIComponent( companyName )
                + "&agencyCompanyId=" + encodeURIComponent( agencyCompanyId )
                + "&companyAddress1=" + encodeURIComponent( companyAddress1 )
                + "&companyAddress2=" + encodeURIComponent( companyAddress2 )
                + "&companyCity=" + encodeURIComponent( companyCity )
                + "&companyState=" + encodeURIComponent( companyState )
                + "&companyZip=" + encodeURIComponent( companyZip )
                + "&companyPhone=" + encodeURIComponent( companyPhone )
                + "&companyUrl=" + encodeURIComponent( companyUrl )
                + "&lastContacted=" + encodeURIComponent( lastContacted )
                + "&rowStyle=add"
                ;
    var isAsync = true ;
    doLoadAjaxJsonResultWithCallback( uri, data, id, isAsync, function( xhttp, targetId ) {
        var jsonObj   = JSON.parse( xhttp.responseText ) ;
        var row1      = document.getElementById( "ux" + targetId + "-1" ) ;
        var row2      = document.getElementById( "ux" + targetId + "-2" ) ;
        row1.innerHTML = jsonObj.rows[ 0 ] ;
        row2.innerHTML = jsonObj.rows[ 1 ] ;
    } ) ; // END OF doLoadAjaxJsonResultWithCallback( ...
    return false ;
}

/**
 * Actually remove the displayed row from the database and the user's screen.
 *
 * @param id
 * @returns {Boolean}
 */
function doDeleteCompany( id ) {
    var uri     = "AJAXDeleteCompany.php" ;
    var data    = "id=" + encodeURIComponent( id ) ;
    var isAsync = true ;
    doLoadAjaxJsonResultWithCallback( uri, data, id, isAsync, function( xhttp, targetId ) {
        var jsonObj = JSON.parse( xhttp.responseText ) ;
        if ( "OK" == jsonObj.result ) {
            deleteRow( "ux" + id + "-1" ) ;
            deleteRow( "ux" + id + "-2" ) ;
        }
        else {
            var uri2 = "AJAXGetCompanyRow.php" ;
            var jsonObj2 = JSON.parse( xhttp.responseText ) ;
            var result = jsonObj2.result ;
            var data2 = "id=" + id + "&warning=" + result ;
            doLoadAjaxJsonResultWithCallback( uri2, data2, id, isAsync, function( xhttp2, targetId2 ) {
                var jsonObj = JSON.parse( xhttp.responseText ) ;
                var row1    = document.getElementById( "ux" + id + "-1" ) ;
                var row2    = document.getElementById( "ux" + id + "-2" ) ;
                if ( "OK" == jsonObj.result ) {
                    row1.innerHTML = jsonObj2.rows[ 0 ] ;
                    row2.innerHTML = jsonObj2.rows[ 1 ] ;
                }
                else {
                    row1.innerHTML = "<td colspan=\"7\">Undefined result!</td>" ;
                    row2.innerHTML = "<td colspan=\"7\">Reload this page.</td>" ;
                }
            } ) ;  // END OF doAjaxJsonResultWithCallback( 2 )
        }
    } ) ; // END OF doAjaxJsonResultWithCallback( 1 )
    return false ;
}
