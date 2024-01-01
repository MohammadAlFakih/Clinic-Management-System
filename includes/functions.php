<?php 
    function email_exists($email,$dbc){
        $query1 =  "SELECT email FROM patient WHERE email = ?
                    UNION
                    SELECT email FROM doctor WHERE email = ?
                    UNION
                    SELECT email FROM secretary WHERE email = ?";
        $stmt = $dbc->prepare($query1);
        $stmt->bind_param('sss',$email,$email,$email);
        $stmt->execute();
        $result = $stmt->get_result();
        if($result->num_rows > 0){
            return true;
        }
        else{
            return false;
        }
    }

    function hash_password($password){
        $digest=password_hash($password,PASSWORD_DEFAULT);
        return $digest;
    }

    function insert_patient($patient,$dbc){
        $query="INSERT INTO patient (first_name,last_name,email,password,gender,age,phone)
         VALUES (?,?,?,?,?,?,?);";
        $stmt = $dbc->prepare($query);
        $stmt->bind_param("sssssis",$patient->first_name,$patient->last_name,$patient->email,$patient->password,$patient->gender,$patient->age,$patient->phone);
        // Execute the query
        $stmt->execute();
        // Close the statement
        $stmt->close();
    }

    function login($email,$password,$dbc){
        $email=trim($email);
        $password=trim($password);
        $query =   "SELECT password,role,id,first_name,last_name FROM patient WHERE email = ?
                    UNION
                    SELECT password,role,id,first_name,last_name FROM doctor WHERE email = ?
                    UNION
                    SELECT password,role,id,first_name,last_name FROM secretary WHERE email = ?";
        $stmt = $dbc->prepare($query);
        $stmt->bind_param("sss",$email,$email,$email);
        $stmt->execute();
        $result1 =$stmt->get_result();
        if(mysqli_num_rows($result1) > 0){
            $row1 = mysqli_fetch_assoc($result1);
            if(password_verify($password,$row1['password'])){
                return $row1;
            }
            else{
                return null;
            }
        }
    }

    function get_cities($dbc){
        $query = "SELECT city_name FROM city";
        $stmt = $dbc->prepare($query);
        $stmt->execute();
        $result = $stmt->get_result();
        $cities = [];
        while($row = mysqli_fetch_assoc($result)){
            $cities[] = $row['city_name'];
        }
        return $cities;
    }

    function get_specializations($dbc){
        $query = "SELECT alias FROM specialization";
        $stmt = $dbc->prepare($query);
        $stmt->execute();
        $result = $stmt->get_result();
        $specializations = [];
        while($row = mysqli_fetch_assoc($result)){
            $specializations[] = $row['alias'];
        }
        return $specializations;
    }

    function get_doctors($data,$dbc){
        $city = $data['city'];
        $specialization = $data['specialization'];
        $date = $data['date'];
        $dateTime = new DateTime($date);
        $dayOfWeek = $dateTime->format('l');
        $dayOfWeek = strtolower($dayOfWeek);

        $query = "SELECT doc.id doctor_id,dep.id department_id,doc.first_name,doc.last_name,city.city_name,dep.room,dep.details,we.start_hour,we.end_hour
                 FROM doctor doc
                 JOIN department dep ON doc.department_id = dep.id
                 JOIN city ON dep.city_id = city.id
                 JOIN specialization sp ON sp.id = doc.specialization_id
                 JOIN week_schedule we ON we.doctor_id = doc.id
                 WHERE city.city_name = ? AND sp.alias = ? AND we.day = ?";

        $stmt = $dbc->prepare($query);
        $stmt->bind_param("sss",$city,$specialization,$dayOfWeek);
        $stmt->execute();
        $result = $stmt->get_result();
        $stmt->close();


        $doctors = [];
        while($row = $result->fetch_assoc()){
            //For each doctor check his appointments and unavailable time
            $booked_time = [];
            $unavailable_time = [];

            //Doctor Appointments dates
            $query = "SELECT app.start_date, app.end_date
                    FROM appointment app
                    WHERE app.doctor_id = ? AND DATE(app.start_date) = ? AND app.department_id = ?";
            $stmt = $dbc->prepare($query);
            $stmt->bind_param("isi",$row['doctor_id'],$date,$row['department_id']);
            $stmt->execute();
            $appointments = $stmt->get_result();
            $stmt->close();
            
            while($appointment = $appointments->fetch_assoc()){
                $booked_time[] = array('start_date' => $appointment['start_date'], 'end_date' => $appointment['end_date']);
            }

            //Doctor unavailable time
            $query = "SELECT un.start_date, un.end_date
                    FROM unavailable_slots un
                    WHERE un.doctor_id = ? AND DATE(un.start_date) =? AND un.department_id =?";
            $stmt = $dbc->prepare($query);
            $stmt->bind_param("isi",$row['doctor_id'],$date,$row['department_id']);
            $stmt->execute();
            $appointments = $stmt->get_result();
            $stmt->close();
            while($appointment = $appointments->fetch_assoc()){
                $unavailable_time[] = array('start_date' => $appointment['start_date'], 'end_date' => $appointment['end_date']);
            }

            $row['booked_time'] = $booked_time;
            $row['unavailable_time'] = $unavailable_time;
            $doctors[] = $row;
        }
        return $doctors;
    }


    function get_doctor($data,$dbc){
        $city = $data['city'];
        $specialization = $data['specialization'];
        $date = $data['date'];
        $dateTime = new DateTime($date);
        $dayOfWeek = $dateTime->format('l');
        $dayOfWeek = strtolower($dayOfWeek);
        $doctor_id = $data['index'];

        $query = "SELECT doc.id doctor_id, doc.first_name,doc.last_name,city.city_name,
        dep.details,we.start_hour,we.end_hour,doc.department_id,dep.room
        FROM doctor doc
        JOIN department dep ON doc.department_id = dep.id
        JOIN week_schedule we ON doc.id = we.doctor_id
        JOIN specialization sp ON sp.id = doc.specialization_id
        JOIN city ON city.id = dep.city_id
        WHERE doc.id = ? AND city.city_name = ? AND sp.alias = ? AND we.day= ?";
        $stmt = $dbc->prepare($query);
        $stmt->bind_param("isss",$doctor_id,$city,$specialization,$dayOfWeek);
        $stmt->execute();
        $result = $stmt->get_result();
        $stmt->close();

        $doctor = $result->fetch_assoc();

        //Check the doctor appointments and unavailable time
        $booked_time = [];
        $unavailable_time = [];

        //Doctor Appointments dates
        $query = "SELECT app.start_date, app.end_date
                FROM appointment app
                WHERE app.doctor_id = ? AND DATE(app.start_date) = ? AND app.department_id = ?";
        $stmt = $dbc->prepare($query);
        $stmt->bind_param("isi",$doctor['doctor_id'],$date,$doctor['department_id']);
        $stmt->execute();
        $appointments = $stmt->get_result();
        $stmt->close();
        
        while($appointment = $appointments->fetch_assoc()){
            $booked_time[] = array('start_date' => $appointment['start_date'], 'end_date' => $appointment['end_date']);
        }

        //Doctor unavailable time
        $query = "SELECT un.start_date, un.end_date
                FROM unavailable_slots un
                WHERE un.doctor_id = ? AND DATE(un.start_date) =? AND un.department_id =?";
        $stmt = $dbc->prepare($query);
        $stmt->bind_param("isi",$doctor['doctor_id'],$date,$doctor['department_id']);
        $stmt->execute();
        $appointments = $stmt->get_result();
        $stmt->close();
        while($appointment = $appointments->fetch_assoc()){
            $unavailable_time[] = array('start_date' => $appointment['start_date'], 'end_date' => $appointment['end_date']);
        }

        $doctor['booked_time'] = $booked_time;
        $doctor['unavailable_time'] = $unavailable_time;
        
        return $doctor;

    }

    function get_appointments($dbc,$id){
        $query = "SELECT app.*,dep.city,dep.details,doc.first_name,doc.last_name,doc.specialization
                FROM appointment app
                JOIN doctor doc ON app.doctor_id = doc.id
                JOIN department dep ON app.department_id = dep.id
                WHERE app.patient_id = ?";
        $stmt = $dbc->prepare($query);
        $stmt->bind_param("i",$id);
        $stmt->execute();
        $result = $stmt->get_result();
        $stmt->close();
        return $result;
    }
?>