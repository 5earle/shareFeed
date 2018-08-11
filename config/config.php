<?php
ob_start(); //Turns on output buffering
session_start();

$timezone = date_default_timezone_set("America/New_York");

$con = mysqli_connect("localhost", "root", "", "social") or die("Failed to connect"); //Connection variable

?>