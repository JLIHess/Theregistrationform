<?php

	// Set template variables.
	$templateVariables['page'] = 'checkout';
	$templateVariables['title'] = 'Checkout';
	$templateVariables['jqueryUi'] = true;
	
	// Display header.
	include('header.php');
	
?>
<link rel="stylesheet" type="text/css" href='<?php echo BASE_URL; ?>/css/checkout.css'></script>
<script src='<?php echo BASE_URL; ?>/js/formValidator.js'></script>
<script src='<?php echo BASE_URL; ?>/js/checkout.js'></script>
<script>
	var orderId = <?php echo intval($templateVariables['orderId']); ?>;
	var order = <?php echo json_encode($templateVariables['order']); ?>;
	var sponsorships = <?php echo json_encode($templateVariables['sponsorships']); ?>;
	var unitedStatesCountryId = <?php echo UNITED_STATES_COUNTRY_ID; ?>;
	
	// Field validation.
	var formFields = [
		<?php foreach($templateVariables['sponsorships'] as $sponsorship) : ?>
			<?php if($sponsorship['amount'] === null) : ?>
				{'fieldId': 'txtSponsorshipAmount<?php echo $sponsorship['id']; ?>', 
					'validationFunctions': [
							{'function': 'required', 'errorMessage': 'Please enter sponsorship amount'}, 
							{'function': 'numeric', 'errorMessage': 'Please enter a number'}
						],
					'errorArea': 'divSponsorshipAmountError<?php echo $sponsorship['id']; ?>'
				},
			<?php endif; ?>
		<?php endforeach; ?>
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
			<img class="headerIcon" src="<?php echo BASE_URL; ?>/images/cart.png" />
			Summary of Order
		</h1>
		<div class="content">
		<table class="orderSummaryTable">
			<tr>
				<?php if(count($templateVariables['order']['rooms']) > 1) : ?>
					<th>
					</th>
				<?php endif; ?>
				<th>
					Dates
				</th>
				<th>
					Room info
				</th>
				<th>
					Guests
				</th>
				<th>
					Price
				</th>
			</tr>
			<?php foreach($templateVariables['order']['rooms'] as $roomNumber => $room) : ?>
			<?php 
				// Get the number of guests.
				$adults = $teens = $children = $toddlers = $infants = 0;
				foreach($room['guests'] as $guestId) {
					if($templateVariables['order']['guests'][$guestId]['user_type_id'] <= 2) $adults += 1;
					elseif($templateVariables['order']['guests'][$guestId]['user_type_id'] == 3) $teens += 1;
					elseif($templateVariables['order']['guests'][$guestId]['user_type_id'] == 4) $children += 1;
					elseif($templateVariables['order']['guests'][$guestId]['user_type_id'] == 23) $toddlers += 1;
					elseif($templateVariables['order']['guests'][$guestId]['user_type_id'] == 5) $infants += 1;
				}
			?>
				<tr>
					<?php if(count($templateVariables['order']['rooms']) > 1) : ?>
						<td>
							Room #<?php echo $roomNumber + 1; ?>
						</td>
					<?php endif; ?>
					<td>
                        <?php if(($room['program_start_date'] == $room['hotel_start_date']) and ($room['program_end_date'] == $room['hotel_end_date'])) : ?>
                            <?php echo date('m/d/y h:ia', strtotime($room['program_start_date'])); ?> - <?php echo date('m/d/y h:ia', strtotime($room['program_end_date'])); ?>
                        <?php else : ?>
                            Hotel dates:<br />
                            <?php if ((strtotime($room['hotel_start_date']) > 0) && (strtotime($room['hotel_end_date']) > 0)): ?>
                                <?php echo date('m/d/y h:ia', strtotime($room['hotel_start_date'])) . ' - '
                                    . date('m/d/y h:ia', strtotime($room['hotel_end_date'])); ?>
                            <?php else: ?>
                                None
                            <?php endif; ?><br />
                            <br />
                            Program dates:<br />
                            <?php echo date('m/d/y h:ia', strtotime($room['program_start_date'])); ?> - <?php echo date('m/d/y h:ia', strtotime($room['program_end_date'])); ?><br />
                        <?php endif; ?>
					</td>
					<td>
						<?php echo $room['room_type']; ?><br />
						<?php echo $room['occupancy']; ?><br />
						<?php echo $room['bed_type']; ?>
					</td>
					<td>
						<?php if($adults > 0) : ?>
							<?php echo $adults; ?> Adult<?php if($adults > 1) echo 's'; ?><br />
						<?php endif; ?>
						<?php if($teens > 0) : ?>
							<?php echo $teens; ?> Teen<?php if($teens > 1) echo 's'; ?><br />
						<?php endif; ?>
						<?php if($children > 0) : ?>
							<?php echo $children; ?> Child<?php if($children > 1) echo 'ren'; ?><br />
						<?php endif; ?>
						<?php if($toddlers > 0) : ?>
							<?php echo $toddlers; ?> Toddler<?php if($toddlers > 1) echo 's'; ?><br />
						<?php endif; ?>
						<?php if($infants > 0) : ?>
							<?php echo $infants; ?> Infant<?php if($infants > 1) echo 's'; ?><br />
						<?php endif; ?>
					</td>
					<td>
						$ <?php echo number_format(($room['price'] / 100), 2); ?>
					</td>
				<tr>
			<?php endforeach; ?>
		</table>
		<div class="priceLabel">
			Sum:
		</div>
		<div class="priceField">
			$<?php echo number_format(($templateVariables['order']['price'] / 100), 2); ?>
		</div>
		<?php if($templateVariables['order']['cme_price'] > 0) : ?>
			<div class="priceLabel">
				CME Price:
			</div>
			<div class="priceField">
				$<?php echo number_format($templateVariables['order']['cme_price'] / 100, 2); ?>
			</div>
		<?php endif; ?>
		<div class="priceLabel">
			Occupancy Tax:
		</div>
		<div class="priceField">
			$<?php echo number_format($templateVariables['order']['tax'] / 100, 2); ?>
		</div>
		<div class="priceLabel">
			Total:
		</div>
		<div class="priceField">
			$<?php echo number_format($templateVariables['order']['total'] / 100, 2); ?>
		</div>
		<div class="clear"></div>
		</div>
		<div class="showSpacer"></div>
		
		<h1 class="pageHeader">
			<img class="headerIcon" src="<?php echo BASE_URL; ?>/images/heart.png" />
			Sponsorship
		</h1>
		<div class="content">
		<?php foreach($templateVariables['sponsorships'] as $sponsorship) : ?>
			<input type="checkbox" name="sponsorships[]" value="<?php echo $sponsorship['id']; ?>" onchange="clickSponsorship('<?php echo $sponsorship['id']; ?>', this);" <?php if(isset($_POST['sponsorships']) and in_array($sponsorship['id'], $_POST['sponsorships'])) echo 'checked="checked"'; ?> id="checkSponsorship<?php echo $sponsorship['id']; ?>">
			<?php if($sponsorship['amount'] !== null) : ?>
				<label for="checkSponsorship<?php echo $sponsorship['id']; ?>">
					<?php echo $sponsorship['display']; ?> - $<?php echo number_format($sponsorship['amount'] / 100, 2); ?>
				</label>
				<br />
			<?php else: ?>
				<label for="checkSponsorship<?php echo $sponsorship['id']; ?>">
					<?php echo $sponsorship['display']; ?> - $
				</label>
				<input type="text" name="sponsorshipAmount<?php echo $sponsorship['id']; ?>" id="txtSponsorshipAmount<?php echo $sponsorship['id']; ?>" <?php if(!isset($_POST['sponsorships']) or !in_array($sponsorship['id'], $_POST['sponsorships'])) echo 'disabled="disabled"'; ?> value="<?php if(isset($_POST['sponsorshipAmount'.$sponsorship['id']])) echo htmlentities($_POST['sponsorshipAmount'.$sponsorship['id']]); ?>" />				
				<p class="errorMessage" id="divSponsorshipAmountError<?php echo $sponsorship['id']; ?>"></p>
			<?php endif ?>
		<?php endforeach; ?>
		<div id="divSponsorshipNotes" <?php if((!isset($_POST['sponsorships'])) or (count($_POST['sponsorships']) == 0)) echo 'style="display: none;"'; ?>>
			<label for="txtSponsorshipNotes">
				In honor of:
			</label>
			<br />
			<textarea name="sponsorshipNotes" id="txtSponsorshipNotes"><?php if(isset($_POST['sponsorshipNotes'])) echo htmlentities($_POST['sponsorshipNotes']); ?></textarea>
			<p class="fieldExplanation" id="divOtherAmountError">
				If anonymous, please indicate here. Maximum of 25 words per dedication.
			</p>
		</div>
		<div class="spacer15"></div>
		</div>
		<div class="showSpacer"></div>
		
		<h1 class="pageHeader">
			<img class="headerIcon" src="<?php echo BASE_URL; ?>/images/cards.png" />
			Checkout
		</h1>
		<div class="content">
			Please fill the relevant fields and submit to process your order<br />
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
			<?php if(isset($_SESSION['retreatAdmin']) and $_SESSION['retreatAdmin']) : ?>
				<div class="labelDiv">
				</div>
				<div class="fieldDiv">
					<input type="checkbox" name="payByCheck" id="chkPayByCheck" onclick="document.getElementById('divCreditCardFields').style.display = (this.checked) ? 'none' : 'block';" value='1' />
					<label for="chkPayByCheck">Pay by check</label>
				</div>
			<?php endif; ?>
			<div id="divCreditCardFields">
				<div class="labelDiv">
					*First Name
				</div>
				<div class="fieldDiv">
					<input type="text" name="firstNameOnCard" id="txtFirstNameOnCard" value="<?php if(isset($_POST['firstNameOnCard'])) echo htmlentities($_POST['firstNameOnCard']); ?>" />
					<img src="<?php echo BASE_URL; ?>/images/questionMark.png" title="Please enter your first name as it appears on the card" />
					<p class="errorMessage" id="divFirstNameOnCardError"></p>
				</div>
				<div class="labelDiv">
					*Last Name
				</div>
				<div class="fieldDiv">
					<input type="text" name="lastNameOnCard" id="txtLastNameOnCard" value="<?php if(isset($_POST['lastNameOnCard'])) echo htmlentities($_POST['lastNameOnCard']); ?>" />
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
				<?php 
					$customerAddress = ($templateVariables['order']['customer']['billing_address'] != null) ? $templateVariables['order']['customer']['billing_address'] : $templateVariables['order']['customer']['address'];
					$addressLine1 = isset($_POST['addressLine1']) ? trim($_POST['addressLine1']) : $customerAddress['line1']; 
					$city = isset($_POST['city']) ? trim($_POST['city']) : $customerAddress['city'];
					$countryId = isset($_POST['countryId']) ? trim($_POST['countryId']) : $customerAddress['country_id']; 
					$stateTxt = isset($_POST['stateTxt']) ? trim($_POST['stateTxt']) : $customerAddress['state']; 
					$stateSelect = isset($_POST['stateSelect']) ? trim($_POST['stateSelect']) : $customerAddress['state']; 
					$zip = isset($_POST['zip']) ? trim($_POST['zip']) : $customerAddress['zip'];
				?>
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
			<button type="submit" class="checkoutButton" name="submit" value="Checkout">Checkout</button>
			<button type="button" class="backButton" onclick="document.location.href = baseUrl + 'guestRegistrationForm.php?orderId=' + orderId;">Go back</button>
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