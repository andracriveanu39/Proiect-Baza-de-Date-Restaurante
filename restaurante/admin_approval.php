<?php
session_start();

// Check if the user is an administrator
if (!isset($_SESSION['rol']) || $_SESSION['rol'] != 'administrator') {
    header("Location: index.php"); // Redirect to homepage or login page
    exit();
}

$db = mysqli_connect('localhost', 'root', '', 'restaurantedb') or die('Error connecting to MySQL server.');

// Fetch all administrators
$query = "SELECT admin_id, nume_admin, email, approved FROM tblAdmini";
$result = mysqli_query($db, $query);

if (isset($_GET['approve'])) {
    $admin_id = intval($_GET['approve']);
    $approve_query = "UPDATE tblAdmini SET approved = TRUE WHERE admin_id = $admin_id";
    mysqli_query($db, $approve_query);
    header("Location: admin_approval.php");
    exit();
}

if (isset($_GET['refuse'])) {
    $admin_id = intval($_GET['refuse']);
    $refuse_query = "DELETE FROM tblAdmini WHERE admin_id = $admin_id";
    mysqli_query($db, $refuse_query);
    header("Location: admin_approval.php");
    exit();
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Admin Management</title>
    <link rel="stylesheet" type="text/css" href="style.css">
</head>
<body class="background-login">
    <?php include('header.php'); ?>

    <div class="container">
        <div class="client-section">
            <h1 class="client-table-title">Admin Management</h1>
            <?php if (mysqli_num_rows($result) > 0) { ?>
                <table class="client-table">
                    <thead>
                        <tr>
                            <th>Admin ID</th>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($row = mysqli_fetch_assoc($result)) { ?>
                            <tr>
                                <td><?php echo $row['admin_id']; ?></td>
                                <td><?php echo $row['nume_admin']; ?></td>
                                <td><?php echo $row['email']; ?></td>
                                <td><?php echo $row['approved'] ? 'Approved' : 'Pending'; ?></td>
                                <td> 
                                    <?php if (!$row['approved']) { ?>
                                        <a href="admin_approval.php?approve=<?php echo $row['admin_id']; ?>" class="approve-btn">Approve</a>
                                        <a href="admin_approval.php?refuse=<?php echo $row['admin_id']; ?>" class="refuse-btn">Refuse</a>
                                    <?php } ?>
                                </td>
                            </tr>
                        <?php } ?>
                    </tbody>
                </table>
            <?php } else { ?>
                <p>No administrators found.</p>
            <?php } ?>
        </div>
    </div>

</body>

</html>