/**
 * phpjobseeker
 *
 * Copyright (C) 2009, 2015 Kevin Benton - kbenton at bentonfam dot org
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

function isNumeric(n) {
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
 * @param agencyOf
 * @param addr1
 * @param addr2
 * @param city
 * @param state
 * @param zip
 * @param phone
 * @param url
 * @returns {String}
 */
function ajaxValidateCompany( companyName, agencyOf, addr1, addr2, city, state, zip, phone, url ) {
    var message = '' ;
    if ( ( null == companyName ) || ( '' == companyName ) ) {
        message += "Company Name cannot be blank.\n" ;
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
    var isAnAgency      = ( agencyCompanyId > 0 ) ? 1 : 0 ;
    var companyName     = document.getElementById( "companyNameix" + id ).value ;
    var companyAddress1 = document.getElementById( "companyAddress1ix" + id ).value ;
    var companyAddress2 = document.getElementById( "companyAddress2ix" + id ).value ;
    var companyCity     = document.getElementById( "companyCityix" + id ).value ;
    var companyState    = document.getElementById( "companyStateix" + id ).value ;
    var companyZip      = document.getElementById( "companyZipix" + id ).value ;
    var companyPhone    = document.getElementById( "companyPhoneix" + id ).value ;
    var companyUrl      = document.getElementById( "companyUrlix" + id ).value ;
    var msg             = ajaxValidateCompany( companyName
                                             , isAnAgency
                                             , agencyCompanyId
                                             , companyAddress1
                                             , companyAddress1
                                             , companyCity
                                             , companyState
                                             , companyZip
                                             , companyPhone
                                             , companyUrl
                                             ) ;
    if ( '' !== msg ) {
        alert( msg ) ;
        return false ;
    }
    var uri     = "AJAXAddCompany.php" ;
    var data    = "companyName=" + encodeURIComponent( companyName )
                + "&agencyCompanyId=" + encodeURIComponent( agencyCompanyId )
                + "&isAnAgency=" + encodeURIComponent( isAnAgency )
                + "&companyAddress1=" + encodeURIComponent( companyAddress1 )
                + "&companyAddress2=" + encodeURIComponent( companyAddress2 )
                + "&companyCity=" + encodeURIComponent( companyCity )
                + "&companyState=" + encodeURIComponent( companyState )
                + "&companyZip=" + encodeURIComponent( companyZip )
                + "&companyPhone=" + encodeURIComponent( companyPhone )
                + "&companyUrl=" + encodeURIComponent( companyUrl )
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
 * Actually save the changes to the row and redisplay it.
 *
 * @param id
 * @returns {Boolean}
 */
function saveUpdateCompany( id ) {
    var rowId           = 'ux' + id ;
    var agencyCompanyId = document.getElementById( "agencyCompanyId" + id ).value ;
    var isAnAgency      = ( agencyCompanyId > 0 ) ? 1 : 0 ;
    var companyName     = document.getElementById( "companyName" + id ).value ;
    var companyAddress1 = document.getElementById( "companyAddress1" + id ).value ;
    var companyAddress2 = document.getElementById( "companyAddress2" + id ).value ;
    var companyCity     = document.getElementById( "companyCity" + id ).value ;
    var companyState    = document.getElementById( "companyState" + id ).value ;
    var companyZip      = document.getElementById( "companyZip" + id ).value ;
    var companyPhone    = document.getElementById( "companyPhone" + id ).value ;
    var companyUrl      = document.getElementById( "companyUrl" + id ).value ;
    var msg             = ajaxValidateCompany( companyName
                                             , isAnAgency
                                             , agencyCompanyId
                                             , companyAddress1
                                             , companyAddress1
                                             , companyCity
                                             , companyState
                                             , companyZip
                                             , companyPhone
                                             , companyUrl
                                             ) ;
    if ( '' !== msg ) {
        alert( msg ) ;
        return false ;
    }
    var uri     = "AJAXUpdateCompany.php" ;
    var data    = "id=" + id
                + "&companyName=" + encodeURIComponent( companyName )
                + "&isAnAgency=" + encodeURIComponent( isAnAgency )
                + "&agencyCompanyId=" + encodeURIComponent( agencyCompanyId )
                + "&companyAddress1=" + encodeURIComponent( companyAddress1 )
                + "&companyAddress2=" + encodeURIComponent( companyAddress2 )
                + "&companyCity=" + encodeURIComponent( companyCity )
                + "&companyState=" + encodeURIComponent( companyState )
                + "&companyZip=" + encodeURIComponent( companyZip )
                + "&companyPhone=" + encodeURIComponent( companyPhone )
                + "&companyUrl=" + encodeURIComponent( companyUrl )
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
            var uri2 = "AJAXGetCompany.php" ;
            var jsonObj2 = JSON.parse( xhttp.responseText ) ;
            var result = jsonObj2.result ;
            var data2 = "id=" + id + "&warning=" + result ;
            doLoadAjaxJsonResultWithCallback( uri2, data2, id, isAsync, function( xhttp2, targetId2 ) {
                var jsonObj = JSON.parse( xhttp.responseText ) ;
                var row1    = document.getElementById( "ux" + id + "-1" ) ;
                var row2    = document.getElementById( "ux" + id + "-2" ) ;
                if ( "OK" == jsonObj.result ) {
                    row1.innerHTML = jsonObj.rows[ 0 ] ;
                    row2.innerHTML = jsonObj.rows[ 1 ] ;
                }
                else {
                    row.innerHTML = "Undefined result!" ;
                }
            } ) ;  // END OF doAjaxJsonResultWithCallback( 2 )
        }
    } ) ; // END OF doAjaxJsonResultWithCallback( 1 )
    return false ;
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

function validateApplicationStatus() {
    var retVal = true ;
    var message = '' ;
    var formObj = document.forms["applicationStatus"] ;
    var sk = formObj["sortKey"].value ;
    if ( ( null == formObj["statusValue"].value ) || ( '' == formObj["statusValue"].value ) ) {
        retVal = false ;
        message += "Status Value is required.\n" ;
    }
    if ( ( null == sk ) || ( '' == sk ) || ( ! isNumeric( sk ) ) ) {
        retVal = false ;
        message += "Sort Key is required and must be numeric.\n" ;
    }
    if ( false == retVal ) {
        alert( message ) ;
    }
    return retVal ;
}

function validateCompany() {
    var retVal = true ;
    var message = '' ;
    var formObj = document.forms["company"] ;
    if ( ( null == formObj["companyName"].value ) || ( '' == formObj["companyName"].value ) ) {
        retVal = false ;
        message += "Company Name is required.\n" ;
    }
    if ( ( null == formObj["companyCity"].value ) || ( '' == formObj["companyCity"].value ) ) {
        retVal = false ;
        message += "Company City is required.\n" ;
    }
    if ( ( null == formObj["companyState"].value ) || ( '' == formObj["companyState"].value ) ) {
        retVal = false ;
        message += "Company State is required.\n" ;
    }
    if ( false == retVal ) {
        alert( message ) ;
    }
    return retVal ;
}

function validateContact() {
    var retVal = true ;
    var message = '' ;
    var formObj = document.forms["contact"] ;
    var ci = formObj["companyId"].value ;
    if ( ( null == ci ) || ( '' == ci ) || ( ! isNumeric( ci ) ) || ( 1 > ci ) ) {
        retVal = false ;
        message += "Contact's Company must be selected.\n" ;
    }
    if ( ( null == formObj["contactName"].value ) || ( '' == formObj["contactName"].value ) ) {
        retVal = false ;
        message += "Contact's Name is required.\n" ;
    }
    if ( ( null == formObj["contactEmail"].value ) || ( '' == formObj["contactEmail"].value ) ) {
        retVal = false ;
        message += "Contact's Email is required.\n" ;
    }
    if ( ( null == formObj["contactPhone"].value ) || ( '' == formObj["contactPhone"].value ) ) {
        retVal = false ;
        message += "Contact's Phone is required.\n" ;
    }
    if ( false == retVal ) {
        alert( message ) ;
    }
    return retVal ;
}

function validateJob() {
    var retVal = true ;
    var message = '' ;
    var formObj = document.forms["job"] ;
    var cni = formObj["contactId"].value ;
    var cmi = formObj["companyId"].value ;
    var asi = formObj["applicationStatusId"].value ;
    if ( ( null == cni ) || ( '' == cni ) || ( ! isNumeric( cni ) ) || ( 1 > cni ) ) {
        retVal = false ;
        message += "Primary Contact is required.\n" ;
    }
    if ( ( null == cmi ) || ( '' == cmi ) || ( ! isNumeric( cmi ) ) || ( 1 > cmi ) ) {
        retVal = false ;
        message += "Company is required.\n" ;
    }
    if ( ( null == asi ) || ( '' == asi ) || ( ! isNumeric( asi ) ) || ( 1 > asi ) ) {
        retVal = false ;
        message += "Application Status is required.\n" ;
    }
    if ( ( null == formObj["urgency"].value ) || ( '' == formObj["urgency"].value ) ) {
        retVal = false ;
        message += "Urgency is required.\n" ;
    }
    if ( ( null == formObj["nextActionDue"].value ) || ( '' == formObj["nextActionDue"].value ) ) {
        retVal = false ;
        message += "Next Action Due is required.\n" ;
    }
    if ( ( null == formObj["nextAction"].value ) || ( '' == formObj["nextAction"].value ) ) {
        retVal = false ;
        message += "Next Action is required.\n" ;
    }
    if ( ( null == formObj["positionTitle"].value ) || ( '' == formObj["positionTitle"].value ) ) {
        retVal = false ;
        message += "Position Title is required.\n" ;
    }
    if ( ( null == formObj["location"].value ) || ( '' == formObj["location"].value ) ) {
        retVal = false ;
        message += "Location is required.\n" ;
    }
    if ( false == retVal ) {
        alert( message ) ;
    }
    return retVal ;
}

function validateNote() {
    var retVal = true ;
    var message = '' ;
    var formObj = document.forms["note"] ;
    if ( ( null == formObj["appliesToTable"].value ) || ( '' == formObj["appliesToTable"].value ) ) {
        retVal = false ;
        message += "Applies To Table is required.\n" ;
    }
    if ( ( null == formObj["appliesToId"].value ) || ( '' == formObj["appliesToId"].value ) ) {
        retVal = false ;
        message += "Applies To ID is required.\n" ;
    }
    if ( ( null == formObj["noteText"].value ) || ( '' == formObj["noteText"].value ) ) {
        retVal = false ;
        message += "Note Text is required.\n" ;
    }
    if ( false == retVal ) {
        alert( message ) ;
    }
    return retVal ;
}

function validateSearch() {
    var retVal = true ;
    var message = '' ;
    var formObj = document.forms["search"] ;
    if ( ( null == formObj["engineName"].value ) || ( '' == formObj["engineName"].value ) ) {
        retVal = false ;
        message += "Engine Name is required.\n" ;
    }
    if ( ( null == formObj["searchName"].value ) || ( '' == formObj["searchName"].value ) ) {
        retVal = false ;
        message += "Search Name is required.\n" ;
    }
    if ( ( null == formObj["url"].value ) || ( '' == formObj["url"].value ) ) {
        retVal = false ;
        message += "URL is required.\n" ;
    }
    if ( false == retVal ) {
        alert( message ) ;
    }
    return retVal ;
}
