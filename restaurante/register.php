<?php
session_start();

$db = mysqli_connect('localhost', 'root', '', 'restaurantedb') or die('Error connecting to MySQL server.');

$errors = array();
$username = "";
$email = "";

// Determine the rol from GET or POST
$rol = isset($_GET['rol']) ? $_GET['rol'] : (isset($_POST['rol']) ? $_POST['rol'] : 'client'); // Default to client

if (isset($_POST['reg_user'])) {
    $username = mysqli_real_escape_string($db, $_POST['username']);
    $email = mysqli_real_escape_string($db, $_POST['email']);
    $password_1 = mysqli_real_escape_string($db, $_POST['password_1']);
    $password_2 = mysqli_real_escape_string($db, $_POST['password_2']);
    $rol = mysqli_real_escape_string($db, $_POST['rol']);

    if (empty($username)) {
        array_push($errors, "Username is required");
    }
    if (empty($email)) {
        array_push($errors, "Email is required");
    }
    if (empty($password_1)) {
        array_push($errors, "Password is required");
    }
    if ($password_1 != $password_2) {
        array_push($errors, "The two passwords do not match");
    }

    //  Check if user exists in either table
    $user_check_query = "SELECT nume_client as username, email, 'client' as user_type FROM tblClienti WHERE nume_client='$username' OR email='$email' 
                    UNION 
                    SELECT nume_admin as username, email, 'administrator' as user_type FROM tblAdmini WHERE nume_admin='$username' OR email='$email' LIMIT 1";
    $result = mysqli_query($db, $user_check_query);
    $user = mysqli_fetch_assoc($result);

    if ($user) {
        if ($user[strpos(array_keys($user)[1], 'nume_') !== false ? array_keys($user)[1] : array_keys($user)[1]]  === $username) {
            array_push($errors, "Username already exists");
        }
        if ($user['email'] === $email) {
            array_push($errors, "Email already exists");
        }
    }

    if (count($errors) == 0) {
        $password = $password_1;  // Store password in plain text!

        if ($rol == 'client') {
            $query = "INSERT INTO tblClienti (nume_client, email, password, rol, data_inregistrare) 
                      VALUES('$username', '$email', '$password', '$rol', CURDATE())";
            mysqli_query($db, $query);
            $_SESSION['username'] = $username;
            $_SESSION['rol'] = $rol;
            $_SESSION['success'] = "You are now registered";
            header('location: index.php');

        } else if ($rol == 'administrator') {
            $query = "INSERT INTO tblAdmini (nume_admin, email, password, rol, data_inregistrare, approved) 
                      VALUES('$username', '$email', '$password', '$rol', CURDATE(), FALSE)"; // NOT APPROVED
            mysqli_query($db, $query);
            //  Don't log them in, show a message
            $_SESSION['success'] = "Your registration is successful, but your account needs to be approved by an existing administrator.";
            header('location: login.php');
        }
    }
}
?>
<!DOCTYPE html>
<html>

<head>
    <title>Registration</title>
    <link rel="stylesheet" type="text/css" href="style.css">
</head>

<body class="background-register">
    <div class="container">
        <div class="form-container">
            <h2>Register</h2>
            <form method="post" action="register.php">
                <?php include('errors.php'); ?>
                <div class="form-group">
                    <label>Username</label>
                    <input type="text" name="username" value="<?php echo $username; ?>">
                </div>
                <div class="form-group">
                    <label>Email</label>
                    <input type="email" name="email" value="<?php echo $email; ?>">
                </div>
                <div class="form-group">
                    <label>Password</label>
                    <input type="password" name="password_1">
                </div>
                <div class="form-group">
                    <label>Confirm password</label>
                    <input type="password" name="password_2">
                </div>
                <input type="hidden" name="rol" value="<?php echo $rol; ?>">
                <div class="form-group">
                    <button type="submit" class="btn" name="reg_user">Register</button>
                </div>
                <p>Already a member? <a href="login.php">Sign in</a></p>
            </form>
        </div>
    </div>
</body>

</html>