<link rel="stylesheet" href="http://localhost/Clinic-Management-System/static/css/bars.css">
<div class="container">
<div class="work">
    <p>Work hours:🟢</p>
    <p>Unavailable hours:⚫</p>
    <p>Busy hours:🔴</p>
</div>
<div class="edges">
<p>
    <?php 
    echo substr($doctor['start_hour'],0,5);
    ?>
</p>
<p>
    <?php 
    echo substr($doctor['end_hour'],0,5);
    ?>
</p>
</div>
<div class="work_hours_bar"></div>
<div class="unavailable_hours">
    <?php 
    $result = merge_intervals($doctor['unavailable_time'],$doctor['start_hour'],$doctor['end_hour']);
    $merged_time= $result[0];
    $merged_bars = $result[1];
    $last_start=0;
    for($i=0;$i<count($merged_bars);$i++){
        //echo $merged_bars[$i]['width'];
        echo '<div style="width:'.$merged_bars[$i]['start']-$last_start.'vw"></div>';
        echo '<div class="unavailable_hours_bar" style="width:'.$merged_bars[$i]['width'].'vw;">'.
        float_to_time($merged_time[$i]['start_date']+time_to_float($doctor['start_hour']))." till ".
        float_to_time($merged_time[$i]['end_date']+time_to_float($doctor['start_hour'])).'</div>';
        $last_start = $merged_bars[$i]['start']+$merged_bars[$i]['width'];
    }
    ?>
</div>
<div class="unavailable_hours">
    <?php 
    $result = merge_intervals($doctor['booked_time'],$doctor['start_hour'],$doctor['end_hour']);
    $merged_time= $result[0];
    $merged_bars = $result[1];
    $last_start=0;
    for($i=0;$i<count($merged_bars);$i++){
        echo '<div style="width:'.$merged_bars[$i]['start']-$last_start.'vw"></div>';
        echo '<div class="booked_hours_bar" style="width:'.$merged_bars[$i]['width'].'vw;">'.
        float_to_time($merged_time[$i]['start_date']+time_to_float($doctor['start_hour']))." till ".
        float_to_time($merged_time[$i]['end_date']+time_to_float($doctor['start_hour'])).'</div>';
        $last_start = $merged_bars[$i]['start']+$merged_bars[$i]['width'];
    }
    ?>
</div>
</div>