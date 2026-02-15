<?php
/**
 * Login / Authentication Page
 */

// 1. Define Constants (Required for Authenticator)
// UPDATE THIS to your specific URL if needed, or keep as is if it works
define("DEVELOPER_URL", "https://smarttimetabler.gt.tc/login.php");
define("AUTHENTICATION_SERVICE_URL", "http://studentnet.cs.manchester.ac.uk/authenticate/");
define("AUTHENTICATION_LOGOUT_URL", "http://studentnet.cs.manchester.ac.uk/systemlogout.php");

// 2. Start Session & Load Authenticator
session_start();
require_once("Authenticator.php");

// 3. Authenticate User
// If not logged in, this redirects them to the Uni login page automatically.
Authenticator::validateUser();

// 4. Connect to Database (THIS WAS MISSING!)
require 'db_connect.php'; 

// 5. Get User Data
$student_id = Authenticator::getUsername();
$student_name = Authenticator::getFullName();

// 6. Save to Session
$_SESSION['user_id'] = $student_id;

// 7. Insert/Update User in Database
// We use the correct PHP syntax: ->prepare and ->execute
$sql = "INSERT IGNORE INTO Users (UserID, user_name) VALUES (:id, :name)";

try {
    if (!isset($conn)) {
        die("Error: Database connection failed. Check db_connect.php");
    }

    $stmt = $conn->prepare($sql);
    $stmt->execute([
        ':id' => $student_id,
        ':name' => $student_name
    ]);
    
    // 8. Redirect to Dashboard
    header("Location: index.php");
    exit();

} catch (PDOException $e) {
    die("Database Error: " . $e->getMessage());
}
?>