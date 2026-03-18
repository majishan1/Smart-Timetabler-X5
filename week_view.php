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
        
        h2 {
            margin: 5px;
        }

        .calendarContainer {
            height: 85vh;
            overflow-y: auto;
            overflow-x: hidden;

            border-radius: 8px;
            background-color: #ffffff;

            scrollbar-width: thin;
            scrollbar-color: #264653 #f0efeb;
        }

        .calendarContainer::-webkit-scrollbar {
            width: 12px;
        }

        .calendarContainer::-webkit-scrollbar-track {
            border-radius: 0px 8px 8px 0px;
            background: #f0efeb;
        }

        .calendarContainer::-webkit-scrollbar-thumb {
            background-color: #264653;
            border-radius: 10px;
            border: 3px solid #f0efeb;
            transition: background-color 0.2s;
        }

        .calendarContainer::-webkit-scrollbar-thumb:hover {
            background-color: #2a9d8f;
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
            box-shadow: 0 -1px 3px #00000080;
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

        .top_info {
            display: flex;
            flex-direction: row;
            justify-content: space-between;
        }

        .change_button_container {
            display: flex;
            gap: 0;
        }

        .week_change {
            background-color: #9dd3a1;
            color: #264653;
            font-size: 0.9em;
            text-align: center;
            border: none;
            padding: 10px 20px;
            margin: 5px 0px 5px 0px;
            transition: background-color 0.2s;
        }

        .week_change:hover {
            background-color: #2a9d8f
        }

        #today {
            background-color: #edd3a7;
        }

        #today:hover {
           background-color: #e76f51;
        }

        #prev_week {
            border-radius: 8px 0px 0px 8px;
        }

        #next_week {
            border-radius: 0px 8px 8px 0px;
        }

        #invis {
            flex: 3;
            border: 1px solid #d8d2c2;
        }

    </style>

    <script>
        let week_offset = 0;
        const events = <?php echo json_encode($events) ?>;
    </script>
    <script src="week_view.js" defer></script>

</head>
<body>
    <h1>Week View</h1>
    <div class="top_info">
        <h2 id="month"></h2>
        <div class="change_button_container">
            <button type="button" class="week_change" id="prev_week">Previous Week</button>
            <button type="button" class="week_change" id="today">Today</button>
            <button type="button" class="week_change" id="next_week">Next Week</button>
        </div>
    </div>
    <div class='calendarContainer'>
        <div id='-1' class='day_names'>
            <div id="invis" class="day_name">Times</div>
        </div>
        <div class="week" id="WeekID">
            <div id='7' class='times'></div>
            <div id='0' class='days'></div>
            <div id='1' class='days'></div>
            <div id='2' class='days'></div>
            <div id='3' class='days'></div>
            <div id='4' class='days'></div>
            <div id='5' class='days'></div>
            <div id='6' class='days'></div>
        </div>
    </div>
</body>
</html>