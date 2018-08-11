<?php
/**
 * Created by PhpStorm.
 * User: dance
 * Date: 7/18/2018
 * Time: 11:12 AM
 */

class User{
    private $user;
    private $con;

    /**
     * User constructor.
     * @param $user
     * @param $con
     */
    public function __construct($user,$con){
        $this->console_log($con);
        $this->con = $con;


        $user_details_query = mysqli_query($con, "SELECT * FROM users WHERE username='$user'") or die(mysqli_error($con));
        $this->user = mysqli_fetch_array($user_details_query);
    }

    public function getUsername() {
        return $this->user['username'];
    }

    public function getNumPosts() {
        $username = $this->user['username'];
        $query = mysqli_query($this->con, "SELECT num_posts FROM users WHERE username='$username'");
        $row = mysqli_fetch_array($query);
        return $row['num_posts'];
    }

    public function getFirstAndLastName() {
        $username = $this->user['username'];
        $query = mysqli_query($this->con, "SELECT first_name, last_name FROM users WHERE username='$username'");
        $row = mysqli_fetch_array($query);
        return $row['first_name'] . " " . $row['last_name'];
    }
    public function getProfilePicture() {
        $username = $this->user['username'];
        $query = mysqli_query($this->con, "SELECT profile_pic FROM users WHERE username='$username'");
        $row = mysqli_fetch_array($query);
        return $row['profile_pic'];
    }

    public function isClosed() {
        $username = $this->user['username'];
        $query = mysqli_query($this->con, "SELECT user_closed FROM users WHERE username='$username'");
        $row = mysqli_fetch_array($query);

        if($row['user_closed'] == 'yes')
            return true;
        else
            return false;
    }

    public function isPrivate() {
        $username = $this->user['username'];
        $query = mysqli_query($this->con, "SELECT private FROM users WHERE username='$username'");
        $row = mysqli_fetch_array($query);

        if($row['private'] == 'yes')
            return true;
        else
            return false;
    }

    public function isFriend($username_to_check){
        $usernameComma = ','.$username_to_check.',';
        if((strstr($this->user['friend_array'],$usernameComma)) || $username_to_check == $this->user['username']){
            return true;
        } else {
            return false;
        }
    }
    public function didReceiveRequest($user_from){
        $user_to = $this->user['username'];
        $sql = "SELECT * FROM friend_request WHERE user_to='$user_to' AND user_from='$user_from'";
        $check_request_query = mysqli_query($this->con,$sql) or die(mysqli_error($this->con));
        if(mysqli_num_rows($check_request_query) > 0) {
            return true;
        }
        else {
            return false;
        }
    }
    public function didSendRequest($user_to){
        $user_from = $this->user['username'];
        $sql = "SELECT * FROM friend_request WHERE user_to='$user_to' AND user_from='$user_from'";
        $check_request_query = mysqli_query($this->con,$sql) or die (mysqli_error($this->con));
        if(mysqli_num_rows($check_request_query) > 0) {
            return true;
        }
        else {
            return false;
        }
    }
    public function removeFriend($user_to_remove) {
        $logged_in_user = $this->user['username'];
        $sql = "SELECT friend_array FROM users WHERE username='$user_to_remove'"; // get the friend to be removed
        $query = mysqli_query($this->con, $sql) or die(mysqli_error($this.$this->con));
        $row = mysqli_fetch_array($query);
        $friend_array_username = $row['friend_array'];


         // remove the friend conection from both parties
        $new_friend_array = str_replace($user_to_remove . ",", "", $this->user['friend_array']);
        $remove_friend = mysqli_query($this->con, "UPDATE users SET friend_array='$new_friend_array' WHERE username='$logged_in_user'");

        $new_friend_array = str_replace($this->user['username'] . ",", "", $friend_array_username);
        $remove_friend = mysqli_query($this->con, "UPDATE users SET friend_array='$new_friend_array' WHERE username='$user_to_remove'");
    }
    public function sendRequest($currentUserProfileName) {
        $user_from = $this->user['username'];
        $sql = "INSERT INTO friend_request VALUES('', '$currentUserProfileName', '$user_from')";
        $query = mysqli_query($this->con,$sql) or die(mysqli_error($this->con));
    }
    public function getMutalFriends($user_to_check){
        // get friend of the logged n user
        $mutal_friend = 0;
        $user_array = $this->user['friend_array'];
        $user_array_explode = explode(",",$user_array); // split string intoa array at given character

        // get firend of the prfoile that we are on
        $query = mysqli_query($this->con,"SELECT friend_array FROM users WHERE username='$user_to_check'")
        or die(mysqli_error($this->con));
        $row = mysqli_fetch_array($query);
        $user_to_check_array = $row['friend_array'];
        $user_to_check_array_explode = explode(",",$user_to_check_array);

        // check both friedns for mutal friend
        foreach ($user_array_explode as $a ){
            foreach ($user_to_check_array_explode as $b){
                if($a == $b && $a !=""){
                    $mutal_friend ++;
                }
            }
        }
        return $mutal_friend;
    }
    public function getFriendArray() {
        $username = $this->user['username'];
        $query = mysqli_query($this->con, "SELECT friend_array FROM users WHERE username='$username'");
        $row = mysqli_fetch_array($query);
        return $row['friend_array'];
    }
    public function console_log( $data ){
        echo '<script>';
        echo 'console.log('. json_encode( $data ) .')';
        echo '</script>';
    }
    public function getNumberOfFriendRequests(){
        $username = $this->user['username'];
        $query = mysqli_query($this->con, "SELECT * FROM friend_request WHERE user_to='$username'")
        or die(mysqli_error($this->con));

        return mysqli_num_rows($query);
    }


}
?>