<html>

<?php

$debug = 0;

function getvalue($key) {
	if (array_key_exists($key, $_GET)) $value = $_GET[$key];
	else $value = "";
	return $value;
}

function dtint($time_seconds) {
	$seconds = $time_seconds % 60;
	$minutes = ($time_seconds - $seconds) / 60;
	$interval = new DateInterval("PT" . $minutes . "M" . $seconds . "S");

	return $interval;
}

$rundate = getvalue("date");
if (! $rundate) $rundate = strftime("%Y-%m-%d");

?>

<head>
<title>NPR Morning Edition - <? print $rundate ?> Schedule</title>
</head>
<body>
<h1>NPR Morning Edition - <? print $rundate ?> Schedule</h1>

<?php

#
# Form the query for NPR
#

$nprurl = 'http://api.npr.org/query';
$nprurl .= '?id=3';
$nprurl .= "&date={$rundate}";
$nprurl .= "&numResults=25";
$nprurl .= "&fields=title,show,audio";
$nprurl .= "&output=NPRML";
$nprurl .= "&apiKey=MDAwMTk0NzQ1MDEyOTc4NzE1NzJmMGYzZg001";

if ($debug) echo "<h2>$nprurl</h2>\n";

# Query NPR, error out if not available

$xmls = file_get_contents($nprurl);
if (strpos($xmls, "There were no results") > 0) {
	printf("<h4>Could not get Morning Edition segment list for date %s.</h4>\n",		$rundate);
	exit;
}

# Parse the XML

$xml = simplexml_load_string($xmls);

# Start display table

?>
<table><tr>
<th>Segment</th>
<th>Duration</th>
<th>Cummulative</th>
<th>Block</th>
<th>Title</th>
<th>Playlist</th>
<th>MP3</th>
</tr>
<?php

# Broadcast clock data

$blocklen = array("A" => 629, "B" => 429, "C" => 444, "D" => 239, "E" => 449);

# Table display formats

$fmts = "<td>%s</td>";
$fmtu = "<td><a href='%s' target='_blank'>%s</a></td>";

# Order segment rows

foreach($xml->list->story as $istory)
	$slist[intval($istory->show->segNum)] = $istory;

# Display segment rows
#
$durtotal = 0;
$block = 'A';

for($i = 1; array_key_exists($i, $slist); $i ++) {
	$istory = $slist[$i];
	printf("<tr>");
	printf($fmts, $istory->show->segNum);

	$dur = intval($istory->audio->duration);

#	$seconds = $dur % 60;
#	$minutes = ($dur - $seconds) / 60;
#	$durint = new DateInterval("PT" . $minutes . "M" . $seconds . "S");

	$durint = dtint($dur);

	printf($fmts, $durint->format("%I:%S"));

	# If this segment is 30s or less, it must be a return
	# Otherwise, determine if this segment would push this broadcast clock
	# block over the size of the block.
	# If it does, advance to the next block

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

#	$seconds = $durtotal % 60;
#	$minutes = ($durtotal - $seconds) / 60;
#	$durtotalint = new DateInterval("PT" . $minutes . "M" . $seconds . "S");

	$durtotalint = dtint($durtotal);

	printf($fmts, $durtotalint->format("%I:%S"));

	printf($fmts, $block);

	# If this was a return, the next segment is in the C-block

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
