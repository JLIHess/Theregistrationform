<?php
	session_start();
	echo '<html><head><meta http-equiv="Content-Type" content="text/html; charset=utf-8" /></head><body>';
	echo "<pre>".print_r($_SESSION, true)."</pre></body></html"; 
?>