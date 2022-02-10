<form action="/retreat/register/guestRegistrationForm.php" id="reservation-form" class="sky-form" method="post">
    <input id="basic-dates" type="hidden" value="<?php echo implode(',', $basicDates) ?>">
    <input id="package-dates" type="hidden" value="<?php echo implode(',', $packageDates) ?>">
    <input type="hidden" name="is_admin" value="<?php echo $isAdmin ?>" />

    <div id="rooming-form">
        <?php if (!empty($formData)): ?>
            <?php foreach ($formData as $formId => $data): ?>
                <header id="header-form-<?php echo $formId ?>">Room #<span class="numb"><?php echo $formId + 1 ?></span>
                    <?php if ($formId > 0): ?>
                        <button data-room_id="<?php echo $formId ?>"
                                id="remove-room-<?php echo $formId ?>"
                                type="button" class="remove-room button button-secondary">X</button>
                    <?php endif; ?>
                </header>
                <?php $this->renderPartial('_form', array(
                    'formId' => $formId, 'suite' => $suite, 'data' => $data, 'packageDates' => $packageDates,
                    'isAdmin' => $isAdmin
                )); ?>
            <?php endforeach; ?>
        <?php else: ?>
            <header id="header-form-<?php echo $formId ?>">Room #<span class="numb">1</span></header>
            <?php $this->renderPartial('_form', array(
                'formId' => $formId, 'suite' => $suite, 'data' => array(), 'packageDates' => $packageDates,
                'isAdmin' => $isAdmin, 'formData' => $formData
            )); ?>
        <?php endif; ?>
    </div>
    <footer>
        <button id="submit" type="button" class="button">Next</button>
        <button id="add-room" type="button" class="button button-secondary">Add a Room</button>
    </footer>
</form>
<script src="<?php echo BASE_URL ?>js/jquery.rooming-form.js"></script>

<div id="login-popup" class="popup sky-form">

    <div class="popup-content">
        <span class="close-popup">×</span>

        <h1>Returning Guests</h1>

        <div id="email-login" class="toggle-panel active">
            <p>Sign in to speed up the checkout process</p>
            <section>
                <label class="label"><strong>Email address</strong></label>
                <label class="input" for="login_email">
                    <i class="icon-prepend fa fa-envelope"></i>
                    <input id="login_email" type="email" name="login_email" autocomplete="off" value="" />
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
                    <input id="login_phone_code" type="hidden" name="login_phone_code" value="">
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
                    <input id="login_password" type="password" name="login_password" autocomplete="off" value="" />
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
                    <input id="login_verification_code_email" type="text" name="login_verification_code" value="" />
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
                    <input id="login_verification_code_sms" type="text" name="login_verification_code" value="" />
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
                    <input id="login_verification_call" type="text" name="login_verification_code" value="" />
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
