
<h1>
    Deine Angaben
</h1>
<form method="POST" enctype="multipart/form-data">
    <input type="email" name="r[email]" placeholder="Deine E-Mail Adresse" required value="<?=$_SESSION['card_data']['r']['email']?>" />
    <input type="text" name="r[name]" placeholder="Name des Empfängers" required value="<?=$_SESSION['card_data']['r']['name']?>" />

    <button type="submit" class="btn">
        Weiter
    </button>
</form>