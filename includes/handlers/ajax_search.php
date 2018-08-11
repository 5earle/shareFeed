<?php
include("../../config/config.php");
include("../../includes/classes/User.php");

//get incomeing datas
$query = $_POST['query'];
$userLoggedIn = $_POST['userLoggedIn'];

// break up query string
$names = explode(" ",$query);

//If query contains an underscore, assume user is searching for usernames
if(strpos($query,'_') !== false){
    $usersReturnedQuery = mysqli_query($con, "SELECT * FROM users WHERE username LIKE '$query%' AND user_closed='no' LIMIT 8")
    or die(mysqli_error($con));
}

//If there are two words, assume they are first and last names respectively
else if(count($names) == 2){
    $usersReturnedQuery = mysqli_query($con, "SELECT * FROM users WHERE (first_name LIKE '$names[0]%' AND last_name LIKE '$names[1]%') AND user_closed='no' LIMIT 8")
    or die(mysqli_error($con));
}

//If query has one word only, search first names or last names
else {
    $usersReturnedQuery = mysqli_query($con, "SELECT * FROM users WHERE (first_name LIKE '$names[0]%' OR last_name LIKE '$names[0]%') AND user_closed='no' LIMIT 8")
    or die(mysqli_error($con));
}

if($query !== ""){
    while($row = mysqli_fetch_array($usersReturnedQuery)){
        $user = new User($userLoggedIn,$con);

        if($row['username'] != $userLoggedIn){
            $mutual_friends = $user->getMutalFriends($row['username']) . " friends in common";
        } else {
            $mutual_friends = "";
        }

        echo "<div class='resultDisplay'>
				<a href='" . $row['username'] . "' style='color: #1485BD'>
					<div class='liveSearchProfilePic'>
						<img src='" . $row['profile_pic'] ."'>
					</div>

					<div class='liveSearchText'>
						" . $row['first_name'] . " " . $row['last_name'] . "
						<p>" . $row['username'] ."</p>
						<p id='grey'>" . $mutual_friends ."</p>
					</div>
				</a>
				</div>";

    }
}
?>