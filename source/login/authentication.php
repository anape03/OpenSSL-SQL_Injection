<?php
    include('../connection.php');
    $username = $_POST['username'];
    $password = $_POST['password'];
    
    // To prevent from mysqli injection    
    $username = stripcslashes($username);
    $password = stripcslashes($password);
    $username = mysqli_real_escape_string($con, $username);
    $password = mysqli_real_escape_string($con, $password);

    // Get user data
    $sql = "SELECT * FROM users WHERE username = '$username'";
    $result = mysqli_query($con, $sql);
    $row = mysqli_fetch_array($result, MYSQLI_ASSOC);
    $count = mysqli_num_rows($result);

    $locked = false; // is the user locked from too many failed loggin attempts
    $success = false;
    $pass_change = false; // user must change password
    $description = '{}';
    
    // Check login
    if($count == 1) {
        $description = json_decode($row['description'], true);
        $locked = $description['locked'] == '1' ? true : false;

        if ($locked) { // user is locked, must change password
            $success = false;
        } else {       // user isn't locked
            // Check how long it's been since last password change
            $currentTime = strtotime('now');
            $lastPassChange = strtotime($description['last_pass_change']);
            $difference_in_minutes = ($currentTime - $lastPassChange) / 60 % 60;

            if ($difference_in_minutes >= 5) { 
                // User must change password
                $success = false;
                $pass_change = true;
            } else {
                // Check password for login
                $existingHashFromDb = $row['password'];
                $isPasswordCorrect = password_verify($password, $existingHashFromDb);
                if ($isPasswordCorrect){
                    $success = true;
                } else {
                    $success = false;
                }
            }
            
        }
        
    } else {
        $success = false;
    }

    // Log event
    $sql = "INSERT INTO logging (username, success) VALUES ('".$username."', '".($success ? 1 : 0)."');";
    $result = mysqli_query($con, $sql);

    if($result) {
        if ($success){
            echo "<p id='successful-login'>Login successful.</p>";
        } else {
            if (!$pass_change){ // User was not required to change password
                echo "<p id='unsuccessful-login'>Login failed. Invalid username or password.</p>";
            } else { // User is required to change password
                echo "<p id='change-password'>Login failed. It's been too long since last password change.</p>";
                lockUser($con, $username, $description);
            }

            if ($locked) {
                $message = "User \'".$username."\' has beed locked. Please change your password.";
                echo "<script type='text/javascript'>alert('".$message."');</script>";
            } else {
                // Check loggin attempts:
                $sql = "SELECT * FROM logging WHERE username = '$username'";
                $result = mysqli_query($con, $sql);
                $rows = mysqli_fetch_all($result, MYSQLI_ASSOC);

                // Count latest consecutive failed loggin attempts for specific username
                $count = 0; 
                foreach (array_reverse($rows) as $row) {
                    if ($row['success'] == 0) {
                        $count++;
                    } else { // found last successful attempt => stop counting
                        break;
                    }
                }
                unset($row); // break the reference with the last element

                if ($count >= 3) { // 3 consecutive failed attempts for specific username
                    $message = "There were ".$count." failed attempts for user \'".$username."\', please change your password.";
                    echo "<script type='text/javascript'>alert('".$message."');</script>";

                    lockUser($con, $username, $description);
                }
            }

        }
    } else {
        echo "Error: ".$sql."<br>".mysqli_error($con);
    }

    function lockUser($con, $username, $description) {
        $newDescription = $description;
        $newDescription['locked'] = '1';

        $sql = "UPDATE users SET description = '".json_encode($newDescription)."' WHERE username = '".$username."';";
        $result = mysqli_query($con, $sql);

        if(!$result) {
            echo "Error: ".$sql."<br>".mysqli_error($con);
        }
    }
?>