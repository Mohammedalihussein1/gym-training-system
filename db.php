<?php
$conn = mysqli_connect("localhost", "root", "", "fittrack");
if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}
?>