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
    if ($ev_date >= $this_week_s && $ev_date <= $this_week_e) {
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
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: Arial,sans-serif
        }

        body {
            padding: 20px;
            background: #74747441;
        }

        h1 {
            text-align: center;
            font-size: 2.8em;
            margin-bottom: 25px;
            color: #212529
        }

        .wrap {
            width: 100%;
            overflow-x: auto;
            background: #fff;
            border-radius: 8px
        }
        table {
            width: 100%;
            border-collapse: collapse;
            min-width: 900px
        }

        th,td {
            border: 1px solid #dee2e6;
            padding: 10px;
            text-align: center
        }

        thead th {
            background: #e9ecef;
            position: sticky;
            top: 0;
            z-index: 10;
            font-weight: 600
        }

        tbody td:first-child {
            width: 80px;
            background: #f8f9fa;
            font-weight: 500
        }

        tbody td {
            height: 65px;
            vertical-align: top
        }

        .week {
            display: flex;
            width: 100%;
            text-align: center;
            position: relative;
        }

        .day_names {
            width: 100%;
            display: flex;
            text-align: center;
            background-color: #a9a9a9a9;
        }

        .day_name {
            flex: 7;
            border: 1px solid #a9a9a9;
            overflow: hidden;
        }

        .times {
            flex: 3;
            position: relative;
            border: 1px solid #a9a9a9;
            background-color: #a9a9a9a9;

            background-image: repeating-linear-gradient(
                to bottom,
                transparent,
                transparent 59px,
                #a9a9a9 59px,
                #a9a9a9 60px
            );
        }

        .time {
            height: 60px;
            flex: 24;
            overflow: hidden;
        }

        .days {
            height: 1440px;
            flex: 7;
            border: 1px solid #a9a9a9;
            position: relative;

            background-image: repeating-linear-gradient(
                to bottom,
                transparent,
                transparent 59px,
                #a9a9a9 59px,
                #a9a9a9 60px
            );
        }

        .event_block {
            position: absolute;
            width: 97.5%;
            left: 1.25%;
            border: 1px solid black;
            border-left-width: 5px;
            border-radius: 5px;

            font-size: 0.8em;
            overflow: hidden;
        }

        .event_block.LECTURE { border-left-color: #26a036;}
        .event_block.ADVISEMENT {border-left-color: #6e6e6e;}
        .event_block.LABORATORY {border-left-color: #ed35e0;}
        .event_block.DROP-IN {border-left-color: #4debf0}
        .event_block.EXAMPLES {border-left-color: orange;}
        .event_block.WORKSHOP {border-left-color: yellow;}

        .cur_time_line {
            position: absolute;
            width: 0%;
            border: 1px dashed #e39e1e;
            z-index: 5;
        }

        #invis {
            flex: 3;
            border: 1px solid #a9a9a9;
        }

    </style>

</head>
<body>
    <h1>Week View</h1>
    <div id='-1' class='day_names'>
        <div id="invis" class="day_name">Times</div>
        <script>
            let this_week_s = new Date(<?php json_encode($this_week_s->format('c'))?>);
            let days = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'];
            let titles_div = document.getElementById('-1');
            for (var i = 0; i < days.length; i++) {
                let name = days[i];
                let name_div = document.createElement('div');
                name_div.className = "day_name";
                name_div.innerHTML = name + "<br>" + this_week_s.getDate();
                this_week_s.setDate(this_week_s.getDate() + 1);
                titles_div.appendChild(name_div);
            }
        </script>
    </div>
    <div class="week" id="WeekID">
        <div id='0' class='times'>
            <script>
                let time_col = document.getElementById('0');
                for (var i = 0; i < 24; i++) {
                    let time = document.createElement('div')
                    time.className = "time";
                    time.innerHTML = i > 9 ? i + ":00": "0" + i + ":00";
                    time_col.appendChild(time);
                }
            </script>
        </div>
        <div id='1' class='days'></div>
        <div id='2' class='days'></div>
        <div id='3' class='days'></div>
        <div id='4' class='days'></div>
        <div id='5' class='days'></div>
        <div id='6' class='days'></div>
        <div id='7' class='days'></div>
    </div>
    <p id='temp'></p>
    <script>
        const events = <?php echo json_encode($selected_week_arr); ?>;
        for (var i = 0; i < events.length; i++) {
            let event = events[i];

            let event_dur = parseInt(event['duration']);
            let event_time = new Date(event['start_time']);

            let event_day = event_time.getDay();
            let event_hour = event_time.getHours();
            let event_min = event_time.getMinutes();

            let mins_into_day = event_hour * 60 + event_min;

            event_day = (event_day === 0)? 7: event_day;
            let day_col = document.getElementById(String(event_day));

            let new_event_div = document.createElement('div');
            new_event_div.className = "event_block" + " " + event['type'];
            new_event_div.innerHTML = event['module'];
            new_event_div.style.top = mins_into_day + "px"
            new_event_div.style.height = event_dur + "px"

            

            day_col.appendChild(new_event_div);

        }

        // Make a line to show the current time, up to the day of the week
        let date_now = new Date();
        let today = (date_now.getDay() === 0)? 7: date_now.getDay();
        let line_height = date_now.getHours() * 60 + date_now.getMinutes();

        let week_box = document.getElementById('WeekID');
        let time_line = document.createElement('hr');

        time_line.className = "cur_time_line";
        time_line.style.top = line_height + "px";
        time_line.style.width = 100/52 * (3 + 7 * today) + "%";

        week_box.appendChild(time_line);

    </script>
    
</body>
</html>