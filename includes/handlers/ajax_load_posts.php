<?php
include("../../config/config.php");
include("../classes/User.php");
include("../classes/Post.php");

$limit = 10; //Number of posts to be loaded per call

/*$_REQUEST is a merging of $_GET and $_POST where $_POST overrides $_GET.
Good to use $_REQUEST on self refrential forms for validation*/
$posts = new Post($_REQUEST['userLoggedIn'],$con);
$posts->loadPost();
?>

<!--ajax makes data load without reloading the page-->
