<?php
include("includes/header.php");




// check if incomeing user name is set
if(isset($_GET['profile_username'])){
    $currentUserProfileName = $_GET['profile_username']; // get the current page profile name that we are on
    $sql = "SELECT * FROM users WHERE username='$currentUserProfileName'";
    $user_details_query = mysqli_query($con,$sql) or die(mysqli_error($con));
    $user_array = mysqli_fetch_array($user_details_query);
    //check substr comma separated
    $num_friends = (substr_count($user_array['friend_array'], ",")) - 1;
}
// if we go to friend page a remove from friend list
if(isset($_POST['remove_friend'])){
    $user = new User($userLoggedInName,$con);
    $user -> removeFriend($currentUserProfileName);
}
// if we go to friend page and add to friend list
if(isset($_POST['add_friend'])){
    $user = new User($userLoggedInName,$con);
    $user -> sendRequest($currentUserProfileName);
}
?>

<style type="text/css">
    .wrapper{
        margin-left: 0px;
        padding-left: 0px;
    }
</style>

<!--profile image-->
<div class="profile_left">
    <img src="<?php echo $user_array['profile_pic'] ?>">

    <div class="profile_info">
        <p><?php echo "Posts: " . $user_array['num_posts']; ?></p>
        <p><?php echo "Likes: " . $user_array['num_likes']; ?></p>
        <p><?php echo "Friends: " . $num_friends ?></p>
    </div>

    <form action="<?php echo $currentUserProfileName?>" method="POST">
        <?php

        //redirect if user closed the account
        $profile_user_obj = new User($currentUserProfileName,$con);
        if($profile_user_obj->isClosed()){
            header("Location: user_closed.php");
        }

        //get the loggin user
        $logged_in_user_obj = new User($userLoggedInName,$con);

        //redirect if user page is private
        if(!$logged_in_user_obj->isFriend($currentUserProfileName) && $profile_user_obj->isPrivate()){
            header("Location: user_closed.php");
        }

        //show if logged is user is not equal to the page that is being visited
        if($userLoggedInName != $currentUserProfileName){
            //show removed button if they are freinds
            if($logged_in_user_obj->isFriend($currentUserProfileName)) {
                echo '<input type="submit" name="remove_friend" class="danger" value="Remove Friend"><br>';
            }
            /*can check if for their page or go to icon on my page*/
            else if ($logged_in_user_obj->didReceiveRequest($currentUserProfileName)) {
                echo '<input type="submit" name="respond_request" class="warning" value="Respond to Request"><br>';
            }
            else if ($logged_in_user_obj->didSendRequest($currentUserProfileName)) {
                echo '<input type="submit" name="" class="default" value="Request Sent"><br>';
            }
            else
                echo '<input type="submit" name="add_friend" class="success" value="Add Friend"><br>';
        }

        ?>
    </form>

    <!-- call modal-->
    <input type="submit" class="deep_blue" data-toggle="modal" data-target="#post_form" value="Post Something">

    <!--show mutual friends-->
    <?php
    if($userLoggedInName != $currentUserProfileName){
        echo '<div class="profile_info_button">';
        echo $logged_in_user_obj->getMutalFriends($currentUserProfileName).' Mutual friends';
        echo '</div>';
    }
    ?>

</div>
<!--profile image-->

<!--show all of your post-->
<div class="profile_main_column column">
    <div class="tab-content">
        <div role="tabpanel" class="tab-pane fade in active" id="newsfeed_div">
        </div>
    </div>

    <div class="posts_area"></div>
    <img id="loading" src="assets/images/icons/loading.gif">
</div>


<!-- Modal -->
<div class="modal fade" id="post_form" tabindex="-1" role="dialog" aria-labelledby="postModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="exampleModalLabel">Post something</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                This will appear on the user's profile page and also their newsfeed

                <form class="profile_post" action="index.php" method="POST">
                   <div class="form-group">
                       <textarea name="post_body" class="form-control"></textarea>
                       <input type="hidden" name="user_from" value="<?php echo $userLoggedInName; ?>">
                       <input type="hidden" name="user_to" value="<?php echo $currentUserProfileName; ?>">
                   </div>
                </form>

            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                <button type="button" class="btn btn-primary" name="post_button" id="submit_profile_post">Post</button>
            </div>
        </div>
    </div>
</div>
<!-- Modal -->


<!--show the post ajax-->
<!--ajax helps to send and recive data with out refreshing the page-->
<script>
    let userLoggedIn = '<?php echo $userLoggedInName; ?>';
    let profileUserName = '<?php echo $currentUserProfileName?>';

    $(document).ready(function() {

        $('#loading').show();

        //Original ajax request for loading first posts 
        $.ajax({
            url: "includes/handlers/ajax_load_profile_post.php",
            type: "POST",
            data: "page=1&userLoggedIn=" + userLoggedIn + "&profileUserName=" + profileUserName,
            cache:false,

            success: function(data) {
                $('#loading').hide();
                $('.posts_area').html(data);
            }
        });

        $(window).scroll(function() {
            let height = $('.posts_area').height(); //Div containing posts
            let scroll_top = $(this).scrollTop();
            let page = $('.posts_area').find('.nextPage').val();
            let noMorePosts = $('.posts_area').find('.noMorePosts').val();

            if ((document.body.scrollHeight == document.body.scrollTop + window.innerHeight) && noMorePosts == 'false') {
                $('#loading').show();

                let ajaxReq = $.ajax({
                    url: "includes/handlers/ajax_load_profile_posts.php",
                    type: "POST",
                    data: "page=" + page + "&userLoggedIn=" + userLoggedIn + "&profileUsername=" + profileUserName,
                    cache:false,

                    success: function(response) {
                        $('.posts_area').find('.nextPage').remove(); //Removes current .nextpage 
                        $('.posts_area').find('.noMorePosts').remove(); //Removes current .nextpage 

                        $('#loading').hide();
                        $('.posts_area').append(response);
                    }
                });

            } //End if 

            return false;

        }); //End (window).scroll(function())


    });

</script>



</div>
</body>
</html>