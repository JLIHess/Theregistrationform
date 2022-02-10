// This function is called to validate a form.
function validateForm(fields, focus) {

	if(typeof(focus) == 'undefined') focus = true;

	// Clear all the error fields.
	for(i in fields) {
		field = fields[i];
		if((typeof(field['errorArea']) != 'undefined') && (document.getElementById(field['errorArea']))) {document.getElementById(field['errorArea']).innerHTML = '';}
		jQuery('#' + field['fieldId']).removeClass('error');
	}

	// Are all fields valid.
	var allFieldsValid = true;
	var errorField = null;

	// Loop through the fields
	for(var i = fields.length - 1; i >= 0; i--) {

		// Get the field
		var field = fields[i];

		if (jQuery("#" + field['fieldId']).length == 0) {
			continue;
		}

		// Loop through the validation functions.
		for(j in field['validationFunctions']) {

			// Should we skip disabled fields.
			if((typeof(field['validateDisabled']) == 'undefined') || (!field['validateDisabled'])) {
				if(document.getElementById(field['fieldId']).disabled || (!jQuery("#" + field['fieldId']).is(":visible"))) {continue;}
			}

			var validationFunction = field['validationFunctions'][j];

			// call the function.
			var functionResponse = window['validate' + validationFunction['function'].charAt(0).toUpperCase() + validationFunction['function'].slice(1)](validationFunction, field)

			// Check if it was not valid.
			if(!functionResponse[0]) {

				// Get the error message.
				var errorMessage = (typeof(validationFunction['errorMessage']) != 'undefined') ? validationFunction['errorMessage'] : functionResponse[1];

				// Display the error message.
				if((typeof(field['errorArea']) != 'undefined') && (document.getElementById(field['errorArea']))) {
					document.getElementById(field['errorArea']).innerHTML = errorMessage;
				} else {
					alert(errorMessage);
				}

				// Add error class.
				jQuery('#' + field['fieldId']).addClass('error');

				// Rerun the validator if the value changes.
				jQuery('#' + field['fieldId']).one("change", (function(thisField){return function(){validateForm([thisField], false)};})(field));

				// Rerun the validator if the value of a field which this function depends on changes.
				if(validationFunction['function'].toLowerCase() == 'match') {
					jQuery('#' + validationFunction['matchingFieldId']).one("change", (function(thisFieldId){return function(){jQuery('#' + thisFieldId).trigger( "change" )};})(field['fieldId']));
				}

				errorField = document.getElementById(field['fieldId']);
				allFieldsValid = false;
			}


		}

	}

	// Focus on the field.
	if(focus && (!allFieldsValid)) {errorField.focus();}

	return allFieldsValid;
}

// Calculate Age
function _calculateAge(birthday) { // birthday is a date
    var ageDifMs = Date.now() - birthday.getTime();
    var ageDate = new Date(ageDifMs); // miliseconds from epoch
    return Math.abs(ageDate.getUTCFullYear() - 1970);
}

// Validate date of birth fields.
function validateDateOfBirth(functionInfo, fieldInfo) {

	// get the value.
	var fieldValue = document.getElementById(fieldInfo['fieldId']).value;
	var age = 'adult';

	if (fieldValue.match(/^\d{4}\-\d{2}\-\d{2}$/i)) {

		fieldValue = fieldValue.split('-');
		var dateOfBirth = new Date(fieldValue[0], fieldValue[1] - 1, fieldValue[2]);
		ageInYears = _calculateAge(dateOfBirth);
		if (functionInfo.age != 'undefined') {
			age = functionInfo.age;
		}

		if (age == 'teen') {
			if (ageInYears < 14 || ageInYears > 17) {
				return [false, 'Please enter a valid Date of Birth. Should be 14-17 years.'];
			}
		} else if (age == 'child') {
				if (ageInYears < 4 || ageInYears > 13) {
				return [false, 'Please enter a valid Date of Birth. Should be 4-13 years.'];
			}
		} else if (age == 'toddler') {
			if (ageInYears < 2 || ageInYears > 3) {
				return [false, 'Please enter a valid Date of Birth. Should be 2-3 years.'];
			}
		} else if (age == 'infant') {
			if (ageInYears < 0 || ageInYears > 1) {
				return [false, 'Please enter a valid Date of Birth. Should be 0-1 year.'];
			}
		}
	} else {
		return [false, 'Please enter a valid Date'];
	}
	return [true];
}

// Validate required fields.
function validateRequired(functionInfo, fieldInfo) {

	// get the value.
	var fieldValue = document.getElementById(fieldInfo['fieldId']).value;

	// Check if it's empty.
	if(fieldValue.match(/^\s*$/)) return [false, 'This field cannot be left empty'];

	// It's valid.
	else return [true];

}

// Validate Email fields.
function validateEmail(functionInfo, fieldInfo) {

	// get the value.
	var fieldValue = document.getElementById(fieldInfo['fieldId']).value;

	// Check if it's empty.
	if(fieldValue.match(/^\s*$/)) return [true];

	// Make sure it's a valid email address.
	if(fieldValue.match(/^[a-z0-9!#$%&'*+/=?^_`{|}~-]+(?:\.[a-z0-9!#$%&'*+/=?^_`{|}~-]+)*@(?:[a-z0-9](?:[a-z0-9-]*[a-z0-9])?\.)+[a-z0-9](?:[a-z0-9-]*[a-z0-9])?$/i)) return [true];

	// It's not valid.
	else return [false, 'Please enter a valid email address'];

}

function validateUniqueEmail(functionInfo, fieldInfo) {

	var field = jQuery("#" + fieldInfo['fieldId']),
		fieldClass = field.attr("class"),
		fieldValue = field.val();

	fieldValue = fieldValue.toLowerCase();
	fieldValue = fieldValue.trim();

	var count = 0;
	jQuery("." + fieldClass).each(function() {

		var elementValue = jQuery(this).val();
		elementValue = elementValue.toLowerCase();
		elementValue = elementValue.trim();
		elementId = jQuery(this).attr("id");

		if (elementValue != "" && elementId != fieldInfo['fieldId']) {
			if (fieldValue == elementValue) {
				count = count + 1;
			}
		}

	});
	if (count > 0) {
		return [false, 'This email address already exists in this registration'];
	} else {
		return [true];
	}
}

// Validate phone number.
function validatePhone(functionInfo, fieldInfo) {

	// get the value.
	var fieldValue = document.getElementById(fieldInfo['fieldId']).value;

	// Check if it's empty.
	if(fieldValue.match(/^\s*$/)) return [true];

	// Remove non number.
	fieldValue = fieldValue.replace(/[\(\)\-\s\.]/g, "");
	if(fieldValue.match(/^\d{7,}$/)) return [true];

	// It's not valid.
	else return [false, 'Please enter a valid Phone number'];

}

// Validate email fields.
function validateNumeric(functionInfo, fieldInfo) {

	// get the value.
	var fieldValue = document.getElementById(fieldInfo['fieldId']).value;

	// Check if it's empty.
	if(fieldValue.match(/^\s*$/)) return [true];

	// Check if it's a number.
	if((fieldValue - parseFloat(fieldValue) + 1) >= 0) return [true];

	// It's not valid.
	else return [false, 'Please enter a number'];

}

// Validate matching fields.
function validateMatch(functionInfo, fieldInfo) {

	// get the value.
	var fieldValue = document.getElementById(fieldInfo['fieldId']).value;

	// get the matching field value.
	var matchingFieldValue = document.getElementById(functionInfo['matchingFieldId']).value;

	// Check if it's empty.
	if(fieldValue === matchingFieldValue) return [true];

	// It's not valid.
	else return [false, 'The two fields do not match'];

}

// Validate select fields.
function validateSelect(functionInfo, fieldInfo) {

	// get the value.
	var fieldValue = document.getElementById(fieldInfo['fieldId']).options[document.getElementById(fieldInfo['fieldId']).selectedIndex].value;

	// Check if it's empty.
	if((fieldValue == '') || (fieldValue == 0)) {return [false, 'You must make a selection'];}

	// It's valid.
	else return [true];

}

// Validate fields with Regular expressions.
function validateRegex(functionInfo, fieldInfo) {

	// get the value.
	var fieldValue = document.getElementById(fieldInfo['fieldId']).value.trim();

	// Check if it's empty.
	if(fieldValue.match(/^\s*$/)) {return [true];}

	// Match the Regex.
	var regex = (functionInfo['regex'] instanceof RegExp) ? functionInfo['regex'] : new RegExp(functionInfo['regex']);
	if(fieldValue.match(regex)) {return [true];}

	// It's not valid.
	else {return [false, 'Invalid entry'];}

}

// Validate Untrimmed fields with Regular expressions.
function validateRegexUntrimmed(functionInfo, fieldInfo) {

	// get the value.
	var fieldValue = document.getElementById(fieldInfo['fieldId']).value;

	// Check if it's empty.
	if(fieldValue.match(/^\s*$/)) {return [true];}

	// Match the Regex.
	var regex = (functionInfo['regex'] instanceof RegExp) ? functionInfo['regex'] : new RegExp(functionInfo['regex']);
	if(fieldValue.match(regex)) {return [true];}

	// It's not valid.
	else {return [false, 'Invalid entry'];}

}

// Validate credit card numbers.
function validateCc(functionInfo, fieldInfo) {

	// Replace non-numeric characters.
	document.getElementById(fieldInfo['fieldId']).value = document.getElementById(fieldInfo['fieldId']).value.replace(/[\s\-]+/g, '');

	// get the value.
	var fieldValue = document.getElementById(fieldInfo['fieldId']).value;

	// Check if it's empty.
	if(fieldValue.match(/^\s*$/)) {return [true];}

	// Check if it contains any non numeric characters.
	if(fieldValue.match(/[^0-9]/)) return [false, 'Please enter a valid credit card number'];

	// Make sure it matches the Luhn algorithm.
	if(!luhn(fieldValue)) return [false, 'Please enter a valid credit card number'];

	// Get the card types.
	var cardTypes = (typeof(functionInfo['cardTypes']) != 'undefined') ? functionInfo['cardTypes'] : ['visa', 'mc', 'amex', 'discover'];

	// Make sure it matches the lunn

	// Match the Regex.
	if((cardTypes.indexOf("visa") >= 0) && fieldValue.match(/^4[0-9]{12}(?:[0-9]{3})?$/)) {return [true];}
	if((cardTypes.indexOf("mc") >= 0) && fieldValue.match(/^5[1-5][0-9]{14}$/)) {return [true];}
	if((cardTypes.indexOf("amex") >= 0) && fieldValue.match(/^3[47][0-9]{13}$/)) {return [true];}
	if((cardTypes.indexOf("discover") >= 0) && fieldValue.match(/^6(?:011|5[0-9]{2})[0-9]{12}$/)) {return [true];}
	if((cardTypes.indexOf("dc") >= 0) && fieldValue.match(/^3(?:0[0-5]|[68][0-9])[0-9]{11}$/)) {return [true];}
	if((cardTypes.indexOf("jcb") >= 0) && fieldValue.match(/^(?:2131|1800|35[0-9]{3})[0-9]{11}$/)) {return [true];}

	// It's not valid.
	else {return [false, 'Please enter a valid credit card number'];}

}

// Luhn's algorithm used to validate credit cards.
function luhn(cardnumber) {

	// Build an array with the digits in the card number
	var digits = cardnumber.split('');
	for (var i = 0; i < digits.length; i++) {
		digits[i] = parseInt(digits[i], 10);
	}

	// Run the Luhn algorithm on the array
	var sum = 0;
	var alt = false;
	for (i = digits.length - 1; i >= 0; i--) {
		if (alt) {
			digits[i] *= 2;
			if (digits[i] > 9) {
				digits[i] -= 9;
			}
		}
		sum += digits[i];
		alt = !alt;
	}

	// Check the result
	return(sum % 10 == 0);
}