<?php
session_start();

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

require 'db_connect.php';
$current_user_id = $_SESSION['user_id'];
$todos = [];
$error_message = "";

try {
    // Fetch all incomplete tasks for this user, sorted by deadline (closest first)
    $sql = "SELECT * FROM ToDo WHERE UserID = :uid AND is_complete = 0 ORDER BY deadline ASC";
    $stmt = $conn->prepare($sql);
    $stmt->execute([':uid' => $current_user_id]);
    
    $todos = $stmt->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    $error_message = "Database Error: " . $e->getMessage();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>My To-Do List</title>
    <style>
        body {
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Arial, sans-serif;
            background-color: #f4f7f6;
            color: #333;
            padding: 20px;
            max-width: 800px;
            margin: 0 auto;
        }
        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-bottom: 2px solid #ddd;
            padding-bottom: 10px;
            margin-bottom: 20px;
        }
        .btn-add {
            background: #28a745;
            color: white;
            padding: 10px 15px;
            text-decoration: none;
            border-radius: 5px;
            font-weight: bold;
        }
        .btn-add:hover { background: #218838; }
        
        .date-header {
            background: #343a40;
            color: white;
            padding: 8px 15px;
            border-radius: 5px;
            margin-top: 30px;
            margin-bottom: 15px;
            font-size: 1.1em;
            font-weight: bold;
        }
        
        .todo-card {
            background: white;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.05);
            padding: 15px 20px;
            margin-bottom: 15px;
            border-left: 5px solid #6c757d; /* Default Grey */
            display: flex;
            align-items: flex-start;
            gap: 15px;
        }
        .todo-card.task { border-left-color: #007bff; } /* Blue for Tasks */
        .todo-card.event { border-left-color: #9c27b0; } /* Purple for Events */
        
        /* Custom Checkbox */
        .checkbox-container input {
            width: 22px;
            height: 22px;
            cursor: pointer;
            accent-color: #28a745;
            margin-top: 5px;
        }

        .todo-content {
            flex-grow: 1;
        }

        .todo-title {
            margin: 0 0 5px 0;
            font-size: 1.25em;
            color: #111;
        }
        
        .badge {
            display: inline-block;
            padding: 3px 8px;
            border-radius: 12px;
            font-size: 0.8em;
            font-weight: bold;
            color: white;
            margin-right: 10px;
            margin-bottom: 8px;
        }
        .badge.task { background: #007bff; }
        .badge.event { background: #9c27b0; }
        .badge.tag { background: #e0e0e0; color: #333; border: 1px solid #ccc; }

        .todo-details {
            font-size: 0.9em;
            color: #555;
            margin-bottom: 5px;
        }
        
        .todo-desc {
            font-size: 0.9em;
            color: #666;
            background: #f8f9fa;
            padding: 10px;
            border-radius: 5px;
            margin-top: 8px;
            border: 1px solid #eee;
        }

        .empty-state {
            text-align: center;
            padding: 50px;
            background: white;
            border-radius: 8px;
            color: #666;
            border: 2px dashed #ccc;
        }
    </style>
</head>
<body>

    <div class="header">
        <h1>My To-Do List</h1>
        <a href="add_todo.php" class="btn-add">+ Add Task</a> 
    </div>

    <?php if ($error_message): ?>
        <p style="color: red;"><?php echo $error_message; ?></p>
    <?php endif; ?>

    <?php
    if (empty($todos)) {
        echo "<div class='empty-state'>
                <h2>All caught up! 🎉</h2>
                <p>You have no pending tasks or events.</p>
              </div>";
    } else {
        $current_date = "";

        foreach ($todos as $todo) {
            // Format the deadline Date and Time
            if (!empty($todo['deadline'])) {
                $timestamp = strtotime($todo['deadline']);
                $date_string = date('l, j F Y', $timestamp); // e.g., "Wednesday, 11 March 2026"
                $time_string = date('H:i', $timestamp);      // e.g., "14:30"
            } else {
                $date_string = "No Deadline";
                $time_string = "Anytime";
            }

            // Print a Date Header if we are on a new day
            if ($date_string !== $current_date) {
                echo "<div class='date-header'>" . $date_string . "</div>";
                $current_date = $date_string;
            }

            // Figure out styling based on the type (Task vs Event)
            $type_lower = strtolower($todo['type']);
            $card_class = ($type_lower == 'event') ? 'event' : 'task';
            
            // Format tags (splits comma-separated tags into an array)
            $tags_html = "";
            if (!empty($todo['tags'])) {
                $tag_array = explode(',', $todo['tags']);
                foreach ($tag_array as $tag) {
                    $tags_html .= "<span class='badge tag'>🏷️ " . htmlspecialchars(trim($tag)) . "</span>";
                }
            }
            ?>
            
            <div class="event-card todo-card <?php echo $card_class; ?>">
                
                <div class="checkbox-container">
                    <input type="checkbox" title="Mark as complete">
                </div>
                
                <div class="todo-content">
                    <h3 class="todo-title"><?php echo htmlspecialchars($todo['title']); ?></h3>
                    
                    <span class="badge <?php echo $card_class; ?>"><?php echo htmlspecialchars($todo['type']); ?></span>
                    <?php echo $tags_html; ?>
                    
                    <div class="todo-details">
                        <strong>⏰ Due:</strong> <?php echo $time_string; ?> 
                        <?php if ($todo['duration'] > 0): ?>
                            | <strong>⏳ Takes:</strong> <?php echo htmlspecialchars($todo['duration']); ?> mins
                        <?php endif; ?>
                    </div>
                    
                    <?php if (!empty($todo['description'])): ?>
                        <div class="todo-desc">
                            <?php echo nl2br(htmlspecialchars($todo['description'])); ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
            <?php
        }
    }
    ?>

</body>
</html>