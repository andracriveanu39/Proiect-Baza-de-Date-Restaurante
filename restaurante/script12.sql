#SOURCE C:/wamp64c/www/myPage/script12.sql; 
/*          Folositi pentru cale simbolul "/", NU "\"         */ 


/*#############################################################*/
/*        PARTEA 1 - STERGEREA SI RECREAREA BAZEI DE DATE      */

DROP DATABASE restauranteDB;
CREATE DATABASE restauranteDB;
USE restauranteDB;

/*#############################################################*/

/*#############################################################*/
/*                  PARTEA 2 - CREAREA TABELELOR              */

CREATE TABLE tblRestaurante(
    restaurant_id INT AUTO_INCREMENT PRIMARY KEY,
    nume_restaurant VARCHAR(100) NOT NULL,
    oras VARCHAR(50),
    tip_bucatarie VARCHAR(50),
    livrare_disponibila BOOLEAN,
    CONSTRAINT chk_oras CHECK (oras IS NOT NULL)
	
);

CREATE TABLE tblProdus(
    produs_id INT AUTO_INCREMENT PRIMARY KEY,
    nume_produs VARCHAR(100) NOT NULL,
    pret_produs DECIMAL(6,2),
    categorie VARCHAR(50)
);

CREATE TABLE tblClienti(
    client_id INT AUTO_INCREMENT PRIMARY KEY,
    nume_client VARCHAR(100) NOT NULL,
    email VARCHAR(100) UNIQUE,
    data_inregistrare DATE,
	password VARCHAR(100) NOT NULL,
    rol ENUM('client')
);

CREATE TABLE tblAdmini (
	admin_id INT AUTO_INCREMENT PRIMARY KEY,
    nume_admin VARCHAR(100) NOT NULL,
    email VARCHAR(100) UNIQUE,
    data_inregistrare DATE,
	password VARCHAR(100) NOT NULL,
    rol ENUM('administrator'),
	approved BOOLEAN DEFAULT False
);

CREATE TABLE tblRezervari(
    rezervare_id INT AUTO_INCREMENT PRIMARY KEY,
    client_id INT,
    restaurant_id INT,
    data_ora DATETIME,
    numar_persoane INT,
    status_rezervare ENUM('confirmata', 'anulata', 'in_asteptare'),
    FOREIGN KEY (client_id) REFERENCES tblClienti(client_id),
    FOREIGN KEY (restaurant_id) REFERENCES tblRestaurante(restaurant_id)
);

CREATE TABLE tblComenzi (
    comanda_id INT AUTO_INCREMENT PRIMARY KEY,
    client_id INT,
    restaurant_id INT,
    data_comanda DATETIME,
    tip_plata ENUM('cash', 'card'),
    status_comanda ENUM('finalizata', 'in_pregatire'),
    FOREIGN KEY (client_id) REFERENCES tblClienti(client_id),
    FOREIGN KEY (restaurant_id) REFERENCES tblRestaurante(restaurant_id)
); 

CREATE TABLE tblProdusRestaurant(
     restaurant_id INT, 
     produs_id INT,
     nr_feluri INT, 
     FOREIGN KEY (restaurant_id) REFERENCES tblRestaurante(restaurant_id) ON DELETE CASCADE,
     FOREIGN KEY (produs_id) REFERENCES tblProdus(produs_id) ON DELETE CASCADE
);

CREATE TABLE tblProdusComenzi(
     comanda_id INT, 
     produs_id INT,
     cantitate INT, 
     FOREIGN KEY (comanda_id) REFERENCES tblComenzi(comanda_id) ON DELETE CASCADE,
     FOREIGN KEY (produs_id) REFERENCES tblProdus(produs_id) ON DELETE CASCADE
);

CREATE TABLE contact_messages(
	id_message INT AUTO_INCREMENT PRIMARY KEY,
	nume_message VARCHAR(64),
	email_message VARCHAR(64),
	telefon_message VARCHAR(15),
	mesaj TEXT,
	data_crearii TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
/*#############################################################*/




/*#############################################################*/
/*         PARTEA 3 - INSERAREA INREGISTRARILOR IN TABELE      */

INSERT INTO tblRestaurante (nume_restaurant, oras, tip_bucatarie, livrare_disponibila) VALUES
('La Mama', 'Bucuresti', 'Romaneasca', TRUE),
('Sushi Time', 'Cluj', 'Japoneza', TRUE),
('Bella Napoli', 'Timisoara', 'Italiana', TRUE),
('BurgerZone', 'Iasi', 'Fast Food', FALSE),
('Casa Bunicii', 'Sibiu', 'Romaneasca', TRUE),
('Veggie Love', 'Bucuresti', 'Vegetariana', TRUE),
('Dragon Wok', 'Constanta', 'Chinezeasca', FALSE),
('Steak House', 'Oradea', 'Americana', TRUE),
('Little India', 'Brasov', 'Indiana', TRUE),
('Fusion Kitchen', 'Craiova', 'Fusion', TRUE);

INSERT INTO tblAdmini (nume_admin, email, data_inregistrare, password, rol, approved) VALUES
('Amariei Alexandra', 'alexandra@gmail.com', '2020-01-01', 'alex','administrator', TRUE),
('Criveanu Andra', 'andra@gmail.com', '2020-01-01','andra', 'administrator',TRUE),
('Mitran Ioana', 'ioana@gmail.com', '2020-01-01','ioana', 'administrator',TRUE),
('Lambuta Raluca', 'raluca@gmail.com', '2020-01-01','raluca', 'administrator',TRUE),
('Reut Denisa', 'denisa@gmail.com', '2020-01-01','denisa', 'administrator',TRUE);

INSERT INTO tblClienti (nume_client, email, data_inregistrare, password, rol) VALUES
('Andrei Popescu', 'andrei_popescu22@gmail.com', '2024-01-15', 'parola', 'client'),
('Ioana Ionescu', 'ioana.ionescu2004@gmail.com', '2023-12-01','parola', 'client'),
('Mihai Georgescu', 'mgeorgescu11@yahoo.com', '2024-03-22','parola', 'client'),
('Alina Dobre', 'alina_andreea@gmail.com', '2023-11-10','parola', 'client'),
('Radu Stan', 'radu.stan26@gmail.com', '2024-02-05','parola', 'client'),
('Irina Matei', 'irinacristinamatei@gmail.com', '2024-01-01','parola', 'client'),
('Cristian Pavel', 'cristian.pavel19@yahoo.com', '2023-10-20','parola', 'client'),
('Elena Tudor', 'tudor.elena@gmail.com', '2024-03-30','parola', 'client'),
('Vlad Rusu', 'vlad_rusu007@gmail.com', '2023-12-25','parola', 'client'),
('Diana Popa', 'dianamaria_popa@gmail.com', '2024-04-02','parola', 'client');


INSERT INTO tblProdus (nume_produs, pret_produs, categorie) VALUES
('Ciorba de burta', 21.50, 'Supa'),
('Sarmale cu mamaliga', 34.00, 'Fel principal'),
('Sushi roll somon', 42.00, 'Fel principal'),
('Miso Soup', 18.50, 'Supa'),
('Pizza Margherita', 28.00, 'Pizza'),
('Burger clasic', 29.90, 'Fel principal'),
('Ciorba de vacuta', 19.00, 'Supa'),
('Tofu stir fry', 26.00, 'Fel principal'),
('Pui cu legume', 31.50, 'Fel principal'),
('Platou mixt fusion', 55.00, 'Fel principal');

INSERT INTO tblRezervari (client_id, restaurant_id, data_ora, numar_persoane, status_rezervare) VALUES
(1, 1, '2025-04-12 19:00:00', 2, 'confirmata'),
(2, 2, '2025-04-13 13:00:00', 4, 'in_asteptare'),
(3, 3, '2025-04-10 20:00:00', 1, 'anulata'),
(4, 4, '2025-04-09 18:30:00', 3, 'confirmata'),
(5, 5, '2025-04-15 14:00:00', 5, 'confirmata'),
(6, 6, '2025-04-11 12:00:00', 2, 'confirmata'),
(7, 1, '2025-04-16 19:00:00', 2, 'in_asteptare'),
(8, 2, '2025-04-14 20:00:00', 6, 'confirmata'),
(9, 3, '2025-04-13 17:00:00', 2, 'anulata'),
(10, 10, '2025-04-12 18:00:00', 3, 'confirmata');


INSERT INTO tblComenzi (client_id, restaurant_id, data_comanda, tip_plata, status_comanda) VALUES
(1, 1, '2025-04-09 12:00:00', 'cash', 'finalizata'),
(1, 1, '2025-04-10 12:00:00', 'card', 'in_pregatire'),
(2, 2, '2025-04-11 13:30:00', 'card', 'finalizata'),
(3, 3, '2025-04-12 14:15:00', 'card', 'in_pregatire'),
(4, 2, '2025-04-13 11:45:00', 'cash', 'finalizata'),
(5, 1, '2025-04-13 19:00:00', 'cash', 'finalizata'),
(6, 6, '2025-04-14 20:00:00', 'card', 'finalizata'),
(7, 5, '2025-04-15 18:30:00', 'cash', 'in_pregatire'),
(8, 4, '2025-04-16 12:45:00', 'cash', 'in_pregatire'),
(9, 10, '2025-04-17 13:00:00', 'card', 'in_pregatire');

INSERT INTO tblProdusComenzi (comanda_id, produs_id, cantitate) VALUES 
(1, 1, 2),
(2, 2, 1),
(3, 3, 1),
(3, 4, 2),
(4, 6, 3),
(5, 1, 1),
(5, 2, 1),
(6, 8, 2),
(7, 7, 1),
(8, 9, 2);

INSERT INTO tblProdusRestaurant (restaurant_id, produs_id, nr_feluri) VALUES 
(1, 1, 1),
(1, 2, 1),
(2, 3, 3),
(2, 4, 2),
(3, 5, 2),
(4, 6, 2),
(4, 9, 1),
(10, 10, 2),
(5, 7, 1),
(6, 8, 1);

/*#############################################################*/



/*#############################################################*/
/*  PARTEA 4 - VIZUALIZAREA STUCTURII BD SI A INREGISTRARILOR  */
DESCRIBE tblRestaurante;
DESCRIBE tblProdus;
DESCRIBE tblComenzi;
DESCRIBE tblClienti;
DESCRIBE tblRezervari;
DESCRIBE tblProdusComenzi;
DESCRIBE tblProdusRestaurant;


SELECT * FROM tblRestaurante;
SELECT * FROM tblProdus;
SELECT * FROM tblComenzi;
SELECT * FROM tblClienti;
SELECT * FROM tblRezervari;
SELECT * FROM tblProdusComenzi;
SELECT * FROM tblProdusRestaurant;
SELECT * FROM tblAdmini;

/*#############################################################*/




/* 
- Nu stergeti comentariile de mai sus

- REDENUMITI FISIERUL  scriptXX.sql astfel incat XX sa coincida cu numarul echipei de BD. Ex: script07.sql

- SCRIPTUL AR TREBUI SA POATA FI RULAT FARA NICI O EROARE!

- ATENTIE LA CHEILE STRAINE! Nu uitati sa modificati motorul de stocare pentru tabele, la InnoDB, pentru a functiona constrangerile de cheie straina (vezi laborator 1, pagina 16 - Observatie)

*/