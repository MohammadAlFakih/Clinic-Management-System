<?php

include "DB_Functions.php";
$dbc = connectServer('localhost', 'root', '', 0);
selectDB($dbc,'clinic_db',0);

$query1 = "ALTER TABLE `patient` ADD `pp` VARCHAR(255) NOT NULL DEFAULT 'default.png' ;";

$query2 = "ALTER TABLE `doctor` ADD `pp` VARCHAR(255) NOT NULL DEFAULT 'default.png' ;";

$query3 = "ALTER TABLE `secretary` ADD `pp` VARCHAR(255) NOT NULL DEFAULT 'default.png' ;";

executeQuery($dbc,$query1);
executeQuery($dbc,$query2);
executeQuery($dbc,$query3);

mysqli_close($dbc);
?>


