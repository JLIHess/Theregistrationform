<?php

	// Set template variables.
	$templateVariables['page'] = 'Credit Card';
	$templateVariables['title'] = 'Credit Card';
	$templateVariables['jqueryUi'] = true;
	
	// Display header.
	include('header.php');
	
?>
<link rel="stylesheet" type="text/css" href='<?php echo BASE_URL; ?>/css/checkout.css'></script>
<script src='<?php echo BASE_URL; ?>/js/formValidator.js'></script>
<script>
	var unitedStatesCountryId = <?php echo UNITED_STATES_COUNTRY_ID; ?>;
	
    // Setup tooltips.
    jQuery(function() {
        jQuery(document).tooltip({'position' : { my: "left+15 top", at: "right top", collision: "flipfit" }});
    });

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

	// Field validation.
	var formFields = [
		{'fieldId': 'txtFirstNameOnCard', 
			'validationFunctions': [
					{'function': 'required', 'errorMessage': 'Please enter your first name as it appears on your card'}
				],
			'errorArea': 'divFirstNameOnCardError'
		},
		{'fieldId': 'txtLastNameOnCard', 
			'validationFunctions': [
					{'function': 'required', 'errorMessage': 'Please enter your last name as it appears on your card'}
				],
			'errorArea': 'divLastNameOnCardError'
		},
		{'fieldId': 'txtCardNumber', 
			'validationFunctions': [
					{'function': 'required', 'errorMessage': 'Please enter your credit card number'}, 
					{'function': 'cc', 'errorMessage': 'Please enter a valid credit card number'} 
				],
			'errorArea': 'divCardNumberError'
		},
		{'fieldId': 'txtSecurityNumber', 
			'validationFunctions': [
					{'function': 'required', 'errorMessage': 'Please enter your security code'}, 
					{'function': 'regex', 'regex': '^[0-9]{3,4}$', 'errorMessage': 'Please enter a valid security number'} 
				],
			'errorArea': 'divSecurityNumberError'
		},
		{'fieldId': 'txtAddressLine1', 
			'validationFunctions': [
					{'function': 'required', 'errorMessage': 'Please enter your billing address'}, 
				],
			'errorArea': 'pAddressLine1Error'
		},
		{'fieldId': 'txtCity', 
			'validationFunctions': [
					{'function': 'required', 'errorMessage': 'Please enter your billing city'}, 
				],
			'errorArea': 'pCityError'
		},
		{'fieldId': 'selectCountryId', 
			'validationFunctions': [
					{'function': 'select', 'errorMessage': 'Please enter your billing country'}, 
				],
			'errorArea': 'pCountryIdError'
		},
		{'fieldId': 'selectState', 
			'validationFunctions': [
					{'function': 'select', 'errorMessage': 'Please select your billing state'}, 
				],
			'errorArea': 'pStateError'
		},
		{'fieldId': 'txtState', 
			'validationFunctions': [
					{'function': 'required', 'errorMessage': 'Please enter your billing state / province'}, 
				],
			'errorArea': 'pStateError'
		},
		{'fieldId': 'txtZip', 
			'validationFunctions': [
					{'function': 'required', 'errorMessage': 'Please enter your billing zip / postal code'}, 
				],
			'errorArea': 'pZipError'
		}				
	];	

	
</script>
<div class="pageContent">
	<form method="post" onsubmit="return validateForm(formFields);">
		<h1 class="pageHeader">
			<img class="headerIcon" src="<?php echo BASE_URL; ?>/images/cards.png" />
			Registration & Payment
		</h1>
		<div class="content">
            <p>
                First time students:<br />
                Please note your card will be held on file, but you will not be charged. If you cancel after Sunday July 2, 2017, or fail to arrive the day of the event, you will be charged $150.
            </p>
			<br />
			<br />
            <p>
                Second time students:<br />
                You will be charged a $99 non-refundable fee upon completing this form. If you cancel after Sunday July 2, 2017, or fail to arrive the day of the event, you will be charged an additional $150.
            </p>
			<br />
			<br />
            <p>
                All your information is transmitted securely using strong encryption.
            </p>
			<br />
			<?php if(count($templateVariables['errors']) > 0) : ?>
				<div id="divErrors">
					<ul>
						<?php foreach($templateVariables['errors'] as $error) : ?>
							<li>
								Error: <?php echo $error; ?>
							</li>
						<?php endforeach; ?>
					</ul>
				</div>
			<?php endif; ?>
			<div id="divCreditCardFields">
				<?php 
                    $firstNameOnCard = isset($_POST['firstNameOnCard']) ? trim($_POST['firstNameOnCard']) : $templateVariables['order']['customer']['first_name']; 
                    $lastNameOnCard = isset($_POST['lastNameOnCard']) ? trim($_POST['lastNameOnCard']) : $templateVariables['order']['customer']['last_name']; 
					$customerAddress = ($templateVariables['order']['customer']['billing_address'] != null) ? $templateVariables['order']['customer']['billing_address'] : $templateVariables['order']['customer']['address'];
					$addressLine1 = isset($_POST['addressLine1']) ? trim($_POST['addressLine1']) : $customerAddress['line1']; 
					$city = isset($_POST['city']) ? trim($_POST['city']) : $customerAddress['city'];
					$countryId = isset($_POST['countryId']) ? trim($_POST['countryId']) : $customerAddress['country_id']; 
					$stateTxt = isset($_POST['stateTxt']) ? trim($_POST['stateTxt']) : $customerAddress['state']; 
					$stateSelect = isset($_POST['stateSelect']) ? trim($_POST['stateSelect']) : $customerAddress['state']; 
					$zip = isset($_POST['zip']) ? trim($_POST['zip']) : $customerAddress['zip'];
				?>
				<div class="labelDiv">
					*First Name
				</div>
				<div class="fieldDiv">
					<input type="text" name="firstNameOnCard" id="txtFirstNameOnCard" value="<?php echo htmlentities($firstNameOnCard); ?>" />
					<img src="<?php echo BASE_URL; ?>/images/questionMark.png" title="Please enter your first name as it appears on the card" />
					<p class="errorMessage" id="divFirstNameOnCardError"></p>
				</div>
				<div class="labelDiv">
					*Last Name
				</div>
				<div class="fieldDiv">
					<input type="text" name="lastNameOnCard" id="txtLastNameOnCard" value="<?php echo htmlentities($lastNameOnCard); ?>" />
					<img src="<?php echo BASE_URL; ?>/images/questionMark.png" title="Please enter your last name as it appears on the card" />
					<p class="errorMessage" id="divLastNameOnCardError"></p>
				</div>
				<div class="labelDiv">
					*Card number
				</div>
				<div class="fieldDiv">
					<input type="text" name="cardNumber" id="txtCardNumber" value="<?php if(isset($_POST['cardNumber'])) echo htmlentities($_POST['cardNumber']); ?>" />
					<img src="<?php echo BASE_URL; ?>/images/questionMark.png" title="Please enter the full card number" />
					<p class="errorMessage" id="divCardNumberError"></p>
				</div>
				<div class="labelDiv">
					*Expiry date
				</div>
				<div class="fieldDiv">
					<select name="expirationMonth" class="expirationMonth">
						<?php for($i = 1; $i <= 12; $i++) : ?>
							<option value="<?php echo $i; ?>" <?php if(isset($_POST['expirationMonth']) and ($_POST['expirationMonth'] == $i)) echo 'selected="selected"'; ?>><?php echo str_pad($i, 2, '0', STR_PAD_LEFT); ?></option>
						<?php endfor; ?>
					</select>
					<select name="expirationYear" class="expirationYear">
						<?php for($i = (date("Y") * 1); $i <= ((date("Y") * 1) + 10); $i++) : ?>
							<option value="<?php echo $i; ?>" <?php if(isset($_POST['expirationYear']) and ($_POST['expirationYear'] == $i)) echo 'selected="selected"'; ?>><?php echo $i; ?></option>
						<?php endfor; ?>
					</select>
					<img src="<?php echo BASE_URL; ?>/images/questionMark.png" title="Please enter month and year for your card expiry" />
				</div>
				<div class="labelDiv">
					*Security number
				</div>
				<div class="fieldDiv">
					<input type="text" name="securityNumber" id="txtSecurityNumber" value="<?php if(isset($_POST['securityNumber'])) echo htmlentities($_POST['securityNumber']); ?>" />
					<img src="<?php echo BASE_URL; ?>/images/questionMark.png" title="The security number is found at the end of the name strip on the back of your card" />
					<p class="errorMessage" id="divSecurityNumberError"></p>
				</div>
				<div class="labelDiv">
					*Billing address
				</div>
				<div class="fieldDiv">
					<input type="text" name="addressLine1" id="txtAddressLine1" value="<?php echo htmlentities($addressLine1); ?>" />
					<img src="<?php echo BASE_URL; ?>/images/questionMark.png" title="Please enter your billing address" />
					<p class="errorMessage" id="pAddressLine1Error"></p>
				</div>
				<div class="labelDiv">
					*Billing city
				</div>
				<div class="fieldDiv">
					<input type="text" name="city" id="txtCity" value="<?php echo htmlentities($city); ?>" />
					<img src="<?php echo BASE_URL; ?>/images/questionMark.png" title="Please enter your billing city" />
					<p class="errorMessage" id="pCityError"></p>
				</div>
				<div class="labelDiv">
					*Billing country
				</div>
				<div class="fieldDiv">
					<select name="countryId" id="selectCountryId" onchange="countryChanged();">
						<?php foreach($templateVariables['countries'] as $currentCountryId => $currentCountry) : ?>
							<option value="<?php echo $currentCountryId; ?>" <?php if($currentCountryId == $countryId) echo 'selected="selected"'; ?>>
								<?php echo htmlentities($currentCountry); ?>
							</option>
						<?php endforeach; ?>
					</select>
					<img src="<?php echo BASE_URL; ?>/images/questionMark.png" title="Please enter your billing country" />
					<p class="errorMessage" id="pCountryIdError"></p>
				</div>
				<div class="labelDiv">
					*Billing state / province
				</div>
				<div class="fieldDiv">
					<select name="stateSelect" id="selectState" <?php if($countryId != UNITED_STATES_COUNTRY_ID) echo 'style="display: none;"'; ?>>
						<option value="" <?php if($stateSelect == '') echo 'selected="selected"'; ?>>
							Please select
						</option>
						<?php foreach($templateVariables['states'] as $currentState) : ?>
							<option value="<?php echo htmlentities($currentState); ?>" <?php if($currentState == $stateSelect) echo 'selected="selected"'; ?>>
								<?php echo htmlentities($currentState); ?>
							</option>
						<?php endforeach; ?>
					</select>
					<input name="stateTxt" type="text" id="txtState" <?php if($countryId == UNITED_STATES_COUNTRY_ID) echo 'style="display: none;"'; else echo 'value="'.($stateTxt).'"'; ?> />
					<img src="<?php echo BASE_URL; ?>/images/questionMark.png" title="Please enter your billing state / province" />
					<p class="errorMessage" id="pStateError"></p>
				</div>
				<div class="labelDiv">
					*Billing Zip / Postal
				</div>
				<div class="fieldDiv">
					<input type="text" name="zip" id="txtZip" value="<?php echo htmlentities($zip); ?>" />
					<img src="<?php echo BASE_URL; ?>/images/questionMark.png" title="Please enter your billing zip / postal code" />
					<p class="errorMessage" id="pZipError"></p>
				</div>
				<div class="clear"></div>
			</div>
			<hr style="margin-top: 20px;">
			<button type="submit" class="checkoutButton" name="submit" value="Purchase">Purchase</button>
            <p class="bold">Do not double click.</p>
            <br />
            <p>Transaction process may take up to 2 minutes.</p>
			<div class="clear"></div>
			<div id="divAuthorizeNetSeal">
				<!-- (c) 2005, 2015. Authorize.Net is a registered trademark of CyberSource Corporation --> 
				<div class="AuthorizeNetSeal"> <script type="text/javascript" language="javascript">var ANS_customer_id="c3bbeb1c-73df-47d3-b5c5-504a2afedbcf";</script> <script type="text/javascript" language="javascript" src="//verify.authorize.net/anetseal/seal.js" ></script> <a href="http://www.authorize.net/" id="AuthorizeNetText" target="_blank">Credit Card Processing</a> </div>
			</div>
			<div class="clear"></div>
		</div>
	</form>
</div>
<?php

	// Display header.
	include('footer.php');
	
?>