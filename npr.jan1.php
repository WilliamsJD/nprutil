<html>
<head>
<title>NPR Morning Edition - Jan 1, 2015 Schedule</title>
</head>
<body>
<h1>NPR Morning Edition - Jan 1, 2015 Schedule</h1>

<?php

$url = 'http://api.npr.org/query?id=3&date=2015-01-01&numResults=25&fields=title,show,audio&output=NPRML&apiKey=MDAwMTk0NzQ1MDEyOTc4NzE1NzJmMGYzZg001';

$xmls = file_get_contents($url);

$xml = simplexml_load_string($xmls);

?>
<table><tr>
<th>Segment</th>
<th>Duration</th>
<th>Cummulative</th>
<th>Block</th>
<th>Title</th>
<th>Playlist</th>
<th>MP3</th>
</tr><tr>
<th>segNum</th>
<th>duration</th>
<th>durtotal</th>
<th>block</th>
<th>title</th>
<th>mp3</th>
<th>mp3url</th>
</tr>
<?php

$blocklen = array("A" => 629, "B" => 429, "C" => 444, "D" => 239, "E" => 449);

$fmts = "<td>%s</td>";
$fmtu = "<td><a href='%s'>%s</a></td>";

foreach($xml->list->story as $istory)
	$slist[intval($istory->show->segNum)] = $istory;

$durtotal = 0;
$block = 'A';

for($i = 1; array_key_exists($i, $slist); $i ++) {
	$istory = $slist[$i];
	printf("<tr>");
	printf($fmts, $istory->show->segNum);

	$dur = intval($istory->audio->duration);

	$seconds = $dur % 60;
	$minutes = ($dur - $seconds) / 60;
	$durint = new DateInterval("PT" . $minutes . "M" . $seconds . "S");

	printf($fmts, $durint->format("%I:%S"));

	if ($dur <= 30) {
		$block = "R";
		$durtotal = 0;
	} else {
		if ($durtotal + $dur <= $blocklen[$block]) {
			$durtotal += $dur;
		} else {
			$durtotal = $dur;
			$block ++;
			if ($block > 'E') $block = "A";
		}
	}

	$seconds = $durtotal % 60;
	$minutes = ($durtotal - $seconds) / 60;
	$durtotalint = new DateInterval("PT" . $minutes . "M" . $seconds . "S");

	printf($fmts, $durtotalint->format("%I:%S"));

	printf($fmts, $block);
	if ($block == "R") $block = "C";

	printf($fmts, $istory->title);
	printf($fmtu, $istory->audio->format->mp3, "playlist");

	$mp3url = "npr.jan1.play.php?mp3playlist={$istory->audio->format->mp3}";
	printf($fmtu, $mp3url, "mp3");

	printf("</tr>\n");

}

?>

</table>

</body>
</html>
