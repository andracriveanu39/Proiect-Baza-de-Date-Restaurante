<?php
session_start();

$db = mysqli_connect('localhost', 'root', '', 'restaurantedb') or die('Error connecting to MySQL server.');

$errors = array();

// Initialize variables
$username = "";
$password = "";
$login_type = "client"; // Default to client

if (isset($_POST['login_user'])) {
    $username = mysqli_real_escape_string($db, $_POST['username']);
    $password = mysqli_real_escape_string($db, $_POST['password']);
    $login_type = mysqli_real_escape_string($db, $_POST['login_type']); // Get login type

    if (empty($username)) {
        array_push($errors, "Username is required");
    }
    if (empty($password)) {
        array_push($errors, "Password is required");
    }

    if (count($errors) == 0) {
        if ($login_type == "client") {
            $query = "SELECT client_id, nume_client, rol, password FROM tblClienti WHERE nume_client = '$username'";
        } else if ($login_type == "administrator") {
            $query = "SELECT admin_id, nume_admin, rol, password, approved FROM tblAdmini WHERE nume_admin = '$username'"; // Get 'approved'
        }

        $result = mysqli_query($db, $query); // Line 32 is here
        $user = mysqli_fetch_assoc($result);

        if ($user) {
            // Verify the password 
            if ($password == $user['password']) {  // Direct comparison
                if ($login_type == "administrator" && !$user['approved']) {
                    array_push($errors, "Your account is not yet approved.");
                } else {
                    $_SESSION['username'] = $user[strpos(array_keys($user)[1], 'nume_') !== false ? array_keys($user)[1] : array_keys($user)[1]]; // Dynamic username
                    $_SESSION['rol'] = $user['rol'];
                    $_SESSION['success'] = "You are now logged in";
                    header('location: index.php');
                    exit(); // Important: Exit after redirect
                }
            } else {
                array_push($errors, "Wrong username/password combination");
            }
        } else {
            array_push($errors, "Wrong username/password combination");
        }
    }
}
?>

<?php include('errors.php'); ?>

<!DOCTYPE html>
<html>

<head>
    <title>Login</title>
    <link rel="stylesheet" type="text/css" href="style.css">
</head>

<body class="backgroundlogin">
    <div class="login-container">
        <div class="login-form">
            <h2>Login</h2>
            <form method="post" action="login.php">
                <div class="form-group">
                    <label>Username</label>
                    <input type="text" name="username" value="<?php echo $username; ?>">
                </div>
                <div class="form-group">
                    <label>Password</label>
                    <input type="password" name="password" value="<?php echo $password; ?>">
                </div>

                <div class="form-group">
                    <label>Login As:</label>
                    <select name="login_type">
                        <option value="client" <?php if ($login_type == 'client') echo 'selected'; ?>>Client</option>
                        <option value="administrator" <?php if ($login_type == 'administrator') echo 'selected'; ?>>Administrator</option>
                    </select>
                </div>

                <div class="form-group">
                    <button type="submit" class="btn" name="login_user">Login</button>
                </div>
                <p>
                    Not yet a member? <a href="#" onclick="showRegistrationOptions()">Sign up</a>
                </p>

                <div id="registration-options" style="display:none;">
                    <p>Register as:</p>
                    <a href="register.php?rol=client">Client</a> |
                    <a href="register.php?rol=administrator">Administrator</a>
                </div>

                <script>
                    function showRegistrationOptions() {
                        document.getElementById('registration-options').style.display = 'block';
                    }
                </script>

            </form>
        </div>
    </div>

</body>

</html>