/**
 * Load the results of an AJAX call into the target ID
 *
 * @param uri		URI
 * @param data		Data in URL-encoded format
 * @param targetId	The response will be loaded here.
 * @param isAsync	Load the response asynchronously.
 */
function doLoadAjaxJsonResult( uri, data, targetId, isAsync ) {
	var xhttp = new XMLHttpRequest() ;
	xhttp.onreadystatechange = function() {
		if ( xhttp.readyState == 4 && xhttp.status == 200 ) {
			document.getElementById( targetId ).innerHTML = xhttp.responseText ;
		}
	} ;
	xhttp.open( "PUT", uri, isAsync ) ;
	xhttp.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
	xhttp.send( data ) ;
}

/**
 * Load the results of an AJAX call into the target ID
 *
 * @param uri		URI
 * @param data		Data in URL-encoded format
 * @param targetId	The response will be loaded here.
 * @param isAsync	Load the response asynchronously.
 * @param callback	A user-defined routine to handle the results.
 */
function doLoadAjaxJsonResultWithCallback( uri, data, targetId, isAsync, callback ) {
	var xhttp = new XMLHttpRequest() ;
	xhttp.onreadystatechange = function() {
		if ( xhttp.readyState == 4 && xhttp.status == 200 ) {
			callback( xhttp, targetId ) ;
		}
	} ;
	xhttp.open( "PUT", uri, isAsync ) ;
	xhttp.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
	xhttp.send( data ) ;
}

function isNumeric(n) {
    return ! isNaN( parseFloat( n ) ) && isFinite( n ) ;
}

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

function doEditApplicationStatus( id ) {
	document.getElementById( 'disp_' + id ).style.display = 'none' ;
	document.getElementById( 'edit_' + id ).style.display = 'table-row' ;
	document.getElementById( 'status_' + id ).style.display = 'none' ;
	return false ;
}

function doCancelApplicationStatusChange( id ) {
	document.getElementById( 'disp_' + id ).style.display = 'table-row' ;
	document.getElementById( 'edit_' + id ).style.display = 'none' ;
	document.getElementById( 'status_' + id ).style.display = 'none' ;
	// @todo Put the original values back.
	return false ;
}

function doSaveApplicationStatus( id ) {
	document.getElementById( 'disp_' + id ).style.display = 'table-row' ;
	document.getElementById( 'edit_' + id ).style.display = 'none' ;
	document.getElementById( 'status_' + id ).style.display = 'table-row' ;
	document.getElementById( 'result_' + id ).innerHTML = "Saving..." ;
	var formObj = document.forms[ "appstat_" + id ] ;
	var proceed = true ;
	var message = '' ;
	var statusValue = formObj[ 'statusValue' ].value ;
	var style       = formObj[ 'style' ].value ;
	var isActive    = formObj[ 'isActive' ].checked ;
	var sortKey     = formObj[ 'sortKey' ].value ;
	if ( ( null == formObj[ 'statusValue' ].value ) || ( '' == formObj[ 'statusValue' ].value ) ) {
		proceed = false ;
		message += "Value is required.\n" ;
	}
	if ( ( null == sortKey ) || ( '' == sortKey ) || ( ! isNumeric( sortKey ) ) ) {
		proceed = false ;
		message += "Sort Key is required.\n" ;
	}
	if ( ! proceed ) {
		alert( message ) ;
		return false ;
	}
	// Data is validated. Now make the AJAX call and save the row.
	// ajaxSaveApplicationStatusRow?id=$id&statusValue=
	var uri = 'ajaxSaveApplicationStatusRow.php?id='
		    + id
		    + '&statusValue='
		    + encodeURIComponent( statusValue )
		    + '&style='
		    + encodeURIComponent( style )
		    + '&isActive='
		    + ( ( isActive ) ? '1' : '0' )
		    + '&sortKey='
		    + encodeURIComponent( sortKey )
		    ;
	alert( 'When implemented, would call ' + uri ) ;
	return false ;
}

function doDeleteApplicationStatus( id ) {
	return false ;
}