<?php
// Receive variables safely
$xt = isset($_POST['arrX']) ? $_POST['arrX'] : [];
$yt = isset($_POST['arrY']) ? $_POST['arrY'] : [];
$t = isset($_POST['arrTime']) ? $_POST['arrTime'] : [];

$xtd = isset($_POST['arrXD']) ? $_POST['arrXD'] : [];
$ytd = isset($_POST['arrYD']) ? $_POST['arrYD'] : [];
$td = isset($_POST['arrTimeD']) ? $_POST['arrTimeD'] : [];

$mdt = isset($_POST['arrMD']) ? $_POST['arrMD'] : [];
$mut = isset($_POST['arrMU']) ? $_POST['arrMU'] : [];

$file = isset($_POST['file']) ? $_POST['file'] : null;

// Check if basic required fields exist
if (!$file || empty($xt) || empty($yt) || empty($t)) {
    echo "ERROR: Missing essential data.\n";
    exit();
}

// Build the text
$mytext = "";

// Add main drawing points
for ($c = 0; $c < count($xt); $c++) {
    $mytext .= $t[$c] . "," . $xt[$c] . "," . $yt[$c] . "\n";
}

// Add separator
$mytext .= "-1,-1,-1\n";

// Add drawing dynamics if available
if (!empty($xtd) && !empty($ytd) && !empty($td)) {
    for ($c = 0; $c < count($xtd); $c++) {
        $mytext .= $td[$c] . "," . $xtd[$c] . "," . $ytd[$c] . "\n";
    }
}

// Add separator
$mytext .= "-1,-1,-1\n";

// Add mouse down times if available
if (!empty($mdt)) {
    for ($c = 0; $c < count($mdt); $c++) {
        $mytext .= $mdt[$c] . ",,\n";
    }
}

// Add separator
$mytext .= "-1,-1,-1\n";

// Add mouse up times if available
if (!empty($mut)) {
    for ($c = 0; $c < count($mut); $c++) {
        $mytext .= $mut[$c] . ",,\n";
    }
}

// Make sure the directory exists
$arr = explode('/', $file); // "./subjects/subjectid/presentation.csv"
$subjectDir = "./subjects/" . $arr[2];
if (!is_dir($subjectDir)) {
    mkdir($subjectDir, 0777, true);
}

// Write the file
$fh = fopen($file, 'w') or die("ERROR: Unable to open file.");
fwrite($fh, $mytext);
fclose($fh);

// Success
echo "1";
?>

