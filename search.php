<?php
include("includes/header.php");

if(isset($_GET['qInput'])) {
    $query = $_GET['qInput'];
}
else {
    $query = "";
}

// type is comeing from line 54
if(isset($_GET['type'])) {
    $type = $_GET['type'];
}
else {
    $type = "name";
}


?>

<div class="main_column column" id="main_column">

    <?php
    if($query == "")
        echo "You must enter something in the search box.";
    else {

        //If query contains an underscore, assume user is searching for usernames
        if($type == "username")
            $usersReturnedQuery = mysqli_query($con, "SELECT * FROM users WHERE username LIKE '$query%' AND user_closed='no' LIMIT 8");
        //If there are two words, assume they are first and last names respectively
        else {

            $names = explode(" ", $query);
            // asume is first middle and last name, but skip middle for now
            if(count($names) == 3)
                $usersReturnedQuery = mysqli_query($con, "SELECT * FROM users WHERE (first_name LIKE '$names[0]%' AND last_name LIKE '$names[2]%') AND user_closed='no'");
            //search first names or last names
            else if(count($names) == 2)
                $usersReturnedQuery = mysqli_query($con, "SELECT * FROM users WHERE (first_name LIKE '$names[0]%' AND last_name LIKE '$names[1]%') AND user_closed='no'");
            else
                $usersReturnedQuery = mysqli_query($con, "SELECT * FROM users WHERE (first_name LIKE '$names[0]%' OR last_name LIKE '$names[0]%') AND user_closed='no'");
        }

        //Check if results were found
        if(mysqli_num_rows($usersReturnedQuery) == 0)
            echo "We can't find anyone with a " . $type . " like: " .$query;
        else
            echo mysqli_num_rows($usersReturnedQuery) . " results found: <br> <br>";

       //suggest  diffrent users to loookup
        echo "<p id='grey'>Try searching for:</p>";
        echo "<a href='search.php?qInput=" . $query ."&type=name'>Names</a>, <a href='search.php?qInput=" . $query ."&type=username'>Usernames</a><br><br><hr id='search_hr'>";

        while($row = mysqli_fetch_array($usersReturnedQuery)) {
            // user is from header.php
            $user_obj = new User($user['username'],$con);

            $button = "";
            $mutual_friends = "";
            if($user['username'] != $row['username']) {

                //Generate button depending on friendship status
                if($user_obj->isFriend($row['username']))
                    $button = "<input type='submit' name='" . $row['username'] . "' class='danger' value='Remove Friend'>";
                else if($user_obj->didReceiveRequest($row['username']))
                    $button = "<input type='submit' name='" . $row['username'] . "' class='warning' value='Respond to request'>";
                else if($user_obj->didSendRequest($row['username']))
                    $button = "<input type='submit' class='default' value='Request Sent'>";
                else
                    $button = "<input type='submit' name='" . $row['username'] . "' class='success' value='Add Friend'>";

                $mutual_friends = $user_obj->getMutalFriends($row['username']) . " friends in common";


                //Button forms
                // the button name was set to the user name
                if(isset($_POST[$row['username']])) {

                    if($user_obj->isFriend($row['username'])) {
                        $user_obj->removeFriend($row['username']);
                        //send me back to same page
                        header("Location: http://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]");
                    }
                    else if($user_obj->didReceiveRequest($row['username'])) {
                        header("Location: requests.php");
                    }
                    else if($user_obj->didSendRequest($row['username'])) {

                    }
                    else {
                        $user_obj->sendRequest($row['username']);
                        header("Location: http://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]");
                    }

                }



            }
            // the button name was set to the user name
            echo "<div class='search_result'>
					<div class='searchPageFriendButtons'>
						<form action='' method='POST'>
							" . $button . "
							<br>
						</form>
					</div>


					<div class='result_profile_pic'>
						<a href='" . $row['username'] ."'><img src='". $row['profile_pic'] ."' style='height: 100px;'></a>
					</div>

						<a href='" . $row['username'] ."'> " . $row['first_name'] . " " . $row['last_name'] . "
						<p id='grey'> " . $row['username'] ."</p>
						</a>
						<br>
						" . $mutual_friends ."<br>

				</div>
				<hr id='search_hr'>";

        } //End while
    } // end else


    ?>



</div>
