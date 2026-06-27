<?php
session_start();
session_destroy();
setcookie("fit_user", "", time() - 3600, "/");
header("Location: index.php");
exit;
?>