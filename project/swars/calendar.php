<?php
/*          INSERT THIS IN SQL QUERY WHEN CREATING TABLES FOR DATABASE(bookingcalendar)

CREATE TABLE `bookings` (
 `id` int(11) NOT NULL AUTO_INCREMENT,
 `date` date NOT NULL,
 `name` varchar(255) NOT NULL,
 `timeslot` VARCHAR(255) NOT NULL,
 `email` varchar(255) NOT NULL,
 PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=latin1

*/

session_start();

// echo $_SESSION['username'];

function build_calendar($month, $year) {
    $mysqli = new mysqli('localhost','root','','bookingcalendar');
     // Create array containing abbreviations of days of week.
     $daysOfWeek = array('Monday','Tuesday','Wednesday','Thursday','Friday','Saturday','Sunday');
     $firstDayOfMonth = mktime(0,0,0,$month,1,$year);
     // How many days does this month contain?
     $numberDays = date('t',$firstDayOfMonth);
     // Retrieve some information about the first day of the
     // month in question.
     $dateComponents = getdate($firstDayOfMonth);
     // What is the name of the month in question?
     $monthName = $dateComponents['month'];
     // What is the index value (0-6) of the first day of the
     // month in question.
     $dayOfWeek = $dateComponents['wday'];
    if($dayOfWeek==0){
        $dayOfWeek = 6;
    }else{
        $dayOfWeek = $dayOfWeek-1;
    }
     // Create the table tag opener and day headers
    $datetoday = date('Y-m-d');
    
    $calendar = "<table class='table table-bordered'>";
    echo '<a href="Navigation.php"><i style="color:black;font-size:30px;margin-left:-50px;padding-top:20px;" class="fa fa-arrow-left"></i></a>';
    $calendar .= "<center><h2 style='margin-top:-25px;font-weight:bold'>$monthName $year</h2>";
    $calendar.= "<a style='position:static;margin-left:1px;margin-top:-80px;font-weight:bold;font-size:20px;padding-top:0;height:30px;width:30px;border:none;border-radius:50%;background-color:#f9b79f;color:white;' class='btn btn-xs btn-primary' href='?month=".date('m', mktime(0, 0, 0, $month-1, 1, $year))."&year=".date('Y', mktime(0, 0, 0, $month-1, 1, $year))."'>&#8249;</a> ";
    
    $calendar.= " <a style='margin-left:90px;padding: 5px 15px;border-radius:20px;border:none;background-color:#f9b79f' class='btn btn-xs btn-primary' href='?month=".date('m')."&year=".date('Y')."'>Current Month</a> ";
    
    $calendar.= "<a style='position:static;margin-left:90px;margin-top:-80px;font-weight:bold;font-size:20px;padding-top:0;height:30px;width:30px;border:none;border-radius:50%;background-color:#f9b79f;color:white;' class='btn btn-xs btn-primary' href='?month=".date('m', mktime(0, 0, 0, $month+1, 1, $year))."&year=".date('Y', mktime(0, 0, 0, $month+1, 1, $year))."'>&#8250;</a></center><br>";

      $calendar .= "<tr>";
     // Create the calendar headers
     foreach($daysOfWeek as $day) {
          $calendar .= "<th  class='header'>$day</th>";
     } 
     // Create the rest of the calendar
     // Initiate the day counter, starting with the 1st.
     $currentDay = 1;
     $calendar .= "</tr><tr>";
     // The variable $dayOfWeek is used to
     // ensure that the calendar
     // display consists of exactly 7 columns.

     if ($dayOfWeek > 0) { 
         for($k=0;$k<$dayOfWeek;$k++){
                $calendar .= "<td  class='empty'></td>"; 
         }
     }

     $month = str_pad($month, 2, "0", STR_PAD_LEFT);
  
     while ($currentDay <= $numberDays) {
          // Seventh column (Saturday) reached. Start a new row.
        if ($dayOfWeek == 7) {
            $dayOfWeek = 0;
            $calendar .= "</tr><tr>";
        }
        $currentDayRel = str_pad($currentDay, 2, "0", STR_PAD_LEFT);
        $date = "$year-$month-$currentDayRel";
        $dayname = strtolower(date('l', strtotime($date)));
        $eventNum = 0;
        $today = $date==date('Y-m-d')? "today" : "";
                     // BLOCK A DAY OF A WEEK 
        if($dayname=='' || $dayname==''){
            $calendar.="<td><h4>$currentDay</h4> <button class='btn btn-danger btn-xs'>Holiday</button>";
        } 
        elseif($date<date('Y-m-d')){
            $calendar.="<td style='background-color:#f2f2f2'><h4>$currentDay</h4> <button class='btn btn-danger btn-xs' style='visibility:hidden'>Disabled</button>";
        }else{
            $totalbookings = checkSlots($mysqli,$date);
            if($totalbookings==20){
                $calendar.="<td class='$today'><h4>$currentDay</h4> <a href='#' class='btn btn-danger btn-xs'>All Booked</a>";
            }else{
                $availableslots = 20 - $totalbookings;
                $calendar.="<td class='$today'><h4>$currentDay</h4> <a href='book.php?date=".$date."' style='border-color:#71c171;background-color:#71c171;border-radius:20px;padding: 2px 8px;' class='btn btn-success btn-xs'>Book</a><br><small><i>$availableslots slots left</i></small>";
            }
        }
        
        $calendar .="</td>";
        // Increment counters
        $currentDay++;
        $dayOfWeek++;
     }
     
     // Complete the row of the last week in month, if necessary
     if ($dayOfWeek != 7) { 
          $remainingDays = 7 - $dayOfWeek;
            for($l=0;$l<$remainingDays;$l++){
                $calendar .= "<td class='empty'></td>"; 
         }
     }
     $calendar .= "</tr>";
     $calendar .= "</table>";
     return $calendar;
    }

    // TODO: FIX
    function checkSlots($mysqli, $date){
        $stmt = $mysqli->prepare("select * from bookings where date = ?");
        $stmt->bind_param('s',$date);
        $totalbookings = 0;
        if($stmt->execute()){
            $result = $stmt->get_result();
            if($result->num_rows > 0){
                while($row = $result->fetch_assoc()){
                    $totalbookings++;
                }
                $stmt->close();
            }
        }
        return $totalbookings;
    }

     $account = $_SESSION['username'];

    $db = mysqli_connect('localhost','root','','bookingcalendar');
    $query = "SELECT * FROM bookings WHERE account = '$account'";

    $test = mysqli_query($db, $query);
    $count = mysqli_num_rows($test);

    // function displayOrderById() {
    //     // need naka link sa may account
       
    //    // gettype($test);

    //     if ($count > 0) {
    //      while($row = $test->fetch_assoc()) {
    //        // echo "id: " . $row["id"]. " - Name: " . $row["date"]. " " . $row["name"]. "<br>";
    //         }
    //     } else {
    //         echo "empty";
    //     }

    //     return $test->fetch_assoc();
    // }

    // var_dump(displayOrderById());

    
?>

<html lang="en">
<head>
    <title>Calendar</title>
    <script src="https://unpkg.com/sweetalert/dist/sweetalert.min.js"></script>
    <link rel="stylesheet" type="text/css" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.4.0/css/bootstrap.min.css">
    <link rel="stylesheet" href="calendar.css">
</head>
<style>
    .swal-icon img {
        max-width: 30%;
        max-height: 30%;
    }
</style>
<body>
    <div class="container">
        <div class="row">
            <div class="col-md-12">
                <?php
                     $dateComponents = getdate();
                     if(isset($_GET['month']) && isset($_GET['year'])){
                         $month = $_GET['month']; 			     
                         $year = $_GET['year'];
                     }else{
                         $month = $dateComponents['mon']; 			     
                         $year = $dateComponents['year'];
                     }
                    echo build_calendar($month,$year);
                ?>
                <div class="form-group">
                    <form action="calendar.php" method="get">
                    <button type="submit" name="reserve">Display Reservation</button>
                    </form>
                </div>

              
            </div>
        </div>
    </div>

    <section class="main-content">
        <div class="container">
            <h1>Reservations</h1>
            <br>
            <table class="table-1">
                <thead>
                <tr>
                    <th>Date</th>
                    <th>Time</th>
                    <th>Name</th>
                    <th>Email</th>
                    <th>Guests</th>
                    <th>Contact</th>
                    <th>Action</th>
                </tr>
                </thead>
                <tbody>
                <?php
                if(isset($_GET['reserve'])) {
                    if ($count > 0){
                        while($row = $test->fetch_assoc()) {
                            ?>
                         <tr>
                            <td><?php echo $row["date"] ?></td>
                            <td><?php echo $row["timeslot"] ?></td>
                            <td><?php echo $row["name"] ?></td>
                            <td><?php echo $row["email"] ?></td>
                            <td><?php echo $row["guest"] ?></td>
                            <td><?php echo $row["contact"] ?></td>
                            <td>
                                <form action='calendar.php' method='get'>
                                    <button style='cursor:pointer;padding:8px;font-size:12px;border-radius:4px;background-color:#ff6666;color:white;border:none
                                    'name='del_button' value='<?php echo $row["id"] ?>'>CANCEL</button>
                                </form>
                            </td>
                        </tr>
                            <?php
                        }
                    }
                    else {
                        //if user has no reservation yet
                        echo '<script type="text/javascript">swal("AWW", "You have no reservations yet!", "pics/aww.png");</script>';
                    }
                }

                
                    if(isset($_GET['del_button'])) {
                        cancelReservation($_GET['del_button']);
                    } 
                    
                    function cancelReservation($qId) {
                            $conn = new mysqli('localhost', 'root', '', 'bookingcalendar');

                            if ($conn->connect_error) {
                                die("Connection failed: " . $conn->connect_error);
                            }

                            $q_params = "DELETE FROM bookings WHERE id=$qId";
                            
                            if($conn->query($q_params) === TRUE) {
                                echo '<script type="text/javascript">if(!swal("Success!", "Record Successfully Deleted!", "success"));{
                                    window.location.href("admin.php") 
                                }</script>';
                            } else {
                                echo "Error deleting record: " . $conn->error;
                            }
                        }

                ?>
                </tbody>
            </table>
        </div>
    </section>
  
</body>

</html>
