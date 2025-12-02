<?php
session_start();
session_destroy(); // Hancurkan sesi login
header("Location: login.php"); // Tendang balik ke login
exit;
?>