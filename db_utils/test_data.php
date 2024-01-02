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
$query4='INSERT INTO doctor( department_id, email, password, first_name, last_name, age, gender, specialization_id, role) VALUES 
(1,"a@gmail.com","123","Amani","Fakih",20,"F",1,"doctor"),
(2,"b@gmail.com","123","Yara","Fakih",20,"F",1,"doctor"),
(3,"c@gmail.com","123","Chaza","Fakih",20,"F",1,"doctor"),
(4,"d@gmail.com","123","Lamis","Fakih",20,"F",2,"doctor"),
(5,"e@gmail.com","123","Mhamad","Fakih",20,"F",3,"doctor"),
(6,"f@gmail.com","123","Yo3rob","Fakih",20,"F",3,"doctor"),
(7,"g@gmail.com","123","Yassin","Fakih",20,"F",2,"doctor"),
(8,"h@gmail.com","123","Ayman","Fakih",20,"F",5,"doctor"),
(9,"i@gmail.com","123","Ali","Fakih",20,"F",1,"doctor"),
(10,"j@gmail.com","123","Hasan","Fakih",20,"F",8,"doctor"),
(1,"k@gmail.com","123","Romio","Fakih",20,"F",7,"doctor"),
(2,"l@gmail.com","123","Sandy","Fakih",20,"F",6,"doctor"),
(3,"m@gmail.com","123","Achraf","Fakih",20,"F",6,"doctor"),
(4,"n@gmail.com","123","Roro","Fakih",20,"F",6,"doctor");
';
executeQuery($dbc,$query4);
mysqli_close($dbc);
?>