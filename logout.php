<?php
require_once 'includes/functions.php';

startSession();

// Destroy all session data
session_unset();
session_destroy();

// Redirect to login page
header("Location: login.php");
exit();
?>
