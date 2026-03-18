<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();
require 'db_connect.php';

$conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$current_user_id = (string) $_SESSION['user_id'];
$message = "";
$message_type = "";

$defaults = [
    'full_name' => '',
    'display_name' => '',
    'email' => '',
    'bio' => '',
    'timetable_url' => '',
    'default_view' => 'week',
    'first_day_of_week' => 'monday',
    'start_hour' => 8,
    'end_hour' => 18,
    'show_weekends' => 1,
    'auto_refresh_timetable' => 0,
    'default_event_duration' => 60,
    'default_event_colour' => '#007bff',
    'show_completed_todos' => 1,
    'default_reminder_minutes' => 15,
    'confirm_before_delete' => 1,
    'compact_view' => 0,
    'show_dashboard_tips' => 1,
    'time_format' => '24h',
    'date_format' => 'DD/MM/YYYY'
];

try {
    if ($_SERVER["REQUEST_METHOD"] === "POST") {
        $settings = [
            'full_name' => trim($_POST['full_name'] ?? ''),
            'display_name' => trim($_POST['display_name'] ?? ''),
            'email' => trim($_POST['email'] ?? ''),
            'bio' => trim($_POST['bio'] ?? ''),
            'timetable_url' => trim($_POST['timetable_url'] ?? ''),
            'default_view' => $_POST['default_view'] ?? 'week',
            'first_day_of_week' => $_POST['first_day_of_week'] ?? 'monday',
            'start_hour' => (int) ($_POST['start_hour'] ?? 8),
            'end_hour' => (int) ($_POST['end_hour'] ?? 18),
            'show_weekends' => isset($_POST['show_weekends']) ? 1 : 0,
            'auto_refresh_timetable' => isset($_POST['auto_refresh_timetable']) ? 1 : 0,
            'default_event_duration' => (int) ($_POST['default_event_duration'] ?? 60),
            'default_event_colour' => trim($_POST['default_event_colour'] ?? '#007bff'),
            'show_completed_todos' => isset($_POST['show_completed_todos']) ? 1 : 0,
            'default_reminder_minutes' => (int) ($_POST['default_reminder_minutes'] ?? 15),
            'confirm_before_delete' => isset($_POST['confirm_before_delete']) ? 1 : 0,
            'compact_view' => isset($_POST['compact_view']) ? 1 : 0,
            'show_dashboard_tips' => isset($_POST['show_dashboard_tips']) ? 1 : 0,
            'time_format' => $_POST['time_format'] ?? '24h',
            'date_format' => $_POST['date_format'] ?? 'DD/MM/YYYY'
        ];

        if (!empty($settings['email']) && !filter_var($settings['email'], FILTER_VALIDATE_EMAIL)) {
            throw new Exception("Please enter a valid email address.");
        }

        if ($settings['start_hour'] < 0 || $settings['start_hour'] > 23) {
            throw new Exception("Start hour must be between 0 and 23.");
        }

        if ($settings['end_hour'] < 0 || $settings['end_hour'] > 23) {
            throw new Exception("End hour must be between 0 and 23.");
        }

        if ($settings['default_event_duration'] < 0) {
            throw new Exception("Default event duration cannot be negative.");
        }

        if ($settings['default_reminder_minutes'] < 0) {
            throw new Exception("Reminder minutes cannot be negative.");
        }

        $sql = "INSERT INTO UserSettings (
                    UserID, full_name, display_name, email, bio,
                    timetable_url, default_view, first_day_of_week,
                    start_hour, end_hour, show_weekends, auto_refresh_timetable,
                    default_event_duration, default_event_colour,
                    show_completed_todos, default_reminder_minutes,
                    confirm_before_delete, compact_view, show_dashboard_tips,
                    time_format, date_format
                ) VALUES (
                    :UserID, :full_name, :display_name, :email, :bio,
                    :timetable_url, :default_view, :first_day_of_week,
                    :start_hour, :end_hour, :show_weekends, :auto_refresh_timetable,
                    :default_event_duration, :default_event_colour,
                    :show_completed_todos, :default_reminder_minutes,
                    :confirm_before_delete, :compact_view, :show_dashboard_tips,
                    :time_format, :date_format
                )
                ON DUPLICATE KEY UPDATE
                    full_name = VALUES(full_name),
                    display_name = VALUES(display_name),
                    email = VALUES(email),
                    bio = VALUES(bio),
                    timetable_url = VALUES(timetable_url),
                    default_view = VALUES(default_view),
                    first_day_of_week = VALUES(first_day_of_week),
                    start_hour = VALUES(start_hour),
                    end_hour = VALUES(end_hour),
                    show_weekends = VALUES(show_weekends),
                    auto_refresh_timetable = VALUES(auto_refresh_timetable),
                    default_event_duration = VALUES(default_event_duration),
                    default_event_colour = VALUES(default_event_colour),
                    show_completed_todos = VALUES(show_completed_todos),
                    default_reminder_minutes = VALUES(default_reminder_minutes),
                    confirm_before_delete = VALUES(confirm_before_delete),
                    compact_view = VALUES(compact_view),
                    show_dashboard_tips = VALUES(show_dashboard_tips),
                    time_format = VALUES(time_format),
                    date_format = VALUES(date_format)";

        $stmt = $conn->prepare($sql);
        $stmt->execute([
            ':UserID' => $current_user_id,
            ':full_name' => $settings['full_name'],
            ':display_name' => $settings['display_name'],
            ':email' => $settings['email'],
            ':bio' => $settings['bio'],
            ':timetable_url' => $settings['timetable_url'],
            ':default_view' => $settings['default_view'],
            ':first_day_of_week' => $settings['first_day_of_week'],
            ':start_hour' => $settings['start_hour'],
            ':end_hour' => $settings['end_hour'],
            ':show_weekends' => $settings['show_weekends'],
            ':auto_refresh_timetable' => $settings['auto_refresh_timetable'],
            ':default_event_duration' => $settings['default_event_duration'],
            ':default_event_colour' => $settings['default_event_colour'],
            ':show_completed_todos' => $settings['show_completed_todos'],
            ':default_reminder_minutes' => $settings['default_reminder_minutes'],
            ':confirm_before_delete' => $settings['confirm_before_delete'],
            ':compact_view' => $settings['compact_view'],
            ':show_dashboard_tips' => $settings['show_dashboard_tips'],
            ':time_format' => $settings['time_format'],
            ':date_format' => $settings['date_format']
        ]);

        $message = "Settings saved successfully.";
        $message_type = "success";
    }

    $stmt = $conn->prepare("SELECT * FROM UserSettings WHERE UserID = :uid LIMIT 1");
    $stmt->execute([':uid' => $current_user_id]);
    $saved_settings = $stmt->fetch(PDO::FETCH_ASSOC);

    $settings = $saved_settings ? array_merge($defaults, $saved_settings) : $defaults;

} catch (Exception $e) {
    $settings = $defaults;
    $message = $e->getMessage();
    $message_type = "error";
} catch (PDOException $e) {
    $settings = $defaults;
    $message = "Database error: " . $e->getMessage();
    $message_type = "error";
}
?>

<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Settings - Smart Timetabler</title>
    <link rel="stylesheet" type="text/css" href="home.css" />
</head>
<body>
    <h2 class="title">User Settings</h2>

    <main id="AddPage">
        <?php if (!empty($message)): ?>
            <div class="<?php echo $message_type === 'success' ? 'settings-message success-message' : 'settings-message error-message'; ?>">
                <?php echo htmlspecialchars($message); ?>
            </div>
        <?php endif; ?>

        <div class="background settings-card">
            <form method="POST" action="">
                <section class="settings-section">
                    <h3>Profile</h3>

                    <div class="input-group">
                        <label for="full_name">Full Name</label>
                        <input type="text" id="full_name" name="full_name" value="<?php echo htmlspecialchars($settings['full_name']); ?>" />
                    </div>

                    <div class="input-group">
                        <label for="display_name">Display Name</label>
                        <input type="text" id="display_name" name="display_name" value="<?php echo htmlspecialchars($settings['display_name']); ?>" />
                    </div>

                    <div class="input-group">
                        <label for="email">Email Address</label>
                        <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($settings['email']); ?>" />
                    </div>

                    <div class="input-group">
                        <label for="bio">Bio</label>
                        <textarea id="bio" name="bio" rows="4" placeholder="Tell us a little about yourself"><?php echo htmlspecialchars($settings['bio']); ?></textarea>
                    </div>
                </section>

                <section class="settings-section">
                    <h3>Timetable Preferences</h3>

                    <div class="input-group">
                        <label for="timetable_url">Timetable iCal URL</label>
                        <input type="text" id="timetable_url" name="timetable_url" value="<?php echo htmlspecialchars($settings['timetable_url']); ?>" placeholder="Paste your university iCal link" />
                    </div>

                    <div class="row">
                        <div class="input-group wide">
                            <label for="default_view">Default Calendar View</label>
                            <select id="default_view" name="default_view">
                                <option value="day" <?php echo $settings['default_view'] === 'day' ? 'selected' : ''; ?>>Day</option>
                                <option value="week" <?php echo $settings['default_view'] === 'week' ? 'selected' : ''; ?>>Week</option>
                                <option value="month" <?php echo $settings['default_view'] === 'month' ? 'selected' : ''; ?>>Month</option>
                            </select>
                        </div>

                        <div class="input-group wide">
                            <label for="first_day_of_week">First Day of Week</label>
                            <select id="first_day_of_week" name="first_day_of_week">
                                <option value="monday" <?php echo $settings['first_day_of_week'] === 'monday' ? 'selected' : ''; ?>>Monday</option>
                                <option value="sunday" <?php echo $settings['first_day_of_week'] === 'sunday' ? 'selected' : ''; ?>>Sunday</option>
                            </select>
                        </div>
                    </div>

                    <div class="row">
                        <div class="input-group narrow">
                            <label for="start_hour">Start Hour</label>
                            <input type="number" id="start_hour" name="start_hour" min="0" max="23" value="<?php echo htmlspecialchars($settings['start_hour']); ?>" />
                        </div>

                        <div class="input-group narrow">
                            <label for="end_hour">End Hour</label>
                            <input type="number" id="end_hour" name="end_hour" min="0" max="23" value="<?php echo htmlspecialchars($settings['end_hour']); ?>" />
                        </div>
                    </div>

                    <div class="checkbox-group">
                        <label><input type="checkbox" name="show_weekends" <?php echo (int)$settings['show_weekends'] === 1 ? 'checked' : ''; ?> /> Show weekends</label>
                        <label><input type="checkbox" name="auto_refresh_timetable" <?php echo (int)$settings['auto_refresh_timetable'] === 1 ? 'checked' : ''; ?> /> Auto refresh timetable</label>
                    </div>
                </section>

                <section class="settings-section">
                    <h3>Task and Event Preferences</h3>

                    <div class="row">
                        <div class="input-group narrow">
                            <label for="default_event_duration">Default Event Duration (mins)</label>
                            <input type="number" id="default_event_duration" name="default_event_duration" min="0" value="<?php echo htmlspecialchars($settings['default_event_duration']); ?>" />
                        </div>

                        <div class="input-group narrow">
                            <label for="default_reminder_minutes">Reminder Minutes</label>
                            <input type="number" id="default_reminder_minutes" name="default_reminder_minutes" min="0" value="<?php echo htmlspecialchars($settings['default_reminder_minutes']); ?>" />
                        </div>
                    </div>

                    <div class="input-group">
                        <label for="default_event_colour">Default Event Colour</label>
                        <input type="text" id="default_event_colour" name="default_event_colour" value="<?php echo htmlspecialchars($settings['default_event_colour']); ?>" placeholder="#007bff" />
                    </div>

                    <div class="checkbox-group">
                        <label><input type="checkbox" name="show_completed_todos" <?php echo (int)$settings['show_completed_todos'] === 1 ? 'checked' : ''; ?> /> Show completed to-dos</label>
                        <label><input type="checkbox" name="confirm_before_delete" <?php echo (int)$settings['confirm_before_delete'] === 1 ? 'checked' : ''; ?> /> Confirm before deleting</label>
                    </div>
                </section>

                <section class="settings-section">
                    <h3>Interface Preferences</h3>

                    <div class="row">
                        <div class="input-group wide">
                            <label for="time_format">Time Format</label>
                            <select id="time_format" name="time_format">
                                <option value="24h" <?php echo $settings['time_format'] === '24h' ? 'selected' : ''; ?>>24-hour</option>
                                <option value="12h" <?php echo $settings['time_format'] === '12h' ? 'selected' : ''; ?>>12-hour</option>
                            </select>
                        </div>

                        <div class="input-group wide">
                            <label for="date_format">Date Format</label>
                            <select id="date_format" name="date_format">
                                <option value="DD/MM/YYYY" <?php echo $settings['date_format'] === 'DD/MM/YYYY' ? 'selected' : ''; ?>>DD/MM/YYYY</option>
                                <option value="MM/DD/YYYY" <?php echo $settings['date_format'] === 'MM/DD/YYYY' ? 'selected' : ''; ?>>MM/DD/YYYY</option>
                                <option value="YYYY-MM-DD" <?php echo $settings['date_format'] === 'YYYY-MM-DD' ? 'selected' : ''; ?>>YYYY-MM-DD</option>
                            </select>
                        </div>
                    </div>

                    <div class="checkbox-group">
                        <label><input type="checkbox" name="compact_view" <?php echo (int)$settings['compact_view'] === 1 ? 'checked' : ''; ?> /> Compact view</label>
                        <label><input type="checkbox" name="show_dashboard_tips" <?php echo (int)$settings['show_dashboard_tips'] === 1 ? 'checked' : ''; ?> /> Show dashboard tips</label>
                    </div>
                </section>

                <button type="submit">Save Settings</button>
            </form>
        </div>
    </main>

    <footer class="toolbar">
        <button onclick="window.location.href='view_todo.php'">To-Do</button>
        <button class="add-btn" onclick="window.location.href='home.php'">+</button>
        <button onclick="window.location.href='view_timetable.php'">Calendar</button>
        <button onclick="window.location.href='settings.php'">Settings</button>
    </footer>
</body>
</html>