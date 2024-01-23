<!DOCTYPE html>
<html>
<body>
<?php
	//creation data base ***************
	$dbc=connectServer('localhost','root','',0);	
	createDB($dbc,"clinic_db");
?>
</body>
</html>
