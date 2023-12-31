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
        $query =   "SELECT password,role,id,first_name FROM patient WHERE email = ?
                    UNION
                    SELECT password,role,id,first_name FROM doctor WHERE email = ?
                    UNION
                    SELECT password,role,id,first_name FROM secretary WHERE email = ?";
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

    function get_doctors($data,$dbc){
        $city = $data['city'];
        $specialization = $data['specialization'];
        $date = $data['date'];
        $dateTime = new DateTime($date);
        $dayOfWeek = $dateTime->format('l');
        $dayOfWeek = strtolower($dayOfWeek);

        $query = "SELECT doc.id, doc.first_name,doc.last_name,dep.city,sc.sequence,dep.details,we.start_hour,we.end_hour
                 FROM doctor doc
                 JOIN department dep ON doc.department_id = dep.id
                 LEFT JOIN schedule sc ON doc.id = sc.doctor_id AND sc.date = ? 
                 JOIN week_schedule we ON we.doctor_id = doc.id
                 WHERE dep.city = ? AND doc.specialization = ? AND we.day = ?";
        $stmt = $dbc->prepare($query);
        $stmt->bind_param("ssss",$date,$city,$specialization,$dayOfWeek);
        $stmt->execute();
        $result = $stmt->get_result();
        $stmt->close();


        $doctors = [];
        while($row = $result->fetch_assoc()){
            if($row['sequence']==null){
                $query="INSERT INTO schedule (doctor_id,sequence,date)
                VALUES (?,?,?)";
                $stmt = $dbc->prepare($query);
                $hours_of_work = $row['end_hour']-$row['start_hour'];
                $hours_of_work = intval($hours_of_work*2);
                $default = str_repeat("F",$hours_of_work);
                $stmt->bind_param("iss",$row['id'],$default,$date);
                $stmt->execute();
                $stmt->close();
                $row['sequence'] = $default;
            }
            $doctors[] = $row;
        }
        return $doctors;
    }

    function check_status($sequence){
        if($sequence=='B')
            return 'not-free';
        else
            return 'free';
    }

    function float_to_hour($decimalHours){
        $hours = floor($decimalHours);
        $minutes = ($decimalHours - $hours) * 60;
        $time = "";
        if($hours<10){
            $time="0";
        }
        $time.= $hours.":";
        if($minutes<10){
            $time.="0";
        }
        $time.= $minutes;
        return $time;
    }

    function get_doctor($data,$dbc){
        $city = $data['city'];
        $specialization = $data['specialization'];
        $date = $data['date'];
        $dateTime = new DateTime($date);
        $dayOfWeek = $dateTime->format('l');
        $dayOfWeek = strtolower($dayOfWeek);
        $id = $data['index'];

        $query = "SELECT doc.id, doc.first_name,doc.last_name,dep.city,sc.sequence,dep.details,we.start_hour,we.end_hour
        FROM doctor doc
        JOIN department dep ON doc.department_id = dep.id
        JOIN week_schedule we ON doc.id = we.doctor_id
        LEFT JOIN schedule sc ON doc.id = sc.doctor_id AND sc.date = ? 
        WHERE doc.id = ? AND dep.city = ? AND doc.specialization = ? AND we.day= ?";
        $stmt = $dbc->prepare($query);
        $stmt->bind_param("sisss",$date,$id,$city,$specialization,$dayOfWeek);
        $stmt->execute();
        $result = $stmt->get_result();
        $stmt->close();
        return $result->fetch_assoc();
    }
?>