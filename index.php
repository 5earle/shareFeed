<?php
include("includes/header.php");


    if (isset($_POST['post']) && $_POST['randcheck']==$_SESSION['rand']) {
        $post = new Post($userLoggedInName, $con);
        $post->submitPost($_POST['post_text'], 'none');
    }

?>

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

<div class="main_column column">
    <form class="post_form" action="index.php" method="POST">
        <?php
        // Preventing POST on reloading a form
        $rand_post_check = rand();
        $_SESSION['rand']=$rand_post_check ;
        ?>
        <textarea name="post_text" id="post_text" placeholder="Got something to say?"></textarea>
        <input type="hidden" value="<?php echo $rand_post_check ; ?>" name="randcheck" />
        <input type="submit" name="post" id="post_button" value="Post">
        <hr>

    </form>

   <!-- --><?php
/*    $post = new Post($userLoggedInName, $con);
    $post->loadPost();
    */?>

  <!-- post will now go inside this div-->
    <div class="posts_area"></div>
    <img id="loading" src="assets/images/icons/loading.gif">


</div>

<!--ajax helsp to send and recive data with out refreshing the page-->
<script>
    let userLoggedIn = '<?php echo $userLoggedInName; ?>';

    $(document).ready(function() {
        $('#loading').show(); // show the loading.gif

        //Original ajax request for loading first posts 
        $.ajax({
            url: "includes/handlers/ajax_load_posts.php",
            type: "POST",
            data: "page=1&userLoggedIn=" + userLoggedIn,
            cache:false,

            success: function(data) {
                $('#loading').hide(); // close the loading.gif
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
                    url: "includes/handlers/ajax_load_posts.php",
                    type: "POST",
                    data: "page=" + page + "&userLoggedIn=" + userLoggedIn,
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




<!--closeing for headers opening tags-->
</div>
</body>
</html>