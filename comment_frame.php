<html>
<head>
    <title></title>
    <link rel="stylesheet" type="text/css" href="assets/css/style.css">
    <script src="assets/js/socialmedia.js"></script>
</head>
<body>

<style>
    * {
        font-size: 12px;
        font-family: Arial, SansSerif;
    }
</style>
<!--probbaly should put this in the top of html tag-->
<?php
require 'config/config.php';
include "includes/classes/User.php";
include "includes/classes/Post.php";
include "includes/classes/Notification.php";

if (isset($_SESSION['username'])) {
    $userLoggedInName = $_SESSION['username'];
    $user_details_query = mysqli_query($con, "SELECT * FROM users WHERE username='$userLoggedInName'");
    $userObj = mysqli_fetch_array($user_details_query);
} else {
    header('Location: register.php');
}
?>

<!--javascript-->
<script>
    function toggle() {
        let element = document.getElementById('comment_section');
        if (element.style.display == 'block') { /*block is like p tag*/
            element.style.display = 'none';
        } else {
            element.style.display = 'block';
        }
    }
</script>
<!--javascript-->

<?php
//Get id of post that's comming  from Post.php
if (isset($_GET['post_id'])) {
    $post_id = $_GET['post_id'];
}

$user_query = mysqli_query($con, "SELECT added_by, user_to FROM posts WHERE id='$post_id'");
$row = mysqli_fetch_array($user_query);

$posted_to = $row['added_by'];
$user_to = $row['user_to'];

if (isset($_POST['postComment' . $post_id])) {
    $post_body = $_POST['post_body'];
    $post_body = mysqli_real_escape_string($con, $post_body);
    $date_time_now = date('Y-m-d H:i:s');
    $insert_post = mysqli_query($con, "INSERT INTO comments VALUES ('','$post_body','$userLoggedInName','$posted_to','$date_time_now','no','$post_id')");

    // insert notification
    if($posted_to != $userLoggedInName){
        $notification = new Notification($userLoggedInName,$con);
        $notification->insertNotification($post_id,$posted_to,'comment');
    }
    if ($user_to != 'none' && $user_to != $userLoggedInName){
        $notification = new Notification($userLoggedInName,$con);
        $notification->insertNotification($post_id,$user_to,'profile_comment');
    }

    // get notification when there is a comment on a post that u commented on
    $get_commenters = mysqli_query($con, "SELECT * FROM comments WHERE post_id='$post_id'") or die(mysqli_error($con));
    $notified_users = array();

    while($row = mysqli_fetch_array($get_commenters)){
        // no notification if  i commentd on my own post
        // block them for being in the array multiple times
        if($row['posted_by'] != $posted_to && $row['posted_by'] != $user_to
            && $row['posted_by'] != $userLoggedInName && !in_array($row['posted_by'], $notified_users)) {

            $notification = new Notification($userLoggedInName,$con);
            $notification->insertNotification($post_id, $row['posted_by'], "comment_non_owner");

            array_push($notified_users, $row['posted_by']);
        }
    }
    echo "<p style='color: #2ecc71'> Comment Posted </p>";

}
?>
<!--input the comment-->
<form action="comment_frame.php?post_id=<?php echo $post_id; ?>" id="comment_form"
      name="postComment<?php echo $post_id; ?>" method="POST">
    <textarea name="post_body"></textarea>
    <input type="submit" name="postComment<?php echo $post_id; ?>" value="Post" onclick="reLoadCommentsNumber('<?php echo $userLoggedInName; ?>')">
</form>

<!--Load Comments-->
<?php
$get_comments = mysqli_query($con, "SELECT * FROM comments WHERE post_id='$post_id' ORDER BY id DESC");
$count = mysqli_num_rows($get_comments);
if ($count != 0) {
    while ($commnet = mysqli_fetch_array($get_comments)) {
        $comment_body = $commnet['post_body'];
        $posted_to = $commnet['posted_to'];
        $posted_by = $commnet['posted_by'];
        $date_added = $commnet['date_added'];
        $removed = $commnet['removed'];

        //Timeframe
        $date_time_now = date("Y-m-d H:i:s");
        $start_date = new DateTime($date_added); //Time of post
        $end_date = new DateTime($date_time_now); //Current time
        $interval = $start_date->diff($end_date); //Difference between dates

        if ($interval->y >= 1) {
            if ($interval == 1)
                $time_message = $interval->y . " year ago"; //1 year ago
            else
                $time_message = $interval->y . " years ago"; //1+ year ago

        } else if ($interval->m >= 1) {
            if ($interval->d == 0) {
                $days = " ago";
            } else if ($interval->d == 1) {
                $days = $interval->d . " day ago";
            } else {
                $days = $interval->d . " days ago";
            }


            if ($interval->m == 1) {
                $time_message = $interval->m . " month" . $days;
            } else {
                $time_message = $interval->m . " months" . $days;
            }

        } else if ($interval->d >= 1) {
            if ($interval->d == 1) {
                $time_message = "Yesterday";
            } else {
                $time_message = $interval->d . " days ago";
            }
        } else if ($interval->h >= 1) {
            if ($interval->h == 1) {
                $time_message = $interval->h . " hour ago";
            } else {
                $time_message = $interval->h . " hours ago";
            }
        } else if ($interval->i >= 1) {
            if ($interval->i == 1) {
                $time_message = $interval->i . " minute ago";
            } else {
                $time_message = $interval->i . " minutes ago";
            }
        } else {
            if ($interval->s < 30) {
                $time_message = "Just now";
            } else {
                $time_message = $interval->s . " seconds ago";
            }
        }

        $user_obj = new User($posted_by, $con);
        ?>

        <!--load comments-->
        <!--  i guess closing and opening the tags is easier-->
        <div class="comment_section">
            <a href="<?php echo $posted_by ?>" target="_parent"><img src="<?php echo $user_obj->getProfilePicture(); ?>"
                                                                     title="<?php echo $posted_by; ?>"
                                                                     style="float:left;" height="30"></a>
            <a href="<?php echo $posted_by ?>"
               target="_parent"><b><?php echo $user_obj->getFirstAndLastName(); ?></b></a>
            &nbsp;&nbsp;&nbsp;&nbsp; <?php echo $time_message . "<br>" . $comment_body; ?>
            <hr>
        </div>
        <?php
    }
} else {
    echo "<p  style='text-align:center'>No Comments To Show!</p>";
}
?>


</body>
</html>