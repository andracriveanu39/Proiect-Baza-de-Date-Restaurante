<?php
session_start();

// Check if the user is logged in as a client
if (!isset($_SESSION['rol']) || $_SESSION['rol'] != 'client') {
    header("Location: login.php");
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
    $query = "SELECT c.comanda_id, r.nume_restaurant, c.data_comanda, c.tip_plata, c.status_comanda,
                     p.nume_produs, p.pret_produs, pc.cantitate
              FROM tblComenzi c
              JOIN tblRestaurante r ON c.restaurant_id = r.restaurant_id
              JOIN tblProdusComenzi pc ON c.comanda_id = pc.comanda_id
              JOIN tblProdus p ON pc.produs_id = p.produs_id
              WHERE c.client_id = $client_id
                AND (r.nume_restaurant LIKE '%$search_term%' 
                     OR c.data_comanda LIKE '%$search_term%'
                     OR c.tip_plata LIKE '%$search_term%'
                     OR p.nume_produs LIKE '%$search_term%'
                     OR c.status_comanda LIKE '%$search_term%')
              ORDER BY c.data_comanda DESC";
} else {
    $query = "SELECT c.comanda_id, r.nume_restaurant, c.data_comanda, c.tip_plata, c.status_comanda,
                     p.nume_produs, p.pret_produs, pc.cantitate
              FROM tblComenzi c
              JOIN tblRestaurante r ON c.restaurant_id = r.restaurant_id
              JOIN tblProdusComenzi pc ON c.comanda_id = pc.comanda_id
              JOIN tblProdus p ON pc.produs_id = p.produs_id
              WHERE c.client_id = $client_id
              ORDER BY c.data_comanda DESC";
}

$result = mysqli_query($db, $query);

// Organize the results into orders
$orders = array();
while ($row = mysqli_fetch_assoc($result)) {
    $comanda_id = $row['comanda_id'];
    if (!isset($orders[$comanda_id])) {
        $orders[$comanda_id] = array(
            'nume_restaurant' => $row['nume_restaurant'],
            'data_comanda' => $row['data_comanda'],
            'tip_plata' => $row['tip_plata'],
            'status_comanda' => $row['status_comanda'],
            'produse' => array(),
            'total_pret' => 0
        );
    }
    $orders[$comanda_id]['produse'][] = array(
        'nume_produs' => $row['nume_produs'],
        'pret_produs' => $row['pret_produs'],
        'cantitate' => $row['cantitate']
    );
    $orders[$comanda_id]['total_pret'] += $row['pret_produs'] * $row['cantitate'];
}
?>

<!DOCTYPE html>
<html>

<head>
    <title>Istoric Comenzi</title>
    <link rel="stylesheet" type="text/css" href="style.css">
</head>

<body class="background-comenzi">
    <?php include('header.php'); ?>

    <div class="container">
        <div class="client-section">
            <h1 class="client-table-title">Istoric Comenzi</h1>

            <form action="comenzi_clienti.php" method="GET" class="search-form">
                <input type="text" name="search" placeholder="Search your orders..." value="<?php echo htmlspecialchars($search_term); ?>">
                <button type="submit">Search</button>
            </form>

            <?php if (count($orders) > 0) { ?>
                <table class="client-table">
                    <thead>
                        <tr>
                            <th>Restaurant</th>
                            <th>Data Comanda</th>
                            <th>Tip Plata</th>
                            <th>Produse Comandate</th>
                            <th>Total Pret</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($orders as $order) { ?>
                            <tr>
                                <td><?php echo $order['nume_restaurant']; ?></td>
                                <td><?php echo $order['data_comanda']; ?></td>
                                <td><?php echo $order['tip_plata']; ?></td>
                                <td>
                                    <?php
                                    foreach ($order['produse'] as $produs) {
                                        echo $produs['nume_produs'] . ' (' . $produs['cantitate'] . ')<br>';
                                    }
                                    ?>
                                </td>
                                <td><?php echo number_format($order['total_pret'], 2); ?></td>
                                <td><?php echo $order['status_comanda']; ?></td>
                            </tr>
                        <?php } ?>
                    </tbody>
                </table>
            <?php } else { ?>
                <p>Nu aveti comenzi efectuate.</p>
            <?php } ?>
        </div>
    </div>

</body>

</html>