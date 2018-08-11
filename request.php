<?php
include("includes/header.php");

?>

<div class="main_column column" id="main_column">
    <h4>Friend Request</h4>

    <?php
    $sql = "SELECT * FROM friend_request WHERE user_to = '$userLoggedInName'";
    $query = mysqli_query($con,$sql) or die(mysqli_error($con));

    // no request
    if(mysqli_num_rows($query) == 0) {
        echo "You have no friend requests at this time!";
    } else {
        while($rows = mysqli_fetch_array($query)){
            $user_from = $rows['user_from'];
            $user_from_obj = new User($user_from,$con); // create new user object
            echo $user_from_obj->getFirstAndLastName(). ' sent you a firend request!';

            // get list of friends from the send ,so we can edit it
            $user_from_friend_array = $user_from_obj->getFriendArray();

            if(isset($_POST['accept_request'.$user_from])){
                // add each other to there friend list
                $sql = "UPDATE users SET friend_array=CONCAT(friend_array,'$user_from,') WHERE username='$userLoggedInName'";
                $sqlT = "UPDATE users SET friend_array=CONCAT(friend_array,'$userLoggedInName,') WHERE username='$user_from'";

                $add_friend_query = mysqli_query($con,$sql) or die(mysqli_error($con));
                $add_friend_query = mysqli_query($con,$sqlT) or die(mysqli_error($con));

                // remove the request after the connection is made
                $delete_query = mysqli_query($con,"DELETE FROM friend_request WHERE user_to='$userLoggedInName' AND user_from='$user_from'")
                or die(mysqli_error($con));

                echo "You are Now Friends!";
                header("Location: request.php"); // just refresh the page

            }
            if(isset($_POST['ignore_request'.$user_from])){
                $delete_query = mysqli_query($con,"DELETE FROM friend_request WHERE user_to='$userLoggedInName' AND user_from='$user_from'")
                or die(mysqli_error($con));

                echo "Request Ignored!";
                header("Location: request.php"); // just refresh the page
            }
            ?>
            <form action="request.php" method="POST">
                <input type="submit" name="accept_request<?php echo $user_from?>" id="accept_button" value="ACCEPT">
                <input type="submit" name="ignore_request<?php echo $user_from?>" id="ignore_button" value="REJECT">
            </form>
            <?php
        }
    }
    ?>

</div>
