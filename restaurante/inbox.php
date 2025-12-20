<?php
session_start();

// Check if the user is an administrator
if (!isset($_SESSION['rol']) || $_SESSION['rol'] != 'administrator') {
    header("Location: index.php"); // Redirect to homepage or login page
    exit();
}

$db = mysqli_connect('localhost', 'root', '', 'restaurantedb') or die('Error connecting to MySQL server.');

// Fetch all contact messages
$query = "SELECT * FROM contact_messages ORDER BY data_crearii DESC"; // Order by newest first
$result = mysqli_query($db, $query);

// Delete a message
if (isset($_GET['delete'])) {
    $id_message = intval($_GET['delete']);
    $delete_query = "DELETE FROM contact_messages WHERE id_message = $id_message";
    mysqli_query($db, $delete_query);
    header("Location: inbox.php"); // Redirect back to inbox
    exit();
}
?>

<!DOCTYPE html>
<html>

<head>
    <title>Inbox</title>
    <link rel="stylesheet" type="text/css" href="style.css">
    <style>
        .inbox-message {
            background-color: #fff;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            margin-bottom: 20px;
        }

        .inbox-message h3 {
            color: #333;
            margin-top: 0;
        }

        .inbox-message p {
            color: #555;
        }

        .message-info {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 10px;
        }

        .delete-btn {
            background-color: #d9534f;
            color: white;
            padding: 5px 10px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }
    </style>
</head>

<body class="background-login">
    <?php include('header.php'); ?>

    <div class="container">
        <div class="client-section">
            <h1 class="client-table-title">Inbox</h1>
            <?php if (mysqli_num_rows($result) > 0) { ?>
                <?php while ($row = mysqli_fetch_assoc($result)) { ?>
                    <div class="inbox-message">
                        <div class="message-info">
                            <h3>De la: <?php echo htmlspecialchars($row['nume_message']); ?></h3>
                            <p>Email: <?php echo htmlspecialchars($row['email_message']); ?> | Telefon: <?php echo htmlspecialchars($row['telefon_message']); ?></p>
                            <a href="inbox.php?delete=<?php echo $row['id_message']; ?>" class="delete-btn">Șterge</a>
                        </div>
                        <p><?php echo htmlspecialchars($row['mesaj']); ?></p>
                        <p style="font-size: 0.8em; color: #888;">Trimis la: <?php echo $row['data_crearii']; ?></p>
                    </div>
                <?php } ?>
            <?php } else { ?>
                <p>Nu există mesaje în inbox.</p>
            <?php } ?>
        </div>
    </div>

</body>

</html>