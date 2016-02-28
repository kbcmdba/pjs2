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
