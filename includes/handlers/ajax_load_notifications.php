<?php
include("../../config/config.php");
include("../classes/User.php");
include("../classes/Notification.php");

$limit = 7; //Number of messages to load

$notification = new Notification($_REQUEST['userLoggedIn'],$con);
echo $notification->getNotifications($_REQUEST, $limit);

?>