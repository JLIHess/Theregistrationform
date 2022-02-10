<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Reservation</title>

    <link rel="stylesheet" href="<?php echo BASE_URL ?>css/font-awesome.css">
    <link rel="stylesheet" href="<?php echo BASE_URL ?>css/sky-forms.css">
    <link rel="stylesheet" href="<?php echo BASE_URL ?>css/sky-forms-black.css">
    <link rel="stylesheet" href="<?php echo BASE_URL ?>js/intl-tel-input/css/intlTelInput.min.css">
    <link rel="stylesheet" href="<?php echo BASE_URL ?>css/main.css">

    <script src="<?php echo BASE_URL ?>js/jquery-1.10.2.min.js"></script>
    <script src="<?php echo BASE_URL ?>js/jquery-ui-1.11.2.min.js"></script>
    <script src="<?php echo BASE_URL ?>js/jquery.form.min.js"></script>
    <script src="<?php echo BASE_URL ?>js/jquery.validate.min.js"></script>
    <!--[if lt IE 10]>
    <script src="<?php echo BASE_URL ?>js/jquery.placeholder.min.js"></script>
    <![endif]-->
    <!--[if lt IE 9]>
    <script src="https://html5shim.googlecode.com/svn/trunk/html5.js"></script>
    <script src="<?php echo BASE_URL ?>js/sky-forms-ie8.js"></script>
    <![endif]-->
    <script src="<?php echo BASE_URL ?>js/intl-tel-input/js/intlTelInput.min.js"></script>
    <script src="<?php echo BASE_URL ?>js/custom.js"></script>
    <script type="text/javascript">
        var baseUrl = "<?php echo BASE_URL ?>index.php/";
    </script>
</head>


<body class="bg-2015">

    <div class="body">
		<div class="mainheader">
            <a href ="http://jretreat.com/"><img src="img/logo.png"/></a>
            <div class="login-form">
                <?php if ($this->user->isLoggedIn): ?>
                    Welcome <span><?php echo $this->user->getFirstName() ?>!</span> <a href="<?php echo BASE_URL ?>/auth/logout">Logout</a>
                <?php else: ?>
                    <a data-toggle="popup" data-target="#login-popup" href="#">Login</a>
                <?php endif; ?>
            </div>
        </div>
        <?php echo $content; ?>
    </div>
    <!--
    <script type="text/javascript">

        var _gaq = _gaq || [];
        _gaq.push(['_setAccount', 'UA-3470897-2']);
        _gaq.push(['_trackPageview']);

        (function() {
            var ga = document.createElement('script'); ga.type = 'text/javascript'; ga.async = true;
            ga.src = ('https:' == document.location.protocol ? 'https://ssl' : 'http://www') + '.google-analytics.com/ga.js';
            var s = document.getElementsByTagName('script')[0]; s.parentNode.insertBefore(ga, s);
        })();

        window.intercomSettings = {
        app_id: "vd6ktvf2"
        };
    </script>
    <script>(function(){var w=window;var ic=w.Intercom;if(typeof ic==="function"){ic('reattach_activator');ic('update',intercomSettings);}else{var d=document;var i=function(){i.c(arguments)};i.q=[];i.c=function(args){i.q.push(args)};w.Intercom=i;function l(){var s=d.createElement('script');s.type='text/javascript';s.async=true;s.src='https://widget.intercom.io/widget/vd6ktvf2';var x=d.getElementsByTagName('script')[0];x.parentNode.insertBefore(s,x);}if(w.attachEvent){w.attachEvent('onload',l);}else{w.addEventListener('load',l,false);}}})()</script>
    -->
    </body>
</html>

