<?php 
    function email_exists($email,$dbc){
        $query1 = "SELECT * FROM patient WHERE email = '$email'";
        $result1 = mysqli_query($dbc, $query1);
        if(mysqli_num_rows($result1) > 0){
            return true;
        }
        $query1 = "SELECT * FROM doctor WHERE email = '$email'";
        $result1 = mysqli_query($dbc, $query1);
        if(mysqli_num_rows($result1) > 0){
            return true;
        }
        $query1 = "SELECT * FROM secretary WHERE email = '$email'";
        $result1 = mysqli_query($dbc, $query1);
        if(mysqli_num_rows($result1) > 0){
            return true;
        }
        return false;
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
        $query = "SELECT * FROM patient WHERE email = ?";
        $stmt = $dbc->prepare($query);
        $stmt->bind_param("s",$email);
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
        // $query1 = "SELECT * FROM doctor WHERE email = '$email'";
        // $result1 = mysqli_query($dbc, $query1);
        // if(mysqli_num_rows($result1) > 0){
        //     $row1 = mysqli_fetch_assoc($result1);
        //     if(password_verify($password,$row1['password'])){
        //         return true;
        //     }
        //     else{
        //         return false;
        //     }
        // }
    }
?>