<?php

class PostAPI {

    // Create connection
    protected $db;
    
    public $templates;
    public $cardsPath;

    function __construct() {
        list($servername, $database, $username, $password) = require_once('dbconfig.php');

        $this->db = mysqli_connect($servername, $username, $password, $database);
        $this->cardsPath = 'cards/';
        $this->templates = require('templates.php');

        if ($this->db->connect_error) {
            die("Connection failed: " . $this->db->connect_error);
        }

        require_once('libs/WideImage/WideImage.php');
    }

    function __deconstruct() {
        mysqli_close($this->db);
    }

    /* Login user and set session */
    function login () {
        if ($_POST['username'] && $_POST['pw']) {
            $userQuery = $this->db->query("SELECT pw FROM `user` WHERE username = '" . mysqli_real_escape_string($this->db, $_POST['username']) . "'");
            $user = mysqli_fetch_assoc($userQuery);
            if (md5($_POST['pw']) == $user['pw']) {
                $_SESSION['loggedin'] = true;
                header('Location:backend.php');
                return;
            }
        }
        header('Location:');
        exit;
    }

    /* Get array of establishments */
    function getEstablishments () {
        $data = $this->db->query("SELECT * FROM `establishments` ORDER BY plz, city, name");
        $ests = array();
        while ($row = mysqli_fetch_assoc($data)) {
            $ests[] = $row;
        }
        return $ests;
    }

    /* Print subscriber data to AG Grid */
    function getSubscribers ($id = null) {
        $data = $this->db->query("SELECT * FROM `subscribers`" . ($id ? " WHERE id = " . intval($id) : ";"));
        $subscribers = array();
        while ($row = mysqli_fetch_assoc($data)) {
            $subscribers[] = $row;
        }
        if ($id) {
            return $subscribers[0];
        }
        else {
            print_r(json_encode($subscribers));
        }
    }

    /* Accept a card */
    function subscriberAccept ($id = 0) {
        if (is_numeric($id) && $id > 0) {
            $data = $this->getSubscribers($id);
            $this->db->query("UPDATE `subscribers` SET verified = TRUE WHERE id = " . intval($id) . ";");
            echo 'Submit to Post API';
        }
    }

    /* Deny a card */
    function subscriberDeny ($id = 0) {
        if (is_numeric($id) && $id > 0) {
            $data = $this->getSubscribers($id);
            @unlink($this->cardsPath . $data['file'] . '.jpg');
            $this->db->query("DELETE FROM `subscribers` WHERE id = " . intval($id) . ";");
        }
    }

    /* Submit data to Post letter API */
    function saveData () {
        if ($_SESSION && $_SESSION['card_data'] && $_SESSION['card_data']['r'] && $_SESSION['card_data']['file']) {
            $columns = implode(", ",array_keys($_SESSION['card_data']['r'])) . ', file';
            $escaped_values = array_map(array($this->db, 'real_escape_string'), array_values($_SESSION['card_data']['r']));

            $values  = implode("', '", $escaped_values) . "', '" . $_SESSION['card_data']['file'];
            $sql = "INSERT INTO `subscribers`($columns) VALUES ('$values')";
            mysqli_query($this->db, $sql);
            session_unset();
            header("Location: index.php?success=1");
        }
    }
    /* Handle image upload and form data */
    function handleSubmit () {
        if ($_POST) {
            $_SESSION['card_data'] = $_POST;
            $card = $this->applyImageTemplate($_FILES['userimage']['tmp_name']);
        }
    }

    // Deletes all unused card files
    function deleteUnusedFiles () {
        $files = glob($this->cardsPath . '*');
        $keepFiles = [];
        $threshold = strtotime('-3 day');
        
        $data = $this->db->query("SELECT file FROM `subscribers`;");

        while ($row = mysqli_fetch_assoc($data)) {
            $keepFiles[] = $this->cardsPath . $row['file'] . '.jpg';
        }

        foreach ($files as $file) {
            if (is_file($file)) {
                if ($threshold >= filemtime($file) && !in_array($file, $keepFiles)) {
                    @unlink($file);
                }
            }
        }
    }

    function applyImageTemplate ($uploadedImage) {
        $template = htmlspecialchars($_POST['template']);
        $templates_folder = 'assets/templates/';
        $template_image = $templates_folder.$template.'.jpg';
        $card_name = 'card_'.bin2hex(random_bytes(18)).'-'.date('Y-m-d.H:i:s');
        $templateData = $this->templates[$template];

        if (!file_exists($template_image) || !$templateData) {
            $template = 'template1';
            $template_image = $templates_folder.$template.'.jpg';
        }
        
        $template_file = WideImage::loadFromFile($template_image)->resize(1819, 1311, 'fill');
        if ($uploadedImage) {
            $width = $templateData['width'] ?: 300;
            $height = $templateData['height'] ?: 300;
            

            $userimage = WideImage::loadFromFile($uploadedImage);

            // Rotate cam pictures
            $origWidth = $width;
            switch (@exif_read_data($uploadedImage)['Orientation'] ?: 0) {
                case 6:
                case 8:
                    $width = $height;
                    $height = $origWidth;
                    break;
            }

            if ($templateData['crop']) {
                $userimage = $userimage->resize($width, $height, 'outside');
                $userimage = $userimage->crop('center', 'middle', $width, $height);
            }
            else {
                $userimage = $userimage->resize($width, $height, $templateData['fit'] ?: 'inside');
            }

            // Rotate cam pictures
            switch (@exif_read_data($uploadedImage)['Orientation'] ?: 0) {
                case 3:
                    $userimage = $userimage->rotate(180, null, false);
                    break;
                case 6:
                    $userimage = $userimage->rotate(90, null, false);
                    break;
                case 8:
                    $userimage = $userimage->rotate(-90, null, false);
                    break;
            }

            if ($templateData['rotate']) {
                $userimage = $userimage->rotate($templateData['rotate']);
            }

            $new = $template_file->merge($userimage, $templateData['x'], $templateData['y'], 100);
        }
        else {
            $new = $template_file;
        }

        if ($templateData['overlay']) {
            $overlay_image = WideImage::loadFromFile($templates_folder . $templateData['overlay']);
            $new = $new->merge($overlay_image, 0, 0, 100);
        }

        $new->saveToFile($this->cardsPath . $card_name . '.jpg');

        $_SESSION['card_data']['file'] = $card_name;
    }
}
?>