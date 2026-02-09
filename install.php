<?php
/**
 * Database Setup - Currently for min working demo
 * * Creates only the essential tables required for the 
 * min working demo (Timetable, Tasks, Deadlines).
 */

require 'db_connect.php';

try {
    // 1. Users Table
    $sql_users = "CREATE TABLE IF NOT EXISTS Users (
        UserID INT(6) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        username VARCHAR(50) NOT NULL UNIQUE,
        hashed_password VARCHAR(255) NOT NULL
    )";
    $conn->exec($sql_users);
    echo "Table 'Users' created.<br>";

    // 2. Events Table 
    $sql_events = "CREATE TABLE IF NOT EXISTS Events (
        EventID INT(6) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        UserID INT(6) UNSIGNED,
        title VARCHAR(100) NOT NULL,
        start_time DATETIME,
        duration VARCHAR(50), 
        type VARCHAR(50),       -- Used for 'Filtering tasks'
        is_complete BOOLEAN DEFAULT 0, -- Used for 'Rescheduling missed tasks'
        FOREIGN KEY (UserID) REFERENCES Users(UserID) ON DELETE CASCADE
    )";
    $conn->exec($sql_events);
    echo "Table 'Events' created.<br>";

    // 3. ToDo Table

    $sql_todo = "CREATE TABLE IF NOT EXISTS ToDo (
        taskID INT(6) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        UserID INT(6) UNSIGNED,
        name VARCHAR(100) NOT NULL,
        description VARCHAR(255),
        deadline DATETIME,      -- Used for 'Shows deadlines'
        FOREIGN KEY (UserID) REFERENCES Users(UserID) ON DELETE CASCADE
    )";
    $conn->exec($sql_todo);
    echo "Table 'ToDo' created .<br>";

    echo "<hr><strong>Database Ready</strong>";

} catch(PDOException $e) {
    echo "Error: " . $e->getMessage();
}
?>