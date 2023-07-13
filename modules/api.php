<?php

class PostAPI {

    // Create connection
    protected $db;
    
    public $templates;

    function __construct() {
        list($servername, $database, $username, $password) = require_once('dbconfig.php');

        $this->db = mysqli_connect($servername, $username, $password, $database);
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
            @unlink('cards/' . $data['file'] . '.jpg');
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

    function applyImageTemplate ($uploadedImage) {
        $template = htmlspecialchars($_POST['template']);
        $templates_folder = 'assets/templates/';
        $template_image = $templates_folder.$template.'.jpg';
        $card_name = 'card_'.bin2hex(random_bytes(18));

        if (!file_exists($template_image) || !$this->templates[$template]) {
            $template = 'template1';
            $template_image = $templates_folder.$template.'.jpg';
        }
        
        $template_file = WideImage::loadFromFile($template_image)->resize(1819, 1311, 'fill');
        if ($uploadedImage) {
            $width = $this->templates[$template]['width'] ?: 300;
            $height = $this->templates[$template]['height'] ?: 300;

            
            $userimage = WideImage::loadFromFile($uploadedImage);
            if ($this->templates[$template]['crop']) {
                $userimage = $userimage->resize($width, $height, 'outside');
                $userimage = $userimage->crop('center', 'middle', $width, $height);
            }
            else {
                $userimage = $userimage->resize($width, $height, $this->templates[$template]['fit'] ?: 'inside');
            }

            $new = $template_file->merge($userimage, $this->templates[$template]['x'], $this->templates[$template]['y'], 100);

            $new->saveToFile('cards/' . $card_name . '.jpg');
        }
        else {
            $template_file->saveToFile('cards/' . $card_name . '.jpg');
        }

        $_SESSION['card_data']['file'] = $card_name;
    }
}
?>