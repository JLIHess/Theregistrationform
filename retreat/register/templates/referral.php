<?php

	// Set template variables.
	$templateVariables['page'] = 'Referral Dashboard';
	$templateVariables['title'] = 'Referral Dashboard - '.$templateVariables['order']['customer']['first_name']." ".$templateVariables['order']['customer']['last_name'];

	// Display header.
	include('header.php');
	
?>



<style>
#referral-tab .nav-tabs, #stat-tab .nav-tabs{
	border-bottom: none !important;
}


#referral-tab .nav-tabs .nav-item.show .nav-link,#referral-tab .nav-tabs .nav-link.active, 
#stat-tab .nav-tabs .nav-item.show .nav-link,#stat-tab .nav-tabs .nav-link.active{
	border: none !important;
}
#referral-tab .nav-tabs .nav-item.show .nav-link, .nav-tabs .nav-link.active span{
	border: none !important;
	color:#fff;
	background:#c98f7c;
}



#referral-tab .nav-tabs .nav-item.show .nav-link, .nav-tabs .nav-link  svg:not(:root).svg-inline--fa.fa-sort-up{
	display:none;
}
#referral-tab .nav-tabs .nav-item.show .nav-link, .nav-tabs .nav-link.active  svg:not(:root).svg-inline--fa.fa-sort-up{
	display: table-cell;
    width: 102px;
    height: 40%;
    color: #f1f1f1;
    margin: 0 auto;
    padding: 0;
    vertical-align: bottom;
    margin-bottom: -107%;
}

#referral-tab .nav-tabs .nav-item.show .nav-link, #referral-tab .nav-tabs .nav-link span svg:not(:root).svg-inline--fa {
    width: 4em;
    height: 4em;
    padding: 5px;
	display:inline-block;
}

#referral-tab .nav-tabs .nav-item.show .nav-link, #referral-tab .nav-tabs .nav-link.active1::after{
 
 font-size:40px;
 color: #f1f1f1;
    content: "\f0de";
	font-family: "Font Awesome 5 Free"; 

}

#referral-tab .nav-tabs .nav-item.show .nav-link,#referral-tab .nav-tabs .nav-link{
	border: none !important;
	padding: 0;
	
}

#referral-tab .nav-tabs .nav-item.show .nav-link,#referral-tab .nav-tabs .nav-link span{
	
	color:#fff;
	background:#16244b;
	margin:10px;
	/*border-radius: 50%;*/
	height:110px;
	width:110px;
	display: flex;
    align-items: center;
    flex-direction: column;
    justify-content: center;
	font-size: 14px;
	
}
#stat-tab.stat .nav-tabs .nav-item.show .nav-link,#stat-tab.stat .nav-tabs .nav-link span{
	
	color:#16244b;
	background:transparent;
	margin:10px;
	/*border-radius: 50%;*/
	/* height:50px;
	width:110px; */
	display: flex;
    align-items: center;
    flex-direction: column;
    justify-content: center;
	font-size: 14px;
	
}
#stat-tab.stat .nav-tabs .nav-link span p span {
    display: inline;
    margin: 0;
}

#stat-tab.stat .nav-tabs .nav-item.show .nav-link, #stat-tab.stat .nav-tabs .nav-link span svg:not(:root).svg-inline--fa {
     width: 3em;
    height: 3em;
    padding: 1px;
	display:inline-block; 
}



#referral-tab .nav-tabs .nav-item.show .nav-link,#referral-tab .nav-tabs .nav-link:hover,
#stat-tab .nav-tabs .nav-item.show .nav-link,#stat-tab .nav-tabs .nav-link:hover{
	border: none !important;
}

#referral-tab #nav-tab, #stat-tab #nav-tab {
    justify-content: center;
}

#referral-tab .tab-pane {
    padding: 20px;
    background: #f1f1f1;
}

#nav-email div.form-inline{
	padding-top:10px;

}

.modal-body p{
	font-size:14px; line-height:1.6em; margin-bottom:10px;
}

</style>
<link rel="stylesheet" type="text/css" href='<?php echo BASE_URL; ?>/css/complete.css'></script>
<script>
	var orderId = <?php echo intval($templateVariables['orderId']); ?>;
	var order = <?php echo json_encode($templateVariables['order']); ?>;
	var referral = <?php echo json_encode($templateVariables['referral']); ?>;

</script>
<?php require_once('facebook_app.php'); ?>

<div class="mainheader text-center pt-5 px-2"><a href="http://jretreat.com/"><img src="../img/logo.png"></a></div>
<div class="pageContent">
		<h1 class="pageHeader text-center" >
		<span style="font-size:1.5rem;text-transform:uppercase;color:#fff;" ><?php echo $templateVariables['order']['customer']['first_name']." ".$templateVariables['order']['customer']['last_name']; ?>'s referral dashboard </span>


		</h1>




		<?php if (isset($templateVariables['referral'])): ?>
		
		
		

		<div style="font-family: Arial, Helvetica, sans-serif; font-size: 16px; font-weight: bold; text-align: center; padding: 5px 100px;">

		
		</div>


		<div style="font-family: Arial, Helvetica, sans-serif; font-size: 16px;line-height:1.6em;  text-align: center; padding: 20px 50px 0;">






<p style="line-height: 1.8em;"><span style="line-height:3em;padding:10px">Coupon Code: <a href="<?php echo SELECT_ROOMS_URL.'?promo='.$templateVariables['referral']['code']; ?>" target="_blank"><span style="font-size:2em;text-transform:uppercase;color:#16244b;font-weight:bold;text-decoration:none;"><?php echo $templateVariables['referral']['code']; ?></span></a></span><br>
Link: <a href="<?php echo SELECT_ROOMS_URL.'?promo='.$templateVariables['referral']['code']; ?>" > <?php echo SELECT_ROOMS_URL.'?promo='.$templateVariables['referral']['code']; ?></a></p>


<div id="stat-tab" class="mt-4 stat">
		<div class="nav nav-tabs" id="nav-tab" role="tablist">
    <a class="nav-item nav-link " id="nav-email-tab" role="tab"  aria-selected="false"><span><i class="fas fa-shopping-cart" ></i> <p><span class="count"><?php echo $templateVariables['referral']['count']? $templateVariables['referral']['count']:0; ?></span> Order<?php echo $templateVariables['referral']['count'] > 1? 's' : ''; ?></p></span></a>
	<a class="nav-item nav-link " id="nav-mail-tab"  role="tab"  aria-selected="false"><span><i class="fas fa-user"></i> <p><span class="count"><?php echo $templateVariables['referral']['guest_count']? $templateVariables['referral']['guest_count']:0; ?></span> Guest</p></span></a>
	<a class="nav-item nav-link " id="nav-phone-tab" role="tab"  aria-selected="false"><span><i class="fas fa-dollar-sign"></i><p>$<span class="count"><?php echo $templateVariables['referral']['guest_count']? $templateVariables['referral']['guest_count']*$templateVariables['referral']['amount']:0; ?></span></p></span></a>
    
  </div>
  </div>
</div>
</div>
<div class="pageContent">
<div style="font-family: Arial, Helvetica, sans-serif; font-size: 16px;line-height:1.6em;  text-align: center; padding: 20px 50px;">
<span style="font-size:1.4em;text-transform:uppercase;color:#c98f7c;font-weight:bold;line-height: 1.4em;">Invite more friends to join you</span>
<p>They'll save $50, and you'll get a $50 refund.</p>
<div id="referral-tab" class="mt-4">
<nav >
  <div class="nav nav-tabs" id="nav-tab" role="tablist">
    <a class="nav-item nav-link active" id="nav-email-tab" data-toggle="tab" href="#nav-email" role="tab" aria-controls="nav-email" aria-selected="true"><span><i class="fas fa-at"></i></i></span><i class="fas fa-sort-up"></i></a>
	<a class="nav-item nav-link" id="nav-mail-tab" data-toggle="tab" href="#nav-mail" role="tab" aria-controls="nav-mail" aria-selected="false"><span><i class="far fa-envelope"></i></i></span><i class="fas fa-sort-up"></i></a>
	<a class="nav-item nav-link" id="nav-phone-tab" data-toggle="tab" href="#nav-phone" role="tab" aria-controls="nav-phone" aria-selected="false"><span><i class="fas fa-phone"></i></span><i class="fas fa-sort-up"></i></a>
    <a class="nav-item nav-link" id="nav-social-tab" data-toggle="tab" href="#nav-social" role="tab" aria-controls="nav-social" aria-selected="false"><span><i class="fab fa-facebook"></i></span><i class="fas fa-sort-up"></i></a>
  </div>
</nav>
<div class="tab-content mb-2" id="nav-tabContent">
	<div class="tab-pane fade show active" id="nav-email" role="tabpanel" aria-labelledby="nav-email-tab">
		<?php $textEmail = "Hi [NAME], \n
		I'm excited to be attending the JLI Retreat this July 31-August 5 in Providence, Rhode Island. \n
		Will you join me? \n
		Use this link ".SELECT_ROOMS_URL.'?promo='.strtoupper($templateVariables['referral']['code'])." (or coupon code ".strtoupper($templateVariables['referral']['code']).") to save $50 on your registration. \n
		I'm looking forward to having an awesome time together!\n
		Best,\n".$templateVariables['order']['customer']['first_name']." ".$templateVariables['order']['customer']['last_name'];
		?>
	Invite your friends via email (<a href="" data-toggle="modal" data-target="#email-modal" >Sample Email</a>) or <a href="mailto:?subject=Will%20you%20join%20me%3F&body=<?php echo urlencode($textEmail)?>" >invite them from your email</a>

	  <form data-link="register/referral/sendReferralLinkEmail.php">

	  <?php foreach(range(1,5) as $i): ?>
		  <div class="form-inline col-md-12 px-4 d-flex justify-content-center" id="email-line-<?php echo $i;?>">
	  
  <label class="sr-only" for="inlineFormInput">Name</label>
  <div class="input-group mb-2 mr-sm-2 mb-sm-2 col-5">
  <input type="text" name="contact[<?php echo $i;?>][name]" class="form-control" id="inlineFormInput" placeholder="Name">
  </div>
  <label class="sr-only" for="inlineFormInputGroup">Email</label>
  <div class="input-group mb-2 mr-sm-2 mb-sm-2 col-6">
    <input type="email" name="contact[<?php echo $i;?>][email]" class="form-control" id="inlineFormInputGroup" placeholder="Email">
  </div>

  <div class="form-check mb-2 mr-sm-2 mb-sm-2">
    <label class="form-check-success">
       
    </label>
  </div>
</div>
<?php endforeach; ?>
<div class="form-group row">
<div class="col-md-11  pt-1 text-right">
	<input type="hidden" name="referral_id" value="<?php echo $templateVariables['referral']['id'];?>" />
        <button type="submit" class="btn btn-primary">Send</button>
      </div>
    </div>
</form>
	</div>
  	<div class="tab-pane fade" id="nav-mail" role="tabpanel" aria-labelledby="nav-mail-tab">
	  Insert your friends addresses and we'll mail them an invitation.
		  <div class="row">
    <div class="col-md-12">
		  <form class="form-horizontal" role="form" style="text-align:left;" data-link="register/referral/createReferralContact.php">
<div class="row">
<?php foreach(range(1,2) as $i): ?>

     <div class="col-md-6 mt-2">   

		  <!-- Form Name 
		  <label class="control-label"><strong>#<?php echo $i;?> Details</strong></label>-->
		  
		  
	

<div class="form-group"> <!-- Full Name -->
	<label for="full_name_id" class="control-label sr-only">Full Name</label>
	<input type="text" class="form-control" id="full_name_id" name="contact[<?php echo $i;?>][name]" placeholder="Full Name">
</div>	

<div class="form-group"> <!-- Street 1 -->
	<label for="street1_id" class="control-label sr-only">Street Address 1</label>
	<input type="text" class="form-control" id="street1_id" name="contact[<?php echo $i;?>][address]" placeholder="Street address">
</div>					
						

<div class="form-group"> <!-- City-->
	<label for="city_id" class="control-label sr-only">City</label>
	<input type="text" class="form-control" id="city_id" name="contact[<?php echo $i;?>][city]" placeholder="City">
</div>	
<div class="form-group"> <!-- City-->
	<label for="state_id" class="control-label sr-only">State</label>
	<input type="text" class="form-control" id="state_id" name="contact[<?php echo $i;?>][state]" placeholder="State">
</div>								
						


<div class="form-group"> <!-- Zip Code-->
	<label for="zip_id" class="control-label sr-only">Zip Code</label>
	<input type="text" class="form-control" id="zip_id" name="contact[<?php echo $i;?>][zip]" placeholder="Zip/Postal Code">
</div>
	


</div>


          



          

          

        
<?php endforeach; ?>
</div>
<div class="form-group row">
<div class="col-md-12  pt-1 text-right">
<input type="hidden" name="referral_id" value="<?php echo $templateVariables['referral']['id'];?>" />
<input type="hidden" name="type" value="mail" />
        <button type="submit" class="btn btn-primary mr-4">Send</button>
      </div>
    </div>
</form>
</div><!-- /.col-lg-12 -->
</div><!-- /.row -->

	</div>
  	<div class="tab-pane fade" id="nav-phone" role="tabpanel" aria-labelledby="nav-phone-tab">
	  A JLI representative will call your friends to invite them.

		  <form  data-link="register/referral/createReferralContact.php">

<?php foreach(range(1,5) as $i): ?>
	<div class="form-inline d-flex justify-content-center mt-2" id="phone-line-<?php echo $i;?>">

<label class="sr-only" for="inlineFormInput">Name</label>
<div class="input-group mb-2 mr-sm-2 mb-sm-2 col-5">
<input type="text" name="contact[<?php echo $i;?>][name]" class="form-control" id="inlineFormInput" placeholder="Name">
</div>
<label class="sr-only" for="inlineFormInputGroup">Phone</label>
<div class="input-group mb-2 mr-sm-2 mb-sm-2 col-6">
<input type="tel" name="contact[<?php echo $i;?>][phone]" class="form-control" id="inlineFormInputGroup" placeholder="Phone">
</div>

<div class="form-check mb-2 mr-sm-2 mb-sm-2">
<label class="form-check-success">
 
</label>
</div>
</div>
<?php endforeach; ?>
<div class="form-group row">
<div class="col-md-11  pt-1 text-right">
<input type="hidden" name="referral_id" value="<?php echo $templateVariables['referral']['id'];?>" />
<input type="hidden" name="type" value="phone" />
        <button type="submit" class="btn btn-primary ">Send</button>
      </div>
    </div>
</form>
		</div>
  	<div class="tab-pane fade" id="nav-social" role="tabpanel" aria-labelledby="nav-social-tab">

		  <div class="container">
  <div class="row my-4">
    <div class="col-sm" ><button  class="btn btn-outline-primary btn-lg" style="font-size:24px;text-decoration:none" onclick="event.preventDefault();fbShareNJR()">
	<i class="fab fa-facebook-square" ></i> Share on Facebook</button>
    </div>
    <div class="col-sm">
	<button class="btn btn-outline-primary btn-lg" style="font-size:24px;text-decoration:none" onclick="event.preventDefault();twitterShareNJR(event)">
	<i class="fab fa-twitter-square"></i> Share on Twitter</button>
	
    </div>
    
  </div>
</div>

	 
		  

<textarea id="social-text" class="form-control my-2" rows="8" style="border:none;border-radius: 5px;padding:10px;resize: none;">
Yay! I just signed up for the JLI Retreat - July 31-August 5 at the Rhode Island Convention Center.

Who wants to join me?

Use coupon code <?php echo strtoupper($templateVariables['referral']['code']); ?> to save $50
<?php echo SELECT_ROOMS_URL.'?promo='.$templateVariables['referral']['code']; ?>
</textarea>
<button class="btn btn-info copy " data-clipboard-action="copy" data-clipboard-target="#social-text" >Copy text</button>
		  
		
</div>
</div>

<p style="font-size:12px"><em>*May not be combined with any other offer. No cash value. Refunds only valid towards NJR registration. Coupon valid on registrations of 3 days or more. Expires: 6/20/18 Refunds will be processed after 6/30/18</em></p>


		</div>
		</div>


<?php endif ?>
    
    
		
		
	<div class="clear"></div>
</div>

<!-- Facebook Conversion Code for Jretreat Registration Complete - Zalman Abraham 1 -->
<script>(function() {
var _fbq = window._fbq || (window._fbq = []);
if (!_fbq.loaded)
{ var fbds = document.createElement('script'); fbds.async = true; fbds.src = '//connect.facebook.net/en_US/fbds.js'; var s = document.getElementsByTagName('script')[0]; s.parentNode.insertBefore(fbds, s); _fbq.loaded = true; }
})();
window._fbq = window._fbq || [];
window._fbq.push(['track', '6024482446117',
{'value':'0.00','currency':'USD'}
]);
</script>
<noscript><img height="1" width="1" alt="" style="display:none" src="https://www.facebook.com/tr?ev=6024482446117&cd[value]=0.00&cd[currency]=USD&noscript=1" /></noscript>

<!-- Google Code for Jretreat Registration Conversion Page -->
<script type="text/javascript">
/* <![CDATA[ */
var google_conversion_id = 847270230;
var google_conversion_language = "en";
var google_conversion_format = "3";
var google_conversion_color = "ffffff";
var google_conversion_label = "WsIDCJShynIQ1qKBlAM";
var google_remarketing_only = false;
/* ]]> */
</script>
<script type="text/javascript" src="//www.googleadservices.com/pagead/conversion.js">
</script>
<noscript>
<div style="display:inline;">
<img height="1" width="1" style="border-style:none;" alt="" src="//www.googleadservices.com/pagead/conversion/847270230/?label=WsIDCJShynIQ1qKBlAM&guid=ON&script=0"/>
</div>
</noscript>


<div class="modal fade" id="email-modal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
  <div class="modal-dialog" role="document">
    <div class="modal-content ">
	<button type="button" class="close text-right pt-2 pr-2" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true"><i class="far fa-times-circle"></i></span>
        </button>
      
      <div class="modal-body text-left">
	  <p> Hi <?php echo $templateVariables['order']['customer']['first_name'].' '.$templateVariables['order']['customer']['last_name']; ?>,<br></p>

<p>I'm excited to be attending the JLI Retreat this July 31-August 5 in Providence, Rhode Island.<br></p>

<p>Will you join me?<br></p>

<p>Use this <a href="<?php echo SELECT_ROOMS_URL ?>?promo=<?php echo $templateVariables['referral']['code']; ?>"> link</a> 
(or coupon code <span style="text-transform:uppercase"><?php echo $templateVariables['referral']['code']; ?></span>) to save $50 on your registration.<br></p>

<p>I'm looking forward to having an awesome time together!<br></p>

<p>Best,<br></p>

    <p><?php echo $templateVariables['order']['customer']['first_name'].' '.$templateVariables['order']['customer']['last_name']; ?></p>
      </div>
      
    </div>
  </div>
</div>
<script>

	var clipboard = new ClipboardJS('.copy');

clipboard.on('success', function(e) {
    console.info('Action:', e.action);
    console.info('Text:', e.text);
    console.info('Trigger:', e.trigger);

    e.clearSelection();
});

clipboard.on('error', function(e) {
    console.error('Action:', e.action);
    console.error('Trigger:', e.trigger);
});

function fbShareNJR(){
	jQuery('p.copy').trigger( "click" );
	FB.ui({
		method: 'share',
		href: 'https://www.facebook.com/jretreat/videos/10157301689884741/',
		}, function(response){});
} 

function twitterShareNJR(event) {
    var width  = 575,
        height = 600,
        left   = ($(window).width()  - width)  / 2,
        top    = ($(window).height() - height) / 2,
        url    = "https://twitter.com/intent/tweet?url=&text="+encodeURIComponent(jQuery('textarea#social-text').val()),
        opts   = 'status=1' +
                 ',width='  + width  +
                 ',height=' + height +
                 ',top='    + top    +
                 ',left='   + left;
    
    window.open(url, 'twitter', opts);
 
    return false;
  };

jQuery('form').submit(function( event ) {
	event.preventDefault();

	var tab = $(this);
	$.ajax({
		method: "POST",
		url: "<?php echo SELECT_ROOMS_URL ?>"+$(this).data( "link" ),
		data:  $( this ).serialize()
		})
		.done(function( data ) {
			console.log( JSON.parse(data) );
			jsonData = JSON.parse(data);
			

			$.each(jsonData, function(key, d) {
				setTimeout(function () {
				tab.before(
				'<div id="alert-'+key+'" class="alert '+(d.success ? 'alert-success ': 'alert-danger ') + 'alert-dismissable">'+
					'<button type="button" class="close" ' + 
							'data-dismiss="alert" aria-hidden="true">' + 
						'&times;' + 
					'</button>' + 
					'<strong>'+(+d.success ? 'Success! ': 'Error: ') +'</strong>'+
					d.messages + 
				'</div>');
			}, 300);

				setTimeout(function () {
				$("#alert-"+key).fadeTo(500, 0).slideUp(500, function () {
					$("#alert-"+key).remove();
				});
			}, 5000);
			

    
			}); 

			
				
			

			
	});

});
function wait(ms){
   var start = new Date().getTime();
   var end = start;
   while(end < start + ms) {
     end = new Date().getTime();
  }
}
window.setTimeout(function() {
    $(".alert").fadeTo(500, 0).slideUp(500, function(){
        $(this).remove(); 
    });
}, 3000);

$('.count').each(function () {
    $(this).prop('Counter',0).animate({
            Counter: $(this).text()
        }, {
            duration: 1000,
            easing: 'swing',
            step: function (now) {
                $(this).text(Math.ceil(now));
            }
        });
});


var hash = document.location.hash;
var prefix = "tab_";
if (hash) {
    $('.nav-tabs a[href="'+hash.replace(prefix,"")+'"]').tab('show');
} 




	
	</script>

<?php

	// Display header.
	include('footer.php');
	
	
?>