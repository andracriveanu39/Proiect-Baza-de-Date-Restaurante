<?php
session_start();

// Check if the user is logged in as a client
if (!isset($_SESSION['rol']) || $_SESSION['rol'] != 'client') {
    header("Location: login.php"); // Redirect to login if not a client
    exit();
}

$db = mysqli_connect('localhost', 'root', '', 'restaurantedb') or die('Error connecting to MySQL server.');

// Get the client's ID
$client_username = $_SESSION['username'];
$client_query = "SELECT client_id FROM tblClienti WHERE nume_client = '$client_username'";
$client_result = mysqli_query($db, $client_query);
$client_row = mysqli_fetch_assoc($client_result);
$client_id = $client_row['client_id'];

// Search functionality
$search_term = "";
if (isset($_GET['search'])) {
    $search_term = mysqli_real_escape_string($db, $_GET['search']);
    $query = "SELECT r.data_ora, r.numar_persoane, r.status_rezervare, res.nume_restaurant 
              FROM tblRezervari r
              JOIN tblRestaurante res ON r.restaurant_id = res.restaurant_id
              WHERE r.client_id = $client_id
                AND (res.nume_restaurant LIKE '%$search_term%'
                     OR r.data_ora LIKE '%$search_term%'
                     OR r.numar_persoane LIKE '%$search_term%'
                     OR r.status_rezervare LIKE '%$search_term%')
              ORDER BY r.data_ora DESC";
} else {
    $query = "SELECT r.data_ora, r.numar_persoane, r.status_rezervare, res.nume_restaurant 
              FROM tblRezervari r
              JOIN tblRestaurante res ON r.restaurant_id = res.restaurant_id
              WHERE r.client_id = $client_id
              ORDER BY r.data_ora DESC";
}
$result = mysqli_query($db, $query);
?>

<!DOCTYPE html>
<html>

<head>
    <title>Istoric Rezervari</title>
    <link rel="stylesheet" type="text/css" href="style.css">
</head>

<body class="background-rezervari">
    <?php include('header.php'); ?>

    <div class="container">
        <div class="client-section">
            <h1 class="client-table-title">Istoric Rezervari</h1>

            <form action="rezervari_clienti.php" method="GET" class="search-form">
                <input type="text" name="search" placeholder="Search your reservations..." value="<?php echo htmlspecialchars($search_term); ?>">
                <button type="submit">Search</button>
            </form>

            <?php if (mysqli_num_rows($result) > 0) { ?>
                <table class="client-table">
                    <thead>
                        <tr>
                            <th>Restaurant</th>
                            <th>Data si Ora</th>
                            <th>Numar Persoane</th>
                            <th>Status Rezervare</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($row = mysqli_fetch_assoc($result)) { ?>
                            <tr>
                                <td><?php echo $row['nume_restaurant']; ?></td>
                                <td><?php echo $row['data_ora']; ?></td>
                                <td><?php echo $row['numar_persoane']; ?></td>
                                <td><?php echo $row['status_rezervare']; ?></td>
                            </tr>
                        <?php } ?>
                    </tbody>
                </table>
            <?php } else { ?>
                <p>Nu aveti rezervari efectuate.</p>
            <?php } ?>
        </div>
    </div>

</body>

</html>