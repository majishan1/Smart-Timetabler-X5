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
            <form action="get_cal.php" method="get" id="cal-form">
                <label for="cal-form">Calendar URL: </label><br>
                <input type="text" id="cal" name="calendar"><br>
                <input type="submit" value="Import Calendar">
            </form>
        </div>
    </body>
</html>
<?php

    if (isset($_GET['calendar'])) {
        $url = $_GET['calendar'];
        
        if (filter_var($url, FILTER_VALIDATE_URL)) {
            $safe_url = escapeshellarg($url);
            $json_output = shell_exec("python3 ical_parser.py $safe_url");
            
            $dec_arr = json_decode($json_output, true);
           // echo gettype($dec_arr);
            foreach($dec_arr as $key => $out) {
                echo ('UID: ' . $key . "<br>");
                foreach($dec_arr[$key] as $header => $data) {
                    echo ($header . ": " . $dec_arr[$key][$header] . "<br>");
                }
            }



            //echo $decoded_string;
        }
        else {
            echo "Incorrect URL format";
        }
    }







?>