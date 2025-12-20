<?php
session_start();

// Check if the user is an administrator
if (!isset($_SESSION['rol']) || $_SESSION['rol'] != 'administrator') {
    header("Location: index.php");
    exit();
}

$db = mysqli_connect('localhost', 'root', '', 'restaurantedb') or die('Error connecting to MySQL server.');

// Search functionality
$search_term = "";
if (isset($_GET['search'])) {
    $search_term = mysqli_real_escape_string($db, $_GET['search']);
    $query = "SELECT r.rezervare_id, cl.nume_client, res.nume_restaurant, r.data_ora, r.numar_persoane, r.status_rezervare
              FROM tblRezervari r
              JOIN tblClienti cl ON r.client_id = cl.client_id
              JOIN tblRestaurante res ON r.restaurant_id = res.restaurant_id
              WHERE r.rezervare_id LIKE '%$search_term%'
                 OR cl.nume_client LIKE '%$search_term%'
                 OR res.nume_restaurant LIKE '%$search_term%'
                 OR r.data_ora LIKE '%$search_term%'
                 OR r.numar_persoane LIKE '%$search_term%'
                 OR r.status_rezervare LIKE '%$search_term%'
              ORDER BY r.rezervare_id DESC";
} else {
    $query = "SELECT r.rezervare_id, cl.nume_client, res.nume_restaurant, r.data_ora, r.numar_persoane, r.status_rezervare
              FROM tblRezervari r
              JOIN tblClienti cl ON r.client_id = cl.client_id
              JOIN tblRestaurante res ON r.restaurant_id = res.restaurant_id
              ORDER BY r.rezervare_id DESC";
}
$result = mysqli_query($db, $query);

// Handle status updates
if (isset($_GET['update_status'])) {
    $rezervare_id = intval($_GET['update_status']);
    $new_status = $_GET['new_status'];
    $update_query = "UPDATE tblRezervari SET status_rezervare = '$new_status' WHERE rezervare_id = $rezervare_id";
    mysqli_query($db, $update_query);
    header("Location: rezervari_admin.php");
    exit();
}

if (isset($_GET['delete'])) {
    $rezervare_id = intval($_GET['delete']);
    $delete_query = "DELETE FROM tblRezervari WHERE rezervare_id = $rezervare_id";
    mysqli_query($db, $delete_query);
    header("Location: rezervari_admin.php");
    exit();
}
?>

<!DOCTYPE html>
<html>

<head>
    <title>Rezervari Management</title>
    <link rel="stylesheet" type="text/css" href="style.css">
</head>

<body class="background-rezervari">
    <?php include('header.php'); ?>

    <div class="container">
        <div class="client-section">
            <h1 class="client-table-title">Rezervari Management</h1>

            <form action="rezervari_admin.php" method="GET" class="search-form">
                <input type="text" name="search" placeholder="Search reservations..." value="<?php echo htmlspecialchars($search_term); ?>">
                <button type="submit">Search</button>
            </form>

            <?php if (mysqli_num_rows($result) > 0) { ?>
                <table class="client-table">
                    <thead>
                        <tr>
                            <th>Rezervare ID</th>
                            <th>Client Name</th>
                            <th>Restaurant Name</th>
                            <th>Data Ora</th>
                            <th>Numar Persoane</th>
                            <th>Status Rezervare</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($row = mysqli_fetch_assoc($result)) {
                            $is_final = ($row['status_rezervare'] == 'confirmata' || $row['status_rezervare'] == 'anulata');
                        ?>
                            <tr>
                                <td><?php echo $row['rezervare_id']; ?></td>
                                <td><?php echo $row['nume_client']; ?></td>
                                <td><?php echo $row['nume_restaurant']; ?></td>
                                <td><?php echo $row['data_ora']; ?></td>
                                <td><?php echo $row['numar_persoane']; ?></td>
                                <td><?php echo $row['status_rezervare']; ?></td>
                                <td>
                                    <?php if (!$is_final) { ?>
                                        <a href="rezervari_admin.php?update_status=<?php echo $row['rezervare_id']; ?>&new_status=confirmata" class="approve-btn">Confirm</a>
                                        <a href="rezervari_admin.php?update_status=<?php echo $row['rezervare_id']; ?>&new_status=anulata" class="refuse-btn">Anuleaza</a>
                                    <?php } ?>
                                </td>
                            </tr>
                        <?php } ?>
                    </tbody>
                </table>
            <?php } else { ?>
                <p>No rezervari found.</p>
            <?php } ?>
        </div>
    </div>

</body>

</html>