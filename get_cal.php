<!doctype html>
<html>
    <head>
        <title>Calendar Retriever</title>
        <style>
            body{
                display: flex;
                justify-content: center;
                align-items: center;
                min-height: 100vh;
                font-size: 2em;
            }
        </style>
    </head>
    <body>
        <div class='cal_form'>
            <form action="get_cal.php" method="get">
                <label for="calendar">Calendar URL: </label><br>
                <input type="text" id="cal" name="calendar"><br>
                <input type="submit" value="Import Calendar">
            </form>
        </div>
    </body>
</html>
<?php


    function verifyCalendarContent($url) {
        $content = @file_get_contents($url, false, null, 0);

        if ($content === false) {
            return "Not a valid .ics file";
        }
        else if (strpos($content, 'BEGIN:VCALENDAR') !== false) {
            return $url;
        }
        else {
            return "Not a valid .ics file";
        }
    }

    if (isset($_GET['calendar'])) {
        $url = $_GET['calendar'];
        
        if (filter_var($url, FILTER_VALIDATE_URL) && verifyCalendarContent($url) === $url) {
            $safe_url = escapeshellarg($url);
            $parser = shell_exec("python3 ical_parser.py $safe_url");
            echo nl2br($parser);
        }
        else {
            echo "Incorrect URL format";
        }
    }







?>