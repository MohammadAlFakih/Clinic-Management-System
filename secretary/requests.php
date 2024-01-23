<?php
    include "../includes/header.php";
    if(!isset($_SESSION['role'])){
        header('location:../login.php');
        die();
    }
    if($_SESSION['role'] == 'patient'){
        header('location:../index.php');
        die();
    }
?>
<link href="../static/css/requests.css" rel="stylesheet" type="text/css">
<?php
    
    if(isset($_GET['message'])){
        echo '<div class="message">'.$_GET['message'].'</div>';
    }
    //Get the pending requests

    //No filter
    if($_SERVER['REQUEST_METHOD'] == 'GET'  && !isset($_SESSION['filter_date'])){
        $dbc = connectServer('localhost','root','',0);
        selectDB($dbc,'clinic_db',0);
        $requests = get_requests($dbc,$_SESSION['doctor_id'],NULL);
        echo '<div class="filter-container">
        <div class="date"></div>
        <div>
            <form method="post" action="requests.php">
                <label for="filter_date">Filter Date: </label>
                <div class="row">
                <input type="date" name="filter_date" id="filter_date" class="filter">
                <input type="submit" value="Filter" class="accept submit">
                <input type="submit" value="No Filter" class="accept cancel" name="no_filter">
                </div>
            </form>
        </div>
        </div>';
    }

    //With some filter
    else{
        if(isset($_POST['no_filter'])){
            unset($_SESSION['filter_date']);
            header('location:requests.php');
            die();
        }
        if(empty($_POST['filter_date']) && !isset($_SESSION['filter_date'])){
            header('location:requests.php');
            mysqli_close($dbc);
            die();
        }
        else{
            if(!empty($_POST['filter_date'])){
                $filter_date = $_POST['filter_date'];
                $_SESSION['filter_date'] = $filter_date;
            }
            else{
                $filter_date = $_SESSION['filter_date'];
            }

            $dbc = connectServer('localhost','root','',0);
            selectDB($dbc,'clinic_db',0);

            //<----------------------------->
            echo '<br><br>';
            $data = get_doctor_info($dbc,$_SESSION['doctor_id']);
            $data['date']=$filter_date;
            $doctor = get_doctor($data, $dbc,$_SESSION['role']);
            $_SESSION['department_id'] = $doctor['department_id'];
            include "../includes/draw_work_hours.php";
            //<----------------------------->
            
            if((isset($_POST['maximize_hours']) || isset($_POST['first_book']))
             && isset($filter_date) && !empty($filter_date))
                $requests = get_pending($dbc,$_SESSION['doctor_id'],$filter_date);
            else
                $requests = get_requests($dbc,$_SESSION['doctor_id'],$filter_date);
            
            //Accept All requests with first book_filter
            if(isset($_POST['first_book_accept'])){
                $requests = get_requests($dbc,$_SESSION['doctor_id'],$filter_date);
                $result = first_book($requests);
                accept_requests($requests,$result,$dbc);
                header("location:requests.php");
                die();
            }
            //Accept All requests with maximize hours filter
            if(isset($_POST['maximize_hours_accept'])){
                $requests = get_requests($dbc,$_SESSION['doctor_id'],$filter_date);
                $result = maximize_hours($requests);
                accept_requests($requests,$result,$dbc);
                header("location:requests.php");
                die();
            }

            echo '<div class="filter-container">
                    <div class="date">Date : '.$filter_date.'</div>
                    <form method="post" action="requests.php">
                        <div>
                                <label for="filter_date">Filter Date: </label>
                                <div class="row">
                                <input type="date" name="filter_date" id="filter_date" class="filter">
                                <input type="submit" value="Filter" class="accept submit">
                                <input type="submit" value="No Filter" class="accept cancel" name="no_filter">
                                </div>
                        </div>
                        <div class="row methods">
                                <input type="submit" value="First Book Filter" class="accept method" name="first_book">
                                <input type="submit" value="Maximize Work Hours Filter" class="accept method" name="maximize_hours">        
                        </div>
                    </form>
                    </div>';
        }
    }

    if(count($requests) == 0 ){
        echo '<div class="container">
            <div class="message">
                <p>There is no pending requests till now 📅.</p>
            </div></div>';
    }
    else{

        //Apply the algorithm to find the appointments which are booked first
        if(isset($_POST['first_book']) && isset($filter_date) && !empty($filter_date)){
            $result = first_book($requests);
            //Re-assign the results to the requests
            $requests=$result;
        }
        //Apply the algorithm to find the appointments which maximize the work hours of doctor
        else if(isset($_POST['maximize_hours']) && isset($filter_date) && !empty($filter_date)){
            $result = maximize_hours($requests);
            //Re-assign the results to the requests
            $requests=$result;
        }
        echo ' <div class="container"><h1>Requests List</h1>

        <table>
            <thead>
                <tr>
                    <th>#ID</th>
                    <th>Book Date</th>
                    <th>Patient Name</th>
                    <th>Start Date</th>
                    <th>Time</th>
                    <th></th>
                    <th></th>
                    <th></th>
                </tr>
            </thead>
            <tbody>';
            foreach($requests as $request){

                $request['start_date'] = new DateTime($request['start_date']);
                $start_date = $request['start_date']->format('Y-m-d');

                $start_hour = $request['start_date']->format('H:i');
                $end_hour = (new DateTime($request['end_date']))->format('H:i');

                $request['book_date'] = (new DateTime($request['book_date']))->format('Y-m-d H:i');

                echo '<tr class="'.$request['status'].'">
                <td>'.$request['id'].'</td>
                <td>'.$request['book_date'].'</td>
                <td><a href="../includes/profile.php?id='.$request['patient_id'].'&role=patient'.'">'.$request['first_name']." ".$request['last_name'].'</a>
                <br>'.$request['phone'].'
                </td>';
                if(!isset($_GET['edit']) || (isset($_GET['edit']) && $_GET['edit']!=$request['id'])){
                    echo '<td>'.$start_date.'</td>
                    <td>'.$start_hour.' till '.$end_hour.'</td>';
                }
                else if($_GET['edit']==$request['id']){
                    echo '<form method="POST" action="edit_pending_appointment.php">
                            <input type="hidden" name="app_id" value="'.$request['id'].'">
                            <td><input type="date" value="'.$start_date.'" name="new_start_date" class="edit-input"></td>
                            <td>
                            <input type="time" value="'.$start_hour.'" name="new_start_hour"  class="edit-input">
                            till
                            <input type="time" value="'.$end_hour.'" name="new_end_hour"  class="edit-input">
                            </td>
                            ';
                }


                echo '<td>';
                //Allow accept if request is pending
                if($request['status'] == 'pending')
                    echo '<a class="accept" href="http://localhost/Clinic-Management-System/secretary/accept_appointment.php?app_id='.$request['id'].'">Accept</a></td>';
                else
                    echo 'Queued</td>';

                echo '<td><a class="remove" href="http://localhost/Clinic-Management-System/includes/cancel_appointment.php?
                patient_id='.$request['patient_id'].'&app_id='.$request['id'].'">Remove</a></td>';

                if(isset($_GET['edit']) &&  $_GET['edit']==$request['id']){
                    echo '<td><input type="submit" value="Save" class="edit save"></td></form>';
                }
                else{
                echo '<td><a class="edit" href="http://localhost/Clinic-Management-System/secretary/requests.php?edit='.$request['id'].'">Edit</a>
                </td>';
                }
                echo '</tr>';
            }
            echo '</tbody>
        </table></div>';

        //Accept All after using a filter
        if(isset($_POST['first_book'])){
            echo '<form method="post" action="requests.php" class="accept_all">
            <input type="submit" value="Accept All" name="first_book_accept" class="accept">
            </form>';
        }
        else if(isset($_POST['maximize_hours'])){
            echo '<form method="post" action="requests.php" class="accept_all">
            <input type="submit" value="Accept All" name="maximize_hours_accept" class="accept">
            </form>';
        }
    }
    
?>

<?php
    mysqli_close($dbc);
?>