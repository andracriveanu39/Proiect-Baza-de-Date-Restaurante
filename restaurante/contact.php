<?php
session_start();

$db = mysqli_connect('localhost', 'root', '', 'restaurantedb') or die('Error connecting to MySQL server.');

$nume_client = "";
$email = "";

if (isset($_SESSION['username']) && isset($_SESSION['rol']) && $_SESSION['rol'] == 'client') {
    $username = $_SESSION['username'];
    $query = "SELECT nume_client, email FROM tblClienti WHERE nume_client = '$username'";
    $result = mysqli_query($db, $query);
    $client = mysqli_fetch_assoc($result);

    if ($client) {
        $nume_client = $client['nume_client'];
        $email = $client['email'];
    }
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nume_message = htmlspecialchars($_POST['nume']);
    $email_message = htmlspecialchars($_POST['email']);
    $telefon_message = htmlspecialchars($_POST['telefon']);
    $mesaj = htmlspecialchars($_POST['mesaj']);

    $query = "INSERT INTO contact_messages (nume_message, email_message, telefon_message, mesaj) VALUES ('$nume_message', '$email_message', '$telefon_message', '$mesaj')";
    if (mysqli_query($db, $query)) {
        echo '<p class="success-message">Mesajul tău a fost trimis cu succes! Îți vom răspunde în cel mai scurt timp posibil.</p>';
    } else {
        echo '<p class="error-message">A apărut o eroare la trimiterea mesajului. Te rugăm să încerci din nou mai târziu.</p>';
    }

    mysqli_close($db);
}
?>

<!DOCTYPE html>
<html lang="ro">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Contactează-ne</title>
    <link rel="stylesheet" type="text/css" href="style.css">
    <style>
        .contact-section {
            background-color: #fff;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            margin-bottom: 20px;
        }

        .contact-section h2 {
            color: #333;
            text-align: center;
        }

        .contact-section p {
            line-height: 1.6;
            color: #555;
            text-align: center;
        }

        .form-group {
            margin-bottom: 15px;
        }

        label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
            color: #333;
        }

        input[type="text"],
        input[type="email"],
        input[type="tel"],
        textarea {
            width: 100%;
            padding: 10px;
            border: 1px solid #ccc;
            border-radius: 4px;
            box-sizing: border-box;
        }

        textarea {
            resize: vertical;
        }

        button[type="submit"] {
            background-color: #5cb85c;
            color: white;
            padding: 10px 15px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 16px;
        }

        button[type="submit"]:hover {
            background-color: #4cae4c;
        }

        .success-message {
            color: green;
            background-color:rgb(254, 252, 252);
            margin-top: 10px;
            text-align: center;
        }

        .error-message {
            color: red;
            margin-top: 10px;
            text-align: center;
        }

        /* Styles for the email slider with orange boxes */
        .email-slider-container {
            position: relative;
            width: 100%;
            overflow: hidden;
        }

        .email-slider {
            display: flex;
            transition: transform 0.5s ease-in-out;
        }

      .email-item {
    flex: 0 0 100%;
    min-width: 100%;
    box-sizing: border-box;
    padding: 20px;
    background-color: #fff; /* Background alb */
    border: 2px solid red; /* Borduri roșii */
    color: #333;
    text-align: center;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 16px;
}

        .slider-arrow {
            position: absolute;
            top: 50%;
            transform: translateY(-50%);
            width: 40px;
            height: 40px;
            background-color: rgba(0, 0, 0, 0.1);
            color: #fff;
            border: none;
            cursor: pointer;
            font-size: 20px;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: background-color 0.3s ease;
        }

        .slider-arrow:hover {
            background-color: rgba(0, 0, 0, 0.3);
        }

        .slider-arrow.prev {
            left: 10px;
        }

        .slider-arrow.next {
            right: 10px;
        }
    </style>
</head>

<body class="background-login">
    <?php include('header.php'); ?>

    <div class="container">
        <div class="contact-section">
            <h2>Contactează-ne</h2>
            <p>Ai întrebări sau sugestii? Nu ezita să ne contactezi!</p>

            <div class="contact-info">
                <div class="email-slider-container">
                    <div class="email-slider">
                        <div class="email-item">
                            <p>Amariei Alexandra - </p><a href="mailto:admin1@restaurantulmeu.ro"> alexandra@gmail.com </a>
                        </div>
                     <div class="email-item">
                            <p>Criveanu Andra - </p><a href="mailto:admin2@restaurantulmeu.ro"> andra@gmail.com </a>
                        </div>
		     <div class="email-item">
                            <p>Mitran Ioana - </p><a href="mailto:admin2@restaurantulmeu.ro"> ioana@gmail.com </a>
                        </div>
		     <div class="email-item">
                            <p>Lambuta Raluca - </p><a href="mailto:admin2@restaurantulmeu.ro"> raluca@gmail.com </a>
                        </div>
		     <div class="email-item">
                            <p>Reut Denisa - </p><a href="mailto:admin2@restaurantulmeu.ro"> denisa@gmail.com </a>
                        </div>

                    </div>
                    <button class="slider-arrow prev" onclick="prevEmail()">&#9664;</button>
                    <button class="slider-arrow next" onclick="nextEmail()">&#9654;</button>
                </div>
                <p>Telefon: <a href="tel:+407XXXXXXXX">+40744559357</a></p>
                <p>Contactează-ne pentru orice nelămurire!</p>
            </div>
        </div>

        <div class="contact-section">
            <h2>Trimite-ne un mesaj</h2>
            <form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>">
                <div class="form-group">
                    <label for="nume">Nume:</label>
                    <input type="text" id="nume" name="nume" value="<?php echo $nume_client; ?>" required>
                </div>
                <div class="form-group">
                    <label for="email">Email:</label>
                    <input type="email" id="email" name="email" value="<?php echo $email; ?>" required>
                </div>
                <div class="form-group">
                    <label for="telefon">Număr de telefon:</label>
                    <input type="tel" id="telefon" name="telefon">
                </div>
                <div class="form-group">
                    <label for="mesaj">Mesaj:</label>
                    <textarea id="mesaj" name="mesaj" rows="5" required></textarea>
                </div>
                <button type="submit">Trimite mesaj</button>
            </form>
        </div>
    </div>

    <script>
        const slider = document.querySelector('.email-slider');
        const items = document.querySelectorAll('.email-item');
        const sliderContainer = document.querySelector('.email-slider-container');
        let currentIndex = 0;

        function updateSlider() {
            slider.style.transform = `translateX(-${currentIndex * 100}%)`;
        }

        function nextEmail() {
            currentIndex = (currentIndex + 1) % items.length;
            updateSlider();
        }

        function prevEmail() {
            currentIndex = (currentIndex - 1 + items.length) % items.length;
            updateSlider();
        }

        // Calculate and set slider container height
        function setSliderHeight() {
            let maxHeight = 0;
            items.forEach(item => {
                if (item.offsetHeight > maxHeight) {
                    maxHeight = item.offsetHeight;
                }
            });
            sliderContainer.style.height = `${maxHeight}px`;
        }

        // Call on load and resize
        window.addEventListener('load', setSliderHeight);
        window.addEventListener('resize', setSliderHeight);
    </script>
</body>

</html>