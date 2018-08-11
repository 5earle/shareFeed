<?php
/**
 * Created by Earle white.
 * User: ewhite
 * Date: 7/18/2018
 * Time: 11:28 AM
 */

class Post {
    private $user_obj;
    private $con;

    public function __construct($user, $con) {
        $this->con = $con;
        $this->user_obj = new User($user, $con); //useing the string name to make an user object
    }

    public function submitPost($body, $user_to) {
        // clean up the body
        $body = strip_tags($body); //removes html tags
        $body = mysqli_real_escape_string($this->con, $body); // remove like single quotes. i'm
        $check_empty = preg_replace('/\s+/', '', $body); //Deletes all spaces

        if ($check_empty != "") {

            //handling youtube
            $body_array = preg_split("/\s+/",$body);
            //key keeps track of the element in the array
            foreach($body_array as $key => $value){
                //strpos = Find the position of the first occurrence
                if(strpos($value, "https://www.youtube.com/watch?v=") !== false){
                    $link = preg_split("!&!", $value);
                    // replace with iframe embed
                    $value = preg_replace("!watch\?v=!", "embed/", $link[0]);
                    $value = "<br><iframe width=\'420\' height=\'315\' src=\'" . $value ."\'></iframe><br>";
                    $body_array[$key] = $value;
                }

            }

            $body = implode(" ", $body_array);



            //Current date and time
            $date_added = date("Y-m-d H:i:s");

            //Get username
            echo "<script>console.log({$user_to})</script>";
            $added_by = $this->user_obj->getUsername();

            //If user is on own profile, user_to is 'none'
            if ($user_to == $added_by) {
                $user_to = "none";
            }

            //insert post
            $query = mysqli_query($this->con, "INSERT INTO posts VALUES('', '$body', '$added_by', '$user_to', '$date_added', 'no', 'no', '0')");
            $returned_id = mysqli_insert_id($this->con); // return post id

            //Insert notification
             if($user_to != 'none'){
                 $notification = new Notification($added_by,$this->con);
                 $notification->insertNotification($returned_id,$user_to,'profile_post');
             }

            //Update post count for user
            $num_posts = $this->user_obj->getNumPosts();
            $num_posts++;
            $update_query = mysqli_query($this->con, "UPDATE users SET num_posts='$num_posts' WHERE username='$added_by'");

        }
    }

    public function loadPost() {
        // $page = $data['page'];
        $userLoggedInName = $this->user_obj->getUserName();
        /* if ($page == 1) {
             $start = 0;
         } else {
             $start = ($page - 1) * $limit;
         }*/

        $str = ""; //Number of results checked (not necasserily posted)
        $data_query_Post = mysqli_query($this->con, "SELECT * FROM posts WHERE deleted='no' ORDER BY id DESC");

        if (mysqli_num_rows($data_query_Post) > 0) {
            $num_iterations = 0; //Number of results checked (not necasserily posted)
            $count = 1;

            while ($row = mysqli_fetch_array($data_query_Post)) {
                $id = $row['id'];
                $body = $row['body'];
                $added_by = $row['added_by'];
                $date_time = $row['date_added'];

                //make the user to string empty
                if ($row['user_to'] == "none") {
                    $user_to = "";
                } else {
                    // create object from the user thats getting the post
                    $user_to_obj = new User($row['user_to'], $this->con);
                    $user_to_name = $user_to_obj->getFirstAndLastName();
                    $user_to = "to <a href='" . $row['user_to'] . "'>" . $user_to_name . " </a>";
                }
                //Check if user who posted, has their account closed
                $added_by_obj = new User($added_by, $this->con);
                if ($added_by_obj->isClosed()) {
                    continue;
                }
                //$user_logged_obj = new User($userLoggedInName,$this->con);
                if ($this->user_obj->isFriend($added_by)) {

                    /*if ($num_iterations++ < $start)
                        continue;*/

                    //Once 10 posts have been loaded, break
                    /*  if ($count > $limit) {
                          break;
                      } else {
                          $count++;
                      }*/

                    //DELETE my post. line 226
                    if($userLoggedInName == $added_by){
                        /*$delete_button = "<button class='delete_button btn-danger' id='post$id'>X</button>";*/
                        $delete_button = "<button class='delete_button btn-danger' id='post$id'>X</button>";
                    } else {
                        $delete_button = "";
                    }

                    $user_details_query = mysqli_query($this->con, "SELECT first_name, last_name, profile_pic FROM users WHERE username='$added_by'");
                    $user_row = mysqli_fetch_array($user_details_query);
                    $first_name = $user_row['first_name'];
                    $last_name = $user_row['last_name'];
                    $profile_pic = $user_row['profile_pic'];


                    ?> <!--toggle the comment area-->
                    <script>
                        function toggle<?php echo $id;?>() {
                            /*prevent the toggle if user clicks the profile link
                           let target = $(event.target);
                           if(target.is("a")){

                           }*/
                            let element = document.getElementById("toggleComment<?php echo $id;?>");
                            if (element.style.display == 'block') { /*block is like p tag*/
                                element.style.display = 'none';
                            } else {
                                element.style.display = 'block';
                            }

                        }
                    </script>
                    <?php /*and open a new one*/

                    // check number of commnets
                    $comments_check = mysqli_query($this->con, "SELECT * FROM comments WHERE post_id='$id'");
                    $comments_check_num = mysqli_num_rows($comments_check);


                    //Timeframe
                    $date_time_now = date("Y-m-d H:i:s");
                    $start_date = new DateTime($date_time); //Time of post
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
                    // post area
                    //toggle the iframe post
                    $str .= "<div class='status_post'>
								<div class='post_profile_pic'>
									<img src='$profile_pic' width='50'>
								</div>

								<div class='posted_by' style='color:#ACACAC;'>
									<a href='$added_by'> $first_name $last_name </a> $user_to &nbsp;&nbsp;&nbsp;&nbsp;$time_message
									$delete_button
								</div>
								<div id='post_body'>
									$body
									<br>
									<br>
									<br>
								</div>
								<!--show number of comments per post-->
								<div class='newsfeedPostOptions'>
								<div class='comment_toggle' onclick='toggle$id()'>
								 
								  Comments($comments_check_num)&nbsp;&nbsp;
								  
								   <!--handing the bump buttons-->
								  <iframe src='like.php?post_id=$id' scrolling='no'></iframe>
								  
								  </div>&nbsp;
                                </div>
							</div>
							
							<!--send post id over to comment_frame.php-->
							<div class='post_comment' id='toggleComment$id' style='display: none;'>
							<iframe src='comment_frame.php?post_id=$id' id='comment_iframe'  frameborder=0></iframe>
                            </div>
                            
							<hr>";
                }
                //DELETE my post line 100
                ?>
                <script>
                    $(document).ready(function () {
                        $('#post<?php echo $id; ?>').on('click',function () {
                            bootbox.confirm("Are you sure you want to delete this post ?",function (result) {
                                $.post("includes/form_handlers/delete_post.php?post_id=<?php echo $id; ?>", {result:result});
                                if(result)
                                    location.reload();
                            })
                        });
                    });
                </script>
                <?php
            } //End while loop

            if ($count > 1) {
                $str .= "<input type='hidden' class='nextPage' value='" . (0 + 1) . "'>
							<input type='hidden' class='noMorePosts' value='false'>";
            } else {
                $str .= "<input type='hidden' class='noMorePosts' value='true'><p style='text-align: center;'> No more posts to show! </p>";
            }

            //echo $str;

        }
        echo $str;
    }

    public function loadProfilePost($data) {
        // $page = $data['page'];
        $userLoggedInName = $this->user_obj->getUserName();
        $userProfileName = $data['profileUserName'];
        /* if ($page == 1) {
             $start = 0;
         } else {
             $start = ($page - 1) * $limit;
         }*/

        $str = ""; //Number of results checked (not necasserily posted)
        $data_query_Post = mysqli_query($this->con, "SELECT * FROM posts WHERE deleted='no' AND ((added_by='$userProfileName' AND user_to='none') OR user_to='userProfileName')  ORDER BY id DESC")
        or die(mysqli_error($this->con));

        if (mysqli_num_rows($data_query_Post) > 0) {
            $num_iterations = 0; //Number of results checked (not necasserily posted)
            $count = 1;

            while ($row = mysqli_fetch_array($data_query_Post)) {
                $id = $row['id'];
                $body = $row['body'];
                $added_by = $row['added_by'];
                $date_time = $row['date_added'];


                    //DELETE my post. line 226
                    if($userLoggedInName == $added_by){
                        /*$delete_button = "<button class='delete_button btn-danger' id='post$id'>X</button>";*/
                        $delete_button = "<button class='delete_button btn-danger' id='post$id'>X</button>";
                    } else {
                        $delete_button = "";
                    }

                    $user_details_query = mysqli_query($this->con, "SELECT first_name, last_name, profile_pic FROM users WHERE username='$added_by'");
                    $user_row = mysqli_fetch_array($user_details_query);
                    $first_name = $user_row['first_name'];
                    $last_name = $user_row['last_name'];
                    $profile_pic = $user_row['profile_pic'];


                    ?> <!--toggle the comment area-->
                    <script>
                        function toggle<?php echo $id;?>() {
                            /*prevent the toggle if user clicks the profile link
                           let target = $(event.target);
                           if(target.is("a")){

                           }*/
                            let element = document.getElementById("toggleComment<?php echo $id;?>");
                            if (element.style.display == 'block') { /*block is like p tag*/
                                element.style.display = 'none';
                            } else {
                                element.style.display = 'block';
                            }

                        }
                    </script>
                    <?php /*and open a new one*/

                    // check number of commnets
                    $comments_check = mysqli_query($this->con, "SELECT * FROM comments WHERE post_id='$id'");
                    $comments_check_num = mysqli_num_rows($comments_check);


                    //Timeframe
                    $date_time_now = date("Y-m-d H:i:s");
                    $start_date = new DateTime($date_time); //Time of post
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
                    // post area
                    //toggle the iframe post
                    $str .= "<div class='status_post'>
								<div class='post_profile_pic'>
									<img src='$profile_pic' width='50'>
								</div>

								<div class='posted_by' style='color:#ACACAC;'>
									<a href='$added_by'> $first_name $last_name </a> $time_message
									$delete_button
								</div>
								<div id='post_body'>
									$body
									<br>
									<br>
									<br>
								</div>
								<!--show number of comments per post-->
								<div class='newsfeedPostOptions'>
								<div class='comment_toggle' onclick='toggle$id()'>
								 
								  Comments($comments_check_num)&nbsp;&nbsp;
								  
								   <!--handing the bump buttons-->
								  <iframe src='like.php?post_id=$id' scrolling='no'></iframe>
								  
								  </div>&nbsp;
                                </div>
							</div>
							
							<!--send post id over to comment_frame.php-->
							<div class='post_comment' id='toggleComment$id' style='display: none;'>
							<iframe src='comment_frame.php?post_id=$id' id='comment_iframe'  frameborder=0></iframe>
                            </div>
                            
							<hr>";

                //DELETE my post line 100
                ?>
                <script>
                    $(document).ready(function () {
                        $('#post<?php echo $id; ?>').on('click',function () {
                            bootbox.confirm("Are you sure you want to delete this post ?",function (result) {
                                $.post("includes/form_handlers/delete_post.php?post_id=<?php echo $id; ?>", {result:result});
                                if(result)
                                    location.reload();
                            })
                        });
                    });
                </script>
                <?php
            } //End while loop

            if ($count > 1) {
                $str .= "<input type='hidden' class='nextPage' value='" . (0 + 1) . "'>
							<input type='hidden' class='noMorePosts' value='false'>";
            } else {
                $str .= "<input type='hidden' class='noMorePosts' value='true'><p style='text-align: center;'> No more posts to show! </p>";
            }

            //echo $str;

        } else {
            $str = $userProfileName;
        }
        echo $str;
    }


    function console_log($data) {
        echo '<script>';
        echo 'console.log(' . json_encode($data) . ')';
        echo '</script>';/*echo '<pre>';
        var_dump($_SESSION);
        echo '</pre>'*/;
    }

    public function getSinglePost($post_id) {

        $userLoggedIn = $this->user_obj->getUsername();

        //because the link was click
        $opened_query = mysqli_query($this->con, "UPDATE notifications SET opened='yes' WHERE user_to='$userLoggedIn' AND link LIKE '%=$post_id'");

        $str = ""; //String to return
        $data_query = mysqli_query($this->con, "SELECT * FROM posts WHERE deleted='no' AND id='$post_id'");

        if(mysqli_num_rows($data_query) > 0) {


            $row = mysqli_fetch_array($data_query);
            $id = $row['id'];
            $body = $row['body'];
            $added_by = $row['added_by'];
            $date_time = $row['date_added']; //id 78 = toronto_raptors

            //Prepare user_to string so it can be included even if not posted to a user
            if($row['user_to'] == "none") {
                $user_to = "";
            }
            else {
                $user_to_obj = new User($row['user_to'],$this->con);
                $user_to_name = $user_to_obj->getFirstAndLastName();
                $user_to = "to <a href='" . $row['user_to'] ."'>" . $user_to_name . "</a>";
            }

            //Check if user who posted, has their account closed
            $added_by_obj = new User($added_by,$this->con);
            if($added_by_obj->isClosed()) {
                return;
            }

            $user_logged_obj = new User($userLoggedIn,$this->con);
            if($user_logged_obj->isFriend($added_by)){


                if($userLoggedIn == $added_by)
                    $delete_button = "<button class='delete_button btn-danger' id='post$id'>X</button>";
                else
                    $delete_button = "";


                $user_details_query = mysqli_query($this->con, "SELECT first_name, last_name, profile_pic FROM users WHERE username='$added_by'");
                $user_row = mysqli_fetch_array($user_details_query);
                $first_name = $user_row['first_name'];
                $last_name = $user_row['last_name'];
                $profile_pic = $user_row['profile_pic'];


                ?>
                <script>
                    function toggle<?php echo $id;?>() {
                        /*prevent the toggle if user clicks the profile link
                       let target = $(event.target);
                       if(target.is("a")){

                       }*/
                        let element = document.getElementById("toggleComment<?php echo $id;?>");
                        if (element.style.display == 'block') { /*block is like p tag*/
                            element.style.display = 'none';
                        } else {
                            element.style.display = 'block';
                        }

                    }
                </script>
                <?php

                $comments_check = mysqli_query($this->con, "SELECT * FROM comments WHERE post_id='$id'");
                $comments_check_num = mysqli_num_rows($comments_check);


                //Timeframe
                $date_time_now = date("Y-m-d H:i:s");
                $start_date = new DateTime($date_time); //Time of post
                $end_date = new DateTime($date_time_now); //Current time
                $interval = $start_date->diff($end_date); //Difference between dates
                if($interval->y >= 1) {
                    if($interval == 1)
                        $time_message = $interval->y . " year ago"; //1 year ago
                    else
                        $time_message = $interval->y . " years ago"; //1+ year ago
                }
                else if ($interval->m >= 1) {
                    if($interval->d == 0) {
                        $days = " ago";
                    }
                    else if($interval->d == 1) {
                        $days = $interval->d . " day ago";
                    }
                    else {
                        $days = $interval->d . " days ago";
                    }


                    if($interval->m == 1) {
                        $time_message = $interval->m . " month". $days;
                    }
                    else {
                        $time_message = $interval->m . " months". $days;
                    }

                }
                else if($interval->d >= 1) {
                    if($interval->d == 1) {
                        $time_message = "Yesterday";
                    }
                    else {
                        $time_message = $interval->d . " days ago";
                    }
                }
                else if($interval->h >= 1) {
                    if($interval->h == 1) {
                        $time_message = $interval->h . " hour ago";
                    }
                    else {
                        $time_message = $interval->h . " hours ago";
                    }
                }
                else if($interval->i >= 1) {
                    if($interval->i == 1) {
                        $time_message = $interval->i . " minute ago";
                    }
                    else {
                        $time_message = $interval->i . " minutes ago";
                    }
                }
                else {
                    if($interval->s < 30) {
                        $time_message = "Just now";
                    }
                    else {
                        $time_message = $interval->s . " seconds ago";
                    }
                }

                $str .= "<div class='status_post' onClick='toggle$id()'>
								<div class='post_profile_pic'>
									<img src='$profile_pic' width='50'>
								</div>

								<div class='posted_by' style='color:#ACACAC;'>
									<a href='$added_by'> $first_name $last_name </a> $user_to &nbsp;&nbsp;&nbsp;&nbsp;$time_message
									$delete_button
								</div>
								<div id='post_body'>
									$body
									<br>
									<br>
									<br>
								</div>

								<div class='newsfeedPostOptions'>
									Comments($comments_check_num)&nbsp;&nbsp;&nbsp;
									<iframe src='like.php?post_id=$id' scrolling='no'></iframe>
								</div>

							</div>
							<!--get all the comments for this post-->
							<div class='post_comment' id='toggleComment$id' style='display:none;'>
								<iframe src='comment_frame.php?post_id=$id' id='comment_iframe' frameborder='0'></iframe>
							</div>
							<hr>";


                ?>
                <script>

                    $(document).ready(function() {

                        $('#post<?php echo $id; ?>').on('click', function() {
                            bootbox.confirm("Are you sure you want to delete this post?", function(result) {

                                $.post("includes/form_handlers/delete_post.php?post_id=<?php echo $id; ?>", {result:result});

                                if(result)
                                    location.reload();

                            });
                        });


                    });

                </script>
                <?php
            }
            else {
                echo "<p>You cannot see this post because you are not friends with this user.</p>";
                return;
            }
        }
        else {
            echo "<p>No post found. If you clicked a link, it may be broken.</p>";
            return;
        }

        echo $str;
    }
}

?>