<?php
include 'db_connect.php';
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
                <tr><td>0:00</td><td></td><td></td><td></td><td></td><td></td><td></td><td></td></tr>
                <tr><td>1:00</td><td></td><td></td><td></td><td></td><td></td><td></td><td></td></tr>
                <tr><td>2:00</td><td></td><td></td><td></td><td></td><td></td><td></td><td></td></tr>
                <tr><td>3:00</td><td></td><td></td><td></td><td></td><td></td><td></td><td></td></tr>
                <tr><td>4:00</td><td></td><td></td><td></td><td></td><td></td><td></td><td></td></tr>
                <tr><td>5:00</td><td></td><td></td><td></td><td></td><td></td><td></td><td></td></tr>
                <tr><td>6:00</td><td></td><td></td><td></td><td></td><td></td><td></td><td></td></tr>
                <tr><td>7:00</td><td></td><td></td><td></td><td></td><td></td><td></td><td></td></tr>
                <tr><td>8:00</td><td></td><td></td><td></td><td></td><td></td><td></td><td></td></tr>
                <tr><td>9:00</td><td></td><td></td><td></td><td></td><td></td><td></td><td></td></tr>
                <tr><td>10:00</td><td></td><td></td><td></td><td></td><td></td><td></td><td></td></tr>
                <tr><td>11:00</td><td></td><td></td><td></td><td></td><td></td><td></td><td></td></tr>
                <tr><td>12:00</td><td></td><td></td><td></td><td></td><td></td><td></td><td></td></tr>
                <tr><td>13:00</td><td></td><td></td><td></td><td></td><td></td><td></td><td></td></tr>
                <tr><td>14:00</td><td></td><td></td><td></td><td></td><td></td><td></td><td></td></tr>
                <tr><td>15:00</td><td></td><td></td><td></td><td></td><td></td><td></td><td></td></tr>
                <tr><td>16:00</td><td></td><td></td><td></td><td></td><td></td><td></td><td></td></tr>
                <tr><td>17:00</td><td></td><td></td><td></td><td></td><td></td><td></td><td></td></tr>
                <tr><td>18:00</td><td></td><td></td><td></td><td></td><td></td><td></td><td></td></tr>
                <tr><td>19:00</td><td></td><td></td><td></td><td></td><td></td><td></td><td></td></tr>
                <tr><td>20:00</td><td></td><td></td><td></td><td></td><td></td><td></td><td></td></tr>
                <tr><td>21:00</td><td></td><td></td><td></td><td></td><td></td><td></td><td></td></tr>
                <tr><td>22:00</td><td></td><td></td><td></td><td></td><td></td><td></td><td></td></tr>
                <tr><td>23:00</td><td></td><td></td><td></td><td></td><td></td><td></td><td></td></tr>
            </tbody>
        </table>
    </div>
</body>
</html>