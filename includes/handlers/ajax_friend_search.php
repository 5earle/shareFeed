<?php
include("../../config/config.php");
include("../classes/User.php");
include("../classes/Post.php");

$query = $_POST['query'];
$userLoggedIn = $_POST['userLoggedIn'];
$names = explode(' ',$query);

//look inside this string for under score.if found then it must be a username
if(strpos($query, '_') !== false) {
    // search for jason. return jas,jas
    $usersReturn = mysqli_query($con,"SELECT * FROM users WHERE username LIKE '$query%' AND user_closed='no' LIMIT 8")
    or die(mysqli_error($con));

} else if(count($names) == 2) {
    $usersReturned = mysqli_query($con, "SELECT * FROM users WHERE (first_name LIKE '%$names[0]%' AND last_name LIKE '%$names[1]%') AND user_closed='no' LIMIT 8")
    or die(mysqli_error($con));
}
else {
    $usersReturned = mysqli_query($con, "SELECT * FROM users WHERE (first_name LIKE '%$names[0]%' OR last_name LIKE '%$names[0]%') AND user_closed='no' LIMIT 8")
    or die(mysqli_error($con));;
}

if($query != "") {
    while($row = mysqli_fetch_array($usersReturned)) {

        $user = new User($userLoggedIn,$con);

        if($row['username'] != $userLoggedIn) {
            $mutual_friends = $user->getMutalFriends($row['username']) . " friends in common";
        }
        else {
            $mutual_friends = "";
        }

        if($user->isFriend($row['username'])) {
            echo "<div class='resultDisplay'>
					<a href='messages.php?u=" . $row['username'] . "' style='color: #000'>
						<div class='liveSearchProfilePic'>
							<img src='". $row['profile_pic'] . "'>
						</div>

						<div class='liveSearchText'>
							".$row['first_name'] . " " . $row['last_name']. "
							<p style='margin: 0;'>". $row['username'] . "</p>
							<p id='grey'>".$mutual_friends . "</p>
						</div>
					</a>
				</div>";


        }


    }
}

?>