<!DOCTYPE html>
<html>
<body>
<?php
	//creation data base ***************
	$dbc=connectServer('localhost','root','',1);	
	createDB($dbc,"mhamad");
	mysqli_close($dbc);
?>
</body>
</html>
