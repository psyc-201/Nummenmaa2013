<?php
include_once('settings.php');
include_once('lib.php');
include('header.php');

// Get userID if passed via URL
$userID = isset($_GET['userID']) ? preg_replace("/[^a-zA-Z0-9_\-]/", "", $_GET['userID']) : null;
?>

<div id="header">
    <div style="top:0px;float:right;margin-right:10px;color:#ccc">
        <a href="admin/index.php">Admin</a>
    </div>
    <h1><?php echo $title; ?></h1>
</div>

<div id="container">
<?php if ($userID): ?>
    <h2>Welcome!</h2>
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
