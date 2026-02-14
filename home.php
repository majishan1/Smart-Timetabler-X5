<?php
 //include 'db_connect.php';
 
 if ($_SERVER["REQUEST_METHOD"] == "POST") {

  $event_title = $_POST['event_title'];

  // check if event_title is empty
  if (empty($event_title)) {
    echo "Please enter an event title!";
  } else {
    $event_type = $_POST['event_type'];  //might change to selection bar
    $date_time = $_POST['date_time'];
    $duration = $_POST['duration'];
    $description = $_POST['description'];
    $tags = $_POST['tags'];  //might change to selection bar
  }

// For testing, just print the values
        echo "<h3>Form submitted successfully!</h3>";
        echo "<pre>";
        print_r($_POST);
        echo "</pre>";

  // SQL insert logic

}
?>

<!doctype html>
<html lang="en">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Smart Timetabler</title>
    <link rel="stylesheet" , type="text/css" , href="home.css" />
  </head>

  <body>
    <main>
      <div id="HomePage" , class="page">
        <h2>Today</h2>
        <p>
          This is the home page named "Today". This page will be the home screen
          and it will display the schedule of the day.
        </p>
      </div>

      <div id="CalendarPage" , class="page">
        <h2>Calender</h2>
        <p>This is the second page named "Calendar".</p>
      </div>

      <div id="AddPage">
        <h2 class="title">Add New Event</h2>
        <div id="AddPage" , class="background">
          <p>Event Title</p>
          <input type="text" name="event_title" placeholder="e.g., Submit assignment" />
          <p>Event Type</p>
          <input type="text" name="event_type" placeholder="A selection bar?" />
          <div class="row">
            <p class="wide">Date & Start Time</p>
            <p class="narrow">Duration (mins)</p>
          </div>
          <div class="row">
            <input class="wide" type="datetime-local" name="date_time"/>
            <input class="narrow" placeholder="Input Time" name="duration"/>
          </div>
          <p>Description / Location</p>
          <textarea name="description" placeholder="Add details" rows="5"></textarea>
          <p>Tags</p>
          <input type="text" name="tags" placeholder="A selection bar?" />
          <button type="submit">Create Event</button>
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
