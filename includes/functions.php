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
        $query = "SELECT doc.id, doc.first_name,doc.last_name,dep.city,sc.sequence,dep.details
                 FROM doctor doc
                 JOIN department dep ON doc.department_id = dep.id
                 LEFT JOIN schedule sc ON doc.id = sc.doctor_id AND sc.date = ? 
                 WHERE dep.city = ? AND doc.specialization = ? ";
        $stmt = $dbc->prepare($query);
        $stmt->bind_param("sss",$date,$city,$specialization);
        $stmt->execute();
        $result = $stmt->get_result();
        $stmt->close();
        $doctors = [];
        while($row = $result->fetch_assoc()){
            if($row['sequence']==null){
                $query="INSERT INTO schedule (doctor_id,sequence,date)
                VALUES (?,?,?)";
                $stmt = $dbc->prepare($query);
                $default = "FFFFFFFFFF";
                $stmt->bind_param("iss",$row['id'],$default,$date);
                $stmt->execute();
                $stmt->close();
                $row['sequence'] = $default;
            }
            $doctors[] = $row;
        }
        return $doctors;
    }

    function check_status($sequence,$i){
        if($sequence[$i]=='B')
            return 'not-free';
        else
            return 'free';
    }

    function get_doctor($data,$dbc){
        $city = $data['city'];
        $specialization = $data['specialization'];
        $date = $data['date'];
        $id = $data['index'];
        $query = "SELECT doc.id, doc.first_name,doc.last_name,dep.city,sc.sequence,dep.details
        FROM doctor doc
        JOIN department dep ON doc.department_id = dep.id
        LEFT JOIN schedule sc ON doc.id = sc.doctor_id AND sc.date = ? 
        WHERE doc.id = ? AND dep.city = ? AND doc.specialization = ? ";
        $stmt = $dbc->prepare($query);
        $stmt->bind_param("siss",$date,$id,$city,$specialization);
        $stmt->execute();
        $result = $stmt->get_result();
        $stmt->close();
        return $result->fetch_assoc();
    }
?>