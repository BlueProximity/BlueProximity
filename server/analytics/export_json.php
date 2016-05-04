<?php
include "db.php";
// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);
$name = '"name":';
$children = '"children":';
$days = array('Monday'=>0,'Tuesday'=>0,'Wednesday'=>0,'Thursday'=>0,'Friday'=>0,'Saturday'=>0,'Sunday'=>0);
$months = array('January' => 1, 'February' => 2, 'March' => 3, 'April' => 4, 'May' => 5, 'June' => 6,'July' => 7,'August' => 8,'September' => 9,'October' => 10,'November' => 11,'December' => 12 );
$months_name = array('January', 'February', 'March', 'April', 'May', 'June','July','August','September','October','November','December');
$years = array("2014","2015","2016");

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
$query = "SELECT DISTINCT user_name FROM user_stats_retroactive";
$result = $conn->query($query);
$users = array();

while($row = $result->fetch_assoc()) {
    $ind = 0;
    foreach ($row as $k){
        $users[] = $k;
    }
}


$year_user_enter = array();
$year_user_exit = array();
$year_user_total = array();
foreach ($years as $year){
    foreach ($users as $user){
        $query = "SELECT * FROM star_stats_retroactive WHERE YEAR(start_date)=$year AND user_name='$user'";
        $result = $conn->query($query);
        while($row = $result->fetch_assoc()) {
            if(empty($year_user_enter[$user."-".$year])){
                $year_user_enter[$user."-".$year] = 0;
            }
            if(empty($year_user_exit[$user."-".$year])){
                $year_user_exit[$user."-".$year] = 0;
            }
            if(empty($year_user_total[$user."-".$year])){
                $year_user_total[$user."-".$year] = 0;
            }
            $year_user_enter[$user."-".$year] += $row['enter_incident'];
            $year_user_exit[$user."-".$year]+= $row['exit_incident'];
            $year_user_total[$user."-".$year]+= $row['total_use'];

//            echo $row['enter_incident'], "<br>";
        }

    }

}
$year_month_user_enter = array();
$year_month_user_exit = array();
$year_month_user_total = array();
foreach ($years as $year){
    foreach ($users as $user){

        foreach ($months as $month=>$m){
            $query = "SELECT * FROM star_stats_retroactive WHERE MONTH(start_date)=$m AND YEAR(start_date)=$year AND user_name='$user'";
     //   echo $m;
            $result = $conn->query($query);
            if($result){
//                echo "hey";
                while($row = $result->fetch_assoc()) {
                    if(empty($year_month_user_enter[$user."-". $month."-".$year])){
                        $year_month_user_enter[$user."-". $month."-".$year] = 0;
                    }
                    if(empty($year_month_user_exit[$user."-". $month."-".$year])){
                        $year_month_user_exit[$user."-". $month."-".$year] = 0;
                    }
                    if(empty($year_month_user_total[$user."-".$month. "-".$year])){
                        $year_month_user_total[$user."-". $month."-".$year] = 0;
                    }
                    $year_month_user_enter[$user."-". $month."-".$year] += $row['enter_incident'];
                    $year_month_user_exit[$user."-". $month."-".$year]+= $row['exit_incident'];
                    $year_month_user_total[$user."-". $month."-".$year]+= $row['total_use'];

//            echo $row['enter_incident'], "<br>";
                }

            }
        }




    }

}
$year = $years[0];
$k = $months_name[0];

$test = "";
foreach ($years as $year){

    $y ='{"name":"'.$year. '","children":[';
    $mon = "";
    foreach ($months as $k=>$v){
        $month_stat = "{";
        $month_stat .=  $name . '"'.$k .'",'.$children. '[{"name":"todo"}]},{';
//    alert($month_stats);
        $month_stat .=  $name.'"'.$k.' Stats'.'"'.",".$children."[";
        $mon .= $month_stat;
//    echo $month_stat;
        $user_stat = "";
//        $user = $users[0];
        foreach ($users as $user){
            $enter_perc = $year_month_user_enter[$user."-".$k."-".$year]/ $year_month_user_total[$user."-".$k."-".$year];
            $enter_perc = "On entrance did not use ".number_format($enter_perc*100, 2) .'% of the time';
            $exit_perc = $year_month_user_exit[$user."-".$k."-".$year]/ $year_month_user_total[$user."-".$k."-".$year];
            $exit_perc = "On exit did not use ".number_format($exit_perc*100,2) .'% of the time';
            $user_stat .= '{'.$name.'"'.$user.'",'.$children. '[{';
            $user_stat .= $name .'"'.$enter_perc.'"},{';
            $user_stat .=  $name.'"'.$exit_perc.'"'.'}]' .'},' ;
        }
        $user_stat = substr($user_stat,0,strlen($user_stat)-1);
        $mon .= $user_stat . ']},';

//        $mon .= "{".$name.'"'. $.'"' ."}";
//        echo $user_stat, ']}';

//    echo "]}";
//    $mon .= ",";
    }
//echo "{",
    $mon = substr($mon,0, strlen($mon)-1);
    $test .= $y . $mon. "]},";
    $test .= "{".$name.'"'. $year .' Stats",'.$children .'[';

    $user_stat = "";
    foreach ($users as $user){
        $enter_perc = $year_user_enter[$user."-".$year]/ $year_user_total[$user."-".$year];
        $enter_perc = "On entrance did not use ".number_format($enter_perc*100, 2) .'% of the time';
        $exit_perc = $year_user_exit[$user."-".$year]/ $year_user_total[$user."-".$year];
        $exit_perc = "On exit did not use ".number_format($exit_perc*100,2) .'% of the time';
        $user_stat .= '{'.$name.'"'.$user.'",'.$children. '[{';
        $user_stat .= $name .'"'.$enter_perc.'"},{';
        $user_stat .=  $name.'"'.$exit_perc.'"'.'}]' .'},' ;
    }
    $user_stat = substr($user_stat,0,strlen($user_stat)-1);
//    $test .= '{"name":"todo"}';
    $test .= $user_stat;

    $test .= ']},';

}

$test = substr($test, 0 , strlen($test)-1);
echo '{',$name,'"Hand Sanitizer",',$children,'[', $test , "]}";
// working block

//$y ='{"name":"'.$year. '","children":[';
//$mon = "";
//foreach ($months as $k=>$v){
//    $month_stat = "{";
//    $month_stat .=  $name . '"'.$k .'",'.$children. '[{"name":"todo"}]},{';
////    alert($month_stats);
//    $month_stat .=  $name.'"'.$k.' Stats'.'"'.",".$children."[";
//    $mon .= $month_stat;
////    echo $month_stat;
//    $user_stat = "";
//    $user = $users[0];
//    foreach ($users as $user){
//        $enter_perc = $year_month_user_enter[$user."-".$k."-".$year]/ $year_month_user_total[$user."-".$k."-".$year];
//        $enter_perc = "On entrance did not use ".number_format($enter_perc*100, 2) .'% of the time';
//        $exit_perc = $year_month_user_exit[$user."-".$k."-".$year]/ $year_month_user_total[$user."-".$k."-".$year];
//        $exit_perc = "On exit did not use ".number_format($exit_perc*100,2) .'% of the time';
//        $user_stat .= '{'.$name.'"'.$user.'",'.$children. '[{';
//        $user_stat .= $name .'"'.$enter_perc.'"},{';
//        $user_stat .=  $name.'"'.$exit_perc.'"'.'}]' .'},' ;
//    }
//        $user_stat = substr($user_stat,0,strlen($user_stat)-1);
//        $mon .= $user_stat . ']},';
////        echo $user_stat, ']}';
//
////    echo "]}";
////    $mon .= ",";
//}
////echo "{",
//$mon = substr($mon,0, strlen($mon)-1);
//echo $y, $mon, "]}";


// working block







//echo substr($mon,0,strlen($mon)-1);
//,"}";
//echo count($year_month_user_enter), "<br>";
//echo count($year_month_user_exit), "<br>";
//echo count($year_month_user_total), "<br>";
//foreach ($year_month_user_enter as $k=>$v){
//    echo $k, " ",$v,"<br>";
//}
$json = '{"name": "Hand Sanitizer Use", "children":[';
// End of variables
$y ="";

//foreach($years as $year){
    $y .= "{".$name.$year."},";
//    $y .= "{".$name.$year."stats"."},";
//}
//$y = substr($y,0,strlen($y)-1);
//echo $json,$y,"]}";
mysqli_close($conn);

?>

