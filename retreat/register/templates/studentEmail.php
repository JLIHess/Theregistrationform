<!DOCTYPE html>
<html>
	<body>
        <p>Dear <?php echo $templateVariables['order']['customer']['first_name'].' '.$templateVariables['order']['customer']['last_name']; ?>,</p>
        <p style="font-family: Arial, Verdana, sans-serif;">
            Thank you for applying to the Sinai Scholars National Jewish Retreat. Someone from our office will be contacting you shortly for an interview.
        </p>
        <p>Have a great day!</p>
        <p><em>The Sinai Scholars Team</em></p>
	</body>
</html>
