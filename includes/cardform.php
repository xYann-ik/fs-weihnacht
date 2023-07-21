
<form method="POST" enctype="multipart/form-data">
    <input type="email" name="r[email]" placeholder="Deine E-Mail Adresse" required value="<?=$_SESSION['card_data']['r']['email']?>" />
    <input type="text" name="r[name]" placeholder="Name des Empfängers" required value="<?=$_SESSION['card_data']['r']['name']?>" />
    <input type="text" name="r[address]" placeholder="Adresse" required value="<?=$_SESSION['card_data']['r']['address']?>" />
    <div class="flex plzcity">
        <input type="numeric" name="r[plz]" placeholder="PLZ" required value="<?=$_SESSION['card_data']['r']['plz']?>" />
        <input type="text" name="r[city]" placeholder="Ort" required value="<?=$_SESSION['card_data']['r']['city']?>" />
    </div>
    <textarea name="r[message]" maxlength="900" placeholder="<?=$lang['entermessage']?>" rows="6"><?=$_SESSION['card_data']['r']['message']?></textarea>
    <select class="js-choice">
        <?php
            $ests = $p->getEstablishments();
            foreach ($ests as $est) {
                echo '<option>' . $est['name'] . ' - ' . $est['plz'] . ' ' . $est['city'] . '</option>';
            }
        ?>
    </select>
    <div class="templates_wrapper">
        <?php
            foreach ($templates as $id => $tmp) {
                if ($id === array_key_first($templates) && !$_SESSION['card_data']['template']) {
                    $_SESSION['card_data']['template'] = array_key_first($templates);
                }
                ?>
                <div>
                    <input type="radio" id="<?=$id?>" required name="template" value="<?=$id?>" <?=$_SESSION['card_data']['template'] === $id ? 'checked' : ''?>>
                    <label for="<?=$id?>">
                        <img src="assets/templates/<?=$id?>.jpg" />
                        <?php
                        if ($tmp['overlay']) {
                            echo '<img src="assets/templates/'.$tmp['overlay'].'" />';
                        }
                        ?>
                    </label>
                </div>
                <?php
            }
        ?>
    </div>
    <label class="upload">
        <span>
            Dein Foto hochladen!
        </span>
        <input type="file" name="userimage" value="<?=$_SESSION['file']?>" accept="image/*" />
    </label>

    <button type="submit" class="btn">
        Absenden
    </button>
</form>

<script>
  const element = document.querySelector('.js-choice');
  const choices = new Choices(element, {
    shouldSort: false
  });
</script>