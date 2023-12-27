<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns=”http://www.w3.org/1999/ xhtml" xml:lang="en" lang="en">
<head>
<meta http-equiv="content-type" content="text/html; charset=utf-8" />
<title>Create a Table</title>
</head>
<body>
<?php
	
	include "DB_Functions.php";
	include "createdb.php";
	
	$dbc=connectServer('localhost','root','',1);

	//Select DB 
	selectDB($dbc,"mhamad",1);
	
	$query = 'CREATE TABLE `mhamad`.`department`
	(`id` INT NOT NULL AUTO_INCREMENT , `city` VARCHAR(25) NOT NULL , `details` TEXT NULL , `room` INT NULL , PRIMARY KEY (`id`))';
	executeQuery($dbc,$query);

	$query = 'CREATE TABLE `mhamad`.`doctor` (`id` INT NOT NULL AUTO_INCREMENT , `department_id` INT NOT NULL ,
	`email` VARCHAR(30) NOT NULL , `password` TEXT NOT NULL , `first_name` VARCHAR(30) NOT NULL ,
	`last_name` VARCHAR(30) NOT NULL , `age` INT NOT NULL , `gender` CHAR(1) NOT NULL , `phone` VARCHAR(30) NOT NULL ,
	`specialization` VARCHAR(30) NOT NULL , `role` VARCHAR(30) NOT NULL DEFAULT "doctor" , PRIMARY KEY (`id`), UNIQUE (`email`))';
	executeQuery($dbc,$query);

	$query='ALTER TABLE `doctor` ADD CONSTRAINT `doctor_id_department` FOREIGN KEY (`department_id`)
	REFERENCES `department`(`id`) ON DELETE RESTRICT ON UPDATE CASCADE;';
   	executeQuery($dbc,$query);

	//Schedule
	$query='CREATE TABLE `mhamad`.`schedule` (`id` INT NOT NULL AUTO_INCREMENT , `doctor_id` INT NOT NULL ,
	`start_date` DATE NOT NULL , `end_date` DATE NOT NULL , `sequence` VARCHAR(30) NOT NULL DEFAULT "FFFFFFFFFFFFFFFFFFFF" ,
	PRIMARY KEY (`id`)) ENGINE = InnoDB';
	executeQuery($dbc,$query);

	//Doctor Schedule
	$query='ALTER TABLE `schedule` ADD CONSTRAINT `doctor_schedule` FOREIGN KEY (`doctor_id`)
	REFERENCES `doctor`(`id`) ON DELETE CASCADE ON UPDATE CASCADE';
	executeQuery($dbc,$query);

	//Secretary
	$query='CREATE TABLE `mhamad`.`secretary` (`id` INT NOT NULL AUTO_INCREMENT , `doctor_id` INT NULL , `email` VARCHAR(30) NOT NULL ,
	`password` VARCHAR(255) NOT NULL , `first_name` VARCHAR(30) NOT NULL , `last_name` VARCHAR(30) NOT NULL , `age` INT NOT NULL ,
	`gender` CHAR(1) NOT NULL , `phone` VARCHAR(30) NOT NULL , `role` VARCHAR(30) NOT NULL DEFAULT "secretary", PRIMARY KEY (`id`), UNIQUE (`email`))';
	executeQuery($dbc,$query);

	//Doctor Secretary
	$query='ALTER TABLE `secretary` ADD CONSTRAINT `doctor secretary` FOREIGN KEY (`doctor_id`) REFERENCES `doctor`(`id`)
	 ON DELETE SET NULL ON UPDATE CASCADE';
	 executeQuery($dbc,$query);

	//Patient
	$query='CREATE TABLE `mhamad`.`patient` (`id` INT NOT NULL AUTO_INCREMENT , `email` VARCHAR(30) NOT NULL , `password` VARCHAR(255) NOT NULL ,
	`first_name` VARCHAR(30) NOT NULL , `last_name` VARCHAR(30) NOT NULL , `age` INT NOT NULL , `gender` CHAR(1) NOT NULL ,
	`phone` VARCHAR(30) NULL , `role` VARCHAR(30) NOT NULL DEFAULT "patient" , PRIMARY KEY (`id`), UNIQUE (`email`))';
	executeQuery($dbc,$query);

	//Payment
	$query='CREATE TABLE `mhamad`.`payment` (`patient_id` INT NOT NULL , `doctor_id` INT NOT NULL , `balance` INT NOT NULL )';
	executeQuery($dbc,$query);

	//Payment with doctor and patient
	$query='ALTER TABLE `payment` ADD CONSTRAINT `balance_with_doctor` FOREIGN KEY (`doctor_id`) REFERENCES `doctor`(`id`)
	ON DELETE RESTRICT ON UPDATE CASCADE';
	executeQuery($dbc,$query);
	$query='ALTER TABLE `payment` ADD CONSTRAINT `balance_with_patient` FOREIGN KEY (`patient_id`)
	REFERENCES `patient`(`id`) ON DELETE RESTRICT ON UPDATE CASCADE';
	executeQuery($dbc,$query);

	//Appointment
	$query='CREATE TABLE `mhamad`. `appointment` (`id` INT NOT NULL AUTO_INCREMENT , `doctor_id` INT NOT NULL , `patient_id` INT NOT NULL ,
	`department_id` INT NOT NULL , `start_date` DATE NOT NULL , `end_date` DATE NOT NULL , `bill` INT NOT NULL ,
	`status` VARCHAR(30) NOT NULL DEFAULT "upcoming" , PRIMARY KEY (`id`))';
	executeQuery($dbc,$query);

	//Appointment with doctor and patient in department
	$query='ALTER TABLE `appointment` ADD CONSTRAINT `doctor_appointment` FOREIGN KEY (`doctor_id`) REFERENCES `doctor`(`id`) 
	ON DELETE RESTRICT ON UPDATE CASCADE';
	executeQuery($dbc,$query);
	$query='ALTER TABLE `appointment` ADD CONSTRAINT `patient_appointment` FOREIGN KEY (`patient_id`) REFERENCES `patient`(`id`) 
	ON DELETE RESTRICT ON UPDATE CASCADE';
	executeQuery($dbc,$query);
	$query='ALTER TABLE `appointment` ADD CONSTRAINT `appointment_place` FOREIGN KEY (`department_id`)
	REFERENCES `department`(`id`) ON DELETE RESTRICT ON UPDATE NO ACTION';
	executeQuery($dbc,$query);

	//Document
	$query='CREATE TABLE `mhamad`.`document` (`id` INT NOT NULL AUTO_INCREMENT , `appointment_id` INT NOT NULL ,
	`details` TEXT NULL , PRIMARY KEY (`id`))';
	executeQuery($dbc,$query);

	//Appointment Document
	$query='ALTER TABLE `document` ADD CONSTRAINT `appointment_document` FOREIGN KEY (`appointment_id`)
	REFERENCES `appointment`(`id`) ON DELETE CASCADE ON UPDATE CASCADE';
	executeQuery($dbc,$query);

	//Black List
	$query='CREATE TABLE `mhamad`.`black_list` (`patient_id` INT NOT NULL ,
	`doctor_id` INT NOT NULL , `end_date` DATE NOT NULL )';
	executeQuery($dbc,$query);

	//Doctor block patient
	$query='ALTER TABLE `black_list` ADD CONSTRAINT `block_patient` FOREIGN KEY (`patient_id`) REFERENCES `patient`(`id`)
	ON DELETE CASCADE ON UPDATE CASCADE';
	executeQuery($dbc,$query);
	$query='ALTER TABLE `black_list` ADD CONSTRAINT `doctor_block` FOREIGN KEY (`doctor_id`)
	REFERENCES `doctor`(`id`) ON DELETE CASCADE ON UPDATE CASCADE';
	executeQuery($dbc,$query);

	mysqli_close($dbc); // Close the connection.
?>
 </body>
 </html>
