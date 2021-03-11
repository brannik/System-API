<?php
	require("config.php");
	if(isset($_GET["user"])){
		echo "<h1>This is admin panel</h1>";
		echo "<text>Welcome " . $_GET["user"] . " " . $_GET["acc_id"] . "</text>";
	}
	
?>