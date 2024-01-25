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

    function insert_patient($patient,$dbc){
        $query="INSERT INTO patient (first_name,last_name,email,password,gender,age,phone,pp)
         VALUES (?,?,?,?,?,?,?,?);";
        $stmt = $dbc->prepare($query);
        $stmt->bind_param("sssssiss",$patient->first_name,$patient->last_name,$patient->email,$patient->password,$patient->gender,$patient->age,$patient->phone,$patient->profile_pic);
        // Execute the query
        $stmt->execute();
        // Close the statement
        $stmt->close();
    }

    function login($email,$password,$dbc){
        $email=trim($email);
        $password=trim($password);
        $query =   "SELECT pat.password,pat.role,pat.id,pat.first_name,pat.last_name FROM patient pat WHERE pat.email = ?
                    UNION
                    SELECT doc.password,doc.role,doc.id,doc.first_name,doc.last_name FROM doctor doc WHERE doc.email = ?
                    UNION
                    SELECT sec.password,sec.role,sec.id,sec.first_name,sec.last_name FROM secretary sec WHERE sec.email = ?";
        $stmt = $dbc->prepare($query);
        $stmt->bind_param("sss",$email,$email,$email);
        $stmt->execute();
        $result1 =$stmt->get_result();
        if(mysqli_num_rows($result1) > 0){
            $row1 = mysqli_fetch_assoc($result1);
            // Hash the entered password using the same algorithm as in the MySQL trigger
            $hashedPassword = hash('sha256', $password);
            if($hashedPassword==$row1['password']){
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

    function get_patients($dbc) {
        $query = " SELECT email FROM patient ";
        $stmt = $dbc->prepare($query);
        $stmt->execute();
        $result = $stmt->get_result();
        $patients = [];
        while($row = mysqli_fetch_assoc($result)){
            $patients[] = $row['email'];
        }
        $stmt->close();
        return $patients;

    }

    function get_patient_id_from_email($dbc, $email) {
        $query = " SELECT id FROM patient
                    WHERE email = ? ";
        $id = NULL;
        $stmt = $dbc->prepare($query);
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $stmt->bind_result($id);
        $stmt->fetch();
        $stmt->close();
        return $id;

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

    function count_patient_appointments($patient_id,$doctor_id,$date,$dbc){
        $query = "SELECT id FROM appointment WHERE patient_id =? AND doctor_id =? AND DATE(start_date) =?";
        $stmt = $dbc->prepare($query);
        $stmt->bind_param("iss",$patient_id,$doctor_id,$date);
        $stmt->execute();
        $result = $stmt->get_result();
        $stmt->close();
        return mysqli_num_rows($result);
    }

    function get_doctor($data,$dbc,$user){
        $city = $data['city'];
        $specialization = $data['specialization'];
        $date = $data['date'];
        $dateTime = new DateTime($date);
        $dayOfWeek = $dateTime->format('l');
        $dayOfWeek = strtolower($dayOfWeek);
        $doctor_id = $data['doctor_id'];

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
        if($user == 'patient') {
            $query = "SELECT app.start_date, app.end_date
                    FROM appointment app
                    WHERE app.doctor_id = ? AND DATE(app.start_date) = ?
                    ORDER BY app.start_date ASC";
            $stmt = $dbc->prepare($query);
            $stmt->bind_param("is",$doctor['doctor_id'],$date);
            //We don't include the department id in the query because the doctor will be busy even in another department
        }
        else{
            $query = "SELECT app.start_date, app.end_date
                    FROM appointment app
                    WHERE app.doctor_id = ? AND DATE(app.start_date) = ?
                    AND app.status != 'pending' AND app.status != 'queued'
                    ORDER BY app.start_date ASC";
            $stmt = $dbc->prepare($query);
            $stmt->bind_param("is",$doctor['doctor_id'],$date);
            //We don't include the department id in the query because the doctor will be busy even in another department
        }
        $stmt->execute();
        $appointments = $stmt->get_result();
        $stmt->close();
        
        while($appointment = $appointments->fetch_assoc()){
            $booked_time[] = array('start_date' => $appointment['start_date'], 'end_date' => $appointment['end_date']);
        }

        //Doctor unavailable time
        $query = "SELECT un.start_date, un.end_date
                FROM unavailable_slots un
                WHERE un.doctor_id = ? AND DATE(un.start_date) =?
                ORDER BY un.start_date ASC";
        $stmt = $dbc->prepare($query);
        $stmt->bind_param("is",$doctor['doctor_id'],$date);
        //We don't include the department id in the query because the doctor will be busy even in another department
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
        if ($_SESSION['role'] == 'patient'){
            $query = "SELECT app.*,city.city_name,dep.details,doc.first_name,doc.last_name,sp.alias,dep.room,app.status
                    FROM appointment app
                    JOIN doctor doc ON app.doctor_id = doc.id
                    JOIN department dep ON app.department_id = dep.id
                    JOIN city ON dep.city_id = city.id
                    JOIN specialization sp ON sp.id = doc.specialization_id
                    WHERE app.patient_id = ?";
        }
        elseif ($_SESSION['role'] == 'doctor') {
            $query = "SELECT app.*, pa.first_name, pa.last_name, pa.age, pa.gender, pa.phone, pa.email
                    FROM appointment app
                    JOIN patient pa ON app.patient_id = pa.id
                    WHERE app.doctor_id = ?";
        }
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
        if($ratio==0)
            $ratio=1;
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
        $time = (new DateTime($time))->format("H:i");
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
        if($_SESSION['role'] == 'patient'){
            $minimum_app_duration = 0.5; // 30 min 
            $maximum_app_duration = 2; //2 hours
        }
        else{
            $minimum_app_duration = 0; // 0 min 
            $maximum_app_duration = 16; // 16 hours
        }
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

    // Check if this appointment belongs to the doctor
    function check_app_for_doctor($dbc,$doctor_id,$app_id){
        $query = "SELECT id FROM appointment WHERE doctor_id =? AND id =?";
        $stmt = $dbc->prepare($query);
        $stmt->bind_param("ii",$doctor_id,$app_id);
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
                        pa.first_name pa_fname, pa.last_name pa_lname,
                        app.status,app.start_date,app.end_date,dm.details document_details,dm.prescription
                        ,sec.first_name sec_fname,sec.last_name sec_lname
                        ,sec.phone sec_phone
                FROM appointment app
                LEFT JOIN document dm ON app.id = dm.appointment_id
                JOIN doctor doc ON app.doctor_id = doc.id
                JOIN patient pa ON app.patient_id = pa.id
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
        $valid_role = $role=='patient' || $role=='doctor' || $role=='secretary';
        if(!$valid_role){
            return [];
        }
        $inserted_date = "first_name,last_name,email,role,gender,age,phone";
        if($role == 'patient'){
            $query = "SELECT id,pp,".$inserted_date." FROM patient WHERE id =?";
        }
        else if($role == 'doctor'){
            $query = "SELECT doc.id,doc.pp,".$inserted_date.",sp.alias,city.city_name,dep.room,dep.details
            FROM doctor doc
            JOIN department dep ON dep.id = doc.department_id
            JOIN city ON dep.city_id = city.id
            JOIN specialization sp ON sp.id = doc.specialization_id
            WHERE doc.id =?";
        }
        else{
            $query = "SELECT sec.id,sec.pp,sec.first_name,sec.last_name,sec.email,
            sec.role,sec.gender,sec.age,sec.phone,doc.first_name doctor_fname,doc.last_name doctor_lname
            FROM secretary sec
            JOIN doctor doc ON doc.id = sec.doctor_id
            WHERE sec.id =?";
        }
        $stmt = $dbc->prepare($query);
        $stmt->bind_param("i",$user_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $stmt->close();
        return $result->fetch_assoc();
    }

    function get_notifications($dbc,$patient_id){
        $query = "SELECT nt.*,doc.first_name,doc.last_name
                 FROM notifications nt
                 JOIN doctor doc ON doc.id = nt.sender
                 WHERE receiver =? 
                 ORDER BY nt.date DESC";
        $query1 = "UPDATE notifications SET status = 'read' 
                    WHERE receiver = ?";
        
        //Get all notifications
        $stmt = $dbc->prepare($query);
        $stmt->bind_param("i",$patient_id);
        $stmt->execute();
        $result = $stmt->get_result();

        //Make them readed
        $stmt = $dbc->prepare($query1);
        $stmt->bind_param("i",$patient_id);
        $stmt->execute();

        $stmt->close();
        return $result;
    }

    function get_notifications_unreaded($dbc,$patient_id){

        //Get the unreaded notifications
        $query = "SELECT nt.message,nt.date,doc.first_name,doc.last_name
                 FROM notifications nt
                 JOIN doctor doc ON doc.id = nt.sender
                 WHERE receiver =? AND nt.status = 'unread'
                 ORDER BY nt.date DESC";
        $stmt = $dbc->prepare($query);
        $stmt->bind_param("i",$patient_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $stmt->close();
        return $result;
    }

    function get_requests($dbc,$doctor_id,$filter_date){
        if(!$filter_date){
        $query = "SELECT app.*,pt.email,pt.first_name,pt.last_name,pt.phone
                FROM appointment app
                JOIN patient pt ON app.patient_id = pt.id
                WHERE (app.status = 'pending' OR app.status = 'queued') AND app.doctor_id = ? 
                ORDER BY app.book_date ASC";
        $stmt = $dbc->prepare($query);
        $stmt->bind_param("i",$doctor_id);
        }
        else if($filter_date){
            $query = "SELECT app.*,pt.email,pt.first_name,pt.last_name,pt.phone
                    FROM appointment app
                    JOIN patient pt ON app.patient_id = pt.id
                    WHERE DATE(app.start_date) = ? AND app.doctor_id = ?  AND (app.status = 'pending' OR app.status = 'queued')
                    ORDER BY app.book_date ASC";
            $stmt = $dbc->prepare($query);
            $stmt->bind_param("si",$filter_date,$doctor_id);
        }
        $stmt->execute();
        $requests = $stmt->get_result();
        $stmt->close();
        $result = [];
        while($row = $requests->fetch_assoc())
            $result[] = $row;
        return $result;
    }

    function get_pending($dbc,$doctor_id,$filter_date){
        if(!$filter_date){
        $query = "SELECT app.*,pt.email,pt.first_name,pt.last_name,pt.phone
                FROM appointment app
                JOIN patient pt ON app.patient_id = pt.id
                WHERE app.status = 'pending AND app.doctor_id = ? 
                ORDER BY app.book_date ASC";
        $stmt = $dbc->prepare($query);
        $stmt->bind_param("i",$doctor_id);
        }
        else if($filter_date){
            $query = "SELECT app.*,pt.email,pt.first_name,pt.last_name,pt.phone
                    FROM appointment app
                    JOIN patient pt ON app.patient_id = pt.id
                    WHERE DATE(app.start_date) = ? AND app.doctor_id = ?  AND app.status = 'pending'
                    ORDER BY app.book_date ASC";
            $stmt = $dbc->prepare($query);
            $stmt->bind_param("si",$filter_date,$doctor_id);
        }
        $stmt->execute();
        $requests = $stmt->get_result();
        $stmt->close();
        $result = [];
        while($row = $requests->fetch_assoc())
            $result[] = $row;
        return $result;
    }

    function first_book($data){
        $requests = $data;
        $result = [];
        $result[] = $requests[0];
        for($i=1;$i<count($requests);$i++){
            $valid = true;
            //Check if this appointment overlap with taken appointments
            for($j=0;$j<count($result);$j++){
                if(check_overlap($requests[$i],$result[$j]))
                    continue;
                $valid = false;
            }
            if($valid)
                $result[]=$requests[$i];
        }

        return $result;
    }

    function check_overlap($date1,$date2){
        $start_hour1 = time_to_float((new DateTime($date1['start_date']))->format('H:i'));
        $start_hour2 = time_to_float((new DateTime($date2['start_date']))->format('H:i'));
        $end_hour1 = time_to_float((new DateTime($date1['end_date']))->format('H:i'));
        $end_hour2 = time_to_float((new DateTime($date2['end_date']))->format('H:i'));
        if(( $start_hour1 >= $start_hour2 && $start_hour1 < $end_hour2) || ( $end_hour1 > $start_hour2 && $end_hour1 <= $end_hour2)
        || ( $start_hour1 < $start_hour2 && $end_hour1 > $end_hour2) || ( $start_hour1 >= $start_hour2 && $end_hour1 <= $end_hour2)){
            return false;
        }
        return true;
    }

    //<--------------------Maximize-Hours-Algorithm-------------------------------------------->

    function sort_start_date($start_date_1,$start_date_2){
        $value_1 = time_to_float((new DateTime($start_date_1['start_date']))->format('H:i'));
        $value_2 = time_to_float((new DateTime($start_date_2['start_date']))->format('H:i'));
        return $value_1<$value_2?-1:1;
    }

    function maximize_hours($data){
        $requests = $data;
        usort($requests,"sort_start_date");
        $length = count($requests);
        $hours = array_fill(0, $length, 0);
        $next = array_fill(0, $length,0);
        $dp['hours'] = $hours;
        $dp['next'] = $next;
        for($i=0;$i<$length;$i++){
            solve($requests,$i,$dp);
        }
        $maximum = 0;
        $maximum_index = 0;
        for($i=0;$i<$length;$i++){
            //echo $dp['hours'][$i],"<br/>";
            if($dp['hours'][$i] > $maximum){
                $maximum = $dp['hours'][$i];
                $maximum_index = $i;
            }
        }
        $index=$maximum_index;
        $result = [];
        while(count($result)<50){
            $result[] = $requests[$index];
            if($index==$dp['next'][$index])
                break;
            $index = $dp['next'][$index];
        }
        if(count($result)==50){
            return [];
        }
        return $result;
    }

    function solve(&$requests,$i,&$dp){
        if($i>=count($requests))
            return [0,-1];
        if($dp['hours'][$i]){
            return [$dp['hours'][$i],$dp['next'][$i]];
        }
        $duration = float_duration($requests[$i]['start_date'],$requests[$i]['end_date']);
        $nearest = nearest_without_overlap($requests,$i);
        if($nearest>=count($requests) || $requests[$nearest]['start_date']<$requests[$i]['end_date']){
            $dp['next'][$i] = $i;
            $dp['hours'][$i] = $duration;
            return [$duration,$i];
        }
        $maximum = 0;
        $next = -1;
        for($j=$nearest;$j<count($requests);$j++){
            $current = solve($requests,$j,$dp);
            if($current[0]>$maximum){
                $maximum = $current[0];
                $next = $j;
            }
        }
        $dp['next'][$i] =  $next;
        $dp['hours'][$i] = $duration+$maximum;
        return [$dp['hours'][$i],$dp['next'][$i]];
    }

    function nearest_without_overlap(&$requests,$start){
        $left = $start+1;
        $right = count($requests)-1;
        $mid = intval(($left+$right)/2);
        while($left<=$right){
            if($requests[$mid]['start_date'] == $requests[$start]['end_date']){
                return $mid;
            }
            else if($requests[$mid]['start_date'] < $requests[$start]['end_date']){
                $left = $mid + 1;
            }
            else{
                $right = $mid - 1;
            }
            $mid = intval(($left+$right)/2);
            
        }
        return $mid+1;
    }

    function float_duration($start_date,$end_date){
        $start_hour = $start_date;
        $end_hour = $end_date;
        $start_hour = new DateTime($start_hour);
        $start_hour = $start_hour->format('H:i');
        $end_hour = new DateTime($end_hour);
        $end_hour = $end_hour->format('H:i');
        $duration = (time_to_float($end_hour)-time_to_float($start_hour))*60;
        return $duration;
    }

    //<-------------------------------------------------------------------------------------------->

    function accept_requests($all_requests,$requests_accept,$dbc){
            for($i=0;$i<count($all_requests);$i++){
                $accept = false;
                for($j=0;$j<count($requests_accept);$j++){
                    if($requests_accept[$j]['id'] == $all_requests[$i]['id']){
                        $accept = true;
                        break;
                    }
                }
                if($accept)
                    $query = "UPDATE appointment SET status = 'upcoming' WHERE id =?";
                else
                    $query = "UPDATE appointment SET status = 'queued' WHERE id =?";
                $stmt = $dbc->prepare($query);
                $stmt->bind_param("i",$all_requests[$i]['id']);
                $stmt->execute();
                $stmt->close();
                
                //Add accept notification for new appointment request
                if($accept){
                    $query = "INSERT INTO notifications (receiver,sender,appointment_id,reason) VALUES (?,?,?,'accepted')";
                    $stmt = $dbc->prepare($query);
                    $stmt->bind_param("iii",$all_requests[$i]['patient_id'],$all_requests[$i]['doctor_id'],$all_requests[$i]['id']);
                    $stmt->execute();
                    $stmt->close();
                }
        }
    }

    function get_doctor_info($dbc,$doctor_id){
        $query = "SELECT doc.id doctor_id,sp.alias specialization,city.city_name city
                    FROM doctor doc
                    JOIN department dep ON dep.id = doc.department_id
                    JOIN city ON city.id = dep.city_id
                    JOIN specialization sp ON sp.id = doc.specialization_id
                    WHERE doc.id =?";
        $stmt = $dbc->prepare($query);
        $stmt->bind_param("i",$doctor_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $stmt->close();
        return $result->fetch_assoc();
    }

    function update_appointment($dbc,$data){

        //Validate billing information
        $new_bill = $data['new_bill'];
        $new_payed = $data['new_payed'];
        if($new_bill<$new_payed)
        {
            return [false,"Invalid billing information"];
        }


        $new_start_date = $data['new_start_date'];
        $new_start_hour = $data['new_start_hour'];
        $new_end_hour = $_POST['new_end_hour'];

        //Validation on start and end_hour
        $min_duration = 0.5; //min = 30 min
        if(time_to_float($new_end_hour)-time_to_float($new_start_hour)<$min_duration){  
            return [false,"The minimum duration of an appointment is 30 min"];
        }
        $max_duration = 2;
        if(time_to_float($new_end_hour)-time_to_float($new_start_hour)>$max_duration){  
            return [false,"The maximum duration of an appointment is 2 hours"];
        }

        $new_end_date = $new_start_date." ".$new_end_hour;
        $new_start_date .= " ".$new_start_hour;

        //Import the schedule on the given date
        $day_of_week = strtolower(date("l",strtotime($new_start_date)));
        $query = "SELECT *
                    FROM week_schedule
                    WHERE day = ?";
        $stmt = $dbc->prepare($query);
        $stmt->bind_param("s",$day_of_week);
        $stmt->execute();
        $result = $stmt->get_result();
        if(!$result || mysqli_num_rows($result)==0){
            return [false,"Error in week schedule, call the IT team"];
        }
        $schedule = $result->fetch_assoc();
        //Check if the new date is outside the shcedule
        if(time_to_float($schedule['start_hour'])>time_to_float($new_start_hour)
        || time_to_float($schedule['end_hour'])<time_to_float($new_end_hour)){
            return [false,"The new date is outside the work hours range"];
        }
        //Check if the new date overlap with some unavailable hours 
        $query = "SELECT id
        FROM unavailable_slots
        WHERE doctor_id=".$_SESSION['doctor_id']." AND 
        ( ? >= start_date AND ? < end_date) OR ( ? > start_date AND ? <= end_date)
        OR ( ? < start_date AND ? > end_date) OR (?=start_date AND ? = end_date)";
        $stmt = $dbc->prepare($query);
        $stmt->bind_param("ssssssss",$new_start_date,$new_start_date,$new_end_date,
        $new_end_date,$new_start_date,$new_end_date,$new_start_date,$new_end_date);
        $stmt->execute();
        $result = $stmt->get_result();
        if($result && mysqli_num_rows($result)>0){
            return [false,"The new appointment date overlaps with unavailabile slots."];
        }

        //Check if no upcoming appointments overlap with this appointment then put it as pending
        $query = "SELECT id
        FROM appointment
        WHERE doctor_id = ? AND id!= ? AND status != 'pending' AND status!= 'queued'
        AND (( ? >= start_date AND ? < end_date) OR ( ? > start_date AND ? <= end_date)
        OR ( ? < start_date AND ? > end_date) OR (?=start_date AND ? = end_date))";
        $stmt = $dbc->prepare($query);
        $stmt->bind_param("iissssssss",$_SESSION['doctor_id'],$data['app_id'],$new_start_date,$new_start_date,$new_end_date,
        $new_end_date,$new_start_date,$new_end_date,$new_start_date,$new_end_date);
        $stmt->execute();
        $result = $stmt->get_result();
        if($result && mysqli_num_rows($result)>0){
            return [false,"The new appointment date overlaps with some booked uppointments"];
        }

        $query = "UPDATE document SET details=?,prescription=?
                WHERE appointment_id = ?";
        $stmt = $dbc->prepare($query);
        $stmt->bind_param("ssi",$data['new_details'],$data['new_prescription'],$data['app_id']);
        $stmt->execute();
        
        $query = "UPDATE appointment SET bill=?,payed=?,start_date=?,end_date=? WHERE id = ?";
        $stmt = $dbc->prepare($query);
        $stmt->bind_param("ddssi",$data['new_bill'],$data['new_payed'],$new_start_date,$new_end_date,$data['app_id']);
        $stmt->execute();
        return [true,""];
    }

    // ----------------------------Delay appointment------------------------------------
    function delay_appointment($start_date,$app_id){
    if(!isset($_SESSION['role'])){
        header('location:../login.php');
        die();
    }
    if($_SESSION['role'] == 'patient'){
        header('location:../index.php');
        die();
    }

    $dbc = connectServer("localhost","root","",1);
    selectDB($dbc,"clinic_db",1);

    //Get the appointment date
    $query = "SELECT start_date, end_date,patient_id FROM appointment WHERE id = ?";
    $stmt = $dbc->prepare($query);
    $stmt->bind_param("i",$app_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $stmt->close();

    if(mysqli_num_rows($result)==0){
        mysqli_close($dbc);
        header('location:../index.php');
        die();
    }

    $appointment = $result->fetch_assoc();
    $appointment_duration = float_duration($appointment['start_date'],$appointment['end_date']);

    $start_day = new DateTime((new DateTime($start_date))->format('Y-m-d'));
    $start_date = new DateTime((new DateTime($start_date))->format('Y-m-d'));
    //Start the search from the next day
    $start_day->modify("+1 day");
    $daysArray = array();

    // Loop to get the current day and the next 6 days
    while(count($daysArray) < 5 ) {
        // Format the date as desired
        $formattedDate = $start_day->format('Y-m-d');
        
        // Add the formatted date to the array
        if((new DateTime($formattedDate))->format('l')!='Sunday' && (new DateTime($formattedDate))->format('l')!='Saturday')
            $daysArray[] = $formattedDate;
        
        // Move to the next day
        $start_day->modify('+1 day');
    }

    $booked = false; //Flag to check at the end if the appointment is delayed or should be removed

    foreach($daysArray as $day){
        
        if($booked)
            break;

        //Get booked appointment on this day
        $query = "SELECT start_date,end_date
                FROM appointment
                WHERE DATE(start_date) = ? AND doctor_id = ? AND status != 'pending' AND status != 'queued'
                ORDER BY start_date ASC";
        $stmt = $dbc->prepare($query);
        $stmt->bind_param("si", $day,$_SESSION['doctor_id']);
        $stmt->execute();
        $result = $stmt->get_result();

        $booked_slots = [];
        if($result){
            while($row = $result->fetch_assoc()){
                $booked_slots[] = $row;
            }
        }
        //Get unavailable hour on this day
        $query = "SELECT start_date,end_date
                FROM unavailable_slots
                WHERE DATE(start_date) = ? AND doctor_id = ?
                ORDER BY start_date ASC";
        $stmt = $dbc->prepare($query);
        $stmt->bind_param("si", $day,$_SESSION['doctor_id']);
        $stmt->execute();
        $result = $stmt->get_result();

        $unavailable_slots = [];
        if($result){
            while($row = $result->fetch_assoc()){
                $unavailable_slots[] = $row;
            }
        }

        $sorted_busy_slots = [];
        //Sort results by start_date
        $i=0;$j=0;
        while($i<count($booked_slots) && $j<count($unavailable_slots)){
            if($booked_slots[$i] < $unavailable_slots[$j])
                $sorted_busy_slots[] = $booked_slots[$i++];
            else
                $sorted_busy_slots[] = $unavailable_slots[$j++];
        }
        while($i<count($booked_slots)){
            $sorted_busy_slots[] = $booked_slots[$i++];
        }
        while($j<count($unavailable_slots)){
            $sorted_busy_slots[] = $unavailable_slots[$j++];
        }

        //Get the start and end hours on this day
        $query = "SELECT start_hour,end_hour
                FROM week_schedule
                WHERE day = ? AND doctor_id = ?";
        $stmt = $dbc->prepare($query);
        $current_day_of_week = strtolower((new DateTime($day))->format('l'));
        $stmt->bind_param("si",$current_day_of_week,$_SESSION['doctor_id']);
        $stmt->execute();
        $result = $stmt->get_result();
        $schedule = $result -> fetch_assoc();
        //Set the start and end hours
        $start_hour = $schedule['start_hour'];
        $end_hour = $schedule['end_hour'];

        //Date of next day
        $start_date=$day;
        $new_date = (new DateTime($start_date))->format('Y-m-d');
        //Two pointers
        $last_end = $new_date." ".$start_hour;
        if(count($sorted_busy_slots)>0){
            $nearest_start = $sorted_busy_slots[0]['start_date'];
        }
        else{
            $nearest_start = $new_date." ".$end_hour;
        }

        //Iterate the sorted busy slots array with two pointer, one for the end of current busy slot and the other
        // for the start of the nearest busy slot until find a free slot between these two pointers
        //If Not go to the next day

        //<---------------------------------------------------------------->
        for ($i=0;$i<count($sorted_busy_slots);$i++) {
            if(float_duration($last_end,$nearest_start)>=$appointment_duration){
                $booked = true;
                $new_start_datetime = $last_end;
                $new_end_hour = float_to_time(time_to_float($new_start_datetime)+$appointment_duration/60);
                $new_end_datetime = $new_date . ' ' . $new_end_hour;
                $query = "UPDATE appointment SET start_date = ?, end_date = ?, status = 'delayed'
                            WHERE id = ? ";
                $stmt = $dbc->prepare($query);
                $stmt->bind_param("ssi",$new_start_datetime,$new_end_datetime,$app_id);
                $stmt->execute();
                break;
            }
        if($i==count($sorted_busy_slots)-1){
            $nearest_start = $end_hour;
        }
        else{
            $nearest_start = $sorted_busy_slots[$i+1]['start_date'];
        }
        $last_end = $sorted_busy_slots[$i]['end_date'];
        }
        //<---------------------------------------------------------------->

        //Check the slot between the end of last appointment and the end_work_hour
        if(float_duration($last_end,$nearest_start)>=$appointment_duration){
            $booked = true;
            $new_start_datetime = $last_end;
            $new_end_hour = float_to_time(time_to_float($new_start_datetime)+$appointment_duration/60);
            $new_end_datetime = $new_date . ' ' . $new_end_hour;
            $query = "UPDATE appointment SET start_date = ?, end_date = ?, status = 'delayed'
                        WHERE id = ? ";
            $stmt = $dbc->prepare($query);
            $stmt->bind_param("ssi",$new_start_datetime,$new_end_datetime,$app_id);
            $stmt->execute();
        }
    }

    if($booked)
    {
        //Send a notification to the patient
        $query = "INSERT INTO notifications (sender,receiver,message,reason)
        VALUES (?,?,?,'delay')";
        $stmt = $dbc->prepare($query);
        $stmt->bind_param("iis",$_SESSION['doctor_id'],$appointment['patient_id'],$new_start_datetime);
        $stmt->execute();
    }
    else{
        $query = "INSERT INTO notifications (sender,receiver,reason)
        VALUES (?,?,'remove')";
        $stmt = $dbc->prepare($query);
        $stmt->bind_param("iis",$_SESSION['doctor_id'],$appointment['patient_id']);
        $stmt->execute();
    }
    $stmt->close();
    mysqli_close($dbc);
    }


    function compareDays($a, $b) {
        $daysOrder = ['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday'];
    
        $aIndex = array_search($a['day'], $daysOrder);
        $bIndex = array_search($b['day'], $daysOrder);
    
        return $aIndex - $bIndex;
    }

    function overlap_with_accepted($dbc,$start_date,$end_date,$doctor_id){
        $query = "SELECT id FROM appointment WHERE doctor_id = ? 
        AND status!='pending' AND status !='queued' 
        AND (( ? >= start_date AND ? < end_date) OR ( ? > start_date AND ? <= end_date)
        OR ( ? < start_date AND ? > end_date) OR (?>=start_date AND ? <= end_date))";
        $stmt = $dbc->prepare($query);
        $stmt->bind_param("issssssss",$doctor_id,$start_date,$start_date,$end_date,
        $end_date,$start_date,$end_date,$start_date,$end_date);
        $stmt->execute();
        $result = $stmt->get_result();
        if(mysqli_num_rows($result) > 0)
            return true;
        return false;
    }
?>