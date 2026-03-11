<?php
// Turn on error reporting for debugging
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();
require 'db_connect.php';

// force the database to show errors for debugging
$conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

// Kick them out if they aren't logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$current_user_id = $_SESSION['user_id'];
$message = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $event_title = isset($_POST['event_title']) ? trim($_POST['event_title']) : '';

    if (empty($event_title)) {
        $message = "<p style='color:red; text-align:center; font-weight:bold;'>Please enter an event title!</p>";
    } else {
        $event_type = isset($_POST['dropdown']) ? $_POST['dropdown'] : 'Task';  
        $duration = !empty($_POST['duration']) ? (int)$_POST['duration'] : 0;
        $description = isset($_POST['description']) ? $_POST['description'] : '';
        $tags = isset($_POST['tags']) ? $_POST['tags'] : '';
        
        $date_time = !empty($_POST['date_time']) ? str_replace('T', ' ', $_POST['date_time']) . ':00' : NULL;

        try {
            // Check if this exact user already has this exact task at this exact time
            $check_sql = "SELECT taskID FROM ToDo WHERE UserID = :uid AND title = :title AND deadline = :deadline";
            $check_stmt = $conn->prepare($check_sql);
            $check_stmt->execute([
                ':uid' => $current_user_id,
                ':title' => $event_title,
                ':deadline' => $date_time
            ]);

            // If it found a match
            if ($check_stmt->rowCount() > 0) {
                $message = "<p style='color:orange; text-align:center; font-weight:bold;'>You already added this task!</p>";
            } else {
                // It is a new event
                $sql = "INSERT INTO ToDo (UserID, title, type, description, deadline, tags, is_complete, duration) 
                        VALUES (:UserID, :title, :type, :description, :deadline, :tags, :is_complete, :duration)";
                        
                $stmt = $conn->prepare($sql);
                
                $stmt->execute([
                    ':UserID' => $current_user_id,
                    ':title' => $event_title,
                    ':type' => $event_type,
                    ':description' => $description,
                    ':deadline' => $date_time,
                    ':tags' => $tags,
                    ':is_complete' => 0,
                    ':duration' => $duration
                ]);
                
                $message = "<div style='background: #d4edda; color: #155724; padding: 15px; border-radius: 5px; text-align: center; margin: 15px 0; border: 1px solid #c3e6cb;'>
                            <strong>Event successfully added!</strong>
                            </div>";
            }

        } catch (PDOException $e) {
            // Error checkign again for debugging
            $message = "<div style='background: #ffebee; color: #c62828; padding: 15px; border: 2px solid #ef5350; text-align: center; margin: 15px 0;'>
                        <strong>Database Error:</strong><br>" . $e->getMessage() . "
                        </div>";
        }
    }
}
?>

<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Smart Timetabler</title>
    <link rel="stylesheet" type="text/css" href="home.css" />
</head>

<body>
    <main>
        <div id="HomePage" class="page">
            <h2>Today</h2>
            <p>
                This is the home page named "Today". This page will be the home screen
                and it will display the schedule of the day.
            </p>
        </div>

        <div id="CalendarPage" class="page">
            <h2>Calender</h2>
            <p>This is the second page named "Calendar".</p>
        </div>

        <div id="AddPage">
            <h2 class="title">Add New Event</h2>
            
            <?php if (!empty($message)) echo $message; ?>

            <div class="background">
                <form method="POST" action="">
                    <p>Event Title</p>
                    <input type="text" name="event_title" placeholder="e.g., Submit assignment" />
                    <label for="dropdown">Event Type</label><br>
                    <div class="row">
                        <select name="dropdown" id="dropdown" class="dropdowns wide">
                            <option value="" hidden selected disabled>-- Select an option --</option>
                            <option value="Event">Event</option>
                            <option value="Task">Task</option>
                        </select>
                        <div class="narrow"></div>
                    </div>
                    <div class="narrow"></div>
                    <div class="row">
                        <p class="wide">Date & Start Time</p>
                        <p class="narrow">Duration (mins)</p>
                    </div>
                    <div class="row">
                        <input class="wide" type="datetime-local" name="date_time"/>
                        <input class="narrow" placeholder="Input Time" name="duration" type="number"/>
                    </div>
                    <p>Description / Location</p>
                    <textarea name="description" placeholder="Add details" rows="5"></textarea>
                    <p>Tags</p>
                    <input type="text" name="tags" placeholder="A selection bar?" />
                    <button type="submit">Create Event</button>
                </form>
            </div>
        </div>
    </main>

    <footer class="toolbar">
        <button>Today</button>
        <button>+</button>
        <button>Calendar</button>
    </footer>
</body>
</html>