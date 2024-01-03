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
        // $email=trim($email);
        // $password=trim($password);
        // echo $password;
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
            echo $row1['first_name'];
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
            //For each doctor check his unavailable time
            $unavailable_time = [];

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
                WHERE app.doctor_id = ? AND DATE(app.start_date) = ? AND app.department_id = ?
                ORDER BY app.start_date ASC";
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
                WHERE un.doctor_id = ? AND DATE(un.start_date) =? AND un.department_id =?
                ORDER BY un.start_date ASC";
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
        $query = "SELECT app.*,city.city_name,dep.details,doc.first_name,doc.last_name,sp.alias,dep.room,app.status
                FROM appointment app
                JOIN doctor doc ON app.doctor_id = doc.id
                JOIN department dep ON app.department_id = dep.id
                JOIN city ON dep.city_id = city.id
                JOIN specialization sp ON sp.id = doc.specialization_id
                WHERE app.patient_id = ?";
        $stmt = $dbc->prepare($query);
        $stmt->bind_param("i",$id);
        $stmt->execute();
        $result = $stmt->get_result();
        $stmt->close();
        return $result;
    }

    //Merge intervals
    function merge_intervals($intervals,$start_hour,$end_hour){
        if(count($intervals)==0)
            return [[],[]];
        $start_hour = time_to_float($start_hour);
        $end_hour = time_to_float($end_hour);
        $ratio = $end_hour - $start_hour;
        $bar_width = 96;
        $ratio = $bar_width/$ratio;
        sort($intervals);
        $merged_intervals = [];
        // foreach($intervals as $interval)
        // echo $interval['start_date'] . ' ' . $interval['end_date']."<br/>";
        for($i=0;$i<count($intervals);$i++){
            $unavailable_start_date = new DateTime($intervals[$i]['start_date']);
            $unavailable_start_hour = $unavailable_start_date->format('H:i');
            $intervals[$i]['start_date'] = time_to_float($unavailable_start_hour) - $start_hour;
            $unavailable_end_date = new DateTime($intervals[$i]['end_date']);
            $unavailable_end_hour = $unavailable_end_date->format('H:i');
            $intervals[$i]['end_date'] = time_to_float($unavailable_end_hour) - $start_hour;
            //echo $intervals[$i]['start_date'] . ' till ' . $intervals[$i]['end_date']."<br/>";
        }
        $merged_intervals[] = $intervals[0];
        $index = 0;

        for($i=1;$i<count($intervals);$i++)
        {
            if($merged_intervals[$index]['end_date']>=$intervals[$i]['start_date']){
                if($merged_intervals[$index]['end_date']<=$intervals[$i]['end_date'])
                    $merged_intervals[$index]['end_date'] = $intervals[$i]['end_date'];
                else
                    continue;
            }
            else{
                $merged_intervals[] = $intervals[$i];
                $index++;
            }
        }
        $draw_intrevals = [];
        foreach($merged_intervals as $interval){
            $draw_intrevals[] = array('start' => $interval['start_date']*$ratio,
                                     'width' => ($interval['end_date']-$interval['start_date'])*$ratio);
        }
        return [$merged_intervals,$draw_intrevals];
    }

    function time_to_float($time){
        $time = explode(":",$time);
        $time = $time[0]+$time[1]/60;
        return $time;
    }

    function float_to_time($time){
        $hour = intval($time);
        $time = $time - $hour;
        $min = $time * 60;
        $time = "";
        if($hour<10){
            $time.="0";
        }
        $time.=$hour.":";
        if($min<10){
            $time.="0";
        }
        $time.=$min;
        return $time;
    }

    function validate_interval($start_hour, $end_hour,$work_start_hour,$work_end_hour){
        $start_hour = time_to_float($start_hour);
        $end_hour = time_to_float($end_hour);
        $work_start_hour = time_to_float($work_start_hour);
        $work_end_hour = time_to_float($work_end_hour);
        $minimum_app_duration = 0.5; // 30 min ;
        $maximum_app_duration = 2; //2 hours
        if($end_hour-$start_hour<$minimum_app_duration || $end_hour-$start_hour>$maximum_app_duration){
            return false;
        }
        else if($start_hour<$work_start_hour || $end_hour>$work_end_hour){
            return false;
        }
        return true;
    }

    //Check if this appointment is for this patient
    function check_app_for_patient($dbc,$patient_id,$app_id){
        $query = "SELECT id FROM appointment WHERE patient_id =? AND id =?";
        $stmt = $dbc->prepare($query);
        $stmt->bind_param("ii",$patient_id,$app_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $stmt->close();
        if($result->num_rows>0){
            return true;
        }
        else{
            return false;
        }
    }


    function duration($start_date,$end_date){
        $start_hour = $start_date;
        $end_hour = $end_date;
        $start_hour = new DateTime($start_hour);
        $start_hour = $start_hour->format('H:i');
        $end_hour = new DateTime($end_hour);
        $end_hour = $end_hour->format('H:i');
        $duration = (time_to_float($end_hour)-time_to_float($start_hour))*60;
        $display_duration = "";
        if($duration>=60){
            $hours = $duration/60;
            $hours = floor($hours);
            $duration = $duration - $hours*60;
            if($hours==1){
                $display_duration = $hours." hour ";
            }
            else if($hours>1){
                $display_duration = $hours." hours ";
            }
        }
        if($duration>0){
            $display_duration.= $duration." minutes";
        }
        return $display_duration;
    }

    function get_appointment($dbc,$app_id){
        $query = "SELECT app.*,city.city_name,dep.details,doc.first_name,doc.last_name,sp.alias,dep.room,
                        app.status,app.start_date,app.end_date,dm.details document,sec.first_name sec_fname,sec.last_name sec_lname
                        ,sec.phone sec_phone
                FROM appointment app
                LEFT JOIN document dm ON app.id = dm.appointment_id
                JOIN doctor doc ON app.doctor_id = doc.id
                LEFT JOIN secretary sec ON doc.id = sec.doctor_id
                JOIN department dep ON app.department_id = dep.id
                JOIN city ON dep.city_id = city.id
                JOIN specialization sp ON sp.id = doc.specialization_id
                WHERE app.id = ?
                ORDER BY app.start_date ASC";
        $stmt = $dbc->prepare($query);
        $stmt->bind_param("i",$app_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $stmt->close();
        return $result->fetch_assoc();
    }

    function get_user_info($dbc,$user_id,$role){
        $valid_role = $role=='patient' || $role=='doctor' || $role=='secretay';
        if(!$valid_role){
            return [];
        }
        $inserted_date = "first_name,last_name,email,role,gender,age,phone";
        if($role == 'patient'){
            $query = "SELECT id,".$inserted_date." FROM patient WHERE id =?";
        }
        else if($role == 'doctor'){
            $query = "SELECT doc.id,".$inserted_date.",sp.alias,city.city_name,dep.room,dep.details
            FROM doctor doc
            JOIN department dep ON dep.id = doc.department_id
            JOIN city ON dep.city_id = city.id
            JOIN specialization sp ON sp.id = doc.specialization_id
            WHERE doc.id =?";
        }
        $stmt = $dbc->prepare($query);
        $stmt->bind_param("i",$user_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $stmt->close();
        return $result->fetch_assoc();
    }
?>