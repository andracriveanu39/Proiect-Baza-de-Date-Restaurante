<?php
session_start();

// Check if the user is an administrator
if (!isset($_SESSION['rol']) || $_SESSION['rol'] != 'administrator') {
    header("Location: index.php"); // Redirect to homepage or login page
    exit();
}

// The rest of your clienti.php code remains the same
$db = mysqli_connect('localhost', 'root', '', 'restaurantedb') or die('Error connecting to MySQL server.');

$columns = [
    0 => 'client_id',
    2 => 'nume_client',
    3 => 'email',
    4 => 'data_inregistrare',
    5 => 'numar_comenzi',
    6 => 'numar_rezervari'
];
// Initialize sort columns and orders in session if not set
if (!isset($_SESSION['primary_sort'])) {
    $_SESSION['primary_sort'] = 'client_id';
    $_SESSION['primary_order'] = 'asc';
    $_SESSION['secondary_sort'] = null; // No secondary sort initially
    $_SESSION['secondary_order'] = 'asc';
}

// Handle column clicks
if (isset($_GET['sortby'])) {
    $clicked_column = isset($columns[$_GET['sortby']]) ? $columns[$_GET['sortby']] : 'client_id';

    if ($clicked_column == 'client_id') { // Reset to default on ID click
        $_SESSION['primary_sort'] = 'client_id';
        $_SESSION['primary_order'] = 'asc';
        $_SESSION['secondary_sort'] = null;
    } else if ($clicked_column == $_SESSION['primary_sort']) {
        // Toggle primary sort order
        $_SESSION['primary_order'] = ($_SESSION['primary_order'] == 'asc') ? 'desc' : 'asc';
    } else {
        // Make clicked column the primary sort, old primary becomes secondary
        $_SESSION['secondary_sort'] = $_SESSION['primary_sort'];
        $_SESSION['secondary_order'] = $_SESSION['primary_order'];
        $_SESSION['primary_sort'] = $clicked_column;
        $_SESSION['primary_order'] = 'asc';
    }
}

// Build ORDER BY clause
$order_by_clause = "ORDER BY " . $_SESSION['primary_sort'] . " " . strtoupper($_SESSION['primary_order']);
if ($_SESSION['secondary_sort'] !== null) {
    $order_by_clause .= ", " . $_SESSION['secondary_sort'] . " " . strtoupper($_SESSION['secondary_order']);
}

$query = "SELECT 
    c.client_id, 
    c.nume_client, 
    c.email, 
    c.data_inregistrare,
    (SELECT COUNT(*) FROM tblComenzi WHERE client_id = c.client_id) AS numar_comenzi,
    (SELECT COUNT(*) FROM tblRezervari WHERE client_id = c.client_id) AS numar_rezervari
FROM tblClienti c
WHERE c.rol = 'client'

$order_by_clause";

$result = mysqli_query($db, $query);

// New code to fetch and display details
$show_comenzi_client_id = isset($_GET['show_comenzi']) ? intval($_GET['show_comenzi']) : 0;
$show_rezervari_client_id = isset($_GET['show_rezervari']) ? intval($_GET['show_rezervari']) : 0;

?>

<!DOCTYPE html>
<html>
<head>
    <title>Clienti</title>
    <link rel="stylesheet" type="text/css" href="style.css">
    <style>
        .comenzi-client, .rezervari-client {
            display: none;
            margin-top: 10px;
            border-top: 1px solid #ccc;
            padding-top: 10px;
        }
        .comenzi-client h3, .rezervari-client h3 {
            font-size: 1.2em;
            margin-bottom: 5px;
        }
        .comenzi-client table, .rezervari-client table {
            width: 100%;
        }
        .comenzi-client th, .comenzi-client td,
        .rezervari-client th, .rezervari-client td {
            text-align: left;
            padding: 5px;
        }
         .numar-comenzi-clickable, .numar-rezervari-clickable {
            cursor: pointer;
            color: blue;
            text-decoration: underline;
        }
    </style>
</head>
<body class="background-login">
    <?php include('header.php'); ?>

    <div class="container">
        <div class="client-section">
            <h1 class="client-table-title">Clienti</h1>
            <div class="client-table-wrapper">
                <table class="client-list-table">
                    <thead>
                        <tr>
                            <th class="client-id-header">
                                <a href="clienti.php?sortby=0">ID</a>
                            </th>
                            <th class="nume-header">
                                <a href="clienti.php?sortby=2">Nume
                                    <?php
                                    if ($_SESSION['primary_sort'] == 'nume_client') echo '<span class="active-sort">' . ($_SESSION['primary_order'] == 'asc' ? '&#x25B2;' : '&#x25BC;') . '</span>';
                                    elseif ($_SESSION['secondary_sort'] == 'nume_client') echo '<span class="secondary-sort">' . ($_SESSION['secondary_order'] == 'asc' ? '&#x25B2;' : '&#x25BC;') . '</span>';
                                    else echo '&#x25B2;';
                                    ?>
                                </a>
                            </th>
                            <th class="email-header">
                                <a href="clienti.php?sortby=3">Email
                                    <?php
                                    if ($_SESSION['primary_sort'] == 'email') echo '<span class="active-sort">' . ($_SESSION['primary_order'] == 'asc' ? '&#x25B2;' : '&#x25BC;') . '</span>';
                                    elseif ($_SESSION['secondary_sort'] == 'email') echo '<span class="secondary-sort">' . ($_SESSION['secondary_order'] == 'asc' ? '&#x25B2;' : '&#x25BC;') . '</span>';
                                    else echo '&#x25B2;';
                                    ?>
                                </a>
                            </th>
                            <th class="data-inregistrare-header">
                                <a href="clienti.php?sortby=4">Înregistrat
                                    <?php
                                    if ($_SESSION['primary_sort'] == 'data_inregistrare') echo '<span class="active-sort">' . ($_SESSION['primary_order'] == 'asc' ? '&#x25B2;' : '&#x25BC;') . '</span>';
                                    elseif ($_SESSION['secondary_sort'] == 'data_inregistrare') echo '<span class="secondary-sort">' . ($_SESSION['secondary_order'] == 'asc' ? '&#x25B2;' : '&#x25BC;') . '</span>';
                                    else echo '&#x25B2;';
                                    ?>
                                </a>
                            </th>
                            <th class="numar-comenzi-header">
                                <a href="clienti.php?sortby=5">Comenzi
                                    <?php
                                    if ($_SESSION['primary_sort'] == 'numar_comenzi') echo '<span class="active-sort">' . ($_SESSION['primary_order'] == 'asc' ? '&#x25B2;' : '&#x25BC;') . '</span>';
                                    elseif ($_SESSION['secondary_sort'] == 'numar_comenzi') echo '<span class="secondary-sort">' . ($_SESSION['secondary_order'] == 'asc' ? '&#x25B2;' : '&#x25BC;') . '</span>';
                                    else echo '&#x25B2;';
                                    ?>
                                </a>
                            </th>
                            <th class="numar-rezervari-header">
                                <a href="clienti.php?sortby=6">Rezervări
                                    <?php
                                    if ($_SESSION['primary_sort'] == 'numar_rezervari') echo '<span class="active-sort">' . ($_SESSION['primary_order'] == 'asc' ? '&#x25B2;' : '&#x25BC;') . '</span>';
                                    elseif ($_SESSION['secondary_sort'] == 'numar_rezervari') echo '<span class="secondary-sort">' . ($_SESSION['secondary_order'] == 'asc' ? '&#x25B2;' : '&#x25BC;') . '</span>';
                                    else echo '&#x25B2;';
                                    ?>
                                </a>
                            </th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        while ($row = mysqli_fetch_assoc($result)) {
                        ?>
                            <tr>
                                <td class="client-id-cell"><?php echo $row['client_id']; ?></td>
                                <td class="nume-cell"><?php echo $row['nume_client']; ?></td>
                                <td class="email-cell"><?php echo $row['email']; ?></td>
                                <td class="data-inregistrare-cell"><?php echo $row['data_inregistrare']; ?></td>
                                <td class="numar-comenzi-cell">
                                    <?php if ($show_comenzi_client_id == $row['client_id']) { ?>
                                        <a href="clienti.php" class="numar-comenzi-clickable">
                                            <?php echo $row['numar_comenzi']; ?>
                                        </a>
                                    <?php } else { ?>
                                        <a href="clienti.php?show_comenzi=<?php echo $row['client_id']; ?>" class="numar-comenzi-clickable">
                                            <?php echo $row['numar_comenzi']; ?>
                                        </a>
                                    <?php } ?>
                                </td>
                                 <td class="numar-rezervari-cell">
                                    <?php if ($show_rezervari_client_id == $row['client_id']) { ?>
                                        <a href="clienti.php" class="numar-rezervari-clickable">
                                            <?php echo $row['numar_rezervari']; ?>
                                        </a>
                                    <?php } else { ?>
                                        <a href="clienti.php?show_rezervari=<?php echo $row['client_id']; ?>" class="numar-rezervari-clickable">
                                            <?php echo $row['numar_rezervari']; ?>
                                        </a>
                                    <?php } ?>
                                </td>
                            </tr>
                            <?php if ($show_comenzi_client_id == $row['client_id']) { ?>
                                <tr>
                                    <td colspan="7">
                                        <div class="comenzi-client" style="display: block;">
                                            <h3>Comenzi Client:</h3>
                                            <table>
                                                <thead>
                                                    <tr>
                                                        <th>Comanda ID</th>
                                                        <th>Restaurant</th>
                                                        <th>Data Comanda</th>
                                                        <th>Tip Plata</th>
                                                        <th>Status Comanda</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <?php
                                                    $client_id = $row['client_id'];
                                                    $query_comenzi = "SELECT 
                                                                        c.comanda_id,
                                                                        r.nume_restaurant,
                                                                        c.data_comanda,
                                                                        c.tip_plata,
                                                                        c.status_comanda
                                                                    FROM tblComenzi c
                                                                    JOIN tblRestaurante r ON c.restaurant_id = r.restaurant_id
                                                                    WHERE c.client_id = ?";
                                                    $stmt_comenzi = mysqli_prepare($db, $query_comenzi);
                                                    mysqli_stmt_bind_param($stmt_comenzi, "i", $client_id);
                                                    mysqli_stmt_execute($stmt_comenzi);
                                                    $result_comenzi = mysqli_stmt_get_result($stmt_comenzi);

                                                    while ($row_comanda = mysqli_fetch_assoc($result_comenzi)) {
                                                        echo "<tr>
                                                                <td>" . $row_comanda['comanda_id'] . "</td>
                                                                <td>" . htmlspecialchars($row_comanda['nume_restaurant']) . "</td>
                                                                <td>" . $row_comanda['data_comanda'] . "</td>
                                                                <td>" . htmlspecialchars($row_comanda['tip_plata']) . "</td>
                                                                <td>" . htmlspecialchars($row_comanda['status_comanda']) . "</td>
                                                              </tr>";
                                                    }
                                                    ?>
                                                </tbody>
                                            </table>
                                        </div>
                                    </td>
                                </tr>
                            <?php } ?>
                             <?php if ($show_rezervari_client_id == $row['client_id']) { ?>
                                <tr>
                                    <td colspan="7">
                                        <div class="rezervari-client" style="display: block;">
                                            <h3>Rezervari Client:</h3>
                                            <table>
                                                <thead>
                                                    <tr>
                                                        <th>Rezervare ID</th>
                                                        <th>Restaurant</th>
                                                        <th>Data si Ora</th>
                                                        <th>Numar Persoane</th>
                                                        <th>Status Rezervare</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <?php
                                                    $client_id = $row['client_id'];
                                                    $query_rezervari = "SELECT 
                                                                        rz.rezervare_id,
                                                                        r.nume_restaurant,
                                                                        rz.data_ora,
                                                                        rz.numar_persoane,
                                                                        rz.status_rezervare
                                                                    FROM tblRezervari rz
                                                                    JOIN tblRestaurante r ON rz.restaurant_id = r.restaurant_id
                                                                    WHERE rz.client_id = ?";
                                                    $stmt_rezervari = mysqli_prepare($db, $query_rezervari);
                                                    mysqli_stmt_bind_param($stmt_rezervari, "i", $client_id);
                                                    mysqli_stmt_execute($stmt_rezervari);
                                                    $result_rezervari = mysqli_stmt_get_result($stmt_rezervari);

                                                    while ($row_rezervare = mysqli_fetch_assoc($result_rezervari)) {
                                                        echo "<tr>
                                                                <td>" . $row_rezervare['rezervare_id'] . "</td>
                                                                <td>" . htmlspecialchars($row_rezervare['nume_restaurant']) . "</td>
                                                                <td>" . $row_rezervare['data_ora'] . "</td>
                                                                <td>" . $row_rezervare['numar_persoane'] . "</td>
                                                                <td>" . htmlspecialchars($row_rezervare['status_rezervare']) . "</td>
                                                              </tr>";
                                                    }
                                                    ?>
                                                </tbody>
                                            </table>
                                        </div>
                                    </td>
                                </tr>
                            <?php } ?>
                        <?php } ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

</body>
</html>