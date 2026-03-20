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
    'display_name' => '',
    'email' => '',
    'postcode' => 'M14 6YY',
    'preferred_transport' => 'public_transport',
    'start_hour' => 8,
    'end_hour' => 18,
];

try {
    if ($_SERVER["REQUEST_METHOD"] === "POST") {
        $settings = [
            'display_name' => trim($_POST['display_name'] ?? ''),
            'email' => trim($_POST['email'] ?? ''),
            'start_hour' => (int) ($_POST['start_hour'] ?? 8),
            'end_hour' => (int) ($_POST['end_hour'] ?? 18),
            'postcode' => trim($_POST['postcode'] ?? ''),
            'preferred_transport' => trim($_POST['preferred_transport'] ?? 'public_transport')
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


        $sql = "INSERT INTO User_Settings (
                    UserID, display_name, email,
                    start_hour, end_hour, postcode, preferred_transport
                ) VALUES (
                    :UserID, :display_name, :email,
                    :start_hour, :end_hour, :postcode, :preferred_transport
                )
                ON DUPLICATE KEY UPDATE
                    display_name = VALUES(display_name),
                    email = VALUES(email),
                    start_hour = VALUES(start_hour),
                    end_hour = VALUES(end_hour),
                    postcode = VALUES(postcode),
                    preferred_transport = VALUES(preferred_transport)";

        $stmt = $conn->prepare($sql);
        $stmt->execute([
            ':UserID' => $current_user_id,
            ':display_name' => $settings['display_name'],
            ':email' => $settings['email'],
            ':start_hour' => $settings['start_hour'],
            ':end_hour' => $settings['end_hour'],
            ':postcode' => $settings['postcode'],
            ':preferred_transport' => $settings['preferred_transport']
        ]);

        $message = "Settings saved successfully.";
        $message_type = "success";
    }

    // Fixed typo here: UserSettings -> User_Settings
    $stmt = $conn->prepare("SELECT * FROM User_Settings WHERE UserID = :uid LIMIT 1");
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
                        <label for="display_name">Display Name</label>
                        <input type="text" id="display_name" name="display_name" value="<?php echo htmlspecialchars($settings['display_name']); ?>" placeholder="How we should call you" />
                    </div>

                    <div class="input-group">
                        <label for="email">Email Address</label>
                        <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($settings['email']); ?>" placeholder="your.name@student.manchester.ac.uk" />
                    </div>
                </section>

                <section class="settings-section">
                    <h3>Timetable Preferences</h3>

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
                </section>

                <section class="settings-section">
                    <h3>Commute & Location</h3>

                    <div class="input-group">
                        <label for="postcode">Home Postcode</label>
                        <input type="text" id="postcode" name="postcode" value="<?php echo htmlspecialchars($settings['postcode']); ?>" placeholder="e.g. M13 9PL" />
                    </div>

                    <div class="input-group wide">
                        <label for="preferred_transport">Preferred Transport</label>
                        <select id="preferred_transport" name="preferred_transport">
                            <option value="public_transport" <?php echo $settings['preferred_transport'] === 'public_transport' ? 'selected' : ''; ?>>🚌 Bus / Tram (Public Transport)</option>
                            <option value="foot-walking" <?php echo $settings['preferred_transport'] === 'foot-walking' ? 'selected' : ''; ?>>🚶‍♂️ Walking</option>
                            <option value="cycling-regular" <?php echo $settings['preferred_transport'] === 'cycling-regular' ? 'selected' : ''; ?>>🚲 Cycling</option>
                            <option value="driving-car" <?php echo $settings['preferred_transport'] === 'driving-car' ? 'selected' : ''; ?>>🚗 Driving</option>
                        </select>
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