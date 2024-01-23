<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns=”http://www.w3.org/1999/ xhtml" xml:lang="en" lang="en">

<head>
	<meta http-equiv="content-type" content="text/html; charset=utf-8" />
	<title>Create a Table</title>
</head>

<body>
	<?php

	include "DB_Functions.php";
	$dbc = connectServer('localhost', 'root', '', 0);
	$databaseName = "clinic_db";

	// Check if the database exists
	$query = "SELECT SCHEMA_NAME FROM information_schema.SCHEMATA WHERE SCHEMA_NAME = ?";
	$stmt = mysqli_prepare($dbc, $query);
	mysqli_stmt_bind_param($stmt, "s", $databaseName);
	mysqli_stmt_execute($stmt);
	mysqli_stmt_bind_result($stmt, $existingDatabase);
	mysqli_stmt_fetch($stmt);
	if ($existingDatabase == $databaseName) {
		echo "Database already exists";
		mysqli_stmt_close($stmt);
		mysqli_close($dbc);
		header('location:../index.php');
		exit();
	}

	//Create the database
	include "createdb.php";

	//Select DB 
	selectDB($dbc, "clinic_db", 1);

	//Department
	$query = 'CREATE TABLE `clinic_db`.`department`
	(`id` INT NOT NULL AUTO_INCREMENT , `city_id` INT NOT NULL , `details` TEXT NULL , `room` INT NULL , PRIMARY KEY (`id`))';
	executeQuery($dbc, $query);

	//City
	$query = "CREATE TABLE `clinic_db`.`city` (`id` INT NOT NULL AUTO_INCREMENT , `city_name` VARCHAR(50) NOT NULL ,
	PRIMARY KEY (`id`))";
	executeQuery($dbc,$query);

	//Foreign Key Department->city_id
	$query="ALTER TABLE `department` ADD  CONSTRAINT `city_id in department` FOREIGN KEY (`city_id`) REFERENCES
	 `city`(`id`) ON DELETE CASCADE ON UPDATE CASCADE;";
	executeQuery($dbc,$query);

	//Specializations
	$query="CREATE TABLE `clinic_db`.`specialization` (`id` INT NOT NULL AUTO_INCREMENT ,
	 `alias` VARCHAR(50) NOT NULL , PRIMARY KEY (`id`))";
	executeQuery($dbc,$query);

	//Doctor
	$query = 'CREATE TABLE `clinic_db`.`doctor` (`id` INT NOT NULL AUTO_INCREMENT , `department_id` INT NOT NULL ,
	`email` VARCHAR(30) NOT NULL , `password` TEXT NOT NULL , `first_name` VARCHAR(30) NOT NULL ,
	`last_name` VARCHAR(30) NOT NULL , `age` INT NOT NULL , `gender` CHAR(1) NOT NULL , `phone` VARCHAR(30) NOT NULL ,
	`specialization_id` INT NOT NULL , `role` VARCHAR(30) NOT NULL DEFAULT "doctor" , PRIMARY KEY (`id`), UNIQUE (`email`))';
	executeQuery($dbc, $query);

	//Foreign Key Doctor->Department id
	$query = 'ALTER TABLE `doctor` ADD CONSTRAINT `doctor_id_department` FOREIGN KEY (`department_id`)
	REFERENCES `department`(`id`) ON DELETE RESTRICT ON UPDATE CASCADE;';
	executeQuery($dbc, $query);

	//Foreign Key Doctor->Specialization id
	$query="ALTER TABLE `doctor` ADD CONSTRAINT `doctor_specialization_id` FOREIGN KEY (`specialization_id`)
	 REFERENCES `specialization`(`id`) ON DELETE CASCADE ON UPDATE CASCADE;";
	executeQuery($dbc,$query);

	//Secretary
	$query = 'CREATE TABLE `clinic_db`.`secretary` (`id` INT NOT NULL AUTO_INCREMENT , `doctor_id` INT NULL , `email` VARCHAR(30) NOT NULL ,
	`password` VARCHAR(255) NOT NULL , `first_name` VARCHAR(30) NOT NULL , `last_name` VARCHAR(30) NOT NULL , `age` INT NOT NULL ,
	`gender` CHAR(1) NOT NULL , `phone` VARCHAR(30) NOT NULL , `role` VARCHAR(30) NOT NULL DEFAULT "secretary", PRIMARY KEY (`id`), UNIQUE (`email`))';
	executeQuery($dbc, $query);

	//Doctor Secretary
	$query = 'ALTER TABLE `secretary` ADD CONSTRAINT `doctor secretary` FOREIGN KEY (`doctor_id`) REFERENCES `doctor`(`id`)
	 ON DELETE SET NULL ON UPDATE CASCADE';
	executeQuery($dbc, $query);

	//Patient
	$query = 'CREATE TABLE `clinic_db`.`patient` (`id` INT NOT NULL AUTO_INCREMENT , `email` VARCHAR(30) NOT NULL , `password` VARCHAR(255) NOT NULL ,
	`first_name` VARCHAR(30) NOT NULL , `last_name` VARCHAR(30) NOT NULL , `age` INT NOT NULL , `gender` CHAR(1) NOT NULL ,
	`phone` VARCHAR(30) NULL , `role` VARCHAR(30) NOT NULL DEFAULT "patient" , PRIMARY KEY (`id`), UNIQUE (`email`))';
	executeQuery($dbc, $query);

	//Payment
	$query = 'CREATE TABLE `clinic_db`.`payment` (`patient_id` INT NOT NULL , `doctor_id` INT NOT NULL , `balance` INT NOT NULL )';
	executeQuery($dbc, $query);

	//Payment with doctor and patient
	$query = 'ALTER TABLE `payment` ADD CONSTRAINT `balance_with_doctor` FOREIGN KEY (`doctor_id`) REFERENCES `doctor`(`id`)
	ON DELETE RESTRICT ON UPDATE CASCADE';
	executeQuery($dbc, $query);
	$query = 'ALTER TABLE `payment` ADD CONSTRAINT `balance_with_patient` FOREIGN KEY (`patient_id`)
	REFERENCES `patient`(`id`) ON DELETE RESTRICT ON UPDATE CASCADE';
	executeQuery($dbc, $query);

	//Appointment
	$query = 'CREATE TABLE `clinic_db`. `appointment` (`id` INT NOT NULL AUTO_INCREMENT , `doctor_id` INT NOT NULL , `patient_id` INT NOT NULL ,
	`department_id` INT NOT NULL , `start_date` DATETIME NOT NULL , `end_date` DATETIME NOT NULL , `bill` FLOAT NOT NULL DEFAULT 0 ,
	`status` VARCHAR(30) NOT NULL DEFAULT "pending" ,`book_date` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
	 PRIMARY KEY (`id`))';
	executeQuery($dbc, $query);

	//Appointment with doctor and patient in department
	$query = 'ALTER TABLE `appointment` ADD CONSTRAINT `doctor_appointment` FOREIGN KEY (`doctor_id`) REFERENCES `doctor`(`id`) 
	ON DELETE RESTRICT ON UPDATE CASCADE';
	executeQuery($dbc, $query);
	$query = 'ALTER TABLE `appointment` ADD CONSTRAINT `patient_appointment` FOREIGN KEY (`patient_id`) REFERENCES `patient`(`id`) 
	ON DELETE RESTRICT ON UPDATE CASCADE';
	executeQuery($dbc, $query);
	$query = 'ALTER TABLE `appointment` ADD CONSTRAINT `appointment_place` FOREIGN KEY (`department_id`)
	REFERENCES `department`(`id`) ON DELETE RESTRICT ON UPDATE NO ACTION';
	executeQuery($dbc, $query);

	//Document
	$query = 'CREATE TABLE `clinic_db`.`document` (`id` INT NOT NULL AUTO_INCREMENT , `appointment_id` INT NOT NULL ,
	`details` TEXT NULL ,`prescription` TEXT NULL, PRIMARY KEY (`id`))';
	executeQuery($dbc, $query);

	//Appointment Document
	$query = 'ALTER TABLE `document` ADD CONSTRAINT `appointment_document` FOREIGN KEY (`appointment_id`)
	REFERENCES `appointment`(`id`) ON DELETE CASCADE ON UPDATE CASCADE';
	executeQuery($dbc, $query);

	//Trigger toCreate new document on creating new appointment
	$query = "CREATE TRIGGER `new_document` AFTER INSERT ON `appointment` FOR EACH ROW INSERT INTO 
	document (appointment_id,details,prescription) VALUE(NEW.id,NULL,NULL)";
	executeQuery($dbc, $query);

	//Black List
	$query = 'CREATE TABLE `clinic_db`.`black_list` (`patient_id` INT NOT NULL ,
	`doctor_id` INT NOT NULL , `end_date` DATE NOT NULL )';
	executeQuery($dbc, $query);

	//Doctor block patient
	$query = 'ALTER TABLE `black_list` ADD CONSTRAINT `block_patient` FOREIGN KEY (`patient_id`) REFERENCES `patient`(`id`)
	ON DELETE CASCADE ON UPDATE CASCADE';
	executeQuery($dbc, $query);
	$query = 'ALTER TABLE `black_list` ADD CONSTRAINT `doctor_block` FOREIGN KEY (`doctor_id`)
	REFERENCES `doctor`(`id`) ON DELETE CASCADE ON UPDATE CASCADE';
	executeQuery($dbc, $query);

	//Week_schedule
	$query="CREATE TABLE `week_schedule` (
		`day` varchar(12) NOT NULL,
		`doctor_id` int(11) NOT NULL,
		`start_hour` TIME NOT NULL,
		`end_hour` TIME NOT NULL
	  )";
	executeQuery($dbc, $query);
	$query="ALTER TABLE `week_schedule`
	ADD CONSTRAINT `doctor_week_schedule` FOREIGN KEY (`doctor_id`) REFERENCES `doctor` (`id`)
	ON DELETE CASCADE ON UPDATE CASCADE;";
	executeQuery($dbc, $query);


	//Trigger on week_schedule to add default values
	$query="CREATE TRIGGER `Default_week_schedule` AFTER INSERT ON `doctor`
 	FOR EACH ROW INSERT INTO week_schedule (doctor_id,day, start_hour, end_hour)
    VALUES (NEW.id, 'monday','08:00','16:00'),
           (NEW.id, 'tuesday', '08:00','16:00'),
           (NEW.id, 'wednesday', '08:00','16:00'),
           (NEW.id, 'thursday', '08:00','16:00'),
           (NEW.id, 'friday', '08:00','16:00'),
		   (NEW.id, 'saturday', '08:00','08:00'),
           (NEW.id, 'sunday', '08:00','08:00')";
	executeQuery($dbc, $query);

	//Unavailbale Slots
	$query="CREATE TABLE `clinic_db`.`unavailable_slots` (`id` INT NOT NULL AUTO_INCREMENT , `doctor_id` INT NOT NULL ,
	 `start_date` DATETIME NOT NULL , `end_date` DATETIME NOT NULL , `department_id` INT NOT NULL , PRIMARY KEY (`id`))";
	executeQuery($dbc,$query);
	
	//Foreign Key Unavailable Slots->doctor_id
	$query="ALTER TABLE `unavailable_slots` ADD CONSTRAINT `unavailable_slots doctor_id` FOREIGN KEY (`doctor_id`)
	 REFERENCES `doctor`(`id`) ON DELETE CASCADE ON UPDATE CASCADE;";
	executeQuery($dbc,$query);
	
	//Foreign Key Unavailable Slots->department_id
	$query="ALTER TABLE `unavailable_slots` ADD CONSTRAINT `unavailable_slots dep_id` FOREIGN KEY (`department_id`)
	 REFERENCES `department`(`id`) ON DELETE CASCADE ON UPDATE CASCADE;";
	executeQuery($dbc,$query);

	//Notifications
	$query = "CREATE TABLE `clinic_db`.`notifications` (`id` INT NOT NULL AUTO_INCREMENT , `receiver` INT NOT NULL ,
	 `sender` INT NOT NULL , `message` TEXT NOT NULL , `date` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ,
	 `status` VARCHAR(10) NOT NULL DEFAULT 'unread',`appointment_id` INT NULL ,`reason` VARCHAR(10) NOT NULL
	 ,PRIMARY KEY (`id`))";
	executeQuery($dbc, $query);

	//Foreign Key Notifications->receiver
	$query = "ALTER TABLE `notifications` ADD CONSTRAINT `message_reveiver` FOREIGN KEY (`receiver`) REFERENCES `patient`(`id`)
	 ON DELETE CASCADE ON UPDATE CASCADE";
	executeQuery($dbc, $query);
	
	//Foreign Key Notifications->sender
	$query = "ALTER TABLE `notifications` ADD CONSTRAINT `message_sender` FOREIGN KEY (`sender`)
	  REFERENCES `doctor`(`id`) ON DELETE NO ACTION ON UPDATE CASCADE;";
	executeQuery($dbc, $query);

	//Foreign Key Notifications->appointment_id
	$query ="ALTER TABLE `notifications` ADD CONSTRAINT `message_app` FOREIGN KEY (`appointment_id`)
	 REFERENCES `appointment`(`id`) ON DELETE CASCADE ON UPDATE CASCADE;";
	executeQuery($dbc, $query);

	header('location:../index.php');

	mysqli_close($dbc); // Close the connection.
	?>
</body>

</html>