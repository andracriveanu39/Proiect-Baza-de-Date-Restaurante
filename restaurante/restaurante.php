<?php
session_start();
// Verificare strictă a rolului
if (!isset($_SESSION['rol']) || $_SESSION['rol'] !== 'administrator') {
    header("Location: index.php");
    exit();
}

$db = mysqli_connect('localhost', 'root', '', 'restaurantedb') or die('Error connecting to MySQL server.');

// Funcție pentru a afișa mesajele de eroare
function display_error($error, $restaurant_id = null) {
    if ($restaurant_id !== null) {
        echo "<div class='error' id='error_{$restaurant_id}'>$error</div>";
    } else {
        echo "<div class='error'>$error</div>";
    }
}

// Array pentru a ține minte ID-urile restaurantelor "dezactivate" (doar în sesiune)
if (!isset($_SESSION['restaurante_dezactivate'])) {
    $_SESSION['restaurante_dezactivate'] = array();
}

// Definim coloanele pentru sortare
$columns = [
    0 => 'restaurant_id',
    1 => 'nume_restaurant',
    2 => 'oras',
    3 => 'tip_bucatarie',
    4 => 'livrare_disponibila',
    5 => 'nr_feluri'
];

// Inițializare variabile de sesiune pentru sortare (dacă nu există)
if (!isset($_SESSION['primary_sort_rest'])) {
    $_SESSION['primary_sort_rest'] = 'restaurant_id';
    $_SESSION['primary_order_rest'] = 'asc';
    $_SESSION['secondary_sort_rest'] = null;
    $_SESSION['secondary_order_rest'] = 'asc';
}

// Handle column clicks
if (isset($_GET['sortby_rest'])) {
    $clicked_column = isset($columns[$_GET['sortby_rest']]) ? $columns[$_GET['sortby_rest']] : 'restaurant_id';

    if ($clicked_column == 'restaurant_id') {
        $_SESSION['primary_sort_rest'] = 'restaurant_id';
        $_SESSION['primary_order_rest'] = 'asc';
        $_SESSION['secondary_sort_rest'] = null;
    } else if ($clicked_column == $_SESSION['primary_sort_rest']) {
        $_SESSION['primary_order_rest'] = ($_SESSION['primary_order_rest'] == 'asc') ? 'desc' : 'asc';
    } else {
        $_SESSION['secondary_sort_rest'] = $_SESSION['primary_sort_rest'];
        $_SESSION['secondary_order_rest'] = $_SESSION['primary_order_rest'];
        $_SESSION['primary_sort_rest'] = $clicked_column;
        $_SESSION['primary_order_rest'] = 'asc';
    }
}

// Build ORDER BY clause
$order_by_clause = "ORDER BY " . $_SESSION['primary_sort_rest'] . " " . strtoupper($_SESSION['primary_order_rest']);
if ($_SESSION['secondary_sort_rest'] !== null) {
    $order_by_clause .= ", " . $_SESSION['secondary_sort_rest'] . " " . strtoupper($_SESSION['secondary_order_rest']);
}

// Procesare (simulare) dezactivare restaurant
if (isset($_GET['dezactivare'])) {
    $restaurant_id = intval($_GET['dezactivare']);
    if (!in_array($restaurant_id, $_SESSION['restaurante_dezactivate'])) {
        $_SESSION['restaurante_dezactivate'][] = $restaurant_id;
    }
    header("Location: restaurante.php");
    exit();
}

// Procesare (simulare) activare restaurant
if (isset($_GET['activare'])) {
    $restaurant_id = intval($_GET['activare']);
    $key = array_search($restaurant_id, $_SESSION['restaurante_dezactivate']);
    if ($key !== false) {
        unset($_SESSION['restaurante_dezactivate'][$key]);
    }
    header("Location: restaurante.php");
    exit();
}

// Procesare ștergere restaurant (MODIFICAT: acum șterge fizic)
if (isset($_GET['delete'])) {
    $restaurant_id = intval($_GET['delete']);
    $query = "DELETE FROM tblRestaurante WHERE restaurant_id = ?";
    $stmt = mysqli_prepare($db, $query);
    mysqli_stmt_bind_param($stmt, "i", $restaurant_id);
    if (mysqli_stmt_execute($stmt)) {
        $success_message = "Restaurantul a fost șters.";
    } else {
        $error_message = "Eroare la ștergerea restaurantului.";
    }
//Resetare auto increment
if (mysqli_stmt_execute($stmt)) {
        $success_message = "Restaurantul a fost șters.";

        // Află cel mai mare ID rămas
        $query_max_id = "SELECT MAX(restaurant_id) FROM tblRestaurante";
        $result_max_id = mysqli_query($db, $query_max_id);
        $max_id_row = mysqli_fetch_row($result_max_id);
        $max_id = $max_id_row[0];

        // Resetează AUTO_INCREMENT la următorul ID disponibil
        $query_reset_ai = "ALTER TABLE tblRestaurante AUTO_INCREMENT = " . ($max_id + 1);
        mysqli_query($db, $query_reset_ai);

    } else {
        $error_message = "Eroare la ștergerea restaurantului.";
    }
    $action_performed = true;}
    
// Funcție pentru a verifica dacă există deja un restaurant cu același nume
function existaRestaurantCuNume($db, $nume_restaurant, $restaurant_id = null) {
    $query = "SELECT restaurant_id FROM tblRestaurante WHERE nume_restaurant = ?";
    if ($restaurant_id !== null) {
        $query .= " AND restaurant_id != ?";
    }
    $stmt = mysqli_prepare($db, $query);
    if ($restaurant_id !== null) {
        mysqli_stmt_bind_param($stmt, "si", $nume_restaurant, $restaurant_id);
    } else {
        mysqli_stmt_bind_param($stmt, "s", $nume_restaurant);
    }
    mysqli_stmt_execute($stmt);
    mysqli_stmt_store_result($stmt);
    return mysqli_stmt_num_rows($stmt) > 0;
}

// Procesare adăugare restaurant
if (isset($_POST['adauga_restaurant'])) {
    $nume_restaurant = mysqli_real_escape_string($db, $_POST['nume_restaurant']);
    $oras = mysqli_real_escape_string($db, $_POST['oras']);
    $tip_bucatarie = mysqli_real_escape_string($db, $_POST['tip_bucatarie']);
    $livrare_disponibila = isset($_POST['livrare_disponibila']) ? 1 : 0;

    if (existaRestaurantCuNume($db, $nume_restaurant)) {
        display_error("Există deja un restaurant cu acest nume.");
    } else {
        $query = "INSERT INTO tblRestaurante (nume_restaurant, oras, tip_bucatarie, livrare_disponibila) VALUES (?, ?, ?, ?)";
        $stmt = mysqli_prepare($db, $query);
        mysqli_stmt_bind_param($stmt, "sssi", $nume_restaurant, $oras, $tip_bucatarie, $livrare_disponibila);

        if (mysqli_stmt_execute($stmt)) {
            header("Location: restaurante.php");
            exit();
        } else {
            display_error("Eroare la adăugarea restaurantului.");
        }
    }
}

// Procesare modificare restaurant
if (isset($_POST['modifica_restaurant'])) {
    $restaurant_id = intval($_POST['restaurant_id']);
    $nume_restaurant = mysqli_real_escape_string($db, $_POST['nume_restaurant']);
    $oras = mysqli_real_escape_string($db, $_POST['oras']);
    $tip_bucatarie = mysqli_real_escape_string($db, $_POST['tip_bucatarie']);
    $livrare_disponibila = isset($_POST['livrare_disponibila']) ? 1 : 0;

    if (existaRestaurantCuNume($db, $nume_restaurant, $restaurant_id)) {
        display_error("Există deja un restaurant cu acest nume.", $restaurant_id);
    } else {
        $query = "UPDATE tblRestaurante SET nume_restaurant = ?, oras = ?, tip_bucatarie = ?, livrare_disponibila = ? WHERE restaurant_id = ?";
        $stmt = mysqli_prepare($db, $query);
        mysqli_stmt_bind_param($stmt, "sssii", $nume_restaurant, $oras, $tip_bucatarie, $livrare_disponibila, $restaurant_id);

        if (mysqli_stmt_execute($stmt)) {
            header("Location: restaurante.php");
            exit();
        } else {
            display_error("Eroare la modificarea restaurantului.", $restaurant_id);
        }
    }
}

// Get URL parameters
$edit_restaurant_id = isset($_GET['edit']) ? intval($_GET['edit']) : 0;
$show_produse_restaurant_id = isset($_GET['show_produse']) ? intval($_GET['show_produse']) : 0;

// Procesare adăugare produs la restaurant
if (isset($_POST['adauga_produs_restaurant'])) {
    $restaurant_id = intval($_POST['restaurant_id']);
    $produs_id = intval($_POST['produs_id']);
    $nr_feluri = intval($_POST['nr_feluri']);

    // Verifică dacă produsul există deja la restaurant
    $query_verifica = "SELECT * FROM tblProdusRestaurant WHERE restaurant_id = ? AND produs_id = ?";
    $stmt_verifica = mysqli_prepare($db, $query_verifica);
    mysqli_stmt_bind_param($stmt_verifica, "ii", $restaurant_id, $produs_id);
    mysqli_stmt_execute($stmt_verifica);
    mysqli_stmt_store_result($stmt_verifica);

    if (mysqli_stmt_num_rows($stmt_verifica) > 0) {
        display_error("Produsul există deja la acest restaurant.", $restaurant_id);
    } else {
        $query_insert = "INSERT INTO tblProdusRestaurant (restaurant_id, produs_id, nr_feluri) VALUES (?, ?, ?)";
        $stmt_insert = mysqli_prepare($db, $query_insert);
        mysqli_stmt_bind_param($stmt_insert, "iii", $restaurant_id, $produs_id, $nr_feluri);

        if (mysqli_stmt_execute($stmt_insert)) {
            header("Location: restaurante.php?show_produse=" . $restaurant_id); // Redirecționează pentru a reîmprospăta lista de produse
            exit();
        } else {
            display_error("Eroare la adăugarea produsului la restaurant.", $restaurant_id);
        }
    }
}

// Procesare ștergere produs de la restaurant
if (isset($_GET['delete_produs']) && isset($_GET['restaurant_id'])) {
    $produs_id = intval($_GET['delete_produs']);
    $restaurant_id = intval($_GET['restaurant_id']);

    $query_delete = "DELETE FROM tblProdusRestaurant WHERE restaurant_id = ? AND produs_id = ?";
    $stmt_delete = mysqli_prepare($db, $query_delete);
    mysqli_stmt_bind_param($stmt_delete, "ii", $restaurant_id, $produs_id);

    if (mysqli_stmt_execute($stmt_delete)) {
        header("Location: restaurante.php?show_produse=" . $restaurant_id);
        exit();
    } else {
        display_error("Eroare la ștergerea produsului de la restaurant.", $restaurant_id);
    }
}

// Gestionarea clicurilor pe coloane
if (isset($_GET['sortby_rest'])) {
    $clicked_column = isset($columns[$_GET['sortby_rest']]) ? $columns[$_GET['sortby_rest']] : 'restaurant_id';

    // Dacă se dă clic pe ID, se resetează sortarea la starea inițială
    if ($clicked_column == 'restaurant_id') {
        $_SESSION['primary_sort_rest'] = 'restaurant_id';
        $_SESSION['primary_order_rest'] = 'asc';
        $_SESSION['secondary_sort_rest'] = null;
    } 
    // Dacă se dă clic pe coloana principală de sortare, se inversează ordinea
    elseif ($clicked_column == $_SESSION['primary_sort_rest']) {
        $_SESSION['primary_order_rest'] = ($_SESSION['primary_order_rest'] == 'asc') ? 'desc' : 'asc';
    } 
    // Altfel, coloana pe care s-a dat clic devine coloana principală de sortare
    else {
        $_SESSION['secondary_sort_rest'] = $_SESSION['primary_sort_rest'];
        $_SESSION['secondary_order_rest'] = $_SESSION['primary_order_rest'];
        $_SESSION['primary_sort_rest'] = $clicked_column;
        $_SESSION['primary_order_rest'] = 'asc';
    }
}

// Construirea clauzei ORDER BY
$order_by_clause = "ORDER BY " . $_SESSION['primary_sort_rest'] . " " . strtoupper($_SESSION['primary_order_rest']);
if ($_SESSION['secondary_sort_rest'] !== null) {
    $order_by_clause .= ", " . $_SESSION['secondary_sort_rest'] . " " . strtoupper($_SESSION['secondary_order_rest']);
}

// Afișare lista restaurante ACTIVE
$query = "SELECT
              r.restaurant_id,
              r.nume_restaurant,
              r.oras,
              r.tip_bucatarie,
              r.livrare_disponibila,
              COUNT(pr.produs_id) as nr_feluri
          FROM tblRestaurante r
          LEFT JOIN tblProdusRestaurant pr ON r.restaurant_id = pr.restaurant_id
          GROUP BY r.restaurant_id
          $order_by_clause"; // Aici am adăugat clauza ORDER BY
$result = mysqli_query($db, $query);
?>

<!DOCTYPE html>
<html>

<head>
    <title>Restaurante</title>
    <link rel="stylesheet" type="text/css" href="style.css">
    <style>
        .modify-form {
            display: none;
            margin-top: 10px;
            padding: 10px;
            border: 1px solid #ccc;
            background-color: #f9f9f9;
        }

        .error {
            color: red;
            margin-bottom: 10px;
        }

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
    </style>
    </head>

<body class="background-restaurante">
    <?php include('header.php'); ?>

    <div class="container">
        <h1>Administrare Restaurante</h1>
<h2>Lista Restaurante Active</h2>
<table>
    <thead>
        <tr>
            <th>
                <a href="restaurante.php?sortby_rest=0">ID</a>
            </th>
           <th>
                <a href="restaurante.php?sortby_rest=1">Nume
                    <?php
                    if ($_SESSION['primary_sort_rest'] == 'nume_restaurant') {
                        echo '<span class="sort-arrow ' . ($_SESSION['primary_order_rest'] == 'asc' ? 'sort-asc' : 'sort-desc') . '"></span>';
                    } elseif ($_SESSION['secondary_sort_rest'] == 'nume_restaurant') {
                        echo '<span class="sort-arrow ' . ($_SESSION['secondary_order_rest'] == 'asc' ? 'sort-asc' : 'sort-desc') . '"></span>';
                    } else {
                        // Check for a stored sort order for this column
                        if (isset($_SESSION['sort_orders']['nume_restaurant']) && $_SESSION['sort_orders']['nume_restaurant'] == 'asc') {
                            echo '<span class="sort-arrow sort-asc"></span>';
                        } else {
                            echo '<span class="sort-arrow sort-desc"></span>';
                        }
                    }
                    ?>
                </a>
            </th>
            <th>
                <a href="restaurante.php?sortby_rest=2">Oraș
                    <?php
                    if ($_SESSION['primary_sort_rest'] == 'oras') {
                        echo '<span class="sort-arrow ' . ($_SESSION['primary_order_rest'] == 'asc' ? 'sort-asc' : 'sort-desc') . '"></span>';
                    } elseif ($_SESSION['secondary_sort_rest'] == 'oras') {
                        echo '<span class="sort-arrow ' . ($_SESSION['secondary_order_rest'] == 'asc' ? 'sort-asc' : 'sort-desc') . '"></span>';
                    } else {
                         // Check for a stored sort order for this column
                        if (isset($_SESSION['sort_orders']['oras']) && $_SESSION['sort_orders']['oras'] == 'asc') {
                            echo '<span class="sort-arrow sort-asc"></span>';
                        } else {
                            echo '<span class="sort-arrow sort-desc"></span>';
                        }
                    }
                    ?>
                </a>
            </th>
            <th>
                <a href="restaurante.php?sortby_rest=3">Tip Bucătărie
                    <?php
                    if ($_SESSION['primary_sort_rest'] == 'tip_bucatarie') {
                        echo '<span class="sort-arrow ' . ($_SESSION['primary_order_rest'] == 'asc' ? 'sort-asc' : 'sort-desc') . '"></span>';
                    } elseif ($_SESSION['secondary_sort_rest'] == 'tip_bucatarie') {
                        echo '<span class="sort-arrow ' . ($_SESSION['secondary_order_rest'] == 'asc' ? 'sort-asc' : 'sort-desc') . '"></span>';
                    } else {
                         // Check for a stored sort order for this column
                        if (isset($_SESSION['sort_orders']['tip_bucatarie']) && $_SESSION['sort_orders']['tip_bucatarie'] == 'asc') {
                            echo '<span class="sort-arrow sort-asc"></span>';
                        } else {
                            echo '<span class="sort-arrow sort-desc"></span>';
                        }
                    }
                    ?>
                </a>
            </th>
            <th>
                <a href="restaurante.php?sortby_rest=4">Livrare Disponibilă
                    <?php
                    if ($_SESSION['primary_sort_rest'] == 'livrare_disponibila') {
                        echo '<span class="sort-arrow ' . ($_SESSION['primary_order_rest'] == 'asc' ? 'sort-asc' : 'sort-desc') . '"></span>';
                    } elseif ($_SESSION['secondary_sort_rest'] == 'livrare_disponibila') {
                        echo '<span class="sort-arrow ' . ($_SESSION['secondary_order_rest'] == 'asc' ? 'sort-asc' : 'sort-desc') . '"></span>';
                    } else {
                         // Check for a stored sort order for this column
                        if (isset($_SESSION['sort_orders']['livrare_disponibila']) && $_SESSION['sort_orders']['livrare_disponibila'] == 'asc') {
                            echo '<span class="sort-arrow sort-asc"></span>';
                        } else {
                            echo '<span class="sort-arrow sort-desc"></span>';
                        }
                    }
                    ?>
                </a>
            </th>
            <th>
                <a href="restaurante.php?sortby_rest=5">Nr. Feluri
                    <?php
                    if ($_SESSION['primary_sort_rest'] == 'nr_feluri') {
                        echo '<span class="sort-arrow ' . ($_SESSION['primary_order_rest'] == 'asc' ? 'sort-asc' : 'sort-desc') . '"></span>';
                    } elseif ($_SESSION['secondary_sort_rest'] == 'nr_feluri') {
                        echo '<span class="sort-arrow ' . ($_SESSION['secondary_order_rest'] == 'asc' ? 'sort-asc' : 'sort-desc') . '"></span>';
                    } else {
                         // Check for a stored sort order for this column
                        if (isset($_SESSION['sort_orders']['nr_feluri']) && $_SESSION['sort_orders']['nr_feluri'] == 'asc') {
                            echo '<span class="sort-arrow sort-asc"></span>';
                        } else {
                            echo '<span class="sort-arrow sort-desc"></span>';
                        }
                    }
                    ?>
                </a>
            </th>
            <th>Acțiuni</th>
        </tr>
    </thead>
            <tbody>
                <?php while ($row = mysqli_fetch_assoc($result)) {
    if (!in_array($row['restaurant_id'], $_SESSION['restaurante_dezactivate'])) {?>
                        <tr>
                            <td><?php echo $row['restaurant_id']; ?></td>
                            <td><?php echo htmlspecialchars($row['nume_restaurant']); ?></td>
                            <td><?php echo htmlspecialchars($row['oras']); ?></td>
                            <td><?php echo htmlspecialchars($row['tip_bucatarie']); ?></td>
                            <td><?php echo $row['livrare_disponibila'] ? 'Da' : 'Nu'; ?></td>
                            <td>
                                <?php if ($show_produse_restaurant_id == $row['restaurant_id']) { ?>
                                    <a href="restaurante.php" class="nr-feluri-clickable">
                                        <?php echo $row['nr_feluri']; ?>
                                    </a>
                                <?php } else { ?>
                                    <a href="restaurante.php?show_produse=<?php echo $row['restaurant_id']; ?>" class="nr-feluri-clickable">
                                        <?php echo $row['nr_feluri']; ?>
                                    </a>
                                <?php } ?>
                            </td>
                            <td>
                                <a href="restaurante.php?dezactivare=<?php echo $row['restaurant_id']; ?>">Dezactivează</a>
                                <?php if ($edit_restaurant_id == $row['restaurant_id']) { ?>
                                    <a href="restaurante.php">Anulează</a>
                                <?php } else { ?>
                                    <a href="restaurante.php?edit=<?php echo $row['restaurant_id']; ?>">Modifică</a>
                                <?php } ?>

                            </td>
                        </tr>
                        <tr id="modifyForm_<?php echo $row['restaurant_id']; ?>"
                            class="modify-form"
                            <?php if ($edit_restaurant_id == $row['restaurant_id']) echo 'style="display: table-row;"'; ?>>
                            <td colspan="7">
                                <form method="post" action="restaurante.php">
                                    <input type="hidden" name="modifica_restaurant" value="1">
                                    <input type="hidden" name="restaurant_id" value="<?php echo $row['restaurant_id']; ?>">
                                    <label>Nume Restaurant:</label>
                                    <input type="text" name="nume_restaurant" value="<?php echo htmlspecialchars($row['nume_restaurant']); ?>" required><br>
                                    <label>Oraș:</label>
                                    <input type="text" name="oras" value="<?php echo htmlspecialchars($row['oras']); ?>" required><br>
                                    <label>Tip Bucătărie:</label>
                                    <input type="text" name="tip_bucatarie" value="<?php echo htmlspecialchars($row['tip_bucatarie']); ?>" required><br>
                                    <label>Livrare Disponibilă:</label>
                                    <input type="checkbox" name="livrare_disponibila" value="1" <?php if ($row['livrare_disponibila']) echo 'checked'; ?>><br>
                                    <input type="submit" value="Modifică">
                                    <?php
                                    if (isset($_POST['modifica_restaurant']) && existaRestaurantCuNume($db, $_POST['nume_restaurant'], $row['restaurant_id'])) {
                                        echo "<div class='error'>Există deja un restaurant cu acest nume.</div>";
                                    } elseif (isset($_POST['modifica_restaurant']) && !existaRestaurantCuNume($db, $_POST['nume_restaurant'], $row['restaurant_id']) && mysqli_errno($db)) {
                                        echo "<div class='error'>Eroare la modificarea restaurantului.</div>";
                                    }
                                    ?>
                                </form>
                            </td>
                        </tr>
                        <?php if ($show_produse_restaurant_id == $row['restaurant_id']) { ?>
                            <tr>
                                <td colspan="7">
                                    <div class="produse-restaurant" style="display: block;">
                                        <h3>Produse Disponibile:</h3>
                                        <table>
                                            <thead>
                                                <tr>
                                                    <th>Nume Produs</th>
                                                    <th>Preț</th>
                                                    <th>Categorie</th>
                                                    <th>Acțiuni</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php
                                                $restaurant_id = $row['restaurant_id'];
                                                $query_produse = "SELECT p.produs_id, p.nume_produs, p.pret_produs, p.categorie 
                                                                                FROM tblProdus p
                                                                                JOIN tblProdusRestaurant pr ON p.produs_id = pr.produs_id
                                                                                WHERE pr.restaurant_id = ?";
                                                $stmt_produse = mysqli_prepare($db, $query_produse);
                                                mysqli_stmt_bind_param($stmt_produse, "i", $restaurant_id);
                                                mysqli_stmt_execute($stmt_produse);
                                                $result_produse = mysqli_stmt_get_result($stmt_produse);
                                                while ($row_produs = mysqli_fetch_assoc($result_produse)) {
                                                    echo "<tr>
                                                            <td>" . htmlspecialchars($row_produs['nume_produs']) . "</td>
                                                            <td>" . $row_produs['pret_produs'] . "</td>
                                                            <td>" . htmlspecialchars($row_produs['categorie']) . "</td>
                                                            <td><a href='restaurante.php?delete_produs=" . $row_produs['produs_id'] . "&restaurant_id=" . $restaurant_id . "'>Șterge</a></td></tr>";
                                                }
                                                ?>
                                            </tbody>
                                        </table>

                                        <h3>Adaugă Produs Nou:</h3>
                                        <form method="post" action="restaurante.php">
                                            <input type="hidden" name="adauga_produs_restaurant" value="1">
                                            <input type="hidden" name="restaurant_id" value="<?php echo $show_produse_restaurant_id; ?>">

                                            <label for="produs_id">Selectează Produs:</label>
                                            <select name="produs_id" id="produs_id" required>
                                                <?php
                                                    // Afișează produsele care nu sunt deja asociate restaurantului
                                                    $query_produse_disponibile = "SELECT produs_id, nume_produs FROM tblProdus
                                                                                   WHERE produs_id NOT IN (SELECT produs_id FROM tblProdusRestaurant WHERE restaurant_id = ?)";
                                                    $stmt_produse_disponibile = mysqli_prepare($db, $query_produse_disponibile);
                                                    mysqli_stmt_bind_param($stmt_produse_disponibile, "i", $show_produse_restaurant_id);
                                                    mysqli_stmt_execute($stmt_produse_disponibile);
                                                    $result_produse_disponibile = mysqli_stmt_get_result($stmt_produse_disponibile);

                                                    while ($row_produs_disponibil = mysqli_fetch_assoc($result_produse_disponibile)) {
                                                        echo "<option value='" . $row_produs_disponibil['produs_id'] . "'>" . htmlspecialchars($row_produs_disponibil['nume_produs']) . "</option>";
                                                    }
                                                ?>
                                            </select>
                                            <input type="submit" value="Adaugă Produs">
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        <?php } else { ?>
                            <tr>
                                <td colspan="7">
                                    <div class="produse-restaurant">
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
                                            </tbody>
                                        </table>
                                    </div>
                                </td>
                            </tr>
                        <?php } ?>
                <?php }
                } ?>
            </tbody>
        </table>

        <h2>Adaugă Restaurant Nou</h2>
        <form method="post" action="restaurante.php">
            <input type="hidden" name="adauga_restaurant" value="1">
            <label>Nume Restaurant:</label>
            <input type="text" name="nume_restaurant" required>
            <label>Oraș:</label>
            <input type="text" name="oras" required>
            <label>Tip Bucătărie:</label>
            <input type="text" name="tip_bucatarie" required>
            <label>Livrare Disponibilă:</label>
            <input type="checkbox" name="livrare_disponibila" value="1">
            <input type="submit" value="Adaugă Restaurant">
        </form>
        <?php if (isset($_SESSION['rol']) && $_SESSION['rol'] == 'administrator') { ?>
            <h2>Lista Restaurante Dezactivate</h2>
            <?php if (count($_SESSION['restaurante_dezactivate']) > 0) { ?>
                <table>
    <thead>
        <tr>
           <tr>
            <th>
                <a href="restaurante.php?sortby_rest=0">ID</a>
            </th>
            <th>
                <a href="restaurante.php?sortby_rest=1">Nume
                    <?php
                    if ($_SESSION['primary_sort_rest'] == 'nume_restaurant') echo '<span class="active-sort">' . ($_SESSION['primary_order_rest'] == 'asc' ? '&#x25B2;' : '&#x25BC;') . '</span>';
                    elseif ($_SESSION['secondary_sort_rest'] == 'nume_restaurant') echo '<span class="secondary-sort">' . ($_SESSION['secondary_order_rest'] == 'asc' ? '&#x25B2;' : '&#x25BC;') . '</span>';
                    else echo '&#x25B2;';
                    ?>
                </a>
            </th>
            <th>
                <a href="restaurante.php?sortby_rest=2">Oraș
                    <?php
                    if ($_SESSION['primary_sort_rest'] == 'oras') echo '<span class="active-sort">' . ($_SESSION['primary_order_rest'] == 'asc' ? '&#x25B2;' : '&#x25BC;') . '</span>';
                    elseif ($_SESSION['secondary_sort_rest'] == 'oras') echo '<span class="secondary-sort">' . ($_SESSION['secondary_order_rest'] == 'asc' ? '&#x25B2;' : '&#x25BC;') . '</span>';
                    else echo '&#x25B2;';
                    ?>
                </a>
            </th>
            <th>
                <a href="restaurante.php?sortby_rest=3">Tip Bucătărie
                    <?php
                    if ($_SESSION['primary_sort_rest'] == 'tip_bucatarie') echo '<span class="active-sort">' . ($_SESSION['primary_order_rest'] == 'asc' ? '&#x25B2;' : '&#x25BC;') . '</span>';
                    elseif ($_SESSION['secondary_sort_rest'] == 'tip_bucatarie') echo '<span class="secondary-sort">' . ($_SESSION['secondary_order_rest'] == 'asc' ? '&#x25B2;' : '&#x25BC;') . '</span>';
                    else echo '&#x25B2;';
                    ?>
                </a>
            </th>
            <th>
                <a href="restaurante.php?sortby_rest=4">Livrare Disponibilă
                    <?php
                    if ($_SESSION['primary_sort_rest'] == 'livrare_disponibila') echo '<span class="active-sort">' . ($_SESSION['primary_order_rest'] == 'asc' ? '&#x25B2;' : '&#x25BC;') . '</span>';
                    elseif ($_SESSION['secondary_sort_rest'] == 'livrare_disponibila') echo '<span class="secondary-sort">' . ($_SESSION['secondary_order_rest'] == 'asc' ? '&#x25B2;' : '&#x25BC;') . '</span>';
                    else echo '&#x25B2;';
                    ?>
                </a>
            </th>
            <th>Acțiuni</th>
        </tr>
                    </thead>
                    <tbody>
                        <?php
                        foreach ($_SESSION['restaurante_dezactivate'] as $restaurant_id) {
                            $query = "SELECT restaurant_id, nume_restaurant, oras, tip_bucatarie, livrare_disponibila FROM tblRestaurante WHERE restaurant_id = ?";
                            $stmt = mysqli_prepare($db, $query);
                            mysqli_stmt_bind_param($stmt, "i", $restaurant_id);
                            mysqli_stmt_execute($stmt);
                            $result = mysqli_stmt_get_result($stmt);
                            if ($row = mysqli_fetch_assoc($result)) {
                                ?>
                                <tr>
                                    <td><?php echo $row['restaurant_id']; ?></td>
                                    <td><?php echo htmlspecialchars($row['nume_restaurant']); ?></td>
                                    <td><?php echo htmlspecialchars($row['oras']); ?></td>
                                    <td><?php echo htmlspecialchars($row['tip_bucatarie']); ?></td>
                                    <td><?php echo $row['livrare_disponibila'] ? 'Da' : 'Nu'; ?></td>
                                    <td>
                                        <a href="restaurante.php?activare=<?php echo $row['restaurant_id']; ?>">Activează</a>
                                        <a href="restaurante.php?delete=<?php echo $row['restaurant_id']; ?>">Șterge</a>
                                    </td>
                                </tr>
                            <?php }
                        } ?>
                    </tbody>
                </table>
            <?php } else { ?>
                <p>Niciun restaurant dezactivat.</p>
            <?php } ?>
        <?php } ?>
    </div>
</body>

</html>