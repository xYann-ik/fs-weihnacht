<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Feldschlösschen Weihnachtskarte</title>

    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/choices.js/public/assets/styles/choices.min.css"/>
    <script src="https://cdn.jsdelivr.net/npm/choices.js/public/assets/scripts/choices.min.js"></script>
    
    <link rel="stylesheet" href="assets/css/style.css"/>
    
    <?php
        require_once('modules/startup.php');
        require_once('modules/api.php');
        $templates = require_once('modules/templates.php');
        $p = new PostAPI;
        if ($_POST && $_POST['username']) {
            $p->login();
        }
        $p->handleSubmit();

        if ($_GET['save'] == 1) {
            $p->saveData();
        }
    ?>
</head>
<body>
    <header>
        <a href="index.php">
            <img src="assets/img/logo.png" alt="Feldschlösschen" />
        </a>
    </header>
    <section class="container">
        <?php
            if ($_GET['admin'] == 1) {
                if ($_SESSION['loggedin'] == true) {
                    header('Location:backend.php');
                }
                include('includes/login.php');
            }
            else {
                include('includes/step'.$p->step.'.php');
            }
        ?>
    </section>
    <footer>
        <a href="">Teilnahmebedingungen</a>
        <a href="">Impressum</a>
        <a href="?admin=1">Admin</a>
    </footer>
</body>
</html>