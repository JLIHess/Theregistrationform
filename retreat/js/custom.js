(function ( $ ) {

    var formData = {};

    $(document).ready(function() {

        $(document).on('click', '*[data-toggle="popup"]', function() {
            $($(this).data('target')).popup();
        });

        let input = document.querySelector(".phone");

        if (input) {
            var intTel = window.intlTelInput(input, {
                initialCountry: "auto",
                geoIpLookup: function(callback) {
                    $.get('https://ipinfo.io', function() {}, "jsonp").always(function(resp) {
                        let countryCode = (resp && resp.country) ? resp.country : "";
                        callback(countryCode);
                    });
                },
                utilsScript: "/retreat_new/js/intl-tel-input/js/utils.js?1537727621611"
            });

            input.addEventListener('countrychange', function(e) {
                let data = intTel.getSelectedCountryData();
                if (data) {
                    $('#login_phone_code').val(data.dialCode);
                }
            });
        }

        $(document).on('click', '.toggle-link', function() {

            let panel = $(this).parents('.toggle-panel').eq(0),
                target = $(this).data('target'),
                errors = panel.getErrors();

            if (errors.length) {
                panel.showErrors(errors);
            } else {
                $(target).showPanel();
            }
        });

        $(document).on('click', '#verify-email', function() {

            let loginEmail = $('[name="login_email"]').val();

            $.ajax({
                url: '/retreat_new/index.php/auth/verifyEmail',
                data: {login_email: loginEmail},
                type: 'post',
                dataType: 'json',
                success: function (response) {

                    $('.toggle-panel.active').hideErrors();
                    $('.account-email-phone').text(loginEmail);
                    $('.account-email').text(loginEmail);

                    formData.email = loginEmail;

                    if (response.status && response.status == 'error' && response.errors) {
                        $('.toggle-panel.active').showErrors(response.errors);
                    } else {
                        $('#password-login').showPanel();
                    }
                }
            });
        });

        $(document).on('change', '#multiple-account-login input[type="radio"]', function() {

            let label = $(this).parents('.toggle-panel').eq(0).find('label[for="' + $(this).attr('id') + '"]'),
                text = label.find('span');

            if (text.length) {
                $('#password-login .account-name').text(text.text());
            }
        });

        $(document).on('click', '.login-button', function() {

            let loginEmail = $('[name="login_email"]').val(),
                loginPassword = $('[name="login_password"]').val(),
                loginPhone = $('[name="login_phone"]').val(),
                loginUserId = $('[name="login_user_id"]:checked').val(),
                loginToken = $('[name="login_token"]').val();

            $.ajax({
                url: '/retreat_new/index.php/auth/login',
                data: {login_email: loginEmail, login_password: loginPassword, login_user_id: loginUserId, login_phone: loginPhone, login_token: loginToken},
                type: 'post',
                dataType: 'json',
                success: function (response) {
                    if (response.status && response.status == 'error' && response.errors) {
                        $('.toggle-panel.active').showErrors(response.errors);
                    } else if (response.redirect) {
                        window.location = response.redirect;
                    }
                }
            });
        });

        $(document).on('click', '#verify-phone', function() {
            let loginPhone = $('[name="login_phone"]').val(),
                loginPhoneCode = $('[name="login_phone_code"]').val();

            $.ajax({
                url: '/retreat_new/index.php/auth/verifyPhone',
                data: {login_phone: loginPhone, login_phone_code: loginPhoneCode},
                type: 'post',
                dataType: 'json',
                success: function(response) {
                    $('.toggle-panel.active').hideErrors();

                    if (response.status && response.status == 'error' && response.errors) {
                        $('.toggle-panel.active').showErrors(response.errors);
                    } else {

                        formData.phone = loginPhone;

                        $('.account-phone').text(loginPhone);
                        $('#phone-sms-verify-login').showPanel();

                        $.ajax({
                            url: '/retreat_new/index.php/auth/sendVerificationCode',
                            data: {login_phone: loginPhone, login_phone_code: loginPhoneCode, verify_type: 'sms'},
                            type: 'post',
                            dataType: 'json',
                            success: function(response) {
                                if (response.status && response.status == 'error' && response.errors) {
                                    $('.toggle-panel.active').showErrors(response.errors);
                                }
                            }
                        });
                    }
                }
            });
        });

        $(document).on('click', '#email-verify', function() {
            let loginEmail = $('[name="login_email"]').val();

            $.ajax({
                url: '/retreat_new/index.php/auth/sendVerificationCode',
                data: {login_email: loginEmail},
                type: 'post',
                dataType: 'json',
                success: function(response) {
                    if (response.status && response.status == 'error' && response.errors) {
                        $('.toggle-panel.active').showErrors(response.errors);
                    }
                }
            });
        });

        $(document).on('click', '#phone-call-verify', function() {
            let loginPhone = $('[name="login_phone"]').val(),
                loginPhoneCode = $('[name="login_phone_code"]').val();

            $.ajax({
                url: '/retreat_new/index.php/auth/sendVerificationCode',
                data: {login_phone: loginPhone, login_phone_code: loginPhoneCode, verify_type: 'call'},
                type: 'post',
                dataType: 'json',
                success: function(response) {
                    if (response.status && response.status == 'error' && response.errors) {
                        $('.toggle-panel.active').showErrors(response.errors);
                    }
                }
            });
        });

        $(document).on('click', '.verify-code', function() {

            let loginUserId = $('[name="login_user_id"]:checked').val(),
                loginCode = $('.toggle-panel.active [name="login_verification_code"]').val(),
                loginPhoneCode = $('[name="login_phone_code"]').val(),
                loginPhone = formData.phone,
                loginEmail = formData.email;

            $.ajax({
                url: '/retreat_new/index.php/auth/verifyCode',
                data: {login_verification_code: loginCode, login_user_id: loginUserId, login_phone: loginPhone, login_email: loginEmail, login_phone_code: loginPhoneCode},
                type: 'post',
                dataType: 'json',
                success: function (response) {
                    if (response.status && response.status == 'error') {
                        if (response.errors) {
                            $('.toggle-panel.active').showErrors(response.errors);
                        }
                    } else if (response.status && response.status == 'success') {

                        if (response.accounts && response.accounts.length > 1) {

                            let panel = $('#multiple-account-login');
                            let accountItem = panel.find('.account-item').eq(0),
                                copyAccount = accountItem.clone();

                            panel.find('button.toggle-link').data('target', '#password-login');
                            panel.find('.account-list').html('');
                            panel.find('input[name="login_token"]').val(response.token);

                            $.each(response.accounts, function (key, account) {
                                copyAccount = copyAccount.clone();

                                let input = copyAccount.find('input[type="radio"]');
                                input.attr('id', input.attr('name') + '_' + key);
                                input.val(account.user_id);
                                copyAccount.find('span').text(account.name);
                                copyAccount.find('label').attr('for', input.attr('name') + '_' + key);

                                panel.find('.account-list').append(copyAccount.outerHTML());
                            });

                            panel.showPanel();

                        } else if (response.redirect) {
                            window.location = response.redirect;
                        }
                    }
                }
            });
        });

        $(document).on('click', '.toggle-panel .close-popup', function() {
            $('#email-login').showPanel();
        });

        $(document).on('click', '#create-password-button', function(e) {

            e.preventDefault();

            let form = $('#create-password');

            $.ajax({
                url: '/retreat_new/index.php/auth/createPassword',
                data: form.serialize(),
                type: 'post',
                dataType: 'json',
                success: function(response) {
                    if (response.status && response.status == 'error' && response.errors) {

                        form.find('.is-invalid').each(function() {
                            $(this).removeClass('is-invalid');
                        });
                        form.find('.invalid-feedback').each(function() {
                            $(this).remove();
                        });

                        $.each(response.errors, function(k, error) {

                            let input = form.find('input[name="' + error.name + '"]');

                            if (input.length) {

                                input.addClass('is-invalid');
                                input.after($('<div/>').addClass('invalid-feedback').text(error.message));
                            }
                        });

                    } else if (response.status && response.status == 'success') {
                        form.html('<div class="alert alert-primary" role="alert">Succesfully created!</div>');
                    }
                }
            });
        });
    });


    $.fn.outerHTML = function() {
        return $('<div />').append(this.eq(0).clone()).html();
    };

    $.fn.showPanel = function() {

        $('.toggle-panel.active').each(function() {
            $(this).removeClass('active');
        });

        $(this).addClass('active');
    }

    $.fn.popup = function () {
        let $this = $(this),
            closeButton = $this.find('.close-popup');

        $this.toggleClass('show-popup');

        closeButton.off('click');
        closeButton.on('click', function() {

            if ($this.hasClass('show-popup')) {
                $this.removeClass('show-popup');
            } else {
                $this.addClass('show-popup');
            }
        });
    }

    $.fn.showErrors = function (errors) {

        let $this = $(this);
        $this.hideErrors();

        $.each(errors, function(k, error) {

            let id = $this.find('input[name="' + error.name + '"]').attr('id'),
                label = $this.find('label[for="' + id + '"]');

            if (label.length) {

                label.addClass('state-error');

                let parent = label.parents('section').eq(0),
                    note = parent.find('.note.note-error');

                if (note.length) {
                    note.text(error.message);
                } else {
                    parent.append($('<div/>').addClass('note').addClass('note-error').text(error.message));
                }
            }
        });
    }

    $.fn.hideErrors = function() {
        $(this).find('label.state-error').each(function() {
            $(this).removeClass('state-error');
        });
        $(this).find('.note.note-error').each(function() {
            $(this).remove();
        });
    }

    $.fn.getErrors = function() {
        let $this = $(this),
            errors = [];

        $this.find('input, textarea, select').each(function() {

            if ($(this).prop('required')) {

                if ($(this).attr('type') == 'radio') {

                    if ($this.find('input[name='+ $(this).attr('name') +']:checked').length == 0) {
                        errors.push({name: $(this).attr('id'), message: 'Please, select one of the option above'});
                    }
                } else {
                    errors.push({name: $(this).attr('id'), message: 'This field is required'});
                }
            }
        });

        return errors;
    }

}( jQuery ));
