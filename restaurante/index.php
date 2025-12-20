<?php
session_start();
$db = mysqli_connect('localhost', 'root', '', 'restaurantedb') or die('Error connecting to MySQL server.');
?>
<html>
<head>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta charset="UTF-8">
    <link rel="stylesheet" type="text/css" href="style.css">
    <title>RestauranteDB</title>
    <style>
        .home-description {
            padding: 20px;
            background-color: rgba(255, 255, 255, 0.8);
            margin-bottom: 20px;
        }

        .home-images {
            display: flex;
            flex-wrap: wrap;
            justify-content: center;
            align-items: center;
            gap: 15px;
            padding: 20px;
        }

        .home-images img {
            width: 300px;
            height: auto;
            border-radius: 8px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }

        .main-title {
            font-family: 'Brush Script MT', cursive;
            font-size: 5em;
            color: #333;
            text-align: center;
            margin-bottom: 20px;
        }
    </style>
</head>
<body class="background-index">
    <?php include('header.php'); ?>

    <div class="container">
        <h1 class="main-title">Farfuria Zburatoare</h1>
        <div class="home-description">
            <h2>Despre Noi</h2>
            <p>Suntem o echipă pasionată de gastronomie și tehnologie, dedicată să vă aducă cele mai diverse și savuroase opțiuni culinare direct la ușa dumneavoastră sau la masa restaurantului preferat. Am creat o platformă inovatoare care combină comoditatea livrărilor rapide cu posibilitatea de a rezerva mese în localurile de top din oraș.</p>

            <h3>Ce ne face unici?</h3>
            <p><strong>Diversitate culinară:</strong> Explorați o selecție vastă de restaurante, de la bucătării tradiționale la cele mai exotice, toate la un click distanță. Fie că aveți poftă de sushi, pizza, burgeri gourmet sau o cină rafinată, avem ceva pentru fiecare gust.</p>
            <p><strong>Livrare rapidă și eficientă:</strong> Parteneriatele noastre cu restaurantele și curierii ne permit să vă aducem mâncarea preferată caldă și proaspătă, în cel mai scurt timp posibil.</p>
            <p><strong>Rezervări simplificate:</strong> Planificați-vă serile speciale sau ieșirile cu prietenii fără bătăi de cap. Cu doar câteva atingeri, puteți rezerva o masă la restaurantul dorit, evitând aglomerația și timpul de așteptare.</p>
            <p><strong>Experiență personalizată:</strong> Aplicația noastră învață preferințele dumneavoastră și vă sugerează opțiuni adaptate gusturilor și ocaziilor.</p>

            <h3>Misiunea noastră</h3>
            <p>Ne propunem să fim liantul dintre iubitorii de mâncare și restaurantele de calitate, oferind o soluție completă pentru a descoperi, comanda și savura experiențe culinare memorabile. Ne angajăm să susținem afacerile locale, să promovăm diversitatea gastronomică și să contribuim la dinamismul vieții urbane.</p>

            <h3>Valorile noastre</h3>
            <p><strong>Calitate:</strong> Selectăm cu grijă restaurantele partenere, asigurându-ne că oferă ingrediente proaspete, preparate delicioase și servicii impecabile.</p>
            <p><strong>Comoditate:</strong> Simplificăm procesul de comandă și rezervare, economisindu-vă timp și energie.</p>
            <p><strong>Inovație:</strong> Ne perfecționăm constant tehnologia pentru a vă oferi o experiență intuitivă, rapidă și plăcută.</p>
            <p><strong>Comunitate:</strong> Construim relații solide cu restaurantele, curierii și clienții, bazate pe respect și încredere reciprocă.</p>
        </div>
    </div>

    <div class="home-images">
        <img src="images/background.jpg" alt="Restaurant 1">
        <img src="images/background1.jpg" alt="Restaurant 2">
        <img src="images/background2.jpg" alt="Restaurant 3">
        <img src="images/background3.jpg" alt="Restaurant 4">
        <img src="images/background4.jpg" alt="Restaurant 5">
    </div>

</body>
</html>