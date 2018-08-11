<?php
require '../../config/config.php';

if(isset($_GET['post_id'])) {
    $post_id = $_GET['post_id'];
    console_log($post_id);
}

if(isset($_POST['result'])) {
    if($_POST['result'] == 'true')
        $query = mysqli_query($con, "UPDATE posts SET deleted='yes' WHERE id='$post_id'");
}

function console_log($data) {
    echo '<script>';
    echo 'console.log(' . json_encode($data) . ')';
    echo '</script>';/*echo '<pre>';
        var_dump($_SESSION);
        echo '</pre>'*/;
}

?>