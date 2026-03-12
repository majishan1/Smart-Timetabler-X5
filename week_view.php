<?php
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

include 'db_connect.php';
$current_uID = $_SESSION['user_id'];
$events = [];
$err = "";

try {
    $sql = "SELECT * FROM Events WHERE UserID = :u_ID ORDER BY start_time ASC";
    $statement = $conn->prepare($sql);
    $statement->execute(['u_ID' => $current_uID]);

    $events = $statement->fetchAll(PDO::FETCH_ASSOC);
}
catch (PDOException $e){
    $err = "Error: " . $e->getMessage();
    exit();
}

$today = new DateTimeImmutable();
$this_week_s = $today->modify('Monday this week 00:00:00');
$this_week_e = $today->modify('Sunday this week 23:59:59');
$selected_week_arr = [];
foreach ($events as $event) {
    $ev_date = new DateTimeImmutable($event['start_time']);
    if ($ev_date >= $this_week_s && $event['start_time'] <= $this_week_e) {
        $selected_week_arr[] = $event;
    }
}


?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Week View</title>
    <style>
        * {margin:0;padding:0;box-sizing:border-box;font-family:Arial,sans-serif}
        body {padding:20px;background:#f8f9fa}
        h1 {text-align:center;font-size:2.8em;margin-bottom:25px;color:#212529}
        .wrap {width:100%;overflow-x:auto;background:#fff;border-radius:8px}
        table {width:100%;border-collapse:collapse;min-width:900px}
        th,td {border:1px solid #dee2e6;padding:10px;text-align:center}
        thead th {background:#e9ecef;position:sticky;top:0;z-index:10;font-weight:600}
        tbody td:first-child {width:80px;background:#f8f9fa;font-weight:500}
        tbody td {height:65px;vertical-align:top}
    </style>

    <script>
        const events = <?php echo jeson_encode($selected_week_arr); ?>

        var temp = document.getElementById('mon00');
        temp.innerHTML == events;
    </script>
</head>
<body>
    <h1>Week View</h1>
    <div class="wrap">
        <table>
            <thead>
                <tr>
                    <th>Time</th>
                    <th>Monday</th><th>Tuesday</th><th>Wednesday</th>
                    <th>Thursday</th><th>Friday</th><th>Saturday</th><th>Sunday</th>
                </tr>
            </thead>
            <tbody>
                <tr><td>0:00</td><td id="mon00"></td><td id="tue00"></td><td id="wed00"></td><td id="thur00"></td><td id="fri00"></td><td id="sat00"></td><td id="sun00"></td></tr>
                <tr><td>1:00</td><td id="mon01"></td><td id="tue01"></td><td id="wed01"></td><td id="thur01"></td><td id="fri01"></td><td id="sat01"></td><td id="sun01"></td></tr>
                <tr><td>2:00</td><td id="mon02"></td><td id="tue02"></td><td id="wed02"></td><td id="thur02"></td><td id="fri02"></td><td id="sat02"></td><td id="sun02"></td></tr>
                <tr><td>3:00</td><td id="mon03"></td><td id="tue03"></td><td id="wed03"></td><td id="thur03"></td><td id="fri03"></td><td id="sat03"></td><td id="sun03"></td></tr>
                <tr><td>4:00</td><td id="mon04"></td><td id="tue04"></td><td id="wed04"></td><td id="thur04"></td><td id="fri04"></td><td id="sat04"></td><td id="sun04"></td></tr>
                <tr><td>5:00</td><td id="mon05"></td><td id="tue05"></td><td id="wed05"></td><td id="thur05"></td><td id="fri05"></td><td id="sat05"></td><td id="sun05"></td></tr>
                <tr><td>6:00</td><td id="mon06"></td><td id="tue06"></td><td id="wed06"></td><td id="thur06"></td><td id="fri06"></td><td id="sat06"></td><td id="sun06"></td></tr>
                <tr><td>7:00</td><td id="mon07"></td><td id="tue07"></td><td id="wed07"></td><td id="thur07"></td><td id="fri07"></td><td id="sat07"></td><td id="sun07"></td></tr>
                <tr><td>8:00</td><td id="mon08"></td><td id="tue08"></td><td id="wed08"></td><td id="thur08"></td><td id="fri08"></td><td id="sat08"></td><td id="sun08"></td></tr>
                <tr><td>9:00</td><td id="mon09"></td><td id="tue09"></td><td id="wed09"></td><td id="thur09"></td><td id="fri09"></td><td id="sat09"></td><td id="sun09"></td></tr>
                <tr><td>10:00</td><td id="mon10"></td><td id="tue10"></td><td id="wed10"></td><td id="thur10"></td><td id="fri10"></td><td id="sat10"></td><td id="sun10"></td></tr>
                <tr><td>11:00</td><td id="mon11"></td><td id="tue11"></td><td id="wed11"></td><td id="thur11"></td><td id="fri11"></td><td id="sat11"></td><td id="sun11"></td></tr>
                <tr><td>12:00</td><td id="mon12"></td><td id="tue12"></td><td id="wed12"></td><td id="thur12"></td><td id="fri12"></td><td id="sat12"></td><td id="sun12"></td></tr>
                <tr><td>13:00</td><td id="mon13"></td><td id="tue13"></td><td id="wed13"></td><td id="thur13"></td><td id="fri13"></td><td id="sat13"></td><td id="sun13"></td></tr>
                <tr><td>14:00</td><td id="mon14"></td><td id="tue14"></td><td id="wed14"></td><td id="thur14"></td><td id="fri14"></td><td id="sat14"></td><td id="sun14"></td></tr>
                <tr><td>15:00</td><td id="mon15"></td><td id="tue15"></td><td id="wed15"></td><td id="thur15"></td><td id="fri15"></td><td id="sat15"></td><td id="sun15"></td></tr>
                <tr><td>16:00</td><td id="mon16"></td><td id="tue16"></td><td id="wed16"></td><td id="thur16"></td><td id="fri16"></td><td id="sat16"></td><td id="sun16"></td></tr>
                <tr><td>17:00</td><td id="mon17"></td><td id="tue17"></td><td id="wed17"></td><td id="thur17"></td><td id="fri17"></td><td id="sat17"></td><td id="sun17"></td></tr>
                <tr><td>18:00</td><td id="mon18"></td><td id="tue18"></td><td id="wed18"></td><td id="thur18"></td><td id="fri18"></td><td id="sat18"></td><td id="sun18"></td></tr>
                <tr><td>19:00</td><td id="mon19"></td><td id="tue19"></td><td id="wed19"></td><td id="thur19"></td><td id="fri19"></td><td id="sat19"></td><td id="sun19"></td></tr>
                <tr><td>20:00</td><td id="mon20"></td><td id="tue20"></td><td id="wed20"></td><td id="thur20"></td><td id="fri20"></td><td id="sat20"></td><td id="sun20"></td></tr>
                <tr><td>21:00</td><td id="mon21"></td><td id="tue21"></td><td id="wed21"></td><td id="thur21"></td><td id="fri21"></td><td id="sat21"></td><td id="sun21"></td></tr>
                <tr><td>22:00</td><td id="mon22"></td><td id="tue22"></td><td id="wed22"></td><td id="thur22"></td><td id="fri22"></td><td id="sat22"></td><td id="sun22"></td></tr>
                <tr><td>23:00</td><td id="mon23"></td><td id="tue23"></td><td id="wed23"></td><td id="thur23"></td><td id="fri23"></td><td id="sat23"></td><td id="sun23"></td></tr>
            </tbody>
        </table>
    </div>
</body>
</html>