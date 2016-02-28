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

/* Not implemented */
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

/* Not implemented */
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

/* Not implemented */
function validateJob() {
	return false ;
}

/* Not implemented */
function validateNote() {
	return false ;
}

/* Not implemented */
function validateSearch() {
	return false ;
}
