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

///////////////////////////////////////////////////////////////////////////////

/**
 * Add an application status row for user input.
 *
 * @returns {Boolean}
 */
function addContact() {
    var table  = document.getElementById( "contact" ) ;
    var row    = table.insertRow( 1 ) ;
    row.id     = "ix" + rowNumber ;
    var data   = 'id=' + rowNumber + '&mode=add' ;
    doLoadAjaxJsonResultWithCallback( 'AJAXGetContactRow.php'
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
function updateContact( id ) {
    var row  = document.getElementById( 'ux' + id ) ;
    var data = 'id=' + id + '&mode=update' ;
    doLoadAjaxJsonResultWithCallback( 'AJAXGetContactRow.php'
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
function deleteContact( id ) {
    var row  = document.getElementById( 'ux' + id ) ;
    var data = 'id=' + id + '&mode=delete' ;
    doLoadAjaxJsonResultWithCallback( 'AJAXGetContactRow.php'
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
 * @param companyId
 * @param name
 * @param email
 * @param phone
 * @param alternatePhone
 * @returns {String}
 */
function ajaxValidateContact( companyId, name, email, phone, alternatePhone ) {
    var message = '' ;
    if  ( ( null !== companyId )
       && ( '' !== companyId )
       && ( ! isNumeric( companyId ) )
        ) {
        message += "Company must valid.\n" ;
    }
    if ( ( null == name ) || ( '' == name ) ) {
        message += "Name cannot be blank.\n" ;
    }
    return message ;
}

/**
 * Revert the displayed row back to the updatable / deletable row.
 *
 * @param id
 * @returns {Boolean}
 */
function cancelUpdateContactRow( id ) {
    var rowId   = 'ux' + id ;
    var uri     = "AJAXGetContactRow.php" ;
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
 * Save the Contact row displayed and display a replacement row that can be
 * updated or deleted.
 *
 * @param id
 * @returns {Boolean}
 */
function saveAddContact( id ) {
    var rowId     = 'ix' + id ;
    var companyId = document.getElementById( "companyId" + rowId ).value ;
    var name      = document.getElementById( "name" + rowId ).value ;
    var email     = document.getElementById( "email" + rowId ).value ;
    var phone     = document.getElementById( "phone" + rowId ).value ;
    var altPhone  = document.getElementById( "alternatePhone" + rowId ).value ;
    var msg       = ajaxValidateContact( companyId, name, email, phone, altPhone ) ;
    if ( '' !== msg ) {
        alert( msg ) ;
        return false ;
    }
    var uri  = "AJAXAddContact.php" ;
    var data = "companyId=" + encodeURIComponent( companyId )
             + "&name=" + encodeURIComponent( name )
             + "&email=" + encodeURIComponent( email )
             + "&phone=" + encodeURIComponent( phone )
             + "&alternatePhone=" + encodeURIComponent( altPhone )
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
function saveUpdateContact( id ) {
    var rowId        = 'ux' + id ;
    var companyId = document.getElementById( "companyId" + rowId ).value ;
    var name      = document.getElementById( "name" + rowId ).value ;
    var email     = document.getElementById( "email" + rowId ).value ;
    var phone     = document.getElementById( "phone" + rowId ).value ;
    var altPhone  = document.getElementById( "alternatePhone" + rowId ).value ;
    var msg       = ajaxValidateContact( companyId, name, email, phone, altPhone ) ;
    if ( '' !== msg ) {
        alert( msg ) ;
        return false ;
    }
    var uri  = "AJAXUpdateContact.php" ;
    var data = "id=" + id
             + "companyId=" + encodeURIComponent( companyId )
             + "&name=" + encodeURIComponent( name )
             + "&email=" + encodeURIComponent( email )
             + "&phone=" + encodeURIComponent( phone )
             + "&alternatePhone=" + encodeURIComponent( altPhone )
             + "&rowStyle=add"
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
function doDeleteContact( id ) {
    var uri     = "AJAXDeleteContact.php" ;
    var data    = "id=" + encodeURIComponent( id ) ;
    var isAsync = true ;
    doLoadAjaxJsonResultWithCallback( uri, data, id, isAsync, function( xhttp, targetId ) {
        var jsonObj = JSON.parse( xhttp.responseText ) ;
        if ( "OK" == jsonObj.result ) {
            deleteRow( "ux" + id ) ;
        }
        else {
            var uri2 = "AJAXGetContact.php" ;
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
