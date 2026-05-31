<!DOCTYPE html>
<html>
<body>

<audio id="bg-music" loop>
    <source src="musique.mp3" type="audio/mpeg">
</audio>

<script>
document.addEventListener("click", function() {
    document.getElementById("bg-music").play();
}, { once: true });
</script>

</body>
</html>