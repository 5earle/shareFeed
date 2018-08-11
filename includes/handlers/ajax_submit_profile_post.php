<?php
include("../../config/config.php");
include("../classes/User.php");
include("../classes/Post.php");
include "../classes/Notification.php";

if(isset($_POST['post_body'])){ // the textarea
    $post = new Post($_POST['user_from'], $con); // value of logged in user
    $post->submitPost($_POST['post_body'], $_POST['user_to']);  // profile to send post
}
?>