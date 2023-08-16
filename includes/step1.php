<h1>
    Gestalte jetzt deine Postkarte mit unserem Weihnachtsbier
</h1>
<p>
    Einfach Foto von dir und deinen Freunden hochladen und Freude bereiten.
</p>

<form action="?step=2" method="POST" enctype="multipart/form-data">
    <label class="upload">
        <span>
            Dein Foto hochladen!
        </span>
        <input type="file" name="userimage" value="<?=$_SESSION['file']?>" accept="image/*" />
    </label>
</form>

<script>
    document.querySelector('input[type=file]').addEventListener('change', () => {
        document.querySelector('form').submit()
    })
</script>