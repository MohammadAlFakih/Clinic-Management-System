<?php
//Add test data
include "DB_Functions.php";
$dbc = connectServer('localhost', 'root', '', 1);
selectDB($dbc,'mhamad',1);
$query1='INSERT INTO city (city_name) VALUES ("Beirut"),("Tripoli"),("Sidon"),("Jounieh"),("Tyre"),
("Byblos"),("Baalbek"),("Zahle"),("Nabatieh"),("Ain Dara"),("Saida"),("Batroun"),("Anjar"),
("Bcharre"),("Hermel");';
executeQuery($dbc,$query1);
$query2='INSERT INTO department(city_id, details, room) VALUES 
(1,"None",1), (1,"None",2), (1,"None",3),
 (1,"None",4), (1,"None",5), (7,"None",6),
 (7,"None",7), (7,"None",8), (7,"None",9),
 (15,"None",10), (15,"None",11),(15,"None",12);';
 executeQuery($dbc,$query2);
$query3='INSERT INTO specialization (alias) VALUES
("Cardiology"),
("Dermatology"),
("Endocrinology"),
("Gastroenterology"),
("Hematology"),
("Neurology"),
("Ophthalmology"),
("Orthopedics"),
("Pediatrics"),
("Urology");';
executeQuery($dbc,$query3);
$hashed_pass = password_hash("123",PASSWORD_DEFAULT);
$query4 = 'INSERT INTO doctor (department_id, email, password, first_name, last_name, age, gender, specialization_id, role) VALUES 
    (1, "a@gmail.com", "'.$hashed_pass.'", "Amani", "Fakih", 20, "F", 1, "doctor"),
    (2, "b@gmail.com", "'.$hashed_pass.'", "Yara", "Fakih", 20, "F", 1, "doctor"),
    (3, "c@gmail.com", "'.$hashed_pass.'", "Chaza", "Fakih", 20, "F", 1, "doctor"),
    (4, "d@gmail.com", "'.$hashed_pass.'", "Lamis", "Fakih", 20, "F", 2, "doctor"),
    (5, "e@gmail.com", "'.$hashed_pass.'", "Mhamad", "Fakih", 20, "F", 3, "doctor"),
    (6, "f@gmail.com", "'.$hashed_pass.'", "Yo3rob", "Fakih", 20, "F", 3, "doctor"),
    (7, "g@gmail.com", "'.$hashed_pass.'", "Yassin", "Fakih", 20, "F", 2, "doctor"),
    (8, "h@gmail.com", "'.$hashed_pass.'", "Ayman", "Fakih", 20, "F", 5, "doctor"),
    (9, "i@gmail.com", "'.$hashed_pass.'", "Ali", "Fakih", 20, "F", 1, "doctor"),
    (10, "j@gmail.com", "'.$hashed_pass.'", "Hasan", "Fakih", 20, "F", 8, "doctor"),
    (1, "k@gmail.com", "'.$hashed_pass.'", "Romio", "Fakih", 20, "F", 7, "doctor"),
    (2, "l@gmail.com", "'.$hashed_pass.'", "Sandy", "Fakih", 20, "F", 6, "doctor"),
    (3, "m@gmail.com", "'.$hashed_pass.'", "Achraf", "Fakih", 20, "F", 6, "doctor"),
    (4, "n@gmail.com", "'.$hashed_pass.'", "Roro", "Fakih", 20, "F", 6, "doctor");
';
executeQuery($dbc,$query4);

//Insert secretaries
$query4 = 'INSERT INTO secretary (doctor_id,email, password, first_name, last_name, age, gender, role) VALUES 
    (1, "sa@gmail.com", "'.$hashed_pass.'", "Amani", "Fakih", 20, "F", "secretary"),
    (2, "sb@gmail.com", "'.$hashed_pass.'", "Yara", "Fakih", 20, "F", "secretary"),
    (3, "sc@gmail.com", "'.$hashed_pass.'", "Chaza", "Fakih", 20, "F", "secretary"),
    (4, "sd@gmail.com", "'.$hashed_pass.'", "Lamis", "Fakih", 20, "F", "secretary"),
    (5, "se@gmail.com", "'.$hashed_pass.'", "Mhamad", "Fakih", 20, "F", "secretary"),
    (6, "sf@gmail.com", "'.$hashed_pass.'", "Yo3rob", "Fakih", 20, "F", "secretary"),
    (7, "sg@gmail.com", "'.$hashed_pass.'", "Yassin", "Fakih", 20, "F", "secretary"),
    (8, "sh@gmail.com", "'.$hashed_pass.'", "Ayman", "Fakih", 20, "F", "secretary"),
    (9, "si@gmail.com", "'.$hashed_pass.'", "Ali", "Fakih", 20, "F", "secretary"),
    (10, "sj@gmail.com", "'.$hashed_pass.'", "Hasan", "Fakih", 20, "F", "secretary"),
    (1, "sk@gmail.com", "'.$hashed_pass.'", "Romio", "Fakih", 20, "F", "secretary"),
    (2, "sl@gmail.com", "'.$hashed_pass.'", "Sandy", "Fakih", 20, "F", "secretary"),
    (3, "sm@gmail.com", "'.$hashed_pass.'", "Achraf", "Fakih", 20, "F","secretary"),
    (4, "sn@gmail.com", "'.$hashed_pass.'", "Roro", "Fakih", 20, "F","secretary");
';
executeQuery($dbc,$query4);


mysqli_close($dbc);
?>