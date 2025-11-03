<?php
include_once('settings.php');
include_once('lib.php');
include('header.php');

// Get userID if passed via URL
$userID = isset($_GET['userID']) ? preg_replace("/[^a-zA-Z0-9_\-]/", "", $_GET['userID']) : null;

if ($userID) {
    // Define the directory path
    $dirPath = "/var/www/html/v1/subjects/$userID/";

    // Check if the directory exists, if not, create it
    if (!is_dir($dirPath)) {
        // Attempt to create the directory
        if (mkdir($dirPath, 0777, true)) {
            echo "<p>A new folder has been created for your session.</p>";
            $w = 1;
        } else {
            echo "<p>Error: Failed to create the folder for your session. Please check server permissions.</p>";
            error_log("Error creating directory for $userID at " . $dirPath); // Log to PHP error log
            $w = 2;
        }
    } else {
        echo "<p>Welcome back! Your session folder already exists.</p>";
        $w = 3;
    }

    // Log visit (record the userID and timestamp)
    $logfile = "/var/www/html/v1/subjects/log.csv";
    $timestamp = date("Y-m-d H:i:s");
    $log_entry = "$timestamp,$userID" . (is_dir($dirPath) ? ",revisit\n" : ",new\n");
    file_put_contents($logfile, $log_entry, FILE_APPEND);
}
?>

<div id="header">
    <div style="top:0px;float:right;margin-right:10px;color:#ccc">
        <a href="admin/index.php">Admin</a>
    </div>
    <h1><?php echo $title; ?></h1>
</div>

<div id="container">
<?php if ($userID): ?>
    <!-- Check if directory exists and display appropriate welcome message -->
    <?php if ($w == 1): ?>
        <h2>Welcome!</h2>
    <?php elseif ($w == 3): ?>
        <h2>Welcome back!</h2>
    <?php endif; ?>
    <p>Your Participant ID is: <strong><?php echo htmlspecialchars($userID); ?></strong></p>
    <form method="GET" action="session.php">
        <input type="hidden" name="userID" value="<?php echo htmlspecialchars($userID); ?>">
        <input type="submit" value="Continue to Session">
    </form>

<?php else: ?>
    <form id="intro" method="GET" action="session.php">
        <br><br>
        <input style="font-size:12px;" name="userID" type="text" onclick="this.value=''" value="<?php echo $pagetexts['login-dialog']; ?>">
        <br><br><?php echo $pagetexts['register']; ?>
    </form>
<?php endif; ?>
</div>

<?php include('footer.php'); ?>