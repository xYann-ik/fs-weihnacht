<?php
    if (!isset($data)) {
        $data = $_SESSION['card_data'] + $_SESSION['card_data']['r'];
    }
?>

<div class="card-preview-wrapper">
    <div class="card-preview" tabindex="0">
        <?php
        if ($_SESSION['loggedin'] != true) {
        ?>
        <div class="front">
            <img src="cards/<?=$data['file']?>.jpg" width="1200" />
        </div>
        <?php
        }
        ?>
        <div class="back">
            <div class="message">
                <?=nl2br($data['message'])?>
            </div>
            <div class="address">
                <h3>
                    <?=$data['name']?>
                </h3>
                <h3>
                    <?=$data['address']?>
                </h3>
                <h3>
                    <?=$data['plz'] . ' ' . $data['city']?>
                </h3>
            </div>
        </div>
    </div>
</div>