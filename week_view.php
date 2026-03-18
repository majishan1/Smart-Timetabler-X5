<?php
session_start()

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
            background: #fAf9f6;
            color: #264653;
        }

        h1 {
            text-align: center;
            font-size: 2.8em;
            margin-bottom: 25px;
            color: #264653;
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

        .calendarContainer {
            height: 85vh;
            overflow-y: auto;
            overflow-x: hidden;

            border-radius: 8px;
            background-color: #ffffff;
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
            background-color: #f0efeb;
            position: sticky;
            top: 0;
            z-index: 100;
        }

        .day_name {
            flex: 7;
            border: 1px solid #d8d2c2;
            overflow: hidden;
        }

        .day_name.today {
            color: #e76f51; 
            font-weight: bold;
        }

        .times, .days {
            position: relative;
            border: 1px solid #a9a9a9;

            background-image: repeating-linear-gradient(
                to bottom,
                transparent,
                transparent 59px,
                #eae6df 59px,
                #eae6df 60px
            );
        }

        .times {
            flex: 3;
            background-color: #f9f8f6;
        }

        .time {
            height: 60px;
            flex: 24;
            overflow: hidden;
        }

        .days {
            height: 1440px;
            flex: 7;
        }

        .event_block {
            position: absolute;
            width: 97.5%;
            left: 1.25%;
            border: 1px solid black;
            border-left-width: 5px;
            border-radius: 5px;
            box-shadow: 0 2px 5px #00000080;
            box-sizing: border-box;

            padding: 4px 6px;
            color: #264653;
            font-size: 0.8em;
            overflow: hidden;
        }

        .event_block:hover {
            box-shadow: 0 -1 px 3px #00000080;
        }

        .event_block.LECTURE {background-color: #e8f5e9; border-color: #c2dfe3; border-left-color: #2a9d8f;}
        .event_block.ADVISEMENT {background-color: #f4f1de; border-color: #eae6df; border-left-color: #e07a5f;}
        .event_block.LABORATORY {background-color: #fde2e4; border-color: #fad2e1; border-left-color: #d5bdaf;}
        .event_block.DROP-IN {background-color: #e0fbfc; border-color: #c2dfe3; border-left-color: #98c1d9;}
        .event_block.EXAMPLES {background-color: #ffd6a5; border-color: #ffc685; border-left-color: #f4a261;}
        .event_block.WORKSHOP {background-color: #fff1e6; border-color: #fde2e4; border-left-color: #e76f51;}

        .cur_time_line {
            position: absolute;
            width: 0%;
            border: 1px dashed #e76f51;
            z-index: 5;
        }

        #invis {
            flex: 3;
            border: 1px solid #d8d2c2;
        }

    </style>

    <script>
        let week_offset = 0;
        const events = <?php echo json_encode($events) ?>;

        function new_week(offset_change) {
            week_offset += offset_change;
            let week_events = get_week_events();
            remove_events();
            update_events(week_events);
            remove_date_bar();
            set_date_bar();
        }

        function get_week_events() {
            let week_start = get_start_of_week();

            let week_end = new Date(week_start);
            week_end.setDate(week_start.getDate() + 6);
            week_end.setHours(23, 59, 59);

            let selected_events = [];

            for (var i = 0; i < events.length; i++) {
                let event = events[i];
                let ev_date = new Date(event.start_time);
                if (ev_date >= week_start && ev_date <= week_end) {
                    selected_events.push(event);
                }
            }
            return selected_events;
        }

        function get_start_of_week() {
            let target_date = new Date();
            target_date.setDate(target_date.getDate() + week_offset * 7);
            let day_of_target = target_date.getDay();

            let week_start = new Date(target_date);
            week_start.setDate(target_date.getDate() - day_of_target);
            week_start.setHours(0, 0, 0);
            return week_start;
        }

        function remove_events() {
            let events = document.getElementsByClassName('event_block');
            while (events.length > 0) {
                events[0].parentNode.removeChild(events[0]);
            }
        }

        function update_events(events) {
            for (var i = 0; i < events.length; i++) {
                let event = events[i];

                let event_dur = parseInt(event['duration']);
                let event_time = new Date(event['start_time']);

                let event_day = event_time.getDay();
                let event_hour = event_time.getHours();
                let event_min = event_time.getMinutes();

                let mins_into_day = event_hour * 60 + event_min;

                let day_col = document.getElementById(String(event_day));

                let new_event_div = document.createElement('div');
                new_event_div.className = "event_block" + " " + event['type'].toUpperCase();
                new_event_div.innerHTML = event['module'];
                new_event_div.style.top = mins_into_day + "px"
                new_event_div.style.height = event_dur + "px"

                

                day_col.appendChild(new_event_div);

            }
        }

        function remove_date_bar() {
            let day_container = document.getElementById('-1')
            for (var i = 0; i < 7; i++) {
                let day_name = document.getElementById('d' + i);
                day_container.removeChild(day_name);
            }
        }

        function set_date_bar() {
            let this_week_s = get_start_of_week();
            let days = ['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'];
            let titles_div = document.getElementById('-1');

            for (var i = 0; i < days.length; i++) {
                let name = days[i];
                let name_div = document.createElement('div');
                name_div.className = "day_name";
                name_div.id = "d" + i;
                name_div.innerHTML = name + "<br>" + this_week_s.getDate();
                this_week_s.setDate(this_week_s.getDate() + 1);
                titles_div.appendChild(name_div);
            }

            if (week_offset === 0) {
                let today = new Date();
                let day = document.getElementById('d' + today.getDay());
                day.className += ' today'
            }
        }

    </script>

</head>
<body>
    <h1>Week View</h1>
    <button type="button" onclick="new_week(-1)" class="week_change" id="prev_week">Previous Week</button>
    <button type="button" onclick="new_week(1)" class="week_change" id="next_week">Next Week</button>
    <div class='calendarContainer'>
        <div id='-1' class='day_names'>
            <div id="invis" class="day_name">Times</div>
            <script>
                set_date_bar();
            </script>
        </div>
        <div class="week" id="WeekID">
            <div id='7' class='times'>
                <script>
                    let time_col = document.getElementById('7');
                    for (var i = 0; i < 24; i++) {
                        let time = document.createElement('div')
                        time.className = "time";
                        time.innerHTML = i > 9 ? i + ":00": "0" + i + ":00";
                        time_col.appendChild(time);
                    }
                </script>
            </div>
            <div id='0' class='days'></div>
            <div id='1' class='days'></div>
            <div id='2' class='days'></div>
            <div id='3' class='days'></div>
            <div id='4' class='days'></div>
            <div id='5' class='days'></div>
            <div id='6' class='days'></div>
        </div>
        <script>
            update_events(get_week_events());

            // Make a line to show the current time, up to the day of the week
            let date_now = new Date();
            let today = date_now.getDay() + 1
            let line_height = date_now.getHours() * 60 + date_now.getMinutes();

            let week_box = document.getElementById('WeekID');
            let time_line = document.createElement('hr');

            time_line.className = "cur_time_line";
            time_line.style.top = line_height + "px";
            time_line.style.width = 100/52 * (3 + 7 * today) + "%";

            week_box.appendChild(time_line);

            let today_col = document.getElementById("d" + (today - 1));
            today_col.className += " today";

            let cal_box = document.querySelector('.calendarContainer');
            cal_box.scrollTo(0, ((line_height > 60)? line_height - 60: line_height));

        </script>
    </div>
    
</body>
</html>