

    $sql = "SELECT username FROM users WHERE username = '$username'";
    $sql_exists = "SELECT * FROM users WHERE username = '$username' AND password = '$password' AND approved = 1"; //TODO: not star
    $result = $conn->query($sql);

    if($result->num_rows > 0) {
        $result = $conn->query($sql_exists); 
       if ($result->num_rows < 1) {
        $warningMessage = 'Login failed with incorrect password for username ' . $username;
        $logger->warning($warningMessage);
        $error_message = 'Invalid username or password.';
       } else {
        $userFromDB = $result->fetch_assoc();
        session_regenerate_id(true);

        $_SESSION['authenticated'] = $username;
    //    $logger->info('New session begun for user: ' . $username);   

        if ($userFromDB['default_role_id'] == 1) {        
            $_SESSION['isSiteAdministrator'] = true;
            $logger->info('Administrator login by ' . $username);                
        }else{
            $_SESSION['isSiteAdministrator'] = false;
        }
        header("Location: index.php");
        exit(); }
    } else {
        $error_message = 'Invalid username or password.'; 
        $logger->warning('Login failed for nonexistent user: ' . $username); 
        //TODO: Track number of failed login attempts separately for rejection purposes 
    }

