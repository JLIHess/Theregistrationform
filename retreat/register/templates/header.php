<!DOCTYPE html>
<html>
	<head>
		<title>
		Retreat - <?php echo $templateVariables['title']; ?></title>

		<link rel="stylesheet" href="/retreat/js/intl-tel-input/css/intlTelInput.min.css">
		<link rel="stylesheet" type="text/css" href='<?php echo BASE_URL; ?>css/reset.css' />
		<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/css/bootstrap.min.css" integrity="sha384-Gn5384xqQ1aoWXA+058RXPxPg6fy4IWvTNh0E263XmFcJlSAwiGgFAW/dAiS6JXm" crossorigin="anonymous">

		<link rel="stylesheet" href="/retreat/css/font-awesome.css">
		<link rel="stylesheet" type="text/css" href='<?php echo BASE_URL; ?>css/style.css' />

		<script defer src="https://use.fontawesome.com/releases/v5.0.8/js/all.js"></script>
		<link href="https://use.fontawesome.com/releases/v5.0.8/css/all.css" rel="stylesheet">
		<link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">

		<script src="/retreat/js/jquery-1.10.2.min.js"></script>

  		<?php if(isset($templateVariables['jqueryUi']) and $templateVariables['jqueryUi']) : ?>
			<script src="https://code.jquery.com/ui/1.12.1/jquery-ui.min.js" integrity="sha256-VazP97ZCwtekAsvgPBSUwPFKdrwD3unUfSGVYrahUqU=" crossorigin="anonymous"></script>
			<link rel="stylesheet" type="text/css" href='https://code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css'></script>
		<?php endif; ?>

		<script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.12.9/umd/popper.min.js" integrity="sha384-ApNbgh9B+Y1QKtv3Rn7W3mgPxhU9K/ScQsAP7hUibX39j7fakFPskvXusvfa0b4Q" crossorigin="anonymous"></script>
		<script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/js/bootstrap.min.js" integrity="sha384-JZR6Spejh4U02d8jOt6vLEHfe/JQGiRRSQQxSfFWpi1MquVdAyjUar5+76PVCmYl" crossorigin="anonymous"></script>
		<script src='<?php echo BASE_URL; ?>js/functions.js'></script>
		<script src="https://cdnjs.cloudflare.com/ajax/libs/clipboard.js/2.0.0/clipboard.min.js"></script>

		<script src="/retreat/js/intl-tel-input/js/intlTelInput.min.js"></script>
    	<script src="/retreat/js/custom.js"></script>

		<script>
			var baseUrl = '<?php echo BASE_URL; ?>';
		</script>

	</head>
	<body>
		<div class="mainheader">
            <a href="http://jretreat.com/"><img src="/retreat_new/img/logo.png"></a>
            <div class="login-form">
                    <?php if (isset($_SESSION['auth'])): ?>
			            Welcome <span><?php echo $_SESSION['auth']['first_name'] ?></span> | <a href="/retreat_new/auth/logout">Logout</a>
			        <?php else: ?>
			            <a data-toggle="popup" data-target="#login-popup" href="#">Login</a>
			        <?php endif; ?>
            </div>
        </div>
