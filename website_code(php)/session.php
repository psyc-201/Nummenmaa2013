<?php
// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

include_once('settings.php');
include_once('lib.php');

// Initialize user array
$users = array();
$subjDir = opendir('./subjects/');

// Get all user directories
while (false !== ($f = readdir($subjDir))) {
    if ($f == "." || $f == "..")
        continue;
    array_push($users, $f);
}
closedir($subjDir);

// Get user ID from URL
$userID = isset($_GET['userID']) ? preg_replace("/[^a-zA-Z0-9_\-]/", "", $_GET['userID']) : null;

// Check if user ID is valid
if (empty($userID)) {
    die("Error: Missing or invalid userID.");
}

// Prepare for error messages
$helpful = array('user' => "$userID");

// Check if the user exists in the directory
$found = 0;
foreach ($users as $user) {
    if ($userID == $user) {
        $found = 1;
        break;
    }
}

if ($found == 0) {
    include('header.php');
    die(insertVarValues($pagetexts['uid_error'], $helpful));
}

// User found, let's start the session

// Path to the user's presentation file
$pfpath = './subjects/' . $userID . '/presentation.txt';

// If the presentation file is missing, generate it
if (!is_file($pfpath)) {
    makePresentation($pfpath);
}

// Load the presentation
$presentation = loadTxt($pfpath, 0);

// Count how many tasks have been done
$done = 0;
$annpath = './subjects/' . $userID . '/';
foreach ($presentation as $p) {
    if (is_file($annpath . trim($p) . ".csv")) {
        $done++;
    }
}

// Calculate the progress percentage
$amount = $done / count($presentation);
$amount = floor($amount * 10000) / 10000; // Round to four decimal points
$perc = 100 * $amount;

// Start generating the output
$outtext = '';
$outtext .= "<div id='header'>";
$outtext .= "<span style='font-size:14px;font-weight:bold;margin-left:10px;position:absolute;'>id: " . $userID . "</span>";
$outtext .= "<div id='progress-bar' class='all-rounded'> <div id='progress-bar-percentage' class='all-rounded' style='width:" . $perc . "%'><span>" . $perc . "%</span>";
$outtext .= "</div></div></div><div id='container'>";

// Get the welcome text from pagetexts
// $outtext .= "<h1>" . insertVarValues($pagetexts['welcome'], $helpful) . "</h1>";

// If the user has completed the presentation, show a thank-you message
// Initialize $auto with a default value
$auto = isset($_GET['auto']) ? $_GET['auto'] : null;

// If the user has completed the presentation, show a thank-you message
if ($amount == 1) {
    // Automatic redirection to the Qualtrics survey after completion
    if ($auto == 1) {
        // Append userID to the Qualtrics URL as a query parameter
        $qualtricsURL = "https://ucsd.co1.qualtrics.com/jfe/form/SV_b1NNeCgCe0ToG6G?userID=" . urlencode($userID);
        header("Location: " . $qualtricsURL);
        exit(); // Stop further script execution to avoid output
    }

    $outtext .= insertVarValues($pagetexts['thank-you'], $helpful);
} else {
    $helpful['percentage'] = $perc;

    // If the user has not completed the presentation, show instructions
    if ($amount < 1) {
        // Check if there is a specified welcome file
        if (isset($welcome)) {
            $welcome = loadTxt($welcomefile, 0);
        }
        $outtext .= "<h2 style='margin-left: 20px; margin-right: 20px;'>" . insertVarValues($pagetexts['instructions'], $helpful) . "</h2>";

        // Add instructions from the welcome file, if it exists
        if (isset($instructions)) {
            // Add left and right margins to the instructions
            $outtext .= "<div style='margin-left: 20px; margin-right: 20px;'>" . $instructions . "</div>";
        }

        // Get the next presentation task
        $presentation = $presentation[$done];

        // Determine the appropriate link for the annotation page
        if (in_array($type, $allowedTypes)) {
            $link = $type;
        } else {
            die("Error: variable $type missing from settings.php.");
        }

        // Determine where to send the user next
        if ($type == "paintwords" || $type == "paintimages") {
            $goto = "paintannotate.php?perc=$perc&userID=$userID&presentation=$presentation";
        } else {
            $goto = $link . "annotate.php?perc=$perc&userID=$userID&presentation=$presentation";
        }
        $helpful['goto'] = $goto;
        $outtext .= insertVarValues($pagetexts['start'], $helpful);
    }
}

// Handle automatic redirection (if applicable)
$auto = "";
if (array_key_exists('auto', $_GET)) {
    $auto = $_GET['auto'];
}

if ($auto == 1 && $amount != 1) {
    header("Location: $goto");
    exit();
} else {
    // Output the page
    include('header.php');
    echo $outtext;
}
?>
