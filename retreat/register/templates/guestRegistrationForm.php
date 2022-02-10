<?php

// Set template variables.
$templateVariables['page'] = 'guestRegistrationForm';
$templateVariables['title'] = 'Register guests';
$templateVariables['jqueryUi'] = true;

// Display header.
include('header.php');

?>

<link rel="stylesheet" type="text/css" href='<?php echo BASE_URL; ?>/css/guestRegistrationForm.css'/>
<script>
    var orderId = <?php echo intval($templateVariables['orderId']); ?>;
    var order = <?php echo json_encode($templateVariables['order']); ?>;
    var unitedStatesCountryId = <?php echo UNITED_STATES_COUNTRY_ID; ?>;
</script>
<script src='<?php echo BASE_URL; ?>/js/formValidator.js'></script>
<script src='<?php echo BASE_URL; ?>/js/guestRegistrationForm.js'></script>
<div class="pageContent">
    <h1 class="pageHeader">
        Room Information
    </h1>

    <div class="orderSummaryDiv">
        <table class="orderSummaryTable">
            <tr>
                <?php if (count($templateVariables['order']['rooms']) > 1) : ?>
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
            <?php foreach ($templateVariables['order']['rooms'] as $roomNumber => $room) : ?>
            <?php
            // Get the number of guests.
            $adults = $teens = $children = $toddlers = $infants = 0;
            foreach ($room['guests'] as $guestId) {
                if ($templateVariables['order']['guests'][$guestId]['user_type_id'] <= 2) $adults += 1;
                elseif ($templateVariables['order']['guests'][$guestId]['user_type_id'] == 3) $teens += 1;
                elseif ($templateVariables['order']['guests'][$guestId]['user_type_id'] == 4) $children += 1;
                elseif ($templateVariables['order']['guests'][$guestId]['user_type_id'] == 23) $toddlers += 1;
                elseif ($templateVariables['order']['guests'][$guestId]['user_type_id'] == 5) $infants += 1;
            }
            ?>
            <tr>
                <?php if (count($templateVariables['order']['rooms']) > 1) : ?>
                    <td>
                        Room #<?php echo $roomNumber + 1; ?>
                    </td>
                <?php endif; ?>
                <td>
                    <?php if (($room['program_start_date'] == $room['hotel_start_date']) and ($room['program_end_date'] == $room['hotel_end_date'])) : ?>
                        <?php echo date('m/d/y h:ia', strtotime($room['program_start_date'])); ?> - <?php echo date('m/d/y h:ia', strtotime($room['program_end_date'])); ?>
                    <?php else : ?>
                        Hotel dates:<br/>
                        <?php if ((strtotime($room['hotel_start_date']) > 0) && (strtotime($room['hotel_end_date']) > 0)): ?>
                            <?php echo date('m/d/y h:ia', strtotime($room['hotel_start_date'])) . ' - '
                                       . date('m/d/y h:ia', strtotime($room['hotel_end_date'])); ?>
                        <?php else: ?>
                            None
                        <?php endif; ?><br/>
                        <br/>
                        Program dates:<br/>
                        <?php echo date('m/d/y h:ia', strtotime($room['program_start_date'])); ?> - <?php echo date('m/d/y h:ia', strtotime($room['program_end_date'])); ?>
                        <br/>
                    <?php endif; ?>
                </td>
                <td>
                    Room Type: <?php echo $room['room_type']; ?><br/>
                    <?php echo $room['occupancy']; ?><br/>
                    <?php if ($room['bed_type'] != ''): ?>
                        Bedding type: <?php echo $room['bed_type']; ?><br/>
                    <?php endif; ?>
                    <?php if (isset($room['additional_options'], $room['additional_options']['bedding'])): ?>
                        Additional Options:
                        <?php echo implode(', ', $room['additional_options']['bedding']); ?>
                    <?php endif; ?>
                </td>
                <td>
                    <?php if ($adults > 0) : ?>
                        <?php echo $adults; ?> Adult<?php if ($adults > 1) echo 's'; ?><br/>
                    <?php endif; ?>
                    <?php if ($teens > 0) : ?>
                        <?php echo $teens; ?> Teen<?php if ($teens > 1) echo 's'; ?><br/>
                    <?php endif; ?>
                    <?php if ($children > 0) : ?>
                        <?php echo $children; ?> Child<?php if ($children > 1) echo 'ren'; ?><br/>
                    <?php endif; ?>
                    <?php if ($toddlers > 0) : ?>
                        <?php echo $toddlers; ?> Toddler<?php if ($toddlers > 1) echo 's'; ?><br/>
                    <?php endif; ?>
                    <?php if ($infants > 0) : ?>
                        <?php echo $infants; ?> Infant<?php if ($infants > 1) echo 's'; ?><br/>
                    <?php endif; ?>
                </td>
                <td>

                    $<?php echo number_format((($room['price'] / 100) + (+$room['tax'] / 100)), 2); ?>
                </td>
            <tr>
                <?php endforeach; ?>
        </table>
    </div>
    <div class="box">
        Please review the information above, if you'd like to make changes <a href="<?php echo SELECT_ROOMS_URL; ?>"
                                                                              onclick="return ((order.guests[order.rooms[0].guests[0]].user == null) ? true : confirm('Going back will cause the guest information to be lost.\nAre you sure you want to continue?'));">click
            here</a>.
    </div>
    <br/>

    <div class="showSpacer"></div>
    <h1 class="pageHeader">
        Guest registration
    </h1>
    <hr>
    <?php if (!empty($currentUser['id'])): ?>
        <p style="text-align: center; font-size: 20px;"><i style="color: #18d418;" class="fa fa-check-circle"></i> You are currently logged in as <?php echo ($currentUser['prefix']? $currentUser['prefix'] . ' ' : '') . $currentUser['name'] ?></p>
    <?php else: ?>
        <p style="text-align: center; font-size: 20px;">Tip: If you've attended the retreat in the past, you can speed up registration by <a style="text-decoration: underline;" data-toggle="popup" data-target="#login-popup" href="#">logging in.</a></p>
    <?php endif ?>
    <hr>
    <div class="guestIntro">Please enter information about your guests.</div>
    <div id="divOrder">
        <?php foreach ($templateVariables['order']['rooms'] as $num => $room) : ?>
            <div class="roomDiv accordian" id="divRoom<?php echo $room['id']; ?>">
                <div class="headerDiv">
                    Room #<?php echo($num + 1); ?> (<?php echo $room['room_type']; ?>)
                </div>
                <div class="contentDiv">
                    <?php $adults = $teens = $children = $toddlers = $infants = 0; ?>
                    <?php foreach ($room['guests'] as $k => $guestId) : ?>
                        <?php

                        // Get the guest information.
                        $guest = $order['guests'][$guestId];
                        $user = $guest['user'];

                        if ($k == 0 && $currentUser) {
                            $user = $currentUser;
                        }

                        $prefix = ($user == null) ? '' : $user['prefix'];
                        $firstName = ($user == null) ? '' : $user['first_name'];
                        $middleName = ($user == null) ? '' : $user['middle_name'];
                        $lastName = ($user == null) ? '' : $user['last_name'];
                        $name = '';
                        if (trim($firstName) != '') $name .= htmlentities($firstName . ' ');
                        if (trim($middleName) != '') $name .= htmlentities($middleName . ' ');
                        if (trim($lastName) != '') $name .= htmlentities($lastName);
                        $dob = ($user == null) ? '' : htmlentities($user['date_of_birth']);
                        $email = ($user == null) ? ((isset($_SESSION['retreatAdmin']) and $_SESSION['retreatAdmin']) ? 'info@jretreat.com' : '') : htmlentities($user['email']);
                        $dayPhone = ($user == null) ? '' : htmlentities($user['day_phone']);
                        $homePhone = ($user == null) ? '' : htmlentities($user['home_phone']);
                        $cellPhone = ($user == null) ? '' : htmlentities($user['cell_phone']);
                        $tagName = ($user == null) ? '' : htmlentities($user['tag_name']);
                        $gender = ($user == null) ? '' : htmlentities($user['gender']);
                        $cmeCredits = $guest['cme_credits'];
                        $addressLine1 = (($user == null) or ($user['address'] == null)) ? '' : htmlentities($user['address']['line1']);
                        $addressLine2 = (($user == null) or ($user['address'] == null)) ? '' : htmlentities($user['address']['line2']);
                        $city = (($user == null) or ($user['address'] == null)) ? '' : htmlentities($user['address']['city']);
                        $state = (($user == null) or ($user['address'] == null)) ? '' : htmlentities($user['address']['state']);
                        $zip = (($user == null) or ($user['address'] == null)) ? '' : htmlentities($user['address']['zip']);
                        $countryId = (($user == null) or ($user['address'] == null)) ? UNITED_STATES_COUNTRY_ID : $user['address']['country_id'];
                        $emergencyContact = ($user == null) ? ((isset($_SESSION['retreatAdmin']) and $_SESSION['retreatAdmin']) ? 'JLI' : '') : htmlentities($user['emergency_contact']);
                        $emergencyRelation = ($user == null) ? ((isset($_SESSION['retreatAdmin']) and $_SESSION['retreatAdmin']) ? 'Registration Office' : '') : htmlentities($user['emergency_relation']);
                        $emergencyPhone = ($user == null) ? ((isset($_SESSION['retreatAdmin']) and $_SESSION['retreatAdmin']) ? '718-221-6900' : '') : htmlentities($user['emergency_phone']);
                        $relationToPrimary = htmlentities($guest['relation_to_primary']);
                        $notes = isset($guest['notes'])? $guest['notes']: '';
                        $additionalNotes = isset($guest['additional_notes'])? htmlentities($guest['additional_notes']) : '';
                        $referredBy = ($user == null) ? '' : htmlentities($user['referred_by']);
                        $jliStudent = ($user == null) ? '' : htmlentities($user['jli_student']);
                        $shliach = ($user == null) ? '' : htmlentities($user['shliach']);

                        ?>
                        <div class="guestDiv accordian" id="divGuest<?php echo $guestId; ?>">

                            <?php if ($guest['primary']) : ?>
                                <?php $adults += 1; ?>
                                <div class="headerDiv">
                                    Please enter your personal information
                                </div>
                            <?php elseif ($guest['user_type_id'] == 2) : ?>
                                <?php $adults += 1; ?>
                                <div class="headerDiv">
                                    Add personal information for Adult #<?php echo $adults; ?>
                                </div>
                            <?php elseif ($guest['user_type_id'] == 3) : ?>
                                <?php $teens += 1; ?>
                                <div class="headerDiv">
                                    Add personal information for Teen #<?php echo $teens; ?>
                                </div>
                            <?php elseif ($guest['user_type_id'] == 4) : ?>
                                <?php $children += 1; ?>
                                <div class="headerDiv">
                                    Add personal information for Child #<?php echo $children; ?>
                                </div>
                            <?php elseif ($guest['user_type_id'] == 23) : ?>
                                <?php $toddlers += 1; ?>
                                <div class="headerDiv">
                                    Add personal information for Toddler #<?php echo $toddlers; ?>
                                </div>
                            <?php elseif ($guest['user_type_id'] == 5) : ?>
                                <?php $infants += 1; ?>
                                <div class="headerDiv">
                                    Add personal information for Infant #<?php echo $infants; ?>
                                </div>
                            <?php endif; ?>
                            <div class="contentDiv">

                                <form id="formGuest<?php echo $guestId; ?>" onsubmit="return false;">
                                    <div class="clear"></div>

                                    <?php if (!empty($guests) && $k > 0): ?>
                                        <div class="labelDiv">
                                            Optional: Autofill information from a previous registration:
                                        </div>
                                         <div class="fieldDiv">
                                            <div class="form-check py-1">
                                                <select id="guestId<?php echo $guest['id'] ?>" name="guest_id" data-id="<?php echo $guestId; ?>" class="guest-prefill">
                                                    <option value="">Select</option>
                                                    <?php foreach ($templateVariables['guests'] as $userGuest): ?>
                                                        <option data-id="<?php echo $userGuest['id'] ?>" data-info='<?php echo json_encode($userGuest) ?>'
                                                            value="<?php echo $userGuest['id'] ?>" <?php echo ($guest['user_id'] == $userGuest['id']? 'selected="selected"' : '') ?>><?php echo ($userGuest['prefix']? $userGuest['prefix'] . ' ' : '') . $userGuest['name']; ?></option>
                                                    <?php endforeach; ?>
                                                </select>
                                            </div>
                                        </div>
                                    <?php else: ?>
                                        <input id="guestId<?php echo $guest['id'] ?>" type="hidden" name="guest_id" value="<?php echo !empty($currentUser['id'])? $currentUser['id'] : '' ?>">
                                    <?php endif ?>

                                    <div class="clear"></div>
                                    <div class="formSectionHeader">
                                        Personal info
                                    </div>
                                    <hr/>
                                    <div class="clear"></div>
                                    <?php if ($guest['user_type_id'] <= 2) : ?>
                                        <div class="labelDiv">
                                            <label for="selectPrefix<?php echo $guestId; ?>">Prefix*:</label>
                                        </div>
                                        <div class="fieldDiv">
                                            <select id="selectPrefix<?php echo $guestId; ?>">
                                                <option
                                                    value="" <?php if ($prefix == '') echo 'selected="selected"'; ?>>
                                                </option>
                                                <option
                                                    value="Mr." <?php if ($prefix == 'Mr.') echo 'selected="selected"'; ?>>
                                                    Mr.
                                                </option>
                                                <option
                                                    value="Mrs." <?php if ($prefix == 'Mrs.') echo 'selected="selected"'; ?>>
                                                    Mrs.
                                                </option>
                                                <option
                                                    value="Ms." <?php if ($prefix == 'Ms.') echo 'selected="selected"'; ?>>
                                                    Ms.
                                                </option>
                                                <option
                                                    value="Dr." <?php if ($prefix == 'Dr.') echo 'selected="selected"'; ?>>
                                                    Dr.
                                                </option>
                                                <option
                                                    value="Rabbi" <?php if ($prefix == 'Rabbi') echo 'selected="selected"'; ?>>
                                                    Rabbi
                                                </option>
                                            </select>
                                            <p class="errorMessage" id="pSelectPrefixError<?php echo $guestId; ?>"></p>
                                        </div>
                                    <?php endif; ?>
                                    <div class="labelDiv">
                                        <label for="txtName<?php echo $guestId; ?>">
                                            Full name*:
                                        </label>
                                    </div>
                                    <div class="fieldDiv">
                                        <input type="text" id="txtName<?php echo $guestId; ?>"
                                               value="<?php echo $name; ?>"/>

                                        <p class="fieldExplanation">
                                            First name, middle name, last name
                                        </p>

                                        <p class="errorMessage" id="pNameError<?php echo $guestId; ?>"></p>
                                    </div>
                                    <?php if (!$guest['primary']) : ?>
                                        <div class="labelDiv">
                                            <label for="txtRelationToPrimary<?php echo $guestId; ?>">
                                                Relationship to Primary Guest*:
                                            </label>
                                        </div>
                                        <div class="fieldDiv">
                                            <select id="selectRelationToPrimary<?php echo $guestId; ?>"
                                                    onchange="document.getElementById('txtRelationToPrimary<?php echo $guestId; ?>').style.display = (this.options[this.selectedIndex].value != 'Other') ? 'none' : 'inline'; console.log(this.options[this.selectedIndex].value);">
                                                <option value="">Select relationship...</option>
                                                <?php foreach ($suggestedRelationships as $relationship) : ?>
                                                    <option
                                                        value="<?php echo htmlentities($relationship); ?>" <?php if ($relationship == $relationToPrimary) echo 'selected="selected"'; ?>>
                                                        <?php echo htmlentities($relationship); ?>
                                                    </option>
                                                <?php endforeach; ?>
                                                <option
                                                    value="Other" <?php if ((trim($relationToPrimary) != '') and (!in_array($relationToPrimary, $suggestedRelationships))) echo 'selected="selected"'; ?>>
                                                    Other
                                                </option>
                                            </select>
                                            <input type="text" id="txtRelationToPrimary<?php echo $guestId; ?>"
                                                   value="<?php if ((trim($relationToPrimary) != '') and (!in_array($relationToPrimary, $suggestedRelationships))) echo $relationToPrimary; ?>"
                                                   style="display: <?php echo ((trim($relationToPrimary) != '') and (!in_array($relationToPrimary, $suggestedRelationships))) ? 'inline' : 'none'; ?>;"/>

                                            <p class="errorMessage"
                                               id="pRelationToPrimaryError<?php echo $guestId; ?>"></p>
                                        </div>
                                    <?php endif; ?>
                                    <div class="labelDiv">
                                        <label for="txtDob<?php echo $guestId; ?>">
                                            Date of birth<?php if ($guest['user_type_id'] > 2) : ?>*<?php endif; ?>:
                                        </label>
                                    </div>
                                    <div class="fieldDiv">
                                        <input type="text" class="dob" id="txtDob<?php echo $guestId; ?>"
                                               value="<?php echo $dob; ?>"/>

                                        <p class="errorMessage" id="pDobError<?php echo $guestId; ?>"></p>
                                    </div>
                                    <?php if ($guest['user_type_id'] <= 2) : ?>
                                        <?php if ($guest['primary']): ?>
                                            <div class="labelDiv">
                                                <label for="txtEmail<?php echo $guestId; ?>">
                                                    Email*:
                                                </label>
                                            </div>
                                            <div class="fieldDiv">
                                                <input type="text" id="txtEmail<?php echo $guestId; ?>" class="txtEmail"
                                                       value="<?php echo $email; ?>"/>

                                                <p class="errorMessage" id="pEmailError<?php echo $guestId; ?>"></p>
                                            </div>
                                        <?php else: ?>
                                            <div class="labelDiv">
                                                <label for="txtGuestEmail<?php echo $guestId; ?>">
                                                    Email:
                                                </label>
                                            </div>
                                            <div class="fieldDiv">
                                                <input type="text" id="txtGuestEmail<?php echo $guestId; ?>" class="txtGuestEmail"
                                                       value="<?php echo $email; ?>"/>

                                                <p class="errorMessage" id="pGuestEmailError<?php echo $guestId; ?>"></p>
                                            </div>
                                        <?php endif ?>
                                        <div class="labelDiv">
                                            <label for="txtDayPhone<?php echo $guestId; ?>">
                                                Day phone*:
                                            </label>
                                        </div>
                                        <div class="fieldDiv">
                                            <input type="text" id="txtDayPhone<?php echo $guestId; ?>"
                                                   value="<?php echo $dayPhone; ?>"/>

                                            <p class="errorMessage" id="pDayPhoneError<?php echo $guestId; ?>"></p>
                                        </div>
                                        <div class="labelDiv">
                                            <label for="txtHomePhone<?php echo $guestId; ?>">
                                                Home phone:
                                            </label>
                                        </div>
                                        <div class="fieldDiv">
                                            <input type="text" id="txtHomePhone<?php echo $guestId; ?>"
                                                   value="<?php echo $homePhone; ?>"/>
                                            <p class="errorMessage" id="pHomePhoneError<?php echo $guestId; ?>"></p>
                                        </div>
                                        <div class="labelDiv">
                                            <label for="txtCellPhone<?php echo $guestId; ?>">
                                                Cell phone:
                                            </label>
                                        </div>
                                        <div class="fieldDiv">
                                            <input type="text" id="txtCellPhone<?php echo $guestId; ?>"
                                                   value="<?php echo $cellPhone; ?>"/>
                                            <p class="errorMessage" id="pCellPhoneError<?php echo $guestId; ?>"></p>
                                        </div>
                                    <?php endif; ?>
                                    <div class="labelDiv">
                                        <label for="txtTagName<?php echo $guestId; ?>">
                                            Name as it should appear on your name tag*:
                                        </label>
                                    </div>
                                    <div class="fieldDiv">
                                        <input type="text" id="txtTagName<?php echo $guestId; ?>"
                                               value="<?php echo $tagName; ?>"/>

                                        <p class="errorMessage" id="pTagNameError<?php echo $guestId; ?>"></p>
                                    </div>
                                    <div class="labelDiv">
                                        <label for="selectGender<?php echo $guestId; ?>">
                                            Gender:
                                        </label>
                                    </div>
                                    <div class="fieldDiv">
                                        <select id="selectGender<?php echo $guestId; ?>">
                                            <option value="" <?php if ($gender == '') echo 'selected="selected"'; ?>>

                                            </option>
                                            <option value="m" <?php if ($gender == 'm') echo 'selected="selected"'; ?>>
                                                Male
                                            </option>
                                            <option value="f" <?php if ($gender == 'f') echo 'selected="selected"'; ?>>
                                                Female
                                            </option>
                                        </select>
                                    </div>
                                    <?php if ($guest['user_type_id'] <= 3) : ?>
                                        <div class="labelDiv">
                                            <label for="chkCmeCredits<?php echo $guestId; ?>">CME credits</label>
                                        </div>
                                        <div class="fieldDiv">
                                            <input type="checkbox"
                                                   id="chkCmeCredits<?php echo $guestId; ?>" <?php if ($cmeCredits) echo 'checked="checked"'; ?> />
                                            <label for="chkCmeCredits<?php echo $guestId; ?>">
                                                I would like to participate in the <a href="http://www.jretreat.com/cme"
                                                                                      target="_blank">Jewish Medical
                                                    Ethics</a> Conference at the National Jewish Retreat - $200
                                            </label>
                                        </div>
                                    <?php endif; ?>
                                    <div class="clear"></div>
                                    <div class="hiddenWhenInactive">
                                        <?php if ($guest['user_type_id'] <= 4) : ?>
                                            <div class="formSectionHeader">
                                                Address
                                            </div>
                                            <hr/>
                                            <div class="clear"></div>
                                            <?php if (!$guest['primary']) : ?>
                                                <div class="labelDiv">
                                                </div>
                                                <div class="fieldDiv">
                                                    <input type="checkbox" id="chkUseSameAddress<?php echo $guestId; ?>"
                                                           onclick="document.getElementById('divAddress<?php echo $guestId; ?>').style.display = (this.checked) ? 'none' : 'block';" <?php if ($user == null) echo 'checked="checked"'; ?> />
                                                    <label for="chkUseSameAddress<?php echo $guestId; ?>">Use same
                                                        address</label>
                                                </div>
                                                <div class="clear"></div>
                                            <?php endif; ?>
                                            <div
                                                id="divAddress<?php echo $guestId; ?>" <?php if ((!$guest['primary']) and ($user == null)) echo 'style="display: none;"'; ?>>
                                                <div class="labelDiv">
                                                    <label for="txtAddress<?php echo $guestId; ?>">
                                                        Address*:
                                                    </label>
                                                </div>
                                                <div class="fieldDiv">
                                                    <input type="text" id="txtAddressLine1<?php echo $guestId; ?>"
                                                           value="<?php echo $addressLine1; ?>"/>

                                                    <p class="errorMessage"
                                                       id="pAddressLine1Error<?php echo $guestId; ?>"></p>
                                                </div>
                                                <div class="labelDiv">
                                                </div>
                                                <div class="fieldDiv">
                                                    <input type="text" id="txtAddressLine2<?php echo $guestId; ?>"
                                                           value="<?php echo $addressLine2; ?>"/>
                                                </div>
                                                <div class="labelDiv">
                                                    <label for="txtCity<?php echo $guestId; ?>">
                                                        City*:
                                                    </label>
                                                </div>
                                                <div class="fieldDiv">
                                                    <input type="text" id="txtCity<?php echo $guestId; ?>"
                                                           value="<?php echo $city; ?>"/>

                                                    <p class="errorMessage" id="pCityError<?php echo $guestId; ?>"></p>
                                                </div>
                                                <div class="labelDiv">
                                                    <label for="selectCountryId<?php echo $guestId; ?>">
                                                        Country*:
                                                    </label>
                                                </div>
                                                <div class="fieldDiv">
                                                    <select id="selectCountryId<?php echo $guestId; ?>"
                                                            onchange="countryChanged('<?php echo $guestId; ?>');">
                                                        <?php foreach ($templateVariables['countries'] as $currentCountryId => $currentCountry) : ?>
                                                            <option
                                                                value="<?php echo $currentCountryId; ?>" <?php if ($currentCountryId == $countryId) echo 'selected="selected"'; ?>>
                                                                <?php echo htmlentities($currentCountry); ?>
                                                            </option>
                                                        <?php endforeach; ?>
                                                    </select>

                                                    <p class="errorMessage"
                                                       id="pCountryIdError<?php echo $guestId; ?>"></p>
                                                </div>
                                                <div class="labelDiv">
                                                    <label for="txtState<?php echo $guestId; ?>">
                                                        State / Province*:
                                                    </label>
                                                </div>
                                                <div class="fieldDiv">
                                                    <select
                                                        id="selectState<?php echo $guestId; ?>" <?php if ($countryId != UNITED_STATES_COUNTRY_ID) echo 'style="display: none;"'; ?>>
                                                        <option
                                                            value="" <?php if ($state == '') echo 'selected="selected"'; ?>>
                                                            Please select
                                                        </option>
                                                        <?php foreach ($templateVariables['states'] as $currentState) : ?>
                                                            <option
                                                                value="<?php echo htmlentities($currentState); ?>" <?php if ($currentState == $state) echo 'selected="selected"'; ?>>
                                                                <?php echo htmlentities($currentState); ?>
                                                            </option>
                                                        <?php endforeach; ?>
                                                    </select>
                                                    <input type="text"
                                                           id="txtState<?php echo $guestId; ?>" <?php if ($countryId == UNITED_STATES_COUNTRY_ID) echo 'style="display: none;"'; else echo 'value="' . ($state) . '"'; ?> />

                                                    <p class="errorMessage" id="pStateError<?php echo $guestId; ?>"></p>
                                                </div>
                                                <div class="labelDiv">
                                                    <label for="txtZip<?php echo $guestId; ?>">
                                                        Zip / Postal*:
                                                    </label>
                                                </div>
                                                <div class="fieldDiv">
                                                    <input type="text" id="txtZip<?php echo $guestId; ?>"
                                                           value="<?php echo $zip; ?>"/>

                                                    <p class="errorMessage" id="pZipError<?php echo $guestId; ?>"></p>
                                                </div>
                                                <div class="clear"></div>
                                            </div>
                                        <?php endif; ?>
                                        <?php if ($guest['user_type_id'] <= 3) : ?>
                                            <div class="formSectionHeader">
                                                Emergency info
                                            </div>
                                            <hr/>
                                            <div class="clear"></div>
                                            <?php if (!$guest['primary']) : ?>
                                                <div class="labelDiv">
                                                </div>
                                                <div class="fieldDiv">
                                                    <input type="checkbox"
                                                           id="chkUseSameEmergencyInfo<?php echo $guestId; ?>"
                                                           onclick="document.getElementById('divEmergencyContact<?php echo $guestId; ?>').style.display = (this.checked) ? 'none' : 'block';" <?php if ($user == null) echo 'checked="checked"'; ?> />
                                                    <label for="chkUseSameEmergencyInfo<?php echo $guestId; ?>">Use same
                                                        Emergency info</label>
                                                </div>
                                                <div class="clear"></div>
                                            <?php endif; ?>
                                            <div
                                                id="divEmergencyContact<?php echo $guestId; ?>" <?php if ((!$guest['primary']) and ($user == null)) echo 'style="display: none;"'; ?>>
                                                <div class="labelDiv">
                                                    <label for="txtEmergencyContact<?php echo $guestId; ?>">
                                                        Contact name*:
                                                    </label>
                                                </div>
                                                <div class="fieldDiv">
                                                    <input type="text" id="txtEmergencyContact<?php echo $guestId; ?>"
                                                           value="<?php echo $emergencyContact; ?>"/>

                                                    <p class="errorMessage"
                                                       id="pEmergencyContactError<?php echo $guestId; ?>"></p>
                                                </div>
                                                <div class="labelDiv">
                                                    <label for="txtEmergencyRelation<?php echo $guestId; ?>">
                                                        Contact relation*:
                                                    </label>
                                                </div>
                                                <div class="fieldDiv">
                                                    <input type="text" id="txtEmergencyRelation<?php echo $guestId; ?>"
                                                           value="<?php echo $emergencyRelation; ?>"/>

                                                    <p class="errorMessage"
                                                       id="pEmergencyRelationError<?php echo $guestId; ?>"></p>
                                                </div>
                                                <div class="labelDiv">
                                                    <label for="txtEmergencyPhone<?php echo $guestId; ?>">
                                                        Contact phone*:
                                                    </label>
                                                </div>
                                                <div class="fieldDiv">
                                                    <input type="text" id="txtEmergencyPhone<?php echo $guestId; ?>"
                                                           value="<?php echo $emergencyPhone; ?>"/>

                                                    <p class="errorMessage"
                                                       id="pEmergencyPhoneError<?php echo $guestId; ?>"></p>
                                                </div>
                                                <div class="clear"></div>
                                            </div>
                                        <?php endif; ?>
                                        <?php if ($guest['user_type_id'] <= 2) : ?>
                                            <div class="formSectionHeader">
                                                Food Specifications
                                            </div>
                                            <hr/>
                                            <div class="clear"></div>
                                            <div class="labelDiv">

                                                <label for="txtNotes<?php echo $guestId; ?>">
                                                    Please note any special considerations that should be taken into account (e.g. food allergies, vegetarian, etc.):<br/><br/>
                                                    Please note, there is cross contamination, we are not a gluten free or nut free facility.
													</label>
                                            </div>
                                            <div class="fieldDiv">

                                                <?php

                                                $diets = Array(
                                                    Array('value'=>'Vegetarian', 'label' => 'Vegetarian'),
                                                    Array('value'=>'Vegan', 'label' => 'Vegan'),
                                                    Array('value'=>'Lactose Intolerant', 'label' => 'Lactose Intolerant'),
                                                    Array('value'=>'Gluten Free', 'label' => 'Gluten Free'),
                                                    //Array('value'=>'Alergies', 'label' => 'Alergies'),
                                                    //Array('value'=>'Other', 'label' => 'Other'),



                                                );

                                                $dietNotes = json_decode($notes);

                                                ?>



                                                <?php foreach ($diets as $d): ?>

                                                <div class="form-check py-1">
                                                    <input type="checkbox" class="form-check-input" value="<?php echo $d['value']; ?>" id="diet-<?php echo $guestId.'-'.$d['value']; ?>" <?php echo isset($dietNotes) && isset($dietNotes->{$d['value']}) && $dietNotes->{$d['value']} == '1'  ? 'checked' : ''; ?> >
                                                    <label class="form-check-label" for="diet-<?php echo $guestId; ?>"><?php echo $d['label']; ?></label>
                                                </div>

                                            <?php endforeach; ?>



                                                <!-- <div class="form-group">
                                                    <label class="pt-3">Notes</label>
                                                    <textarea
                                                        id="txtNotes<?php echo $guestId; ?>"><?php echo isset($dietNotes)  && isset($dietNotes->notes) ? $dietNotes->notes : $notes; ?></textarea>
                                                </div> -->
                                            </div>
                                            <div class="formSectionHeader">
                                                Misc info
                                            </div>
                                            <hr/>
                                            <div class="labelDiv">
                                                <label for="txtReferredBy<?php echo $guestId; ?>">
                                                    How did you hear about this event?
                                                </label>
                                            </div>
                                            <div class="fieldDiv">
                                                <input type="text" id="txtReferredBy<?php echo $guestId; ?>"
                                                       value="<?php echo $referredBy; ?>"/>
                                            </div>
                                        <?php endif; ?>
                                        <?php if ($guest['primary']) : ?>
                                            <div class="labelDiv">
                                                <label for="txtJliStudent<?php echo $guestId; ?>">
                                                    Are you a current or past JLI student?
                                                </label>
                                            </div>
                                            <div class="fieldDiv">
                                                <input type="text" id="txtJliStudent<?php echo $guestId; ?>"
                                                       value="<?php echo $jliStudent; ?>"/>
                                            </div>
                                        <?php endif; ?>
                                        <?php if ($guest['user_type_id'] <= 2) : ?>
                                            <div class="labelDiv">
                                                <label for="txtShliach<?php echo $guestId; ?>">
                                                    What is the name of your local Rabbi/Rebbetzin?
                                                </label>
                                            </div>
                                            <div class="fieldDiv">
                                                <input type="text" id="txtShliach<?php echo $guestId; ?>"
                                                       value="<?php echo $shliach; ?>"/>
                                            </div>
                                            <div class="labelDiv">
                                                <label for="txtAdditionalNotes<?php echo $guestId; ?>">
                                                    Additional Notes
                                                </label>
                                            </div>
                                            <div class="fieldDiv">
                                                <textarea
                                                    id="txtAdditionalNotes<?php echo $guestId; ?>"><?php echo $additionalNotes; ?></textarea>
                                            </div>
                                        <?php endif; ?>
                                        <div class="clear"></div>
                                    </div>
                                    <button type="submit" class="saveButton"
                                            onclick="saveGuestInformation('<?php echo $guestId; ?>'); return false;">
                                        Save
                                    </button>
                                    <button type="button" class="editButton" style="display: none;"
                                            onclick="makeGuestFormActive('<?php echo $guestId; ?>');">Edit
                                    </button>
                                    <div class="clear"></div>
                                </form>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php endforeach; ?>
        <form method="post">
            <button type="submit" class="nextButton disabled" disabled="disabled" name="submit" value="Next">Next
            </button>
        </form>
        <div class="clear"></div>
    </div>
</div>

<div id="login-popup" class="popup sky-form">

    <div class="popup-content">
        <span class="close-popup">&#215;</span>

        <h1>Returning Guests</h1>

        <div id="email-login" class="toggle-panel active">
            <p>Sign in to speed up the checkout process</p>
            <section>
                <label class="label"><strong>Email address</strong></label>
                <label class="input" for="login_email">
                    <i class="icon-prepend fa fa-envelope"></i>
                    <input id="login_email" type="email" name="login_email" autocomplete="off" />
                </label>
            </section>
            <button id="verify-email" type="button" class="button button-secondary">Next</button>
            <section>
                <p><a class="toggle-link" data-target="#phone-login" href="#">Login using your phone number</a></p>
                <p><a class="close-popup" href="#">Continue without signing in</a></p>
            </section>
        </div>

        <div id="phone-login" class="toggle-panel">
            <p>Sign in to speed up the checkout process</p>
            <section>
                <label class="label"><strong>Phone Number</strong></label>
                <label class="input" for="login_phone">
                    <input id="login_phone" class="phone" type="tel" name="login_phone" autocomplete="off" autofocus="on" />
                    <input id="login_phone_code" type="hidden" name="login_phone_code">
                </label>
            </section>
            <button id="verify-phone" type="button" class="button button-secondary">Next</button>
            <section>
                <p><a class="toggle-link" data-target="#email-login" href="#">Login using your email address</a></p>
                <p><a class="close-popup" href="#">Continue without signing in</a></p>
            </section>
        </div>

        <div id="password-login" class="toggle-panel">
            <p>Login as <strong class="account-email"></strong>  <a class="toggle-link" data-target="#email-login" href="#">Change</a></p>
            <section>
                <label class="label"><strong>Enter your password</strong></label>
                <label class="input" for="login_password">
                    <input id="login_password" type="password" name="login_password" autocomplete="off" />
                </label>
            </section>
            <button type="button" class="button button-secondary login-button">Login</button>
            <section>
                <p><a id="email-verify" class="toggle-link" data-target="#email-verify-login" href="#">Forgot password?</a></p>
                <p><a class="close-popup" href="#">Continue without signing in</a></p>
            </section>
        </div>

        <div id="multiple-account-login" class="toggle-panel">
            <section>
                <label class="label">The <strong class="account-email-phone"></strong> is associated with multiple people. To proceed please select your name from the following list:</label>
                <div class="row account-list">
                    <div class="col col-10 account-item">
                        <label class="radio" for="login_user_id_0">
                            <input id="login_user_id_0" type="radio" name="login_user_id" value="" required="required">
                            <i></i><span></span>
                        </label>
                    </div>
                </div>
                <input type="hidden" name="login_token">
            </section>
            <button type="button" class="button button-secondary login-button">Next</button>
            <section>
                <p><a class="close-popup" href="#">Continue without signing in</a></p>
            </section>
        </div>

        <div id="email-verify-login" class="toggle-panel">
            <p>We've sent a verification code to <strong class="account-email"></strong>, please enter it below.</p>
            <section>
                <label class="label"><strong>Your verification code</strong></label>
                <label class="input" for="login_verification_code_email">
                    <input id="login_verification_code_email" type="text" name="login_verification_code" />
                </label>
            </section>
            <button type="button" class="button button-secondary verify-code">Verify</button>
            <section>
                <p>Please do not close or refresh this page while you check for your code.</p>
            </section>
            <footer>
                <p>Can't find the code? <a class="close-popup" href="#">Continue without signing in</a></p>
            </footer>
        </div>

        <div id="phone-sms-verify-login" class="toggle-panel">
            <p>We've send a text messsage with a verification <strong class="account-phone"></strong>, please enter it below.</p>
            <section>
                <label class="label"><strong>Your verification code</strong></label>
                <label class="input" for="login_verification_code_sms">
                    <input id="login_verification_code_sms" type="text" name="login_verification_code" />
                </label>
            </section>
            <section>
                <p>Or we can <a id="phone-call-verify" class="toggle-link" data-target="#phone-call-verify-login">call you instead.</a></p>
            </section>
            <button type="button" class="button button-secondary verify-code">Verify</button>
            <section>
                <p>Please do not close or refresh this page while you check for your code.</p>
            </section>
            <footer>
                <p>Can't find the code? <a class="close-popup" href="#">Continue without signing in</a></p>
            </footer>
        </div>

        <div id="phone-call-verify-login" class="toggle-panel">
            <p>We've calling <strong class="account-phone"></strong>. Please listen to the verification code and enter it below.</p>
            <section>
                <label class="label"><strong>Your verification code</strong></label>
                <label class="input" for="login_verification_call">
                    <input id="login_verification_call" type="text" name="login_verification_code" />
                </label>
            </section>
            <button type="button" class="button button-secondary verify-code">Verify</button>
            <section>
                <p>Please do not close or refresh this page while you check for your code.</p>
            </section>
            <footer>
                <p>Can't find the code? <a class="close-popup" href="#">Continue without signing in</a></p>
            </footer>
        </div>

    </div>
</div>
<?php

// Display footer.
include('footer.php');

?>
