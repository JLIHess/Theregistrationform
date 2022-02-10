// Setup tooltips.
jQuery(function() {
	jQuery('[title]').tooltip({'position' : { my: "left+15 top", at: "right top", collision: "flipfit" }});
});

function clickSponsorship(sponsorshipId, checkbox) {
	
	// Was this checkbox checked?
	if(checkbox.checked) {
		
		// Loop through all the sponsorships.
		for(i in sponsorships) {
		
			// Make sure it's not the current sponsorship.
			if(sponsorships[i]['id'] != sponsorshipId) {
				
				// Uncheck the other sponsorship's checkbox.
				document.getElementById('checkSponsorship' + sponsorships[i]['id']).checked = false;
				
				// Remove the other sponsorship's 'Amount' textbox, and remove any errors it may have had.
				if(document.getElementById('txtSponsorshipAmount' + sponsorships[i]['id']) != null) {
					document.getElementById('txtSponsorshipAmount' + sponsorships[i]['id']).disabled = true;
					jQuery('#txtSponsorshipAmount' + sponsorships[i]['id']).removeClass('error');
					document.getElementById('divSponsorshipAmountError' + sponsorships[i]['id']).innerHTML = '';
				}
				
			}
		}
		
		// Show the 'Amount' text box and focus on it.
		if(document.getElementById('txtSponsorshipAmount' + sponsorshipId) != null) {
			document.getElementById('txtSponsorshipAmount' + sponsorshipId).disabled = false;
			document.getElementById('txtSponsorshipAmount' + sponsorshipId).focus();
		}
			
		// Show the notes field.
		document.getElementById('divSponsorshipNotes').style.display = 'block';
		
	} else {
		
		// Disable the 'Amount' textbox, and remove any errors it may have had.
		if(document.getElementById('txtSponsorshipAmount' + sponsorshipId) != null) {
			document.getElementById('txtSponsorshipAmount' + sponsorshipId).disabled = true;
			jQuery('#txtSponsorshipAmount' + sponsorshipId).removeClass('error');
			document.getElementById('divSponsorshipAmountError' + sponsorshipId).innerHTML = '';
		}
		
		// Hide the notes field.
		document.getElementById('divSponsorshipNotes').style.display = 'none';
		
	}
	
}

// This function is called when the user changes the country.
function countryChanged() {
		
	// Get the country.
	var countryId = document.getElementById('selectCountryId').options[document.getElementById('selectCountryId').selectedIndex].value;
	
	// Check if it's the US.
	if(countryId == unitedStatesCountryId) {
	
		// Change the state textbox to a select
		document.getElementById('txtState').style.display = "none";
		document.getElementById('selectState').style.display = "inline";
		
		// Remove the textbox error.
		if(jQuery('#txtState').hasClass('error')) {
			jQuery('#txtState').removeClass('error');
			document.getElementById('pStateError').innerHTML = "";
		}
		
	} else {
		
		// Change the state select to a textbox
		document.getElementById('selectState').style.display = "none";
		document.getElementById('txtState').style.display = "inline";
		
		// Remove the Select error.
		if(jQuery('#selectState').hasClass('error')) {
			jQuery('#selectState').removeClass('error');
			document.getElementById('pStateError').innerHTML = "";
		}
		
	}
}
