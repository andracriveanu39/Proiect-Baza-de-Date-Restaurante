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
    $query = "SELECT c.comanda_id, cl.nume_client, r.nume_restaurant, c.data_comanda, c.tip_plata, c.status_comanda 
              FROM tblComenzi c
              JOIN tblClienti cl ON c.client_id = cl.client_id
              JOIN tblRestaurante r ON c.restaurant_id = r.restaurant_id
              WHERE c.comanda_id LIKE '%$search_term%'
                 OR cl.nume_client LIKE '%$search_term%'
                 OR r.nume_restaurant LIKE '%$search_term%'
                 OR c.data_comanda LIKE '%$search_term%'
                 OR c.tip_plata LIKE '%$search_term%'
                 OR c.status_comanda LIKE '%$search_term%'
              ORDER BY c.comanda_id DESC";
} else {
    $query = "SELECT c.comanda_id, cl.nume_client, r.nume_restaurant, c.data_comanda, c.tip_plata, c.status_comanda 
              FROM tblComenzi c
              JOIN tblClienti cl ON c.client_id = cl.client_id
              JOIN tblRestaurante r ON c.restaurant_id = r.restaurant_id
              ORDER BY c.comanda_id DESC";
}
$result = mysqli_query($db, $query);

// Handle status updates
if (isset($_GET['update_status'])) {
    $comanda_id = intval($_GET['update_status']);
    $new_status = $_GET['new_status'];
    $update_query = "UPDATE tblComenzi SET status_comanda = '$new_status' WHERE comanda_id = $comanda_id";
    mysqli_query($db, $update_query);
    header("Location: comenzi_admin.php");
    exit();
}

if (isset($_GET['delete'])) {
    $comanda_id = intval($_GET['delete']);
    $delete_query = "DELETE FROM tblComenzi WHERE comanda_id = $comanda_id";
    mysqli_query($db, $delete_query);
    header("Location: comenzi_admin.php");
    exit();
}
?>

<!DOCTYPE html>
<html>

<head>
    <title>Comenzi Management</title>
    <link rel="stylesheet" type="text/css" href="style.css">
</head>

<body class="background-comenzi">
    <?php include('header.php'); ?>

    <div class="container">
        <div class="client-section">
            <h1 class="client-table-title">Comenzi Management</h1>

            <form action="comenzi_admin.php" method="GET" class="search-form">
                <input type="text" name="search" placeholder="Search orders..." value="<?php echo htmlspecialchars($search_term); ?>">
                <button type="submit">Search</button>
            </form>

            <?php if (mysqli_num_rows($result) > 0) { ?>
                <table class="client-table">
                    <thead>
                        <tr>
                            <th>Comanda ID</th>
                            <th>Client Name</th>
                            <th>Restaurant Name</th>
                            <th>Data Comanda</th>
                            <th>Tip Plata</th>
                            <th>Status Comanda</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($row = mysqli_fetch_assoc($result)) {
                            $is_finalizata = ($row['status_comanda'] == 'finalizata');
                        ?>
                            <tr>
                                <td><?php echo $row['comanda_id']; ?></td>
                                <td><?php echo $row['nume_client']; ?></td>
                                <td><?php echo $row['nume_restaurant']; ?></td>
                                <td><?php echo $row['data_comanda']; ?></td>
                                <td><?php echo $row['tip_plata']; ?></td>
                                <td><?php echo $row['status_comanda']; ?></td>
                                <td>
                                    <?php if (!$is_finalizata) { ?>
                                        <a href="comenzi_admin.php?update_status=<?php echo $row['comanda_id']; ?>&new_status=finalizata" class="approve-btn">Finalizat</a>
                                    <?php } ?>
                                </td>
                            </tr>
                        <?php } ?>
                    </tbody>
                </table>
            <?php } else { ?>
                <p>No comenzi found.</p>
            <?php } ?>
        </div>
    </div>

</body>

</html>