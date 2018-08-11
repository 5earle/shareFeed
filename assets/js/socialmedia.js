$(document).ready(function () {
    // search bar
    $('#search_text_input').focus(function () {
        /*if screen is large enough*/
        if(window.matchMedia("(min-width: 800px)").matches){
            //this = current obj = #search_text_input
            $(this).animate({width: '250px'},500); // 500 = speed
        }
    });

    //search submit button
    $('.button_holder').on('click',function () {
        document.search_form.submit();
    });
   /* $('.button_holder').click(function () {
        document.search_form.submit();
    });*/
    // search bar




    //grab Button for profile post
    $('#submit_profile_post').click(function () {
       $.ajax({
           type: "POST",
           url: "includes/handlers/ajax_submit_profile_post.php",
           data: $('form.profile_post').serialize(),  // data comming from the profile_post form
           success: function (msg) {
               $("#post_form").modal('hide'); // grab the button thats toggling the modal

               location.reload();
           },
           error: function () {
               alert('failure');
           }
       }) ;
    });
});

//outside click
$(document).click(function (e) {
   if(e.target.class != 'search_results' && e.target.id != 'search_text_input'){
       $('.search_results').html("");
       $('.search_results_footer').html("");
       $('.search_results_footer').toggleClass("search_results_footer_empty");
       $('.search_results_footer').toggleClass("search_results_footer");
   }
   if(e.target.class != 'dropdown_data_window'){
       $('.dropdown_data_window').html("");
       $('.dropdown_data_window').css({"padding" : "0px", "height": "0px", "border" : "none"});
   }
});

//live search
function getLiveSearchUsers(value,user) {
    //ajax call

     /*  $.post("includes/handlers/ajax_search.php", {query:value, userLoggedIn: user}, function(data) {

       if($(".search_results_footer_empty")[0]) {
           $(".search_results_footer_empty").toggleClass("search_results_footer");
           $(".search_results_footer_empty").toggleClass("search_results_footer_empty");
       }

       $('.search_results').html(data);
       $('.search_results_footer').html("<a href='search.php?qInput=" + value + "'>See All Results</a>");

       if(data == "") {
           $('.search_results_footer').html("");
           $('.search_results_footer').toggleClass("search_results_footer_empty");
           $('.search_results_footer').toggleClass("search_results_footer");
       }

   });*/
    let ajaxReq = $.ajax({
        url:"includes/handlers/ajax_search.php",
        type: "POST",
        data:{query: value, userLoggedIn: user},
        cache: false,
        
        success: function (data) {
            if($(".search_results_footer_empty")[0]) {
                $(".search_results_footer_empty").toggleClass("search_results_footer");
                $(".search_results_footer_empty").toggleClass("search_results_footer_empty");
            }

            $('.search_results').html(data);
            $('.search_results_footer').html("<a href='search.php?qInput=" + value + "'>See All Results</a>");

            if(data == "") {
                $('.search_results_footer').html("");
                $('.search_results_footer').toggleClass("search_results_footer_empty");
                $('.search_results_footer').toggleClass("search_results_footer");
            }
        }
    });
}

function reLoadCommentsNumber(data) {
    $.ajax({
        url: "includes/handlers/ajax_load_posts.php",
        type: "POST",
        data: "page=1&userLoggedIn=" + data,
        cache:false,

        success: function(data) {
            $('#loading').hide(); // close the loading.gif
            $('.posts_area').html(data);
        }
    });
}

function getUsers(value,user) {
$.post("includes/handlers/ajax_friend_search.php",{query: value, userLoggedIn:user},(data)=>{ // the return function
    $('.results').html(data); // messages.php line 77
});
}

function getDropDownData(userLoggedInName,type) {
    if($(".dropdown_data_window").css("height") == "0px") { // if drop down menu is not showing
        let pageName;

        if(type === 'notification'){
            pageName = "ajax_load_notifications.php";
            $("span").remove("#unread_notification");

        } else if(type === 'message') {
            pageName = 'ajax_load_messages.php'; //page to send data
            $("span").remove("#unread_message");
        }

        let ajaxreq = $.ajax({
            url: "includes/handlers/" + pageName,
            type: "POST",
            data: "page=1&userLoggedIn=" + userLoggedInName,
            cache: false,

            // manupulate header: 90,91
            success: function(response) {
                //located in header.php
                $(".dropdown_data_window").html(response);
                $(".dropdown_data_window").css({"padding" : "0px", "height": "280px", "border" : "1px solid #DADADA"});
                $("#dropdown_data_type").val(type);
            }
        });
    } else {
        $(".dropdown_data_window").html("");
        $(".dropdown_data_window").css({"padding" : "0px", "height": "0px", "border" : "none"});
    }
}