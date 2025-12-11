<?php
// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Sanitize user input
$userID = isset($_GET["userID"]) ? preg_replace("/[^a-zA-Z0-9_\-]/", "", $_GET["userID"]) : null;
?>

<html>
<head>
<title>Index Page</title>
<style>
* { margin: 0px; padding: 0px; }
body {
    padding: 10px;
    font-family: Helvetica, Arial, sans-serif;
    background: #dfd;
    border: 1px solid #390;
}
.container {
    padding: 20px;
}
</style>
</head>
<body>
<div class="container">

<?php if ($userID): ?>
    <?php
        // Define path for user's folder
        $dirPath = "../subjects/$userID/";

        echo "<h2>Welcome, Participant</h2>";
        echo "<p>Your user ID is: <strong style='font-size:20px;'>$userID</strong></p>";

        // Check if the directory exists, if not, create it
        if (!is_dir($dirPath)) {
            // Attempt to create the directory
            if (mkdir($dirPath, 0777, true)) {
                echo "<p>A new folder has been created for your session.</p>";
            } else {
                echo "<p>Error: Failed to create the folder for your session. Please check server permissions.</p>";
                error_log("Error creating directory for $userID at " . $dirPath); // Log to PHP error log
            }
        } else {
            echo "<p>Welcome back! Your session folder already exists.</p>";
        }

        // Log visit (record the userID and timestamp)
        $logfile = "../subjects/log.csv";
        $timestamp = date("Y-m-d H:i:s");
        $log_entry = "$timestamp,$userID" . (is_dir($dirPath) ? ",revisit\n" : ",new\n");
        file_put_contents($logfile, $log_entry, FILE_APPEND);
    ?>

    <form action="session.php" method="GET">
        <input type="hidden" name="userID" value="<?= htmlspecialchars($userID) ?>">
        <input type="submit" value="Continue to Session">
    </form>

<?php else: ?>
    <h2>Admin Page</h2>
    <p>No user ID detected. You can manually enter one below.</p>
    <form action="index.php" method="GET">
        <label for="userID">Enter User ID:</label>
        <input type="text" id="userID" name="userID" required>
        <input type="submit" value="Submit">
    </form>
<?php endif; ?>

</div>
</body>
</html>
