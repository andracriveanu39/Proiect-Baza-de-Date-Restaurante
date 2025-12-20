<?php
// header.php
?>
<header>
    <nav>
        <input type="checkbox" id="menu-toggle" class="hamburger-menu">
        <label for="menu-toggle" class="hamburger-icon">&#9776;</label>
        <table class="list" id="menuTable">
            <tr>
                <?php if (isset($_SESSION['rol'])) { ?>
                    <?php if ($_SESSION['rol'] == 'client') { ?>
                        <td><a href="restaurante_clienti.php">Restaurante</a></td>
                        <td><a href="comenzi_clienti.php">Comenzi</a></td>
                        <td><a href="rezervari_clienti.php">Rezervari</a></td>  
                        <td><a href="index.php">Acasă</a></td>
                        <td><a href="contact.php">Contact</a></td>
                        <td><a href="logout.php">Logout</a></td>
                    <?php } elseif ($_SESSION['rol'] == 'administrator') { ?>
                        <td><a href="restaurante.php">Restaurante</a></td>
                        <td><a href="produse_admin.php">Produse</a></td>
                        <td><a href="comenzi_admin.php">Comenzi</a></td>
                        <td><a href="rezervari_admin.php">Rezervari</a></td>
                        <td><a href="clienti.php">Clienti</a></td>
                        <td><a href="admin_approval.php">Approve Admins</a></td>
                        <td><a href="inbox.php">Inbox</a></td>  <td><a href="logout.php">Logout</a></td>
                    <?php } ?>
                <?php } else { ?>
                    <td><a href="restaurante_clienti.php">Restaurante</a></td>
                    <td><a href="index.php">Acasă</a></td>
                    <td><a href="contact.php">Contact</a></td>
                    <td><a href="login.php">Login</a></td>
                <?php } ?>
            </tr>
        </table>
        <div class="menu-dropdown">
            <?php if (isset($_SESSION['rol'])) { ?>
                <?php if ($_SESSION['rol'] == 'client') { ?>
                    <a href="restaurante_clienti.php">Restaurante</a>
                    <a href="comenzi_clienti.php">Comenzi</a>
                    <a href="rezervari_clienti.php">Rezervari</a>  
                    <a href="index.php">Acasă</a>
                    <a href="contact.php">Contact</a>
                    <a href="logout.php">Logout</a>
                <?php } elseif ($_SESSION['rol'] == 'administrator') { ?>
                    <a href="restaurante.php">Restaurante</a>
                    <a href="produse_admin.php">Produse</a>
                    <a href="comenzi_admin.php">Comenzi</a>
                    <a href="rezervari_admin.php">Rezervari</a>
                    <a href="clienti.php">Clienti</a>
                    <a href="admin_approval.php">Approve Admins</a>
                     <a href="inbox.php">Inbox</a>   <a href="logout.php">Logout</a>
                <?php } ?>
            <?php } else { ?>
    <td><a href="restaurante_clienti.php">Restaurante</a></td>
    <td><a href="index.php">Acasă</a></td>
    <td><a href="contact.php">Contact</a></td>
    <td><a href="login.php">Login</a></td>
<?php } ?>
        </div>
    </nav>
</header>