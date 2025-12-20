<?php
session_start();

$db = mysqli_connect('localhost', 'root', '', 'restaurantedb') or die('Error connecting to MySQL server.');

// Get client ID (if client is logged in)
$client_id = null;
if (isset($_SESSION['username']) && $_SESSION['rol'] == 'client') {
    $client_username = $_SESSION['username'];
    $client_query = "SELECT client_id FROM tblClienti WHERE nume_client = '$client_username'";
    $client_result = mysqli_query($db, $client_query);
    $client_row = mysqli_fetch_assoc($client_result);
    $client_id = $client_row['client_id'];
}


$total_comanda = 0; // Initialize total

// Order Processing
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['place_order'])) {
    $restaurant_id = intval($_POST['restaurant_id']);
    $tip_plata = $_POST['tip_plata'];
    date_default_timezone_set('Europe/Bucharest');
    $data_comanda = date("Y-m-d H:i:s");
    $status_comanda = 'in_pregatire';

    $comanda_query = "INSERT INTO tblComenzi (client_id, restaurant_id, data_comanda, tip_plata, status_comanda) 
                      VALUES ($client_id, $restaurant_id, '$data_comanda', '$tip_plata', '$status_comanda')";
    mysqli_query($db, $comanda_query);
    $comanda_id = mysqli_insert_id($db);

    foreach ($_POST['produse'] as $produs_id => $cantitate) {
        $produs_id = intval($produs_id);
        $cantitate = intval($cantitate);
        if ($cantitate > 0) {
            $produs_query = "SELECT pret_produs FROM tblProdus WHERE produs_id = $produs_id";
            $produs_result = mysqli_query($db, $produs_query);
            $produs_row = mysqli_fetch_assoc($produs_result);
            $pret_produs = $produs_row['pret_produs'];
            $total_comanda += $pret_produs * $cantitate;

            $produs_comanda_query = "INSERT INTO tblProdusComenzi (comanda_id, produs_id, cantitate) 
                                     VALUES ($comanda_id, $produs_id, $cantitate)";
            mysqli_query($db, $produs_comanda_query);
        }
    }

    // Optionally, store $total_comanda in session or display it here before redirecting
    $_SESSION['total_comanda'] = $total_comanda; // Store in session

    //header("Location: comenzi_clienti.php");
    //exit();
    $order_placed = true; // Set a flag to indicate order placement
} else {
    $order_placed = false; // Initialize the flag
}

// Reservation Processing
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['make_reservation'])) {
    $restaurant_id = intval($_POST['restaurant_id']);
    $data_ora = $_POST['data_ora'];
    $numar_persoane = intval($_POST['numar_persoane']);
    $status_rezervare = 'in_asteptare'; // Or 'in_pregatire', in functie de logica ta

    // Validate date and time
    date_default_timezone_set('Europe/Bucharest'); // Set timezone for Bucharest
    $selected_datetime = strtotime($data_ora);
    $current_datetime = time();

    if ($selected_datetime < $current_datetime - 60000) { // Subtract 1 minute (60000 milliseconds)
        echo "<script>alert('Nu poti face o rezervare intr-o data/ora trecuta.'); window.location.href='restaurante_clienti.php';</script>";
        exit();
    }

    $rezervare_query = "INSERT INTO tblRezervari (client_id, restaurant_id, data_ora, numar_persoane, status_rezervare) 
                      VALUES ($client_id, $restaurant_id, '$data_ora', $numar_persoane, '$status_rezervare')";
    mysqli_query($db, $rezervare_query);

    header("Location: rezervari_clienti.php"); // Redirect to reservation history
    exit();
}


// Fetch restaurants
$query = "SELECT 
              r.restaurant_id, 
              r.nume_restaurant, 
              r.oras, 
              r.tip_bucatarie, 
              r.livrare_disponibila,
              COUNT(pr.produs_id) as nr_feluri
          FROM tblRestaurante r
          LEFT JOIN tblProdusRestaurant pr ON r.restaurant_id = pr.restaurant_id";

// Adaugă condiția pentru a exclude restaurantele dezactivate
if (isset($_SESSION['restaurante_dezactivate']) && count($_SESSION['restaurante_dezactivate']) > 0) {
    $ids_dezactivate = implode(',', array_map('intval', $_SESSION['restaurante_dezactivate']));
    $query .= " WHERE r.restaurant_id NOT IN ($ids_dezactivate)";
}

$query .= " GROUP BY r.restaurant_id";

$result = mysqli_query($db, $query); ?>

<!DOCTYPE html>
<html>

<head>
    <title>Restaurante</title>
    <link rel="stylesheet" type="text/css" href="style.css">
    <style>
        .produse-restaurant {
            display: none;
            margin-top: 10px;
            border-top: 1px solid #ccc;
            padding-top: 10px;
        }

        .produse-restaurant h3 {
            font-size: 1.2em;
            margin-bottom: 5px;
        }

        .produse-restaurant table {
            width: 100%;
        }

        .produse-restaurant th,
        .produse-restaurant td {
            text-align: left;
            padding: 5px;
        }

        .nr-feluri-clickable {
            cursor: pointer;
            color: blue;
            text-decoration: underline;
        }

        .comenzi-rezervari {
            margin-top: 20px;
            padding: 10px;
            border: 1px solid #ddd;
            background-color: #f9f9f9;
        }

        .order-form,
        .reservation-form {
            display: none;
            margin-top: 20px;
            padding: 10px;
            border: 1px solid #ddd;
            background-color: #f9f9f9;
        }
    </style>
    <script>
        function showOrderForm(restaurantId) {
            var formId = 'order-form-' + restaurantId;
            document.getElementById(formId).style.display = 'block';
        }

        function showReservationForm(restaurantId) {
            var formId = 'reservation-form-' + restaurantId;
            document.getElementById(formId).style.display = 'block';
        }

        // Function to validate reservation date and time
        function validateReservation(formId) {
            const form = document.getElementById(formId);
            const dataOraInput = form.querySelector('input[name="data_ora"]');
            const dataOraValue = dataOraInput.value;

            if (!dataOraValue) {
                alert('Trebuie sa selectezi data si ora.');
                return false;
            }

            const selectedDateTime = new Date(dataOraValue).getTime();
            const currentTime = new Date().getTime(); // Current timestamp


            if (selectedDateTime < currentTime - 60000) { // Subtract 1 minute (60000 milliseconds)
                alert('Nu poti face o rezervare intr-o data/ora trecuta.');
                return false;
            }

            return true;
        }

    </script>
</head>

<body class="background-restaurante">
    <?php include('header.php'); ?>

    <div class="container">
        <h1>Lista Restaurante</h1>

        <table>
            <thead>
                <tr>
                    <th>Nume</th>
                    <th>Oraș</th>
                    <th>Tip Bucătărie</th>
                    <th>Livrare Disponibilă</th>
                    <th>Nr. Feluri</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($row = mysqli_fetch_assoc($result)) { ?>
                    <tr>
                        <td><?php echo $row['nume_restaurant']; ?></td>
                        <td><?php echo $row['oras']; ?></td>
                        <td><?php echo $row['tip_bucatarie']; ?></td>
                        <td><?php echo $row['livrare_disponibila'] ? 'Da' : 'Nu'; ?></td>
                        <td>
                            <a href="restaurante_clienti.php?show_produse=<?php echo $row['restaurant_id']; ?>" class="nr-feluri-clickable">
                                <?php echo $row['nr_feluri']; ?>
                            </a>
                        </td>
                    </tr>
                    <?php if (isset($_GET['show_produse']) && $_GET['show_produse'] == $row['restaurant_id']) { ?>
                        <tr>
                            <td colspan="5">
                                <div class="produse-restaurant" style="display: block;">
                                    <h3>Produse Disponibile:</h3>
                                    <table>
                                        <thead>
                                            <tr>
                                                <th>Nume Produs</th>
                                                <th>Preț</th>
                                                <th>Categorie</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php
                                            $restaurant_id = $row['restaurant_id'];
                                            $query_produse = "SELECT p.produs_id, p.nume_produs, p.pret_produs, p.categorie 
                                                            FROM tblProdus p
                                                            JOIN tblProdusRestaurant pr ON p.produs_id = pr.produs_id
                                                            WHERE pr.restaurant_id = $restaurant_id";
                                            $result_produse = mysqli_query($db, $query_produse);
                                            while ($row_produs = mysqli_fetch_assoc($result_produse)) {
                                                echo "<tr>
                                                        <td>" . $row_produs['nume_produs'] . "</td>
                                                        <td>" . $row_produs['pret_produs'] . "</td>
                                                        <td>" . $row_produs['categorie'] . "</td>
                                                    </tr>";
                                            }
                                            ?>
                                        </tbody>
                                    </table>
                                </div>
                            </td>
                        </tr>
                    <?php } ?>

                    <?php if (isset($_SESSION['rol']) && $_SESSION['rol'] == 'client') { ?>
                        <tr>
                            <td colspan="5">
                                <div class="comenzi-rezervari">
                                    <h3>Comenzi și Rezervări</h3>
                                    <p>
                                        <a href="#" onclick="showOrderForm(<?php echo $row['restaurant_id']; ?>)">Comandă</a> |
                                        <a href="#" onclick="showReservationForm(<?php echo $row['restaurant_id']; ?>)">Rezervă</a>
                                    </p>
                                </div>

                                <div class="order-form" id="order-form-<?php echo $row['restaurant_id']; ?>">
                                    <h4>Comanda de la <?php echo $row['nume_restaurant']; ?></h4>
                                    <form method="post">
                                        <input type="hidden" name="restaurant_id" value="<?php echo $row['restaurant_id']; ?>">
                                        <table>
                                            <thead>
                                                <tr>
                                                    <th>Produs</th>
                                                    <th>Pret</th>
                                                    <th>Cantitate</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php
                                                $restaurant_id = $row['restaurant_id'];
                                                $menu_query = "SELECT p.produs_id, p.nume_produs, p.pret_produs 
                                                               FROM tblProdus p
                                                               JOIN tblProdusRestaurant pr ON p.produs_id = pr.produs_id
                                                               WHERE pr.restaurant_id = $restaurant_id";
                                                $menu_result = mysqli_query($db, $menu_query);
                                                while ($menu_row = mysqli_fetch_assoc($menu_result)) {
                                                    ?>
                                                    <tr>
                                                        <td><?php echo $menu_row['nume_produs']; ?></td>
                                                        <td><?php echo $menu_row['pret_produs']; ?></td>
                                                        <td>
                                                            <input type="number" name="produse[<?php echo $menu_row['produs_id']; ?>]" value="0" min="0">
                                                        </td>
                                                    </tr>
                                                <?php } ?>
                                            </tbody>
                                        </table>

                                        <label for="tip_plata">Tip Plata:</label>
                                        <select name="tip_plata" id="tip_plata">
                                            <option value="cash">Cash</option>
                                            <option value="card">Card</option>
                                        </select>

                                        <button type="submit" name="place_order">Plaseaza Comanda</button>

                                        <?php if ($order_placed) { ?>
                                            <p>Total Comanda: <?php echo number_format($total_comanda, 2); ?> lei</p>
                                        <?php } ?>
                                    </form>
                                </div>

                                <div class="reservation-form" id="reservation-form-<?php echo $row['restaurant_id']; ?>">
                                    <h4>Rezervare la <?php echo $row['nume_restaurant']; ?></h4>
                                    <form method="post" onsubmit="return validateReservation('reservation-form-<?php echo $row['restaurant_id']; ?>');">
                                        <input type="hidden" name="restaurant_id" value="<?php echo $row['restaurant_id']; ?>">
                                        <label for="data_ora">Data si Ora:</label>
                                        <input type="datetime-local" name="data_ora" id="data_ora" required min="<?php echo date('Y-m-d\TH:i'); ?>">

                                        <label for="numar_persoane">Numar Persoane:</label>
                                        <input type="number" name="numar_persoane" id="numar_persoane" min="1" required>

                                        <button type="submit" name="make_reservation">Rezervă</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    <?php } ?>

                <?php } ?>
            </tbody>
        </table>
    </div>
</body>

</html>