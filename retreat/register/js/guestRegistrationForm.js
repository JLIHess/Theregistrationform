// On page load.
jQuery(document).ready(function() {

	// Create accordian.
	createAccordian();

	// Create the datepicker.
	jQuery(".dob").datepicker({
		'dateFormat': "yy-mm-dd",
		'showOn': 'both',
		'buttonImageOnly': true,
		'buttonImage': baseUrl + "/images/datepicker.png",
		'changeMonth': true,
		'changeYear': true,
		'yearRange': '-120:+0'
	});

	// Make all the filled forms inactive.
	for(i in order.guests) {
		if(order.guests[i].user != null) {
			makeGuestFormInactive(i);
			makeGuestFormOpenble(i);
		}
	}

	// Expand the first form.
	expandFirstActiveGuest(false);

	if(isDone()) {
		expandGuest(order.rooms[0].guests[0], false);
	}

	var previousGuests = [];
	$('.guest-prefill').each(function() {

		let $this = $(this),
			optionSelected = $("option:selected", this),
			guestId = $this.data('id'),
			id = optionSelected.data('id');

		if ($this.val()) {

			previousGuests[guestId] = id;

			$('.guest-prefill').each(function() {

				if (parseInt($(this).data('id')) != parseInt(guestId)) {

					$(this).find('option[data-id="' + id + '"]').prop('disabled','disabled');
				}
			});
		}
	});


	$(document).on('change', '.guest-prefill', function() {

		let $this = $(this),
			optionSelected = $("option:selected", this);

		let info = optionSelected.data('info'),
			guestId = $this.data('id'),
			id = optionSelected.data('id');

		if (typeof(previousGuests[guestId]) !== 'undefined') {
			$('.guest-prefill option[data-id="' + previousGuests[guestId] + '"]').each(function() {
				$(this).removeAttr('disabled');
			});
		}
		previousGuests[guestId] = id;

		$('.guest-prefill').each(function() {
			if ($(this).data('id') != guestId) {
				$(this).find('option[data-id="' + id + '"]').prop('disabled','disabled');
			}
		});

		$.each(info, function(key, data) {

			if (key == 'id') {
				$('#guestId' + guestId).val(data);
			} else if (key == 'prefix') {
				$('#selectPrefix' + guestId).val(data);

			} else if (key == 'name') {
				$('#txtName' + guestId).val(data);

			} else if (key == 'date_of_birth' && data != '0000-00-00' && data != '') {
				$('#txtDob' + guestId).val(data);
			} else if (key == 'email') {
				$('#txtGuestEmail' + guestId).val(data);
			} else if (key == 'day_phone') {
				$('#txtDayPhone' + guestId).val(data);
			} else if (key == 'home_phone') {
				$('#txtHomePhone' + guestId).val(data);
			} else if (key == 'cell_phone') {
				$('#txtCellPhone' + guestId).val(data);
			} else if (key == 'tag_name') {
				$('#txtTagName' + guestId).val(data);
			} else if (key == 'gender') {
				$('#selectGender' + guestId).val(data);
			} else if (key == 'address') {
				$.each(data, function(a, addr) {
					if (a == 'line1') {
						$('#txtAddressLine1' + guestId).val(addr);
					} else if (a == 'line2') {
						$('#txtAddressLine2' + guestId).val(addr);
					} else if (a == 'city') {
						$('#txtCity' + guestId).val(addr);
					} else if (a == 'state') {
						$('#selectState' + guestId).val(addr);
					} else if (a == 'zip') {
						$('#txtZip' + guestId).val(addr);
					} else if (a == 'country_id') {
						$('#selectCountryId' + guestId).val(addr);
					}
				});
			} else if (key == 'emergency_contact') {
				$('#txtEmergencyContact' + guestId).val(data);
			} else if (key == 'emergency_relation') {
				$('#txtEmergencyRelation' + guestId).val(data);
			} else if (key == 'emergency_phone') {
				$('#txtEmergencyPhone' + guestId).val(data);
			} else if (key == 'referred_by') {
				$('#txtReferredBy' + guestId).val(data);
			} else if (key == 'shliach') {
				$('#txtShliach' + guestId).val(data);
			} else if (key == 'additional_notes') {
				$('#txtAdditionalNotes' + guestId).val(data);
			} else if (key == 'relation_name') {
				$('#selectRelationToPrimary' + guestId).val(data);
			}
		});
	});

});

// Create error callBack function.
function errorCallBack(data) {
	alert('There has been an error contacting the server.\nThis page will be refreshed.');
	document.location.reload(true);
}

// Get the order information.
function getOrder(orderId) {

	// Check if an order id was sent.
	if(typeof(orderId) == 'undefined') {

		// Try to get the current order.
		if(typeof(window.orderId) != 'undefined') {
			orderId = window.orderId;
		} else {
			// Log the error.
			console.log('Error: getOrder waas called without an order id');
			return;
		}

	}

	// create a deferred object
	var deferObject = jQuery.Deferred();

	// Create callBack function.
	var callBack = function(response) {

		// Get the first character of the returned data.
		var responseCode = response.substring(0, 1);
		var responseData = response.substring(1);

		if(responseCode === '1') {

			// The request was successfull, Get the order information.
			order = jQuery.parseJSON(responseData);

		} else if(responseCode === '0') {

			// User does not have authorization. Redirect them to the homepage.
			window.location = baseUrl;

		} else {

			// There was an error. Alert the user.
			alert(responseData);

		}

		// Allow the next function to be called.
		deferObject.resolve();

	}

	// Contact the server.
	jQuery.get(baseUrl + 'ajax.php?action=getOrder&orderId=' + orderId, callBack).fail(errorCallBack);

	return deferObject;
}

// This function activates a guest form
function makeGuestFormActive(guestId) {

	jQuery('#formGuest' + guestId + ' input, #formGuest' + guestId + ' select, #formGuest' + guestId + ' textarea').prop('disabled', false);
	jQuery('#formGuest' + guestId + ' .saveButton').css("display", "block");
	jQuery('#formGuest' + guestId + ' .editButton').css("display", "none");
	jQuery('#formGuest' + guestId + ' .hiddenWhenInactive').css("display", "block");
	jQuery('#formGuest' + guestId + ' .dob').datepicker('enable');

}

// This function makes a form inactive.
function makeGuestFormInactive(guestId) {

	jQuery('#formGuest' + guestId + ' input, #formGuest' + guestId + ' select, #formGuest' + guestId + ' textarea').prop('disabled', true);
	jQuery('#formGuest' + guestId + ' .saveButton').css("display", "none");
	jQuery('#formGuest' + guestId + ' .editButton').css("display", "block");
	jQuery('#formGuest' + guestId + ' .hiddenWhenInactive').css("display", "none");
	jQuery('#formGuest' + guestId + ' .dob').datepicker('disable');

}

// This function makes a form openable.
function makeGuestFormOpenble(guestId) {

	// Make the room openable.
	if(!jQuery('#divRoom' + order.guests[guestId]['orders_room_id']).hasClass('openable')) {jQuery('#divRoom' + order.guests[guestId]['orders_room_id']).addClass('openable')}

	// Make the guest openable.
	if(!jQuery('#divGuest' + guestId).hasClass('openable')) {jQuery('#divGuest' + guestId).addClass('openable')}

}

function createAccordian() {

	// Hide all rooms and guests.
	var allPanels = jQuery('.accordian .contentDiv').hide();

	// Create room accordian.
	jQuery('.headerDiv').click(function() {

		// Get header and content.
		var header = jQuery(this);
		var parent = header.parent();
		var content = header.next();

		// Make sure it's openable.
		if(!parent.hasClass('openable')) {return;}

		// Check if it's currently open.
		if(!parent.hasClass('open')) {

			// Open only this.
			parent.parent().children().removeClass('open');
			parent.addClass('open');

			// Close other sections.
			parent.parent().find('> > .contentDiv').slideUp();

			//Open this section.
			content.slideDown();

		}

		return false;

	});

	// Return a resolved deferred object so that the next function can be called.
	return jQuery.Deferred().resolve();

}

// This function saves a guests information.
function saveGuestInformation(guestId) {

	// Validate the form.
	if(!validateGuestForm(guestId)) return;

	// Get the form fields.
	var fields = getGuestFormFields(guestId);


	// Create AJAX callback function.
	var callBack = function(data) {

		try {
			// Parse the response.
			response = jQuery.parseJSON(data);
		} catch(err) {
			alert('Could not parse response: ' + data);
			return;
		}

		// Check if there was an error.
		if(!response['success']) {

			// Check if they don't have authorization for this page.
			if(response['error'] == 0) {

				// User does not have authorization. Redirect them to the homepage.
				window.location = baseUrl;

			} else {

				alert(response['error']);
				return;

			}
		}

		// Make the form inactive.
		makeGuestFormInactive(guestId);

		// Update the order variable. Then expand first active form.
		getOrder().
		then(function(){expandFirstActiveGuest(true);});

	}

	jQuery.post(baseUrl + 'ajax.php?action=updateGuestInformation', fields, callBack).fail(errorCallBack);


}

// Validate the form.
function validateGuestForm(guestId) {

	// Create a variable to hold the required fields.
	var fields = [];

	// Add the prefix field.
	fields.push({
		'fieldId': 'selectPrefix' + guestId,
		'validationFunctions': [
			{'function': 'required', 'errorMessage': 'Please select your prefix'},
		],
		'errorArea': 'pSelectPrefixError' + guestId
	});

	// Add the name field.
	fields.push({
		'fieldId': 'txtName' + guestId,
		'validationFunctions': [
			{'function': 'required', 'errorMessage': 'Please enter your full name'},
			{'function': 'regex', 'regex': /^([a-z\,\.\'\-]+\s+)([a-z\,\.\'\-]+\s*)+$/i, 'errorMessage': 'Please enter your full name'}
		],
		'errorArea': 'pNameError' + guestId
	});

	// Add the relationship field.
	if(order.guests[guestId].primary == false) {
		fields.push({
			'fieldId': 'selectRelationToPrimary' + guestId,
			'validationFunctions': [
				{'function': 'select', 'errorMessage': 'Please enter your relationship'},
			],
			'errorArea': 'pRelationToPrimaryError' + guestId
		});
		var relation = document.getElementById('selectRelationToPrimary' + guestId).options[document.getElementById('selectRelationToPrimary' + guestId).selectedIndex]
		if(relation && relation.value == 'Other') {
			fields.push({
				'fieldId': 'txtRelationToPrimary' + guestId,
				'validationFunctions': [
					{'function': 'required', 'errorMessage': 'Please enter your relationship'},
				],
				'errorArea': 'pRelationToPrimaryError' + guestId
			});
			window.a = (fields);
		}
	}


	var userType = order.guests[guestId].user_type_id;

	if (userType == 2) {
		userType = 'adult';
	} else if (userType == 3) {
		userType = 'teen';
	} else if (userType == 4) {
		userType = 'child';
	} else if (userType == 23) {
		userType = 'toddler';
	} else if (userType == 5) {
		userType = 'infant';
	}

	if (userType != 'adult') {
		// Add the DOB field.
		fields.push({
			'fieldId': 'txtDob' + guestId,
			'validationFunctions': [
				{'function': 'dateOfBirth', 'age': userType}
			],
			'errorArea': 'pDobError' + guestId
		});
	}

	//if(order.guests[guestId].user_type_id > 2) fields[fields.length - 1].validationFunctions.push({'function': 'required', 'errorMessage': 'Please enter your date of birth'})

	if(order.guests[guestId].user_type_id <= 2) {

		// Add email field.
		var element = document.getElementById('txtEmail' + guestId);
		if (typeof(element) != 'undefined' && element != null) {
			fields.push({
				'fieldId': 'txtEmail' + guestId,
				'validationFunctions': [
					{'function': 'required', 'errorMessage': 'Please enter your email address'},
					{
						'function': 'uniqueEmail',
						'errorMessage': 'This email address already exists in this registration'
					},
					{'function': 'email'}
				],
				'errorArea': 'pEmailError' + guestId
			});
		}

		// Add guest email field
		element = document.getElementById('txtGuestEmail' + guestId);
		if (typeof(element) != 'undefined' && element != null) {
			fields.push({
				'fieldId': 'txtGuestEmail' + guestId,
				'validationFunctions': [
					{
						'function': 'uniqueEmail',
						'errorMessage': 'This email address already exists in this registration'
					},
					{'function': 'email'}
				],
				'errorArea': 'pGuestEmailError' + guestId
			});
		}

		// Add Day phone.
		fields.push({
			'fieldId': 'txtDayPhone' + guestId,
			'validationFunctions': [
				{'function': 'required', 'errorMessage': 'Please enter your phone number'},
				{'function': 'phone'}
			],
			'errorArea': 'pDayPhoneError' + guestId
		});

	}

	// Add the tag name field.
	fields.push({
		'fieldId': 'txtTagName' + guestId,
		'validationFunctions': [
			{'function': 'required', 'errorMessage': 'Please enter your name tag'},
		],
		'errorArea': 'pTagNameError' + guestId
	});

	// Validate Address fields.
	if((order.guests[guestId].user_type_id <= 4) && (!document.getElementById('chkUseSameAddress' + guestId) || !document.getElementById('chkUseSameAddress' + guestId).checked)) {

		// Add the address line 1 field.
		fields.push({
			'fieldId': 'txtAddressLine1' + guestId,
			'validationFunctions': [
				{'function': 'required', 'errorMessage': 'Please enter your address'},
			],
			'errorArea': 'pAddressLine1Error' + guestId
		});

		// Add the city field.
		fields.push({
			'fieldId': 'txtCity' + guestId,
			'validationFunctions': [
				{'function': 'required', 'errorMessage': 'Please enter your city'},
			],
			'errorArea': 'pCityError' + guestId
		});

		// Add the country field.
		fields.push({
			'fieldId': 'selectCountryId' + guestId,
			'validationFunctions': [
				{'function': 'select', 'errorMessage': 'Please enter your country'},
			],
			'errorArea': 'pCountryIdError' + guestId
		});

		// Add the state fields.
		if(document.getElementById('selectCountryId' + guestId).options[document.getElementById('selectCountryId' + guestId).selectedIndex].value == unitedStatesCountryId) {
			fields.push({
				'fieldId': 'selectState' + guestId,
				'validationFunctions': [
					{'function': 'select', 'errorMessage': 'Please select your state'},
				],
				'errorArea': 'pStateError' + guestId
			});
		} else {
			fields.push({
				'fieldId': 'txtState' + guestId,
				'validationFunctions': [
					{'function': 'required', 'errorMessage': 'Please enter your state / province'},
				],
				'errorArea': 'pStateError' + guestId
			});
		}

		// Add the zip field.
		fields.push({
			'fieldId': 'txtZip' + guestId,
			'validationFunctions': [
				{'function': 'required', 'errorMessage': 'Please enter your zip / postal code'},
			],
			'errorArea': 'pZipError' + guestId
		});


	}


	// Validate emergency contact fields.
	if((order.guests[guestId].user_type_id <= 3) && (!document.getElementById('chkUseSameEmergencyInfo' + guestId) || !document.getElementById('chkUseSameEmergencyInfo' + guestId).checked)) {

		// Add the emergency contact field.
		fields.push({
			'fieldId': 'txtEmergencyContact' + guestId,
			'validationFunctions': [
				{'function': 'required', 'errorMessage': 'Please enter your emergency contact'},
			],
			'errorArea': 'pEmergencyContactError' + guestId
		});

		// Add the emergency relation field.
		fields.push({
			'fieldId': 'txtEmergencyRelation' + guestId,
			'validationFunctions': [
				{'function': 'required', 'errorMessage': 'Please enter the emergency contact relation'},
			],
			'errorArea': 'pEmergencyRelationError' + guestId
		});

		// Add Emergency phone.
		fields.push({
			'fieldId': 'txtEmergencyPhone' + guestId,
			'validationFunctions': [
				{'function': 'required', 'errorMessage': "Please enter your emergency contact's phone number"},
				{'function': 'phone'}
			],
			'errorArea': 'pEmergencyPhoneError' + guestId
		});

	}

	// Validate the fields.
	var valid = validateForm(fields);

	return valid;

}

// Get the fields from a guest form.
function getGuestFormFields(guestId) {

	// Create variable to store the fields.
	var fields = {'id': guestId};

	var currentGuest = document.getElementById('guestId' + guestId);
	if (currentGuest) {
		fields['user_id'] = currentGuest.value;
	}

	// Get the fields.
	fields['name'] = document.getElementById('txtName' + guestId).value;
	fields['tagName'] = document.getElementById('txtTagName' + guestId).value;
	fields['gender'] = document.getElementById('selectGender' + guestId).options[document.getElementById('selectGender' + guestId).selectedIndex].value;
	fields['cmeCredits'] = (document.getElementById('chkCmeCredits' + guestId) && document.getElementById('chkCmeCredits' + guestId).checked) ? '1' : '0';
	if(order.guests[guestId].primary == false) {
		fields['relationToPrimary'] = document.getElementById('selectRelationToPrimary' + guestId).options[document.getElementById('selectRelationToPrimary' + guestId).selectedIndex].value;
		if(fields['relationToPrimary'] == 'Other') fields['relationToPrimary'] = document.getElementById('txtRelationToPrimary' + guestId).value;
	}
	if(order.guests[guestId].user_type_id <= 2) {
		fields['prefix'] = document.getElementById('selectPrefix' + guestId).options[document.getElementById('selectPrefix' + guestId).selectedIndex].value;

		var element = document.getElementById('txtGuestEmail' + guestId);
		if (typeof(element) != 'undefined' && element != null) {
			fields['guest_email'] = document.getElementById('txtGuestEmail' + guestId).value;
		} else {
			fields['email'] = document.getElementById('txtEmail' + guestId).value;
		}

		fields['dayPhone'] = document.getElementById('txtDayPhone' + guestId).value;
		fields['homePhone'] = document.getElementById('txtHomePhone' + guestId).value;
		fields['cellPhone'] = document.getElementById('txtCellPhone' + guestId).value;
		fields['diet'] = {};
		fields['diet']['Vegetarian'] = document.getElementById('diet-' + guestId+'-Vegetarian').checked ? "1" : "0" ;
		fields['diet']['Vegan'] = document.getElementById('diet-' + guestId+'-Vegan').checked ? "1" : "0" ;
		fields['diet']['Lactose Intolerant'] = document.getElementById('diet-' + guestId+'-Lactose Intolerant').checked ? "1" : "0" ;
		fields['diet']['Gluten Free'] = document.getElementById('diet-' + guestId+'-Gluten Free').checked ? "1" : "0" ;
		/* fields['diet']['Alergies'] = document.getElementById('diet-' + guestId+'-Alergies').checked ? "1" : "0" ;
		fields['diet']['Other'] = document.getElementById('diet-' + guestId+'-Other').checked ? "1" : "0" ;
		fields['diet']['notes'] = document.getElementById('txtNotes' + guestId).value; */


		fields['notes'] = JSON.stringify(fields['diet']);
		fields['additional_notes'] = document.getElementById('txtAdditionalNotes' + guestId).value;
		fields['referredBy'] = document.getElementById('txtReferredBy' + guestId).value;
		fields['shliach'] = document.getElementById('txtShliach' + guestId).value;
	}
	if(order.guests[guestId].user_type_id <= 4) {
		fields['useSameAddress'] = (document.getElementById('chkUseSameAddress' + guestId) && document.getElementById('chkUseSameAddress' + guestId).checked) ? '1' : '0';
		fields['addressLine1'] = document.getElementById('txtAddressLine1' + guestId).value;
		fields['addressLine2'] = document.getElementById('txtAddressLine2' + guestId).value;
		fields['city'] = document.getElementById('txtCity' + guestId).value;
		fields['zip'] = document.getElementById('txtZip' + guestId).value;
		fields['countryId'] = document.getElementById('selectCountryId' + guestId).options[document.getElementById('selectCountryId' + guestId).selectedIndex].value;
		if(fields['countryId'] == unitedStatesCountryId) {
			fields['state'] = document.getElementById('selectState' + guestId).options[document.getElementById('selectState' + guestId).selectedIndex].value;
		} else {
			fields['state'] = document.getElementById('txtState' + guestId).value;
		}
	}
	fields['dob'] = document.getElementById('txtDob' + guestId).value;
	if(order.guests[guestId].primary == true) {
		fields['jliStudent'] = document.getElementById('txtJliStudent' + guestId).value;
	}
	if(order.guests[guestId].user_type_id <= 3) {
		fields['useSameEmergencyInfo'] = (document.getElementById('chkUseSameEmergencyInfo' + guestId) && document.getElementById('chkUseSameEmergencyInfo' + guestId).checked) ? '1' : '0';
		fields['emergencyContact'] = document.getElementById('txtEmergencyContact' + guestId).value;
		fields['emergencyRelation'] = document.getElementById('txtEmergencyRelation' + guestId).value;
		fields['emergencyPhone'] = document.getElementById('txtEmergencyPhone' + guestId).value;
	}

	return fields;

}

// This expands a specific guest.
function expandGuest(guestId, scroll) {

	// Make it openable.
	makeGuestFormOpenble(guestId);

	//expand the room.
	jQuery('#divRoom' + order.guests[guestId]['orders_room_id'] + ' > .headerDiv').click();

	// Expand the guest.
	jQuery('#divGuest' + guestId + ' > .headerDiv').click();

	// Scroll to the guest.
	if(scroll) {
		jQuery('#divGuest' + guestId + ' > .contentDiv, #divRoom' + order.guests[guestId]['orders_room_id'] + ' > .contentDiv').promise().done(function(){
			jQuery('html, body').animate({
				scrollTop: jQuery('#divGuest' + guestId + ' > .headerDiv').offset().top
			}, 500);
		});
	}
}

// Expand the first active guest.
function expandFirstActiveGuest(scroll) {

	// Get the first active guest
	var firstActiveGuest = null;
	for(i in order.guests) {
		if(order.guests[i].user == null) {
			firstActiveGuest = order.guests[i].id;
			break;
		}
	}

	// Expand first active guest's form.
	if(firstActiveGuest != null) {
		expandGuest(firstActiveGuest, scroll);
	} else {
		// Expand the first guest if no other guests are active. I removed this because it causes the first guest to be expanded after the user finishes entering information for the last guest.
//		expandGuest(order.rooms[0].guests[0], scroll);
	}

	enableNextButtonWhenDone();

}

// Check if we should enable the 'Next' button.
function enableNextButtonWhenDone() {

	var done = isDone();

	// Enable 'Next' button.
	if(done) {
		jQuery('.nextButton').prop('disabled', false);
		jQuery('.nextButton').removeClass('disabled');
	}

}

// Check if information has been entered on all the users.
function isDone() {

	// Check if there are any users whose information was not filled in.
	var done = true;
	for(i in order.guests) {
		if(order.guests[i].user == null) {
			done = false;
		}
	}

	return done;

}

// This function is called when the user changes the country.
function countryChanged(guestId) {

	// Get the country.
	var countryId = document.getElementById('selectCountryId' + guestId).options[document.getElementById('selectCountryId' + guestId).selectedIndex].value;

	// Check if it's the US.
	if(countryId == unitedStatesCountryId) {

		// Change the state textbox to a select
		document.getElementById('txtState' + guestId).style.display = "none";
		document.getElementById('selectState' + guestId).style.display = "block";

		// Remove the textbox error.
		if(jQuery('#txtState' + guestId).hasClass('error')) {
			jQuery('#txtState' + guestId).removeClass('error');
			document.getElementById('pStateError' + guestId).innerHTML = "";
		}

	} else {

		// Change the state select to a textbox
		document.getElementById('selectState' + guestId).style.display = "none";
		document.getElementById('txtState' + guestId).style.display = "block";

		// Remove the Select error.
		if(jQuery('#selectState' + guestId).hasClass('error')) {
			jQuery('#selectState' + guestId).removeClass('error');
			document.getElementById('pStateError' + guestId).innerHTML = "";
		}

	}

}
