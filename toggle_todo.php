<?php
session_start();
require 'db_connect.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$current_user_id = $_SESSION['user_id'];

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $task_id = $_POST['taskID'];

    try {
        $sql = "UPDATE ToDo 
                SET is_complete = 1, completed_at = NOW()
                WHERE taskID = :taskID AND UserID = :uid";

        $stmt = $conn->prepare($sql);
        $stmt->execute([
            ':taskID' => $task_id,
            ':uid' => $current_user_id
        ]);

        header("Location: view_todo.php");
        exit();

    } catch (PDOException $e) {
        echo "Error updating task: " . $e->getMessage();
    }
} else {
    header("Location: view_todo.php");
    exit();
}
?>
}
?>