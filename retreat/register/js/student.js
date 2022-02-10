// On page load.
jQuery(document).ready(function() {

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

    if (jQuery("#selectCampusId").val() == 'none') {
        jQuery("#campusNone").css({display: 'block'});
    }

    jQuery("#selectCampusId").on('change', function () {
        if (jQuery(this).val() == 'none') {
            jQuery("#campusNone").css({display: 'block'});
        } else {
            jQuery("#campusNone").css({display: 'none'});
        }
    });

});

// Validate the form.
function validateStudentForm() {

    // Create a variable to hold the required fields.
    var fields = [];

    // Add the University field.
    fields.push({
        'fieldId': 'selectCampusId',
        'validationFunctions': [
            {'function': 'select', 'errorMessage': 'Please select your University'},
        ],
        'errorArea': 'pCampusIdError'
    });

    // Add the University other field if University field selected as 'none'
    if ($('#selectCampusId').val() == 'none') {
        fields.push({
            'fieldId': 'txtCampusOther',
            'validationFunctions': [
                {'function': 'required', 'errorMessage': 'Please enter your University'},
            ],
            'errorArea': 'pCampusOtherError'
        });
    }

    // Add the Student Status field.
    fields.push({
        'fieldId': 'selectStudentStatus',
        'validationFunctions': [
            {'function': 'select', 'errorMessage': 'Please select your Student Status'},
        ],
        'errorArea': 'pStudentStatusError'
    });

    // Add the name field.
    fields.push({
        'fieldId': 'txtName',
        'validationFunctions': [
            {'function': 'required', 'errorMessage': 'Please enter your full name'},
            {'function': 'regex', 'regex': /^([a-z\,\.\'\-]+\s+)([a-z\,\.\'\-]+\s*)+$/i, 'errorMessage': 'Please enter your full name'}
        ],
        'errorArea': 'pNameError'
    });

    // Add the DOB field.
    fields.push({
        'fieldId': 'txtDob',
        'validationFunctions': [
            {'function': 'required', 'errorMessage': 'Please enter your date of birth'},
            {'function': 'regex', 'regex': /^\d{4}\-\d{2}\-\d{2}$/i, 'errorMessage': 'Please enter a valid Date'}
        ],
        'errorArea': 'pDobError'
    });

    // Add email field.
    fields.push({
        'fieldId': 'txtEmail',
        'validationFunctions': [
            {'function': 'required', 'errorMessage': 'Please enter your email address'},
            {'function': 'email'}
        ],
        'errorArea': 'pEmailError'
    });

    // Add Cell phone.
    fields.push({
        'fieldId': 'txtCellPhone',
        'validationFunctions': [
            {'function': 'required', 'errorMessage': 'Please enter your phone number'},
            {'function': 'phone'}
        ],
        'errorArea': 'pCellPhoneError'
    });

    // Add the Gender field.
    fields.push({
        'fieldId': 'selectGender',
        'validationFunctions': [
            {'function': 'select', 'errorMessage': 'Please select your gender'},
        ],
        'errorArea': 'pGenderError'
    });


    // Add the address line 1 field.
    fields.push({
        'fieldId': 'txtAddress',
        'validationFunctions': [
            {'function': 'required', 'errorMessage': 'Please enter your address'},
        ],
        'errorArea': 'pAddressError'
    });

    // Add the city field.
    fields.push({
        'fieldId': 'txtCity',
        'validationFunctions': [
            {'function': 'required', 'errorMessage': 'Please enter your city'},
        ],
        'errorArea': 'pCityError'
    });

    // Add the country field.
    fields.push({
        'fieldId': 'selectCountryId',
        'validationFunctions': [
            {'function': 'select', 'errorMessage': 'Please enter your country'},
        ],
        'errorArea': 'pCountryIdError'
    });

    // Add the state fields.
    if(document.getElementById('selectCountryId').options[document.getElementById('selectCountryId').selectedIndex].value == unitedStatesCountryId) {
        fields.push({
            'fieldId': 'selectState',
            'validationFunctions': [
                {'function': 'select', 'errorMessage': 'Please select your state'},
            ],
            'errorArea': 'pStateError'
        });
    } else {
        fields.push({
            'fieldId': 'txtState',
            'validationFunctions': [
                {'function': 'required', 'errorMessage': 'Please enter your state / province'},
            ],
            'errorArea': 'pStateError'
        });
    }

    // Add the zip field.
    fields.push({
        'fieldId': 'txtZip',
        'validationFunctions': [
            {'function': 'required', 'errorMessage': 'Please enter your zip / postal code'},
        ],
        'errorArea': 'pZipError'
    });

    // Add the emergency contact field.
    fields.push({
        'fieldId': 'txtEmergencyContact',
        'validationFunctions': [
            {'function': 'required', 'errorMessage': 'Please enter your emergency contact'},
        ],
        'errorArea': 'pEmergencyContactError'
    });

    // Add the emergency relation field.
    fields.push({
        'fieldId': 'txtEmergencyRelation',
        'validationFunctions': [
            {'function': 'required', 'errorMessage': 'Please enter the emergency contact relation'},
        ],
        'errorArea': 'pEmergencyRelationError'
    });

    // Add Emergency phone.
    fields.push({
        'fieldId': 'txtEmergencyPhone',
        'validationFunctions': [
            {'function': 'required', 'errorMessage': "Please enter your emergency contact's phone number"},
            {'function': 'phone'}
        ],
        'errorArea': 'pEmergencyPhoneError'
    });

    // Add Emergency phone.
    fields.push({
        'fieldId': 'fileProfilePhoto',
        'validationFunctions': [
            {'function': 'required', 'errorMessage': 'Please upload a profile photo'}
        ],
        'errorArea': 'pProfilePhotoError'
    });

    // Add Previous Experience.
    fields.push({
        'fieldId': 'txtPreviousExperience',
        'validationFunctions': [
            {'function': 'required', 'errorMessage': 'Please share your previous experience'}
        ],
        'errorArea': 'pPreviousExperienceError'
    });

    fields.push({
        'fieldId': 'txtHopeToGain',
        'validationFunctions': [
            {'function': 'required', 'errorMessage': 'This field is required'}
        ],
        'errorArea': 'pHopeToGainError'
    });
    fields.push({
        'fieldId': 'txtQuestions',
        'validationFunctions': [
            {'function': 'required', 'errorMessage': 'This field is required'}
        ],
        'errorArea': 'pQuestionsError'
    });
    fields.push({
        'fieldId': 'txtGrow',
        'validationFunctions': [
            {'function': 'required', 'errorMessage': 'This field is required'}
        ],
        'errorArea': 'pGrowError'
    });
    fields.push({
        'fieldId': 'txtMarriage',
        'validationFunctions': [
            {'function': 'required', 'errorMessage': 'This field is required'}
        ],
        'errorArea': 'pMarriageError'
    });
    fields.push({
        'fieldId': 'txtShabbat',
        'validationFunctions': [
            {'function': 'required', 'errorMessage': 'This field is required'}
        ],
        'errorArea': 'pShabbatError'
    });
    fields.push({
        'fieldId': 'txtTorahStudy',
        'validationFunctions': [
            {'function': 'required', 'errorMessage': 'This field is required'}
        ],
        'errorArea': 'pTorahStudyError'
    });
    fields.push({
        'fieldId': 'txtJewishCommunity',
        'validationFunctions': [
            {'function': 'required', 'errorMessage': 'This field is required'}
        ],
        'errorArea': 'pJewishCommunityError'
    });
    fields.push({
        'fieldId': 'txtJewishHolidays',
        'validationFunctions': [
            {'function': 'required', 'errorMessage': 'This field is required'}
        ],
        'errorArea': 'pJewishHolidaysError'
    });
    fields.push({
        'fieldId': 'txtCharity',
        'validationFunctions': [
            {'function': 'required', 'errorMessage': 'This field is required'}
        ],
        'errorArea': 'pCharityError'
    });
    fields.push({
        'fieldId': 'txtGod',
        'validationFunctions': [
            {'function': 'required', 'errorMessage': 'This field is required'}
        ],
        'errorArea': 'pGodError'
    });
    fields.push({
        'fieldId': 'txtJewishPractices',
        'validationFunctions': [
            {'function': 'required', 'errorMessage': 'This field is required'}
        ],
        'errorArea': 'pJewishPracticesError'
    });
    fields.push({
        'fieldId': 'txtIsrael',
        'validationFunctions': [
            {'function': 'required', 'errorMessage': 'This field is required'}
        ],
        'errorArea': 'pIsraelError'
    });
    fields.push({
        'fieldId': 'txtImpact',
        'validationFunctions': [
            {'function': 'required', 'errorMessage': 'This field is required'}
        ],
        'errorArea': 'pImpactError'
    });


    // Validate the fields.
    var valid = validateForm(fields);

    return valid;

}

// This function is called when the user changes the country.
function countryChanged() {

    // Get the country.
    var countryId = document.getElementById('selectCountryId').options[document.getElementById('selectCountryId').selectedIndex].value;

    // Check if it's the US.
    if(countryId == unitedStatesCountryId) {

        // Change the state textbox to a select
        document.getElementById('txtState').style.display = "none";
        document.getElementById('selectState').style.display = "block";

        // Remove the textbox error.
        if(jQuery('#txtState').hasClass('error')) {
            jQuery('#txtState').removeClass('error');
            document.getElementById('pStateError').innerHTML = "";
        }

    } else {

        // Change the state select to a textbox
        document.getElementById('selectState').style.display = "none";
        document.getElementById('txtState').style.display = "block";

        // Remove the Select error.
        if(jQuery('#selectState').hasClass('error')) {
            jQuery('#selectState').removeClass('error');
            document.getElementById('pStateError').innerHTML = "";
        }

    }

}

// This function validates the image that the user uploads.
function validateImage(input) {

    // Remove any previous errors.
    document.getElementById('imgProfilePhoto').src = '';
    document.getElementById('imgProfilePhoto').style.display = "none";

    // Make sure at least one file was loaded (and that the browser supports the file object).
    if(input.files && input.files[0]) {

        // Get the file
        var file = input.files[0];

        // Make sure it contains a valid image extension.
        if(!(validImageRegex).test(file.name)) {
            alert(file.name + " Unsupported Image extension.");
            input.value = null;
            return;
        }

        // Create a Reader object to read the file.
        var reader = new FileReader();

        // This function will be called once the file is read.
        reader.onload = function (e) {

            // Create an Image object to hold the image.
            var image  = new Image();

            image.addEventListener("load", function () {

                document.getElementById('imgProfilePhoto').src = image.src;
                document.getElementById('imgProfilePhoto').style.display = "block";

            });

            image.src = reader.result;

        }

        // Asign the file to the reader.
        reader.readAsDataURL(file);

    }
}

