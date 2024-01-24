USE clinic_db;
INSERT INTO city (city_name) VALUES ("Beirut"),("Tripoli"),("Sidon"),("Jounieh"),("Tyre"),
("Byblos"),("Baalbek"),("Zahle"),("Nabatieh"),("Ain Dara"),("Saida"),("Batroun"),("Anjar"),
("Bcharre"),("Hermel");

INSERT INTO department(city_id, details, room) VALUES 
(1,"Near Lebanese University, in the building of ABC Mall",1),
 (1,"Verdan, Opposite ABC Shopping Center, next to Hotel XYZ ",2),
(1,"Gemmayzeh, Close to Saint Nicholas Stairs, in the heart of Gemmayzeh",3),
(1,"Achrafieh, In the vicinity of Sassine Square, in a residential complex",4),
(1,"Raouche, Near Pigeon Rocks, along the Corniche, in a seaside building",5), 
(7,"The old souq",6),
(7,"Douris, Opposite to the XYZ station",7), (7,"Near Baalback Castle",8),
(7,"Hay Bab Al Hara",9),
(15,"Al dawra, Housseiny Center",10), (15,"Al sabil, in the building of Fayyad",11),
(15,"Al maali, near Al Aytam Station",12);

INSERT INTO specialization (alias) VALUES
("Cardiology"),
("Dermatology"),
("Endocrinology"),
("Gastroenterology"),
("Hematology"),
("Neurology"),
("Ophthalmology");

INSERT INTO doctor (department_id, email, password, first_name, last_name, age, gender, specialization_id, role, phone) VALUES 
    (1, "alijaber@gmail.com", "123", "Ali", "Jaber", 20, "M", 1, "doctor", "00000000"),
    (2, "b@gmail.com", "123", "Ahmad", "Fakih", 20, "M", 1, "doctor", "00000000"),
    (3, "c@gmail.com", "123", "Sandy", "Shiha", 20, "F", 1, "doctor", "00000000"),
    (4, "d@gmail.com", "123", "Lamis", "Oghlo", 20, "F", 2, "doctor", "00000000"),
    (5, "e@gmail.com", "123", "Asaad", "Chehine", 20, "M", 3, "doctor", "00000000"),
    (6, "f@gmail.com", "123", "Fatima", "Hamade", 20, "F", 4, "doctor", "00000000"),
    (7, "g@gmail.com", "123", "Lara", "Ayoub", 20, "F", 2, "doctor", "00000000"),
    (8, "h@gmail.com", "123", "Ayman", "Mokh", 20, "M", 5, "doctor", "00000000"),
    (9, "i@gmail.com", "123", "Ali", "Assi", 20, "M", 6, "doctor", "00000000"),
    (10, "j@gmail.com", "123", "Hasan", "Hameyeh", 20, "M", 7, "doctor", "00000000");

INSERT INTO secretary (doctor_id,email, password, first_name, last_name, age, gender, role, phone) VALUES 
    (1, "sa@gmail.com", "123", "Assan", "Fakih", 20, "F", "secretary", "11111111"),
    (2, "sb@gmail.com", "123", "Yara", "Ali", 20, "F", "secretary", "11111111"),
    (3, "sc@gmail.com", "123", "Chaza", "Mhamud", 20, "F", "secretary", "11111111"),
    (4, "sd@gmail.com", "123", "Lamis", "Abdulla", 20, "F", "secretary", "11111111"),
    (5, "se@gmail.com", "123", "Mhamad", "Hoteit", 20, "M", "secretary", "11111111"),
    (6, "sf@gmail.com", "123", "Yo3rob", "Tala", 20, "M", "secretary", "11111111"),
    (7, "sg@gmail.com", "123", "Yassin", "Fakih", 20, "M", "secretary", "11111111"),
    (8, "sh@gmail.com", "123", "Ayman", "Shamas", 20, "M", "secretary", "11111111"),
    (9, "si@gmail.com", "123", "Ali", "Fawaz", 20, "M", "secretary", "11111111"),
    (10, "sj@gmail.com", "123", "Hasan", "Ayoub", 20, "M", "secretary", "11111111");

INSERT INTO patient (first_name, last_name, email, password, age, gender, role)
VALUES
('Mhamad', 'fakih', 'fakih@gmail.com', '123', 25, 'M', 'patient'),
('Mohamed', 'Ali', 'mohamed.ali1@example.com', 'password123', 25, 'M', 'patient'),
('Fatima', 'Khaled', 'fatima.khaled2@example.com', 'password456', 30, 'F', 'patient'),
('Ahmed', 'Hassan', 'ahmed.hassan3@example.com', 'password789', 22, 'M', 'patient'),
('Aisha', 'Mahmoud', 'aisha.mahmoud4@example.com', 'passwordabc', 35, 'F', 'patient'),
('Omar', 'Abdullah', 'omar.abdullah5@example.com', 'passworddef', 28, 'M', 'patient'),
('Layla', 'Hassan', 'layla.hassan6@example.com', 'passwordghi', 32, 'F', 'patient'),
('Ali', 'Ahmed', 'ali.ahmed7@example.com', 'passwordjkl', 27, 'M', 'patient'),
('Nour', 'Salem', 'nour.salem8@example.com', 'passwordmno', 31, 'F', 'patient'),
('Khaled', 'Omar', 'khaled.omar9@example.com', 'passwordpqr', 29, 'M', 'patient'),
('Sara', 'Ibrahim', 'sara.ibrahim10@example.com', 'passwordstu', 26, 'F', 'patient'),
('Youssef', 'Mohamed', 'youssef.mohamed11@example.com', 'passwordvwx', 33, 'M', 'patient'),
('Hana', 'Hassan', 'hana.hassan12@example.com', 'passwordyz', 28, 'F', 'patient'),
('Amir', 'Omar', 'amir.omar13@example.com', 'password123', 30, 'M', 'patient'),
('Leila', 'Ahmed', 'leila.ahmed14@example.com', 'password456', 25, 'F', 'patient'),
('Fadi', 'Nour', 'fadi.nour15@example.com', 'password789', 29, 'M', 'patient'),
('Rana', 'Ali', 'rana.ali16@example.com', 'passwordabc', 31, 'F', 'patient'),
('Omar', 'Khalid', 'omar.khalid17@example.com', 'passworddef', 27, 'M', 'patient'),
('Habiba', 'Sami', 'habiba.sami18@example.com', 'passwordghi', 34, 'F', 'patient'),
('Majid', 'Layla', 'majid.layla19@example.com', 'passwordjkl', 26, 'M', 'patient'),
('Rasha', 'Adel', 'rasha.adel20@example.com', 'passwordmno', 28, 'F', 'patient');
    
INSERT INTO unavailable_slots (doctor_id,department_id, start_date, end_date)
VALUES
    (1,1, '2024-02-15 11:00:00', '2024-02-15 13:00:00'),
    (1,1, '2024-02-15 12:30:00', '2024-02-15 14:00:00'),
    (1,1, '2024-01-30 09:00:00', '2024-01-30 11:00:00'),
    (2,2, '2024-01-30 10:30:00', '2024-01-30 12:30:00'),
    (4,4, '2024-03-07 13:45:00', '2024-03-07 15:45:00'),
    (5,5, '2024-03-08 08:15:00', '2024-03-08 10:15:00'),
    (8,8, '2024-03-11 12:00:00', '2024-03-11 14:00:00'),
    (9,9, '2024-03-12 14:15:00', '2024-03-12 16:15:00'),
    (1,1, '2024-03-13 15:30:00', '2024-03-13 17:30:00'),
    (1,1, '2024-03-14 08:45:00', '2024-03-14 10:45:00'),
    (3,3, '2024-04-11 10:15:00', '2024-04-11 12:15:00'),
    (4,4, '2024-04-15 11:30:00', '2024-04-15 13:30:00'),
    (5,5, '2024-04-15 12:45:00', '2024-04-15 14:45:00'),
    (6,6, '2024-04-16 14:00:00', '2024-04-16 16:00:00'),
    (7,7, '2024-04-17 15:15:00', '2024-04-17 17:15:00'),
    (1,1, '2024-04-22 12:15:00', '2024-04-22 14:15:00'),
    (2,2, '2024-04-23 13:30:00', '2024-04-23 15:30:00'),
    (5,5, '2024-02-26 08:15:00', '2024-02-26 10:15:00'),
    (6,6, '2024-02-27 09:30:00', '2024-02-27 11:30:00'),
    (7,7, '2024-02-28 10:45:00', '2024-02-28 12:45:00'),
    (8,8, '2024-02-28 12:00:00', '2024-02-28 14:00:00'),
    (9,9, '2024-02-01 13:15:00', '2024-02-01 15:15:00'),
    (1,1, '2024-02-02 14:30:00', '2024-02-02 16:30:00'),
    (3,3, '2024-02-05 10:15:00', '2024-02-05 12:15:00'),
    (4,4, '2024-02-06 11:30:00', '2024-02-06 13:30:00'),
    (5,5, '2024-02-07 12:45:00', '2024-02-07 14:45:00'),
    (6,6, '2024-02-08 14:00:00', '2024-02-08 16:00:00'),
    (7,7, '2024-02-09 15:15:00', '2024-02-09 17:15:00'),
    (1,1, '2024-02-12 11:00:00', '2024-02-12 13:00:00');

INSERT INTO appointment (department_id,doctor_id, patient_id, start_date, end_date)
VALUES
    -- Appointment used to show the functionnality of delay appointments
    (1,1, 11, '2024-01-26 08:30:00', '2024-01-26 09:30:00'),
    (1,1, 1, '2024-01-26 10:00:00', '2024-01-26 11:00:00'),
    (1,1, 2, '2024-01-26 12:00:00', '2024-01-26 14:00:00'),

    (1,1, 11, '2024-02-15 08:30:00', '2024-02-15 09:30:00'),
    (1,1, 1, '2024-02-15 08:00:00', '2024-02-15 09:00:00'),
    (1,1, 2, '2024-02-15 10:00:00', '2024-02-15 11:00:00'),
    (1,1, 3, '2024-02-15 09:30:00', '2024-02-15 10:30:00'),
    (1,1, 4, '2024-02-15 09:00:00', '2024-02-15 10:00:00'),
    (1,1, 5, '2024-02-15 14:30:00', '2024-02-15 15:30:00'),
    (1,1, 6, '2024-02-15 14:30:00', '2024-02-15 15:00:00'),
    (1,1, 7, '2024-02-15 15:30:00', '2024-02-15 16:00:00'),
    (1,1, 8, '2024-02-15 14:00:00', '2024-02-15 15:30:00'),
    (1,1, 9, '2024-02-15 14:00:00', '2024-02-15 16:00:00'),
    (1,1, 10, '2024-02-15 14:30:00', '2024-02-15 16:00:00'),

    (2,2, 2, '2024-01-31 09:45:00', '2024-01-31 10:45:00'),
    (3,3, 3, '2024-02-01 11:00:00', '2024-02-01 12:00:00'),
    (4,4, 4, '2024-02-02 12:15:00', '2024-02-02 13:15:00'),
    (7,7, 7, '2024-02-05 08:15:00', '2024-02-05 09:15:00'),
    (8,8, 8, '2024-02-06 09:30:00', '2024-02-06 10:30:00'),
    (9,9, 9, '2024-02-07 10:45:00', '2024-02-07 11:45:00'),
    (10,10, 10, '2024-02-08 12:00:00', '2024-02-08 13:00:00'),
    (1,1, 11, '2024-02-09 13:15:00', '2024-02-09 14:15:00'),
    (4,4, 14, '2024-02-12 09:45:00', '2024-02-12 10:45:00'),
    (5,5, 15, '2024-02-13 11:00:00', '2024-02-13 12:00:00'),
    (6,6,16, '2024-02-14 12:15:00', '2024-02-14 13:15:00'),
    (7,7, 17, '2024-02-15 13:30:00', '2024-02-15 14:30:00'),
    (8,8 ,18, '2024-02-16 14:45:00', '2024-02-16 15:45:00'),
    (1,1, 1, '2024-02-19 10:45:00', '2024-02-19 11:45:00'),
    (2,2, 2, '2024-02-20 12:00:00', '2024-02-20 13:00:00'),
    (3,3, 3, '2024-02-21 13:15:00', '2024-02-21 14:15:00'),
    (4,4, 4, '2024-02-22 14:30:00', '2024-02-22 15:30:00'),
    (5,5, 5, '2024-02-23 08:30:00', '2024-02-23 09:30:00'),
    (8,8, 8, '2024-02-26 12:15:00', '2024-02-26 13:15:00'),
    (9,9, 9, '2024-02-27 13:30:00', '2024-02-27 14:30:00'),
    (10,10, 10, '2024-02-28 14:45:00', '2024-02-28 15:45:00');