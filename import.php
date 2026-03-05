<?php

session_start();


if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}


$current_user_id = $_SESSION['user_id'];


$ical_url = "";
$message = "";


if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['ical_url'])) {
    

    $ical_url = $_POST['ical_url'];
    
    $message = "URL successfully saved to the variable ical_url!";
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Import Timetable</title>
    <style>
        body { font-family: Arial, sans-serif; padding: 20px; max-width: 600px; margin: 0 auto; }
        .box { border: 2px dashed #ccc; padding: 30px; margin-top: 20px; }
        input[type="url"] { width: 100%; padding: 10px; margin: 10px 0; box-sizing: border-box; }
        button { background: #007bff; color: white; padding: 10px 20px; border: none; cursor: pointer; }
        .success { background: #e8f5e9; color: #2e7d32; padding: 15px; margin-bottom: 20px; border-radius: 5px; }
        .debug-info { background: #333; color: lime; padding: 15px; font-family: monospace; margin-top: 20px; }
    </style>
</head>
<body>

    <h1>Import Your Timetable</h1>
    
    <p>Welcome, your User ID is: <strong><?php echo htmlspecialchars($current_user_id); ?></strong></p>

    <?php if ($message): ?>
        <div class="success"><?php echo $message; ?></div>
        
        <div class="debug-info">
            Data ready for the next step:<br>
            $current_user_id = "<?php echo htmlspecialchars($current_user_id); ?>"<br>
            $ical_url = "<?php echo htmlspecialchars($ical_url); ?>"
        </div>
    <?php endif; ?>

    <div class="box">
        <form method="POST" action="">
            <label for="ical_url"><b>Paste your iCal URL:</b></label>
            <input type="url" name="ical_url" id="ical_url" placeholder="Enter a URL" required>
            <button type="submit">Save URL</button>
        </form>
    </div>

</body>
</html>