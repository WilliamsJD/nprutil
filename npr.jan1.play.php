<html>
<body>

<?php
	
$fmtu = "<td><a href='%s'>%s</a></td>";
$mp3playlist = $_GET["mp3playlist"];
#echo "mp3playlist: $mp3playlist<br/>";
$mp3url = file_get_contents($mp3playlist);
#echo "mp3url: $mp3url<br/>";

header('Location: '. $mp3url);
exit;

?>

</body>
</html>
