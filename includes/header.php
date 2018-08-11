<?php
require 'config/config.php';
include("includes/classes/User.php");
include "includes/classes/Post.php";
include "includes/classes/Message.php";
include("includes/classes/Notification.php");


if(isset($_SESSION['username'])){
    $userLoggedInName = $_SESSION['username'];
    $user_details_query = mysqli_query($con,"SELECT * FROM users WHERE username='$userLoggedInName'");
    $user = mysqli_fetch_array($user_details_query );
} else {
    header('Location: register.php');
}
?>

<!--unread messages-->
<?php
/*<!--unread messages-->*/
$messages = new Message($con,$userLoggedInName);
$num_messages = $messages->getUnreadNumber();

/*unread notifications*/
$notifications = new Notification($userLoggedInName,$con);
$num_notifications = $notifications->getUnreadNumber();

/*unread freidn request*/
$user_obj = new User($userLoggedInName,$con);
$num_request = $user_obj->getNumberOfFriendRequests();
?>



<html>
<head>
    <title></title>
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/2.1.3/jquery.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.1.2/js/bootstrap.min.js" integrity="sha384-o+RDsa0aLu++PJvFqy8fFScvbHFLtbvScb8AjopnFD+iEQ7wo/CG0xlczd+2O/em" crossorigin="anonymous"></script>
    <script src="assets/js/socialmedia.js"></script>
    <script src="assets/js/bootbox.min.js"></script>
    <script src="assets/js/jquery.jcrop.js"></script>
    <script src="assets/js/jcrop_bits.js"></script>

   <!-- css-->
    <!--boostrap 3-->
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.1.2/css/bootstrap.min.css" integrity="sha384-Smlep5jCw/wG7hdkwQ/Z5nLIefveQRIY9nfy6xoR1uRYBtpZgI6339F5dgvm/e9B" crossorigin="anonymous">
    <!--boostrap 3-->
    <!--<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css" integrity="sha384-BVYiiSIFeK1dGmJRAkycuHAHRg32OmUcww7on3RYdg4Va+PmSTsz/K68vbdEjh4u" crossorigin="anonymous">-->
    <link rel="stylesheet" type="text/css" href="assets/css/style.css">
    <link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.1.1/css/all.css" integrity="sha384-O8whS3fhG2OnA5Kas0Y9l3cfpmYjapjI0E4theH4iuMD+pLhbf6JI0jIMfYcK3yZ" crossorigin="anonymous">
    <link rel="stylesheet" href="assets/css/jquery.Jcrop.css" type="text/css" />

</head>
<body class="index_home">
<div class="top_bar">

    <div class="logo">
        <a href="index.php">ShareFeed!</a>
    </div>

    <!--search bar-->
   <!-- form is a get,so jquery will handling the submission-->
    <div class="search">
        <form action="search.php" method="GET" name="search_form">
            <input type="text" onkeyup="getLiveSearchUsers(this.value,'<?php echo $userLoggedInName; ?>')"
            name="qInput" placeholder="Search..." autocomplete="off" id="search_text_input">

            <div class="button_holder">
                <img src="assets/images/icons/magnifying_glass.png">
            </div>
        </form>

        <div class="search_results">
        </div>

        <div class="search_results_footer_empty">
        </div>

    </div>
    <!--search bar-->

    <nav>



        <img src="<?php echo $user['profile_pic'] ?>" style="border-radius: 100px; width: 4%; position: absolute; left: 15px;">
        <a href="index.php">
            <?php
            echo $user['first_name'].' ';
            ?>
        </a>

        <a href="<?php echo $userLoggedInName; ?>">
            <i class="fas fa-home fa-lg"></i>
        </a>

        <!--call the ajax function from socialMedia.js-->
        <a href="javascript:void(0);" onclick="getDropDownData('<?php echo $userLoggedInName;?>','message')">
            <i class="fas fa-envelope fa-lg"></i>
            <?php
            if($num_messages > 0){
                echo '<span class="notification_badge" id="unread_message">' . $num_messages . '</span>';
            }
            ?>
        </a>

        <a href="javascript:void(0);" onclick="getDropDownData('<?php echo $userLoggedInName;?>','notification')">
            <i class="fas fa-bell fa-lg"></i>
            <?php
            if($num_notifications > 0){
                echo '<span class="notification_badge" id="unread_message">' . $num_notifications . '</span>';
            }
            ?>
        </a>

        <a href="request.php">
            <i class="fas fa-users fa-lg"></i>
            <?php
            if($num_request > 0){
                echo '<span class="notification_badge" id="unread_message">' . $num_request . '</span>';
            }
            ?>
        </a>

        <a href="settings.php">
            <i class="fas fa-cog fa-lg"></i>
        </a>

        <a href="includes/handlers/logout.php">
            <i class="fas fa-sign-out-alt fa-lg"></i>
        </a>
    </nav>

    <!--drop down for messages/notification-->
    <div class="dropdown_data_window" style="height:0px; border:none;"></div>
    <input type="hidden" id="dropdown_data_type" value="">

</div>

<!--drop down data scrolling-->
<script>
    let userLoggedIn = '<?php echo $userLoggedIn; ?>';

    $(document).ready(function() {

        $('.dropdown_data_window').scroll(function() {
            let inner_height = $('.dropdown_data_window').innerHeight(); //Div containing data
            let scroll_top = $('.dropdown_data_window').scrollTop();
            let page = $('.dropdown_data_window').find('.nextPageDropdownData').val();
            let noMoreData = $('.dropdown_data_window').find('.noMoreDropdownData').val();

            if ((scroll_top + inner_height >= $('.dropdown_data_window')[0].scrollHeight) && noMoreData == 'false') {

                let pageName; //Holds name of page to send ajax request to
                let type = $('#dropdown_data_type').val();


                if(type == 'notification')
                    pageName = "ajax_load_notifications.php";
                else if(type = 'message')
                    pageName = "ajax_load_messages.php"


                let ajaxReq = $.ajax({
                    url: "includes/handlers/" + pageName,
                    type: "POST",
                    data: "page=" + page + "&userLoggedIn=" + userLoggedIn,
                    cache:false,

                    success: function(response) {
                        $('.dropdown_data_window').find('.nextPageDropdownData').remove(); //Removes current .nextpage
                        $('.dropdown_data_window').find('.noMoreDropdownData').remove(); //Removes current .nextpage


                        $('.dropdown_data_window').append(response);
                    }
                });

            } //End if

            return false;

        }); //End (window).scroll(function())


    });

</script>

<div class="wrapper">

