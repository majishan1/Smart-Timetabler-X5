<?php
// reschedule_tasks.php
// Runs automatic rescheduling for overdue incomplete tasks for the current user.
// Include this file AFTER session, db connection, and $current_user_id are set.

if (!isset($conn) || !isset($current_user_id)) {
    die("reschedule_tasks.php requires \$conn and \$current_user_id to be set first.");
}

$rescheduled_count = 0;
$reschedule_error = "";

try {
    $sql = "SELECT taskID, deadline
            FROM ToDo
            WHERE UserID = :uid
              AND is_complete = 0
              AND deadline IS NOT NULL
              AND deadline < NOW()";

    $stmt = $conn->prepare($sql);
    $stmt->execute([':uid' => $current_user_id]);
    $overdue_tasks = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if (!empty($overdue_tasks)) {
        $update_sql = "UPDATE ToDo SET deadline = :new_deadline WHERE taskID = :taskID";
        $update_stmt = $conn->prepare($update_sql);

        $now = new DateTime();

        foreach ($overdue_tasks as $task) {
            $old_deadline = new DateTime($task['deadline']);
            $new_deadline = clone $old_deadline;

            // Keep adding 1 day until the deadline is in the future
            while ($new_deadline <= $now) {
                $new_deadline->modify('+1 day');
            }

            $update_stmt->execute([
                ':new_deadline' => $new_deadline->format('Y-m-d H:i:s'),
                ':taskID' => $task['taskID']
            ]);

            $rescheduled_count++;
        }
    }
} catch (PDOException $e) {
    $reschedule_error = $e->getMessage();
}
?>