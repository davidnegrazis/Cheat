
<?php
include("db_connect.php");
$c = 0;
$get_msg = "SELECT `message`, `time` FROM `messages` ORDER BY `msg_id` DESC";
$result = mysqli_query($dbc, $get_msg) or DIE("could not fetch messages");
echo "<fieldset><legend>Messages</legend>";
echo "<table border='1'>";
while ($row = mysqli_fetch_array($result)) {
	echo "<tr><td><font size='2'>[" . substr($row['time'], -8) . "] " . $row['message'] . "</font></td></tr>";
	$c++;
}
echo "</table></fieldset>";
?>