<?php

// Set template variables.
$templateVariables['page'] = 'student';
$templateVariables['title'] = 'Student Registration';
$templateVariables['jqueryUi'] = true;

// Display header.
include('header.php');

?>
<link rel="stylesheet" type="text/css" href='<?php echo BASE_URL; ?>/css/student.css'></script>
<script src='<?php echo BASE_URL; ?>/js/student.js'></script>
<script src='<?php echo BASE_URL; ?>/js/formValidator.js'></script>
<script>
    var unitedStatesCountryId = <?php echo UNITED_STATES_COUNTRY_ID; ?>;
    var validImageRegex = /\.(<?php echo implode('|', $templateVariables['validImageExtensions']); ?>)$/i;
</script>

<div class="pageContent">
    <h1 class="pageHeader">
        Student Registration
    </h1>
    <div id="divOrder">
        <div class="roomDiv">
            <div class="contentDiv">
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
                <form method="post" enctype="multipart/form-data" onsubmit="return validateStudentForm();">
                    <div class="clear"></div>
                    <div class="formSectionHeader">
                        Personal info
                    </div>
                    <hr/>
                    <div class="clear"></div>
                    <div class="labelDiv">
                        <label for="txtName">
                            Full name*:
                        </label>
                    </div>
                    <div class="fieldDiv">
                        <input type="text" id="txtName" name="name"
                               value="<?php if(isset($_POST['name'])) echo htmlentities($_POST['name']); ?>"/>
                        <p class="fieldExplanation">
                            First name, middle name, last name
                        </p>
                        <p class="errorMessage" id="pNameError"></p>
                    </div>
                    <div class="labelDiv">
                        <label for="selectCampusId">
                            University*:
                        </label>
                    </div>
                    <div class="fieldDiv">
                        <select id="selectCampusId" name="campusId">
                            <option value="0">Choose One</option>
                            <?php foreach($templateVariables['campuses'] as $campusId => $campus) : ?>
                                <option
                                    value="<?php echo $campusId; ?>" <?php if (isset($_POST['campusId']) and ($_POST['campusId'] == $campusId)) echo 'selected="selected"'; ?>>
                                    <?php echo htmlentities($campus); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <p class="errorMessage" id="pCampusIdError"></p>
                    </div>
                    <div class="labelDiv">
                        <label for="selectStudentStatus">
                            Current Status as of Spring 2017*:
                        </label>
                    </div>
                    <div class="fieldDiv">
                        <?php $studentStatuses = [0 => 'I am a', 1 => 'Freshman', 2 => 'Sophomore', 3 => 'Junior', 4 => 'Senior', 5 => 'Graduate Student', 6 => 'Working']; ?>
                        <select id="selectStudentStatus" name="studentStatus">
                            <?php foreach($studentStatuses as $key => $studentStatus) : ?>
                                <option
                                    value="<?php echo $key; ?>" <?php if (isset($_POST['studentStatus']) and ($_POST['studentStatus'] == $key)) echo 'selected="selected"'; ?>>
                                    <?php echo htmlentities($studentStatus); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <p class="errorMessage" id="pStudentStatusError"></p>
                    </div>
                    <div class="labelDiv">
                        <label for="selectGender">
                            Gender*:
                        </label>
                    </div>
                    <div class="fieldDiv">
                        <select name="gender" id="selectGender">
                            <?php $gender = ['' => '', 'm' => 'Male', 'f' => 'Female']; ?>
                            <?php foreach($gender as $key => $value) : ?>
                                <option value="<?php echo $key; ?>" <?php if(isset($_POST['gender']) and ($_POST['gender'] == $key)) echo 'selected="selected"'; ?>>
                                    <?php echo htmlentities($value); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <p class="errorMessage" id="pGenderError"></p>
                    </div>
                    <div class="labelDiv">
                        <label for="txtEmail">
                            Email*:
                        </label>
                    </div>
                    <div class="fieldDiv">
                        <input type="text" name="email" id="txtEmail"
                               value="<?php if(isset($_POST['email'])) echo htmlentities($_POST['email']); ?>"/>
                        <p class="errorMessage" id="pEmailError"></p>
                    </div>
                    <div class="labelDiv">
                        <label for="txtCellPhone">
                            Cell phone*:
                        </label>
                    </div>
                    <div class="fieldDiv">
                        <input type="text" name="cellPhone" id="txtCellPhone"
                               value="<?php if(isset($_POST['cellPhone'])) echo htmlentities($_POST['cellPhone']); ?>"/>
                        <p class="errorMessage" id="pCellPhoneError"></p>
                    </div>
                    <div class="labelDiv">
                        <label for="txtDob">
                            Date of birth*:
                        </label>
                    </div>
                    <div class="fieldDiv">
                        <input type="text" class="dob" name="dob" id="txtDob"
                               value="<?php if(isset($_POST['dob'])) echo htmlentities($_POST['dob']); ?>"/>
                        <p class="errorMessage" id="pDobError"></p>
                    </div>
                    <div class="labelDiv">
                        <label for="fileProfilePhoto">
                            Profile Photo*:
                        </label>
                    </div>
                    <div class="fieldDiv">
                        <?php if(!empty($_SESSION['retreat']['student']['profilePhoto']['name'])) echo htmlentities($_SESSION['retreat']['student']['profilePhoto']['name']).'<br />'; ?>
                        <input type="file" accept="image/*" name="profilePhoto" id="fileProfilePhoto" onchange="validateImage(this);" />
                        <img id="imgProfilePhoto" src="<?php if(!empty($_SESSION['retreat']['student']['profilePhoto']['name'])) echo BASE_URL.'student.php?action=getImage'; ?>" style="display: <?php echo (empty($_SESSION['retreat']['student']['profilePhoto']['name']) ? 'none' : 'block'); ?>;">
                        <p class="errorMessage" id="pProfilePhotoError"></p>
                    </div>
                    <div class="clear"></div>
                    <div class="formSectionHeader">
                        Permanent Address
                    </div>
                    <hr/>
                    <div class="clear"></div>
                    <div class="labelDiv">
                        <label for="txtAddress">
                            Address*:
                        </label>
                    </div>
                    <div class="fieldDiv">
                        <input type="text" name="address" id="txtAddress"
                               value="<?php if(isset($_POST['address'])) echo htmlentities($_POST['address']); ?>"/>
                        <p class="errorMessage"
                           id="pAddressError"></p>
                    </div>
                    <div class="labelDiv">
                        <label for="txtCity">
                            City*:
                        </label>
                    </div>
                    <div class="fieldDiv">
                        <input type="text" name="city" id="txtCity"
                               value="<?php if(isset($_POST['city'])) echo htmlentities($_POST['city']); ?>"/>
                        <p class="errorMessage" id="pCityError"></p>
                    </div>
                    <div class="labelDiv">
                        <label for="selectCountryId">
                            Country*:
                        </label>
                    </div>
                    <div class="fieldDiv">
                        <select name="countryId" id="selectCountryId"
                                onchange="countryChanged();">
                            <?php foreach ($templateVariables['countries'] as $currentCountryId => $currentCountry) : ?>
                                <option value="<?php echo $currentCountryId; ?>"
                                    <?php if(isset($_POST['countryId']) and ($currentCountryId == $_POST['countryId'])) echo 'selected="selected"'; ?>
                                    <?php if((!isset($_POST['countryId'])) and ($currentCountryId == UNITED_STATES_COUNTRY_ID)) echo 'selected="selected"'; ?>
                                >
                                    <?php echo htmlentities($currentCountry); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <p class="errorMessage"
                           id="pCountryIdError"></p>
                    </div>
                    <div class="labelDiv">
                        <label for="txtState">
                            State / Province*:
                        </label>
                    </div>
                    <div class="fieldDiv">
                        <select name="stateSelect"
                                id="selectState" <?php if(isset($_POST['countryId']) and ($_POST['countryId'] != UNITED_STATES_COUNTRY_ID)) echo 'style="display: none;"'; ?>>
                            <option
                                value="" <?php if(isset($_POST['stateSelect']) and ($_POST['stateSelect'] == '')) echo 'selected="selected"'; ?>>
                                Please select
                            </option>
                            <?php foreach ($templateVariables['states'] as $currentState) : ?>
                                <option
                                    value="<?php echo htmlentities($currentState); ?>" <?php if (isset($_POST['stateSelect']) and ($_POST['stateSelect'] == $currentState)) echo 'selected="selected"'; ?>>
                                    <?php echo htmlentities($currentState); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <input type="text" name="stateTxt"
                               id="txtState" <?php if((!isset($_POST['countryId'])) or ($_POST['countryId'] == UNITED_STATES_COUNTRY_ID)) echo 'style="display: none;"'; else echo 'value="' . (isset($_POST['stateTxt']) ? $_POST['stateTxt'] : '') . '"'; ?> />
                        <p class="errorMessage" id="pStateError"></p>
                    </div>
                    <div class="labelDiv">
                        <label for="txtZip">
                            Zip / Postal*:
                        </label>
                    </div>
                    <div class="fieldDiv">
                        <input type="text" name="zip" id="txtZip"
                               value="<?php if(isset($_POST['zip'])) echo htmlentities($_POST['zip']); ?>"/>
                        <p class="errorMessage" id="pZipError"></p>
                    </div>
                    <div class="clear"></div>
                    <div class="formSectionHeader">
                        Emergency info
                    </div>
                    <hr/>
                    <div class="clear"></div>
                    <div class="labelDiv">
                        <label for="txtEmergencyContact">
                            Contact name*:
                        </label>
                    </div>
                    <div class="fieldDiv">
                        <input type="text" name="emergencyContact" id="txtEmergencyContact"
                               value="<?php if(isset($_POST['emergencyContact'])) echo htmlentities($_POST['emergencyContact']); ?>"/>
                        <p class="errorMessage" id="pEmergencyContactError"></p>
                    </div>
                    <div class="labelDiv">
                        <label for="txtEmergencyRelation">
                            Contact relation*:
                        </label>
                    </div>
                    <div class="fieldDiv">
                        <input type="text" name="emergencyRelation" id="txtEmergencyRelation"
                               value="<?php if(isset($_POST['emergencyRelation'])) echo htmlentities($_POST['emergencyRelation']); ?>"/>
                        <p class="errorMessage" id="pEmergencyRelationError"></p>
                    </div>
                    <div class="labelDiv">
                        <label for="txtEmergencyPhone">
                            Contact phone*:
                        </label>
                    </div>
                    <div class="fieldDiv">
                        <input type="text" name="emergencyPhone" id="txtEmergencyPhone"
                               value="<?php if(isset($_POST['emergencyPhone'])) echo htmlentities($_POST['emergencyPhone']); ?>"/>
                        <p class="errorMessage" id="pEmergencyPhoneError"></p>
                    </div>
                    <div class="clear"></div>
                    <div class="formSectionHeader">
                        
                    </div>
                    <hr/>
                    <div class="clear"></div>
                    <div class="labelDiv">
                        <label for="txtNotes">
                            Notes
                        </label>
                    </div>
                    <div class="fieldDiv">
                        <textarea
                            name="notes" id="txtNotes" style="height: 40px;"><?php if(isset($_POST['notes'])) echo htmlentities($_POST['notes']); ?></textarea>
                        <p class="fieldExplanation">
                            Please note any special considerations that should be taken into
                            account (e.g. food allergies, vegetarian, etc.):
                        </p>
                    </div>
                    <div class="formSectionHeader">
                        Other information
                    </div>
                    <hr/>
                    <div class="clear"></div>
                    <!--<div class="labelDiv">
                        <label for="txtAdditionalNotes">
                            Additional Notes
                        </label>
                    </div>
                    <div class="fieldDiv">
                        <textarea
                            name="additional_notes" id="txtAdditionalNotes"><?php if(isset($_POST['additional_notes'])) echo htmlentities($_POST['additional_notes']); ?></textarea>
                    </div>-->
                    <div class="labelDiv">
                        <label for="txtImpact">
                        In what way did the Sinai Scholars program impact you?*
                        </label>
                    </div>
                    <div class="fieldDiv">
                        <textarea
                            name="impact" id="txtImpact"><?php if(isset($_POST['impact'])) echo htmlentities($_POST['impact']); ?></textarea>
                        <p class="errorMessage" id="pImpactError"></p>
                    </div>
                    <div class="labelDiv">
                        <label for="txtHopeToGain">
                            What do you hope to gain if accepted to this program?*
                        </label>
                    </div>
                    <div class="fieldDiv">
                        <textarea
                            name="hopeToGain" id="txtHopeToGain"><?php if(isset($_POST['hopeToGain'])) echo htmlentities($_POST['hopeToGain']); ?></textarea>
                        <p class="errorMessage" id="pHopeToGainError"></p>
                    </div>
                    <div class="labelDiv">
                        <label for="txtPreviousExperience">
                            Please share your "Jewish journey", for example, how you came to Chabad, a special Jewish moment you experienced, etc.*:
                        </label>
                    </div>

                    <div class="fieldDiv">
                        <textarea
                            name="previousExperience" id="txtPreviousExperience"><?php if(isset($_POST['previousExperience'])) echo htmlentities($_POST['previousExperience']); ?></textarea>
                        <p class="errorMessage" id="pPreviousExperienceError"></p>
                    </div>
                    <h2 class="clear" style="max-width: 80%;padding: 20px 15px;">
                        Please share one sentence with your thought about the following topics. Your thoughts will help us create the program and classes at the event.
                    </h2>
                    <div class="labelDiv">
                        <label for="txtThoughts">
                        Marrying Jewish*
                        </label>
                    </div>
                    <div class="fieldDiv">
                        <textarea
                            name="thoughts[marriage]" id="txtMarriage" style="height: 40px;"><?php if(isset($_POST['thoughts']['marriage'])) echo htmlentities($_POST['thoughts']['marriage']); ?></textarea>
                        <p class="errorMessage" id="pMarriageError"></p>
                    </div>

                    <div class="labelDiv">
                        <label for="txtThoughts">
                        Shabbat*
                        </label>
                    </div>
                    <div class="fieldDiv">
                        <textarea
                            name="thoughts[shabbat]" id="txtShabbat" style="height: 40px;"><?php if(isset($_POST['thoughts']['shabbat'])) echo htmlentities($_POST['thoughts']['shabbat']); ?></textarea>
                        <p class="errorMessage" id="pShabbatError"></p>
                    </div>

                    <div class="labelDiv">
                        <label for="txtThoughts">
                        Torah Study*
                        </label>
                    </div>
                    <div class="fieldDiv">
                        <textarea
                            name="thoughts[torahstudy]" id="txtTorahStudy" style="height: 40px;"><?php if(isset($_POST['thoughts']['torahstudy'])) echo htmlentities($_POST['thoughts']['torahstudy']); ?></textarea>
                        <p class="errorMessage" id="pTorahStudyError"></p>
                    </div>

                    <div class="labelDiv">
                        <label for="txtThoughts">
                        The Jewish Community*
                        </label>
                    </div>
                    <div class="fieldDiv">
                        <textarea
                            name="thoughts[jewishcommunity]" id="txtJewishCommunity" style="height: 40px;"><?php if(isset($_POST['thoughts']['jewishcommunity'])) echo htmlentities($_POST['thoughts']['jewishcommunity']); ?></textarea>
                        <p class="errorMessage" id="pJewishCommunityError"></p>
                    </div>

                    <div class="labelDiv">
                        <label for="txtThoughts">
                        Jewish Holidays*
                        </label>
                    </div>
                    <div class="fieldDiv">
                        <textarea
                            name="thoughts[jewishholidays]" id="txtJewishHolidays" style="height: 40px;"><?php if(isset($_POST['thoughts']['jewishholidays'])) echo htmlentities($_POST['thoughts']['jewishholidays']); ?></textarea>
                        <p class="errorMessage" id="pJewishHolidaysError"></p>
                    </div>

                    <div class="labelDiv">
                        <label for="txtThoughts">
                        Charity*
                        </label>
                    </div>
                    <div class="fieldDiv">
                        <textarea
                            name="thoughts[charity]" id="txtCharity" style="height: 40px;"><?php if(isset($_POST['thoughts']['charity'])) echo htmlentities($_POST['thoughts']['charity']); ?></textarea>
                        <p class="errorMessage" id="pCharityError"></p>
                    </div>

                    <div class="labelDiv">
                        <label for="txtThoughts">
                        God*
                        </label>
                    </div>
                    <div class="fieldDiv">
                        <textarea
                            name="thoughts[god]" id="txtGod" style="height: 40px;"><?php if(isset($_POST['thoughts']['god'])) echo htmlentities($_POST['thoughts']['god']); ?></textarea>
                        <p class="errorMessage" id="pGodError"></p>
                    </div>

                    <div class="labelDiv">
                        <label for="txtThoughts">
                        Jewish Rituals*
                        </label>
                    </div>
                    <div class="fieldDiv">
                        <textarea
                            name="thoughts[jewishpractices]" id="txtJewishPractices" style="height: 40px;"><?php if(isset($_POST['thoughts']['jewishpractices'])) echo htmlentities($_POST['thoughts']['jewishpractices']); ?></textarea>
                        <p class="errorMessage" id="pJewishPracticesError"></p>
                    </div>

                    <div class="labelDiv">
                        <label for="txtThoughts">
                        Israel*
                        </label>
                    </div>
                    <div class="fieldDiv">
                        <textarea
                            name="thoughts[israel]" id="txtIsrael" style="height: 40px;"><?php if(isset($_POST['thoughts']['israel'])) echo htmlentities($_POST['thoughts']['israel']); ?></textarea>
                        <p class="errorMessage" id="pIsraelError"></p>
                    </div>
                   
                    <hr class="clear">
                    <hr class="clear">

                    <div class="labelDiv">
                        <label for="txtQuestions">
                        What questions would you like answered about Judaism?*
                        </label>
                    </div>
                    <div class="fieldDiv">
                        <textarea
                            name="questions" id="txtQuestions"><?php if(isset($_POST['questions'])) echo htmlentities($_POST['questions']); ?></textarea>
                        <p class="errorMessage" id="pQuestionsError"></p>
                    </div>
                    <div class="labelDiv">
                        <label for="txtGrow">
                        In which area of Judaism are you hoping to grow?*
                        </label>
                    </div>
                    <div class="fieldDiv">
                        <textarea
                            name="grow" id="txtGrow"><?php if(isset($_POST['grow'])) echo htmlentities($_POST['grow']); ?></textarea>
                        <p class="errorMessage" id="pGrowError"></p>
                    </div>
                    <div class="clear"></div>
                    <div class="formSectionHeader">
                        Previous attendance
                    </div>
                    <hr/>
                    <div class="clear"></div>
                    <div class="labelDiv">
                        <label>
                            Which previous Retreats have you attended?<br />
                            Please check all that apply:
                        </label>
                    </div>
                    <div class="fieldDiv">
                        <?php foreach($years as $yearId => $year) : ?>
                            <input type="checkbox" name="previousYears[]" value="<?php echo $yearId; ?>"
                                <?php if(isset($_POST['previousYears']) and (in_array($yearId, $_POST['previousYears']))) echo 'checked="checked"'; ?>>
                            <?php echo htmlentities($year); ?><br />
                        <?php endforeach; ?>
                    </div>
                    <div class="labelDiv">
                    </div>
                    <div class="fieldDiv">
                        <h3>
                            If you attended a previous Retreat, and are applying for the second time, please see below for eligibility.
                        </h3>
                        <p>
                            All returning students must have participated in a course at their local Chabad post the previous Retreat.
                        </p>
                    </div>
                    <div class="labelDiv">
                        <label for="txtClasses">

                        </label>
                    </div>
                    <div class="fieldDiv">
                        <p class="">
                            Please tell us about the classes you attended.
                        </p>
                <textarea
                    name="classes" id="txtClasses"><?php if(isset($_POST['classes'])) echo htmlentities($_POST['classes']); ?></textarea>
                    </div>
                    <div class="fieldDiv">
                        <p class="">
                        Please tell us how you "shared Aleph" since the retreat.
                        </p>
                <textarea
                    name="aleph" id="txtAleph"><?php if(isset($_POST['aleph'])) echo htmlentities($_POST['aleph']); ?></textarea>
                    </div>
                    <div class="labelDiv">
                    </div>
                    <div class="fieldDiv">
                        <h3>
                            Program Cost:
                        </h3>
                        <p>
                            The Sinai Scholars Retreat, including program costs, and accommodations, are being offered with a significant scholarship to a limited number of students on each campus. In addition, a travel stipend is available to current students.
                        </p>
                        <h3>
                            Please note:
                        </h3>
                        <p>
                            Students are responsible to book their own travel arrangements.
                        </p>
                        <p>
                            Sinai Scholars Society will pay to reserve rooms for students requesting to join us for this retreat. Hence, we will incur a loss for students who fail to arrive. Students who are accepted and confirm their attendance, will be given until a specific date to cancel. After this date, cancellations will result in a $250 charge.
                        </p>
                    </div>
                    <div class="clear"></div>
                    <button type="submit" class="saveButton">Submit application</button>
                    <div class="clear"></div>
                </form>
            </div>
        </div>
    </div>
</div>
<?php

// Display footer.
include('footer.php');

?>
