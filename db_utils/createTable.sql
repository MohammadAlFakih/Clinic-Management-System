-- Use Database
USE clinic_db;

-- Create Table: department
CREATE TABLE IF NOT EXISTS `department` (
    `id` INT NOT NULL AUTO_INCREMENT,
    `city_id` INT NOT NULL,
    `details` TEXT NULL,
    `room` INT NULL,
    PRIMARY KEY (`id`)
);

-- Create Table: city
CREATE TABLE IF NOT EXISTS `city` (
    `id` INT NOT NULL AUTO_INCREMENT,
    `city_name` VARCHAR(50) NOT NULL,
    PRIMARY KEY (`id`)
);

-- Create Table: specialization
CREATE TABLE IF NOT EXISTS `specialization` (
    `id` INT NOT NULL AUTO_INCREMENT,
    `alias` VARCHAR(50) NOT NULL,
    PRIMARY KEY (`id`)
);

-- Create Table: doctor
CREATE TABLE IF NOT EXISTS `doctor` (
    `id` INT NOT NULL AUTO_INCREMENT,
    `department_id` INT NOT NULL,
    `email` VARCHAR(30) NOT NULL,
    `password` TEXT NOT NULL,
    `first_name` VARCHAR(30) NOT NULL,
    `last_name` VARCHAR(30) NOT NULL,
    `age` INT NOT NULL,
    `gender` CHAR(1) NOT NULL,
    `phone` VARCHAR(30) NOT NULL,
    `specialization_id` INT NOT NULL,
    `role` VARCHAR(30) NOT NULL DEFAULT 'doctor',
    `pp` VARCHAR(255) NOT NULL DEFAULT 'default.png',
    PRIMARY KEY (`id`),
    UNIQUE (`email`),
    FOREIGN KEY (`department_id`) REFERENCES `department`(`id`) ON DELETE RESTRICT ON UPDATE CASCADE,
    FOREIGN KEY (`specialization_id`) REFERENCES `specialization`(`id`) ON DELETE CASCADE ON UPDATE CASCADE
);

-- Create Table: secretary
CREATE TABLE IF NOT EXISTS `secretary` (
    `id` INT NOT NULL AUTO_INCREMENT,
    `doctor_id` INT NULL,
    `email` VARCHAR(30) NOT NULL,
    `password` VARCHAR(255) NOT NULL,
    `first_name` VARCHAR(30) NOT NULL,
    `last_name` VARCHAR(30) NOT NULL,
    `age` INT NOT NULL,
    `gender` CHAR(1) NOT NULL,
    `phone` VARCHAR(30) NOT NULL,
    `role` VARCHAR(30) NOT NULL DEFAULT 'secretary',
    `pp` VARCHAR(255) NOT NULL DEFAULT 'default.png',
    PRIMARY KEY (`id`),
    UNIQUE (`email`),
    FOREIGN KEY (`doctor_id`) REFERENCES `doctor`(`id`) ON DELETE SET NULL ON UPDATE CASCADE
);

-- Create Table: patient
CREATE TABLE IF NOT EXISTS `patient` (
    `id` INT NOT NULL AUTO_INCREMENT,
    `email` VARCHAR(30) NOT NULL,
    `password` VARCHAR(255) NOT NULL,
    `first_name` VARCHAR(30) NOT NULL,
    `last_name` VARCHAR(30) NOT NULL,
    `age` INT NOT NULL,
    `gender` CHAR(1) NOT NULL,
    `phone` VARCHAR(30) NULL,
    `role` VARCHAR(30) NOT NULL DEFAULT 'patient',
    `pp` VARCHAR(255) NOT NULL DEFAULT 'default.png',
    PRIMARY KEY (`id`),
    UNIQUE (`email`)
);

-- Create Table: payment
CREATE TABLE IF NOT EXISTS `payment` (
    `patient_id` INT NOT NULL,
    `doctor_id` INT NOT NULL,
    `balance` INT NOT NULL,
    FOREIGN KEY (`doctor_id`) REFERENCES `doctor`(`id`) ON DELETE RESTRICT ON UPDATE CASCADE,
    FOREIGN KEY (`patient_id`) REFERENCES `patient`(`id`) ON DELETE RESTRICT ON UPDATE CASCADE
);

-- Create Table: appointment
CREATE TABLE IF NOT EXISTS `appointment` (
    `id` INT NOT NULL AUTO_INCREMENT,
    `doctor_id` INT NOT NULL,
    `patient_id` INT NOT NULL,
    `department_id` INT NOT NULL,
    `start_date` DATETIME NOT NULL,
    `end_date` DATETIME NOT NULL,
    `status` VARCHAR(30) NOT NULL DEFAULT 'pending',
    `book_date` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `bill` FLOAT NOT NULL DEFAULT 0,
    `payed` FLOAT NOT NULL DEFAULT 0,
    PRIMARY KEY (`id`),
    FOREIGN KEY (`doctor_id`) REFERENCES `doctor`(`id`) ON DELETE RESTRICT ON UPDATE CASCADE,
    FOREIGN KEY (`patient_id`) REFERENCES `patient`(`id`) ON DELETE RESTRICT ON UPDATE CASCADE,
    FOREIGN KEY (`department_id`) REFERENCES `department`(`id`) ON DELETE RESTRICT ON UPDATE NO ACTION
);

-- Create Table: document
CREATE TABLE IF NOT EXISTS `document` (
    `id` INT NOT NULL AUTO_INCREMENT,
    `appointment_id` INT NOT NULL,
    `details` TEXT NULL,
    `prescription` TEXT NULL,
    PRIMARY KEY (`id`),
    FOREIGN KEY (`appointment_id`) REFERENCES `appointment`(`id`) ON DELETE CASCADE ON UPDATE CASCADE
);

-- Create Table: black_list
CREATE TABLE IF NOT EXISTS `black_list` (
    `patient_id` INT NOT NULL,
    `doctor_id` INT NOT NULL,
    `end_date` DATE NOT NULL,
    PRIMARY KEY (`patient_id`, `doctor_id`),
    FOREIGN KEY (`patient_id`) REFERENCES `patient`(`id`) ON DELETE CASCADE ON UPDATE CASCADE,
    FOREIGN KEY (`doctor_id`) REFERENCES `doctor`(`id`) ON DELETE CASCADE ON UPDATE CASCADE
);

-- Create Table: week_schedule
CREATE TABLE IF NOT EXISTS `week_schedule` (
    `day` VARCHAR(12) NOT NULL,
    `doctor_id` INT NOT NULL,
    `start_hour` TIME NOT NULL,
    `end_hour` TIME NOT NULL,
    PRIMARY KEY (`doctor_id`, `day`),
    FOREIGN KEY (`doctor_id`) REFERENCES `doctor`(`id`) ON DELETE CASCADE ON UPDATE CASCADE
);

-- Create Table: unavailable_slots
CREATE TABLE IF NOT EXISTS `unavailable_slots` (
    `id` INT NOT NULL AUTO_INCREMENT,
    `doctor_id` INT NOT NULL,
    `start_date` DATETIME NOT NULL,
    `end_date` DATETIME NOT NULL,
    `department_id` INT NOT NULL,
    PRIMARY KEY (`id`),
    FOREIGN KEY (`doctor_id`) REFERENCES `doctor`(`id`) ON DELETE CASCADE ON UPDATE CASCADE,
    FOREIGN KEY (`department_id`) REFERENCES `department`(`id`) ON DELETE CASCADE ON UPDATE CASCADE
);

-- Create Table: notifications
CREATE TABLE IF NOT EXISTS `notifications` (
    `id` INT NOT NULL AUTO_INCREMENT,
    `receiver` INT NOT NULL,
    `sender` INT NOT NULL,
    `message` TEXT NOT NULL,
    `date` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `status` VARCHAR(10) NOT NULL DEFAULT 'unread',
    `appointment_id` INT NULL,
    `reason` VARCHAR(10) NOT NULL,
    PRIMARY KEY (`id`),
    FOREIGN KEY (`receiver`) REFERENCES `patient`(`id`) ON DELETE CASCADE ON UPDATE CASCADE,
    FOREIGN KEY (`sender`) REFERENCES `doctor`(`id`) ON DELETE NO ACTION ON UPDATE CASCADE,
    FOREIGN KEY (`appointment_id`) REFERENCES `appointment`(`id`) ON DELETE CASCADE ON UPDATE CASCADE
);

-- Create Trigger: new_document
DELIMITER /
CREATE TRIGGER `new_document` AFTER INSERT ON `appointment` FOR EACH ROW
BEGIN
    INSERT INTO `document` (`appointment_id`, `details`, `prescription`) VALUES (NEW.id, NULL, NULL);
END;
/
DELIMITER ;

-- Create Trigger: Default_week_schedule
DELIMITER /
CREATE TRIGGER `Default_week_schedule` AFTER INSERT ON `doctor` FOR EACH ROW
BEGIN
    INSERT INTO `week_schedule` (`doctor_id`, `day`, `start_hour`, `end_hour`)
    VALUES
        (NEW.id, 'monday', '08:00', '16:00'),
        (NEW.id, 'tuesday', '08:00', '16:00'),
        (NEW.id, 'wednesday', '08:00', '16:00'),
        (NEW.id, 'thursday', '08:00', '16:00'),
        (NEW.id, 'friday', '08:00', '16:00'),
        (NEW.id, 'saturday', '08:00', '08:00'),
        (NEW.id, 'sunday', '08:00', '08:00');
END;
/
DELIMITER ;

-- Create Trigger: hash password before insert
DELIMITER /
CREATE TRIGGER `hash_password_patient` BEFORE INSERT ON `patient` FOR EACH ROW
BEGIN
    SET NEW.password = SHA2(NEW.password,0);
END;
/
CREATE TRIGGER `hash_password_doctor` BEFORE INSERT ON `doctor` FOR EACH ROW
BEGIN
    SET NEW.password = SHA2(NEW.password,0);
END;
/
CREATE TRIGGER `hash_password_secretary` BEFORE INSERT ON `secretary` FOR EACH ROW
BEGIN
    SET NEW.password = SHA2(NEW.password,0);
END;
/
DELIMITER ;
