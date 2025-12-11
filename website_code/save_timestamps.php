<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

$userID = isset($_POST['userID']) ? preg_replace("/[^a-zA-Z0-9_\-]/", "", $_POST['userID']) : null;
$presentation = isset($_POST['presentation']) ? basename($_POST['presentation']) : null;
$start_time = isset($_POST['start_time']) ? $_POST['start_time'] : null;
$submit_time = isset($_POST['submit_time']) ? $_POST['submit_time'] : null;

if (!$userID || !$presentation || !$start_time || !$submit_time) {
    http_response_code(400);
    echo "Missing parameters";
    exit();
}

$logfile = "./subjects/$userID/timestamps.csv";
$header = "trial_id,start_timestamp_ms,submit_timestamp_ms\n";  // start and submit in ms (simply subtract one from the other to get the trial duration)

// If file doesn't exist, create and write header
if (!file_exists($logfile)) {
    file_put_contents($logfile, $header);
}

$row = "$presentation,$start_time,$submit_time\n";
file_put_contents($logfile, $row, FILE_APPEND | LOCK_EX);

echo "Timestamp recorded";
?>
