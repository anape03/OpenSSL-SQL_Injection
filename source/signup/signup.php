<?php
    include('../connection.php');
    $username = $_POST['username'];
    $password = $_POST['password'];

    echo "username: ".$username;
    echo "password: ".$password;

    // to prevent from  mysqli injection
    $username = stripcslashes($username);
    $password = stripcslashes($password);
    $username = mysqli_real_escape_string($con, $username);
    $password = mysqli_real_escape_string($con, $password);

    $hashPassword = password_hash($password, PASSWORD_DEFAULT);

    $sql = "INSERT INTO users (username, password) VALUES ('".$username."', '".$hashPassword."');";
    $result = mysqli_query($con, $sql);

    if($result) {
        echo "<p id='successful-login'>Sign up was successful<p>";
    } else {
        echo "Error: " . $sql . "<br>" . mysqli_error($con);
    }
?>