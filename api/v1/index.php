<?php
if(isset($_REQUEST['request']) && !empty($_REQUEST['request'])){
	require_once 'MyApi.php';
	$API = new MyApi($_REQUEST);
	print($API->processAPI());
	unset($API);
}
?>