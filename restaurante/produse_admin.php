<?php
session_start();
// Verificare rol administrator
if (!isset($_SESSION['rol']) || $_SESSION['rol'] !== 'administrator') {
    header("Location: index.php"); // Redirecționează dacă nu este admin
    exit();
}

$db = mysqli_connect('localhost', 'root', '', 'restaurantedb') or die('Error connecting to MySQL server.');

// Funcție pentru a afișa mesajele de eroare
function display_error($error, $produs_id = null) {
    if ($produs_id !== null) {
        echo "<div class='error' id='error_{$produs_id}'>$error</div>";
    } else {
        echo "<div class='error'>$error</div>";
    }
}

// Array pentru a ține minte ID-urile produselor "dezactivate" (doar în sesiune)
if (!isset($_SESSION['produse_dezactivate'])) {
    $_SESSION['produse_dezactivate'] = array();
}

// Funcție pentru a verifica dacă există deja un produs cu același nume
function existaProdusCuNume($db, $nume_produs, $produs_id = null) {
    $query = "SELECT produs_id FROM tblProdus WHERE nume_produs = ?";
    if ($produs_id !== null) {
        $query .= " AND produs_id != ?";
    }
    $stmt = mysqli_prepare($db, $query);
    if ($produs_id !== null) {
        mysqli_stmt_bind_param($stmt, "si", $nume_produs, $produs_id);
    } else {
        mysqli_stmt_bind_param($stmt, "s", $nume_produs);
    }
    mysqli_stmt_execute($stmt);
    mysqli_stmt_store_result($stmt);
    return mysqli_stmt_num_rows($stmt) > 0;
}


// Procesare adăugare produs
if (isset($_POST['adauga_produs'])) {
    $nume_produs = mysqli_real_escape_string($db, $_POST['nume_produs']);
    $pret_produs = floatval($_POST['pret_produs']);
    $categorie = mysqli_real_escape_string($db, $_POST['categorie']);

    if (existaProdusCuNume($db, $nume_produs)) {
        display_error("Există deja un produs cu acest nume.");
    } else {
        $query = "INSERT INTO tblProdus (nume_produs, pret_produs, categorie) VALUES (?, ?, ?)";
        $stmt = mysqli_prepare($db, $query);
        mysqli_stmt_bind_param($stmt, "sds", $nume_produs, $pret_produs, $categorie);

        if (mysqli_stmt_execute($stmt)) {
            header("Location: produse_admin.php");
            exit();
        } else {
            display_error("Eroare la adăugarea produsului.");
        }
    }
}

// Procesare modificare produs
if (isset($_POST['modifica_produs'])) {
    $produs_id = intval($_POST['produs_id']);
    $nume_produs = mysqli_real_escape_string($db, $_POST['nume_produs']);
    $pret_produs = floatval($_POST['pret_produs']);
    $categorie = mysqli_real_escape_string($db, $_POST['categorie']);

    if (existaProdusCuNume($db, $nume_produs, $produs_id)) {
         // Redirect back with error and product ID to show the form again
         header("Location: produse_admin.php?edit={$produs_id}&error=exists");
         exit();
    } else {
        // Modifică query-ul să NU includă categoria
        $query = "UPDATE tblProdus SET nume_produs = ?, pret_produs = ? WHERE produs_id = ?";
        $stmt = mysqli_prepare($db, $query);
        // Potrivește parametrii
        mysqli_stmt_bind_param($stmt, "sdi", $nume_produs, $pret_produs, $produs_id);


        if (mysqli_stmt_execute($stmt)) {
            header("Location: produse_admin.php");
            exit();
        } else {
            // Redirect back with error and product ID to show the form again
            header("Location: produse_admin.php?edit={$produs_id}&error=update_failed");
            exit();
        }
    }
}

// Get URL parameters for showing/hiding sections
$edit_produs_id = isset($_GET['edit']) ? intval($_GET['edit']) : 0;
$show_restaurante_produs_id = isset($_GET['show_restaurante']) ? intval($_GET['show_restaurante']) : 0;
$error_message = '';
if (isset($_GET['error'])) {
    if ($_GET['error'] == 'exists') {
        $error_message = "Există deja un produs cu acest nume.";
    } elseif ($_GET['error'] == 'update_failed') {
        $error_message = "Eroare la modificarea produsului.";
    }
}


// Afișare lista produse ACTIVE
$query = "SELECT
              p.produs_id,
              p.nume_produs,
              p.pret_produs,
              p.categorie,
              (SELECT COUNT(DISTINCT pr.restaurant_id) 
               FROM tblProdusRestaurant pr 
               WHERE pr.produs_id = p.produs_id) as nr_restaurante
          FROM tblProdus p
          GROUP BY p.produs_id"; // Removed LEFT JOIN - it's not needed for the count
$result = mysqli_query($db, $query);


?>
<!DOCTYPE html>
<html>

<head>
    <title>Administrare Produse</title>
    <link rel="stylesheet" type="text/css" href="style.css">
    <style>
        .modify-form {
            display: none; /* Default hidden */
            margin-top: 10px;
            padding: 10px;
            border: 1px solid #ccc;
            background-color: #f9f9f9;
        }

        .restaurante-produs {
             display: none; /* Default hidden */
        }

        .error {
            color: red;
            margin-bottom: 10px;
        }
    </style>
    </head>

<body class="background-meniu">
    <?php include('header.php'); ?>

    <div class="container">
        <h1>Administrare Produse</h1>

        <?php if ($error_message && $edit_produs_id == 0) display_error($error_message); ?>


        <h2>Lista Produse Active</h2>
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Nume</th>
                    <th>Preț</th>
                    <th>Categorie</th>
                    <th>Nr. Restaurante</th>
                    <th>Modifică</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($row = mysqli_fetch_assoc($result)) {
                    if (!in_array($row['produs_id'], $_SESSION['produse_dezactivate'])) { ?>
                        <tr>
                            <td><?php echo $row['produs_id']; ?></td>
                            <td><?php echo $row['nume_produs']; ?></td>
                            <td><?php echo $row['pret_produs']; ?></td>
                            <td><?php echo $row['categorie']; ?></td>
                            <td>
                                <?php if ($show_restaurante_produs_id == $row['produs_id']) { ?>
                                    <a href="produse_admin.php" class="nr-restaurante-clickable">
                                        <?php echo $row['nr_restaurante']; ?>
                                    </a>
                                <?php } else { ?>
                                    <a href="produse_admin.php?show_restaurante=<?php echo $row['produs_id']; ?>" class="nr-restaurante-clickable">
                                        <?php echo $row['nr_restaurante']; ?>
                                    </a>
                                <?php } ?>
                            </td>
                            <td>
                                <?php if ($edit_produs_id == $row['produs_id']) { ?>
                                     <a href="produse_admin.php">Anulează Modificarea</a>
                                <?php } else { ?>
                                     <a href="produse_admin.php?edit=<?php echo $row['produs_id']; ?>">Modifică</a>
                                <?php } ?>
                            </td>
                        </tr>
                        <tr id="modifyForm_<?php echo $row['produs_id']; ?>"
                            class="modify-form"
                            <?php if ($edit_produs_id == $row['produs_id']) echo 'style="display: table-row;"'; ?>>
                            <td colspan="6">
                                <?php if ($error_message && $edit_produs_id == $row['produs_id']) display_error($error_message, $row['produs_id']); ?>
                                <form method="post" action="produse_admin.php">
                                <input type="hidden" name="modifica_produs" value="1">
                                    <input type="hidden" name="produs_id" value="<?php echo $row['produs_id']; ?>">
                                    <label>Nume Produs:</label>
                                    <input type="text" name="nume_produs" value="<?php echo htmlspecialchars($row['nume_produs']); ?>" required><br>
                                    <label>Preț:</label>
                                    <input type="number" name="pret_produs" step="0.01" value="<?php echo $row['pret_produs']; ?>" required><br>
                                    <label>Categorie:</label>
                                    <input type="hidden" name="categorie" value="<?php echo htmlspecialchars($row['categorie']); ?>">
                                    <span><?php echo htmlspecialchars($row['categorie']); ?></span><br>
                                    <input type="submit" value="Modifică">
                                    <?php
                                    if (isset($_POST['modifica_produs']) && existaProdusCuNume($db, $_POST['nume_produs'], $row['produs_id'])) {
                                        echo "<div class='error'>Există deja un produs cu acest nume.</div>";
                                    } elseif (isset($_POST['modifica_produs']) && !existaProdusCuNume($db, $_POST['nume_produs'], $row['produs_id']) && mysqli_errno($db)) {
                                        echo "<div class='error'>Eroare la modificarea produsului.</div>";
                                    }
                                    ?>
                                </form>
                            </td>
                        </tr>
                         <tr id="restauranteProdus_<?php echo $row['produs_id']; ?>"
                            class="restaurante-produs"
                            <?php if ($show_restaurante_produs_id == $row['produs_id']) echo 'style="display: table-row;"'; ?>>
                            <td colspan="6">
                                <h3>Restaurante care oferă <?php echo $row['nume_produs']; ?>:</h3>
                                <table>
                                    <thead>
                                        <tr>
                                            <th>Nume Restaurant</th>
                                            <th>Oraș</th>
                                            <th>Tip Bucătărie</th>
                                            <th>Livrare Disponibilă</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php
                                        if ($show_restaurante_produs_id == $row['produs_id']) {
                                            $produs_id = $row['produs_id'];
                                            $query_restaurante = "SELECT r.nume_restaurant, r.oras, r.tip_bucatarie, r.livrare_disponibila
                                                                FROM tblRestaurante r
                                                                JOIN tblProdusRestaurant pr ON r.restaurant_id = pr.restaurant_id
                                                                WHERE pr.produs_id = ?";
                                            $stmt_restaurante = mysqli_prepare($db, $query_restaurante);
                                            mysqli_stmt_bind_param($stmt_restaurante, "i", $produs_id);
                                            mysqli_stmt_execute($stmt_restaurante);
                                            $result_restaurante = mysqli_stmt_get_result($stmt_restaurante);

                                            while ($row_restaurant = mysqli_fetch_assoc($result_restaurante)) {
                                                echo "<tr>
                                                        <td>" . htmlspecialchars($row_restaurant['nume_restaurant']) . "</td>
                                                        <td>" . htmlspecialchars($row_restaurant['oras']) . "</td>
                                                        <td>" . htmlspecialchars($row_restaurant['tip_bucatarie']) . "</td>
                                                        <td>" . ($row_restaurant['livrare_disponibila'] ? 'Da' : 'Nu') . "</td>
                                                    </tr>";
                                            }
                                        }
                                        ?>
                                    </tbody>
                                </table>
                            </td>
                        </tr>
                <?php }
                } ?>
            </tbody>
        </table>
        <h2>Adaugă Produs Nou</h2>
        <form method="post" action="produse_admin.php">
            <input type="hidden" name="adauga_produs" value="1">
            <label>Nume Produs:</label>
            <input type="text" name="nume_produs" required><br>
            <label>Preț:</label>
            <input type="number" name="pret_produs" step="0.01" required><br>
            <label>Categorie:</label>
            <input type="text" name="categorie" required><br>
            <input type="submit" value="Adaugă">
        </form>
    </div>
    </body>

</html>