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
<body class="noscroll">
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
                if ($_GET['success'] == 1) {
                    include('includes/thanks.php');
                }
                elseif ($_POST) {
                    include('includes/cardpreview.php');
                    ?>
                    <a class="btn" href="?save=1">
                        <?=$lang['savesend']?>
                    </a>
                    <?php
                }
                else {
                    include('includes/cardform.php');
                }
            }
        ?>
        <a class="btn" href="?admin=1">Admin</a>
    </section>
</body>
</html>