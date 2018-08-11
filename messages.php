<?php
include("includes/header.php");


$message_obj = new Message($con, $userLoggedInName);

if(isset($_GET['u'])){
    $mostRecent_Convo = $_GET['u'];
}
else {
    $mostRecent_Convo = $message_obj->getMostRecentUser();

    if($mostRecent_Convo == false)
        $mostRecent_Convo = 'new';
}
// if user is not trying to start a new converstaion
if($mostRecent_Convo != 'new'){
    // create object for the last user who sent a message aka user_from
    $user_from_obj = new User($mostRecent_Convo,$con);
}
if(isset($_POST['post_message']) && $_POST['randcheck']==$_SESSION['rand']){

    if(isset($_POST['message_body'])){
        $body = mysqli_real_escape_string($con,$_POST['message_body']);
        $date = date('Y-m-d H:i:s');
        $message_obj->sendMessage($mostRecent_Convo,$body,$date);
    }
}

?>
<!--display logged in profile image,post,likes-->
<div class="user_details column">
    <a href="<?php echo $userLoggedInName; ?>"> <img src="<?php echo $user['profile_pic']; ?>"> </a>

    <div class="user_details_left_right">
        <a href="<?php echo $userLoggedInName; ?>">
            <?php echo $user['first_name'] . " " . $user['last_name']; ?> </a>
        <br>
        <?php echo "Posts: " . $user['num_posts'] . "<br>";
        echo "Likes: " . $user['num_likes'];

        ?>
    </div>

</div>

<!--show most recent message-->
<div class="main_column column" id="main_column">
    <?php
    if($mostRecent_Convo != "new"){
        echo "<h4>You and <a href='$mostRecent_Convo'>" . $user_from_obj->getFirstAndLastName() . "</a></h4><hr><br>";
        /*load the messages*/
        echo "<div class='loaded_messages' id='scroll_messages'>";
        echo $message_obj->getMessages($mostRecent_Convo);
        echo "</div>";
        /*load the messages*/
    }
    else { /*<!--option to find a freind and start message-->*/
        echo "<h4>New Message</h4>";
    }
    ?>


    <!--message block-->
   <!--option to find a friend and start message-->
    <div class="message_post">
        <form action="" method="POST">
            <?php
            $rand_post_check = rand();
            $_SESSION['rand']=$rand_post_check ;

            if($mostRecent_Convo == "new") {
                echo "Select the friend you would like to message <br><br>";
                ?>
                <!--this function in socialmedia.js takes cair of the ajax calling-->
                To: <input type='text' onkeyup='getUsers(this.value, "<?php echo $userLoggedInName; ?>")' name='q' placeholder='Name' autocomplete='off' id='seach_text_input'>

                <?php
                /*show the return search result*/
                echo "<div class='results'></div>";
            }
            else {
                echo "<textarea name='message_body' id='message_textarea' placeholder='Write your message ...'></textarea>";
                echo "<input type='submit' name='post_message' class='info' id='message_submit' value='Send'>";
            }

            ?>
            <input type="hidden" value="<?php echo $rand_post_check ; ?>" name="randcheck" />
        </form>

    </div>

    <!--take cair of the scroll_messages reloading the page-->
    <script>
        let div = document.getElementById("scroll_messages");
        div.scrollTop = div.scrollHeight;
    </script>

</div>

<!--show list of conversations-->
<div class="user_details column" id="conversations">
    <h4>Conversations</h4>

    <div class="loaded_conversations">
        <?php echo $message_obj->getConvos(); ?>
    </div>
    <br>
    <a href="messages.php?u=new">New Message</a>
</div>
