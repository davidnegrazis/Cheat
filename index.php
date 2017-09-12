<?php
require("db_connect.php");
include("cardgame_functions.php");
session_start();
if (ISSET($_POST['logout'])) {
	if (ISSET($_SESSION['host'])) {
		$delete = "DELETE FROM `cheat`.`game_data` WHERE `game_data`.`game_id` =" . $_SESSION['game_id'];
		mysqli_query($dbc, $delete) or DIE("could not delete");
	}
	elseif (ISSET($_SESSION['queue'])) {
		$change_queue = "UPDATE `queue` SET `true`='0' WHERE `user_id`=" . $_SESSION['user_id'];
		mysqli_query($dbc, $change_queue) or DIE("could not leave queue");
	}
	unset($_SESSION);
	session_destroy();
	unset($_POST);
}
if (!ISSET($_SESSION['user_id'])) {
	header("Location:cheat_login.php");
}
?>
<html>
<head>
<title>Cheat Lobby</title>
<nav style="float:right">
	<fieldset>
		Welcome, <?php echo $_SESSION['username']; ?>!
		<form method="POST" action="<?php echo $_SERVER['PHP_SELF']; ?>">
			<input type="submit" name="logout" value="Logout">
		</form>
	</fieldset>
</nav>
<body>
<h1>The Lounge</h1>
<br />

<?php

$msg5 = null;
if ($_SESSION['user_id'] == 14 or $_SESSION['user_id'] == 18) {
	echo "<h2><font color='blue'>Welcome, admin!</font></h2>";
	?>
	<form method="POST" action="<?php echo $_SERVER['PHP_SELF']; ?>">
		<input type="text" name="removeid" placeholder="ID of game to remove">
		<input type="submit" name="removegame" value="Delete">
	</form>
	<?php
}

if (ISSET($_POST['removegame'])) {
	if ($_POST['removeid'] != null) {
		$id = $_POST['removeid'];
		$select = "SELECT `game_id`, `game_status` FROM `game_data`";
		$result = mysqli_query($dbc, $select) or DIE("Query error number 1");
		$exists = false;
		while ($row = mysqli_fetch_array($result)) {
			if ($row['game_id'] == $id and $row['game_status'] == "queue") {
				$exists = true;
				break;
			}
		}
		if ($exists == true) {
			$delete = "DELETE FROM `cheat`.`game_data` WHERE `game_data`.`game_id` =" . $id;
			mysqli_query($dbc, $delete) or DIE("Could not delete");
			$msg5 = "Deleted game.";
		}
		else {
			$msg5 = "That game ID was not found!";
		}
		
	}
	else {
		$msg5 = "Did not delect game ID.";
	}
}

if (ISSET($_POST['end'])) {
	$delete = "DELETE FROM `cheat`.`game_data` WHERE `game_data`.`game_id` =" . $_SESSION['game_id'];
	mysqli_query($dbc, $delete) or DIE("could not delete");
}

//check for game' existence
if (ISSET($_SESSION['game_id'])) {
	$get = "SELECT `game_id` FROM `game_data`";
	$result = mysqli_query($dbc, $get) or DIE("slain");
	$exists = false;
	while ($row = mysqli_fetch_array($result)) {
		if ($_SESSION['game_id'] == $row['game_id']) {
			$exists = true;
			break 1;
		}
	}
	if ($exists == false) {
		$_POST['leave'] = true;
	}
	
	//check for my existance in queue
	if ($exists == true) {
		$get_queues = "SELECT `user_id`, `true` FROM `queue` WHERE `game_id`=" . $_SESSION['game_id'];
		$result = mysqli_query($dbc, $get_queues) or DIE("slain");
		$exists = false;
		while ($row = mysqli_fetch_array($result)) {
			if ($row['user_id'] == $_SESSION['user_id'] and $row['true'] == 1) {
				$exists = true;
				break 1;
			}
		}
		if ($exists == false) {
			$_POST['leave'] = true;
		}
	}
}

$msg4 = null;
if (ISSET($_POST['send_msg']) and $_POST['send_msg'] != "false") {
	if (strlen($_POST['message']) > 0 and strlen($_POST['message']) < 151) {
		$call_msg = "<font color='3a3fc4'>" . $_SESSION['username'] . ": " . $_POST['message'] . "</font>";
		$call_msg = mysqli_real_escape_string($dbc, $call_msg);
		$insert_msg = "INSERT INTO `cheat`.`messages` (`msg_id`, `game_id`, `message`, `time`) VALUES ('NULL', '" . $_SESSION['game_id'] . "', '" . $call_msg . "', NOW())";
		mysqli_query($dbc, $insert_msg) or DIE("could not insert chat msg");
		header("Location:index.php");
	}
	else {
		$msg4 = "Invalid length of message. Must be 1-150 characters.";
	}
}


$msg3 = null;
if (ISSET($_POST['delete'])) {
	if (ISSET($_POST['id'])) {
		$change_queue = "UPDATE `queue` SET `true`='0' WHERE `user_id`=" . $_POST['id'];
		mysqli_query($dbc, $change_queue) or DIE("could not leave queue");
		header("Location:index.php");
		$msg3 = "Kicked player.";
	}
	else {
		$msg3 = "You did not select a player to kick.</strong>";
	}
}

if (ISSET($_POST['leave']) and $_POST['leave'] != "false") {
	$change_queue = "UPDATE `queue` SET `true`='0' WHERE `user_id`=" . $_SESSION['user_id'];
	mysqli_query($dbc, $change_queue) or DIE("could not leave queue");
	foreach ($_SESSION as $key => $value) {
		if ($key != "user_id" and $key != "username") {
			unset($_SESSION[$key]);
		}
	}
	$_POST = array();
	$_POST['leave'] = "false";
	$_SESSION['msg'] = null;
}

if (ISSET($_POST['leave']) and $_POST['leave'] == true) {
	$change_queue = "UPDATE `queue` SET `true`='0' WHERE `user_id`=" . $_SESSION['user_id'];
	mysqli_query($dbc, $change_queue) or DIE("could not leave queue");
	foreach ($_SESSION as $key => $value) {
		if ($key != "user_id" and $key != "username") {
			unset($_SESSION[$key]);
		}
	}
	$_POST = array();
	$_POST['leave'] = "false";
	$_SESSION['msg'] = null;
}

//add ai
if (ISSET($_POST['ai'])) {
	$insert_queue = "INSERT INTO `cheat`.`queue` (`queue_id`, `game_id`, `user_id`, `true`) VALUES ('NULL', '" . $_SESSION['game_id'] . "', '1', '1')";
	mysqli_query($dbc, $insert_queue) or DIE("Error adding to queue!");
	header("Location:index.php");
}

$msg = null;
if (ISSET($_POST['go']) and !ISSET($_SESSION['queue'])) {
	if ($_POST['search'] != null) {
		$id = $_POST['search'];
		$select = "SELECT `game_id`, `game_status` FROM `game_data`";
		$result = mysqli_query($dbc, $select) or DIE("Query error number 1");
		$exists = false;
		while ($row = mysqli_fetch_array($result)) {
			if ($row['game_id'] == $id and $row['game_status'] == "queue") {
				$exists = true;
				break;
			}
		}
		if ($exists == true) {
			$insert_queue = "INSERT INTO `cheat`.`queue` (`queue_id`, `game_id`, `user_id`, `true`) VALUES ('NULL', '" . $id . "', '" . $_SESSION['user_id'] . "', '1')";
			mysqli_query($dbc, $insert_queue) or DIE("Error adding to queue!");
			$_SESSION['queue'] = true;
			$_SESSION['msg'] = "Found a game with ID " . $id . "! You've been added to the queue. Waiting for host to start game...";
			$_SESSION['game_id'] = $id;
			$_SESSION['wait'] = true;
		}
		else {
			$msg = "That game ID was not found!";
		}
	}
	else {
		$msg = "You didn't enter a game ID to search for.";
	}
}
if (ISSET($_POST['create']) and $_POST['search'] == null and !ISSET($_SESSION['queue'])) {
	$insert = "INSERT INTO `cheat`.`game_data` (`game_id`, `game_status`, `rank_index`,`turn`, `placed`, `pool`, `cheat`, `cheated`, `show_cheat`, `cheat_caller`, `cheat_accused`, `winner`) VALUES (NULL, 'queue', '0', '', '', '', '', '', '0', '', '', '')";
	mysqli_query($dbc, $insert) or DIE("Query error 2");
	$id = mysqli_insert_id($dbc);
	$insert_queue = "INSERT INTO `cheat`.`queue` (`queue_id`, `game_id`, `user_id`, `true`) VALUES ('NULL', '" . $id . "', '" . $_SESSION['user_id'] . "', '1')";
	mysqli_query($dbc, $insert_queue) or DIE("Queue not updated.");
	$_SESSION['msg'] = "You created a game with ID " . $id;
	$_SESSION['queue'] = true;
	$_SESSION['game_id'] = $id;
	$_SESSION['host'] = true;
	
}

?>

<div style="float:right">
	<?php
	if (!ISSET($_SESSION['queue'])) {
		$get_availablegames = "SELECT `game_id` FROM `game_data` WHERE `game_status`='queue'";
		$result = mysqli_query($dbc, $get_availablegames) or DIE("could not fetch available games.");
		echo "<br />Available games:<br />";
		while ($row = mysqli_fetch_array($result)) {
			echo $row['game_id'] . "<br />";
		}
	}
	?>
</div>

<?php
if (!ISSET($_SESSION['queue'])) {
?>
	<p>Smell that hint of Cuban cigar in the air? We all do.<br />
	Join or create a new game and play!</p>
	<form method="POST" action="<?php echo $_SERVER['PHP_SELF']; ?>">
		<input type="text" name="search" placeholder="Search for a game">'
		<input type="submit" name="go" value="Search">
		<input type="submit" name="refresh" value="Refresh"><br />
		<input type="submit" name="create" value="Create new game">
	</form>
<?php
}
if (ISSET($_SESSION['msg'])) {
	$msg = $_SESSION['msg'];
}
echo $msg;
echo "<br />" . $msg5;

if (ISSET($_SESSION['queue'])) {
	$c = 0;
	$get_msg = "SELECT `message`, `time` FROM `messages` WHERE `game_id`=" . $_SESSION['game_id'] . " ORDER BY `msg_id` DESC";
	$result = mysqli_query($dbc, $get_msg) or DIE("could not fetch messages");
	echo "<fieldset style='float:right'><legend><strong>Messages</strong></legend>";
	echo "<table border='1'>";
	while ($row = mysqli_fetch_array($result)) {
		echo "<tr><td><font size='2'>[" . substr($row['time'], -8) . "] " . $row['message'] . "</font></td></tr>";
		$c++;
		if ($c == 10) {
			break 1;
		}
	}
	echo "</table></fieldset>";
}

if (ISSET($_SESSION['game_id'])) {
	echo "<br /><br />Current players in queue:<br /><br />";
	$select = "SELECT `users`.`user_id`,`users`.`username`, `queue`.`true` FROM `queue` INNER JOIN `users` ON `queue`.`user_id`=`users`.`user_id` WHERE `queue`.`game_id`=" . $_SESSION['game_id'] . " ORDER BY `queue`.`queue_id` ASC";
	$result = mysqli_query($dbc, $select) or DIE("Query error 3");
	$ids = array();
	$queue = array();
	while ($row = mysqli_fetch_array($result)) {
		if ($row['true'] == 1) {
			$ids[] = $row['user_id'];
			$queue[] = $row['username'];
		}
	}
	$x = 1;
	echo "<table border='1'>";
	reset($ids);
	foreach ($queue as $player) {
		echo "<tr><td>";
		if ($player == $_SESSION['username']) {
			echo "<font color='blue'><strong>";
		}
		echo $player;
		if ($player == $_SESSION['username']) {
			echo "</font></strong>";
		}
		if ($x == 1) {
			echo " <strong>(HOST)</strong>";
			$x++;
		}
		if (ISSET($_SESSION['host']) and current($ids) != $_SESSION['user_id']) {
		?>
			<td>
				<form method="POST" action="<?php echo $_SERVER['PHP_SELF']; ?>">
					<input type="radio" name="id" value="<?php echo current($ids); ?>"><br />
					<input type="submit" name="delete" value="Kick">
				</form>
			</td>
		<?php
		}
		next($ids);
		echo "</td></tr>";
	}
	echo "</table>";
}

echo "<br />";



if (ISSET($_SESSION['host'])) {
?>
	<form method="POST" action="<?php echo $_SERVER['PHP_SELF']; ?>">
		<input type="submit" name="ai" value="Add AI">
		<input type="submit" name="refresh" value="Refresh"><br />
		<input type="submit" name="end" value="Delete game">
		<input type="submit" name="start" value="Start game"><br />
	</form>
<?php
}

if (ISSET($_SESSION['wait'])) {
?>
	<form method="POST" action="<?php echo $_SERVER['PHP_SELF']; ?>">
		<input type="submit" name="leave" value="Leave queue">
	</form>
<?php
}

if (ISSET($_SESSION['queue'])) {
?>
	<form method="POST" action="<?php echo $_SERVER['PHP_SELF'] ?>">
		<textarea name="message"></textarea><br />
		<input type="submit" name="send_msg" value="Send message">
	</form>
<?php
}

if (!ISSET($_SESSION['queue'])) {
	$queue = null;
}

$msg2 = null;
if (ISSET($_POST['start'])) {
	if (count($queue) > 2 and count($queue) < 9) {
		$players = count($queue);
		$deck = "2h,3h,4h,5h,6h,7h,8h,9h,10h,jh,qh,kh,ah,2c,3c,4c,5c,6c,7c,8c,9c,10c,jc,qc,kc,ac,2s,3s,4s,5s,6s,7s,8s,9s,10s,js,qs,ks,as,2d,3d,4d,5d,6d,7d,8d,9d,10d,jd,qd,kd,ad";
		$deck = explode(",", $deck);
		shuffle($deck);
		$deal = deal($deck, $players);
		$deal = $deal[1];
		$x = 1;
		$_SESSION['maxturn'] = 0;
		reset($deal);
		$real = 0;
		foreach ($ids as $value) {
			$find = "SELECT `users`.`ai` FROM `users` WHERE `user_id`=" . $value;
			$result3 = mysqli_query($dbc, $find) or DIE("AI find error");
			while ($row2 = mysqli_fetch_array($result3)) {
				if ($row2['ai'] == 1) {
					$ai = 1;
				}
				else {
					$ai = 0;
					$real++;
				}
			}
			$pos = "p" . $x;
			$deal[$pos] = implode(",", $deal[$pos]);
			$insert = "INSERT INTO `cheat`.`game_hands` (`game_id`, `user_id`, `player_id`, `player_hand`, `continue`, `ai`) VALUES ('" . $_SESSION['game_id'] . "', '" . $value . "', '" . $x . "', '" . $deal[$pos] . "', '', '" . $ai . "')";
			mysqli_query($dbc, $insert) or DIE("Issue starting game.");
			$x++;
			$pos++;
			$_SESSION['maxturn']++;
		}
		$update = "UPDATE `cheat`.`game_data` SET `game_status`='active', `turn`='1' WHERE `game_id`=" . $_SESSION['game_id'];
		mysqli_query($dbc, $update) or DIE("Update game error.");
		$_SESSION['rank'] = array('a', '2','3','4','5','6','7','8','9','10','j','q','k');
		$_SESSION['rank_names'] = array('Ace', 'Number 2', 'Number 3', 'Number 4', 'Number 5', 'Number 6', 'Number 7', 'Number 8', 'Number 9', 'Number 10', 'Jack', 'Queen', 'King');
		$_SESSION['rank_index'] = 0;
		//set whether or not it's a singleplayer game
		if ($real == 1) {
			$update = "UPDATE `game_data` SET `singleplayer`='1' WHERE `game_id`=" . $_SESSION['game_id'];
			mysqli_query($dbc, $update) or DIE("dammit.");
		}
		header("Location:cheat_mp.php");
	}
	else {
		$msg2 = "<br />Error: invalid number of players. 3-8 players only.";
	}
}
	

if (ISSET($_SESSION['wait'])) {
	$check = "SELECT `game_status` FROM `game_data` WHERE `game_id`=" . $_SESSION['game_id'];
	$result = mysqli_query($dbc, $check) or DIE("Oops.");
	while ($row = mysqli_fetch_array($result)) {
		if ($row['game_status'] == "active") {
			$_SESSION['maxturn'] = 0;
			$count_players = "SELECT `user_id` FROM `game_hands` WHERE `game_id`=" . $_SESSION['game_id'];
			$result = mysqli_query($dbc, $count_players) or DIE("DAMMIT!!!!");
			while ($row = mysqli_fetch_array($result)) {
				$_SESSION['maxturn']++;
			}
			$_SESSION['rank'] = array('a', '2','3','4','5','6','7','8','9','10','j','q','k');
			$_SESSION['rank_names'] = array('Ace', 'Number 2', 'Number 3', 'Number 4', 'Number 5', 'Number 6', 'Number 7', 'Number 8', 'Number 9', 'Number 10', 'Jack', 'Queen', 'King');
			header("Location:cheat_mp.php");
		}
	}
}

echo "<br />" . $msg2;
echo "<br />" . $msg3;

if (ISSET($_SESSION['queue']) and !ISSET($_POST['start'])) {
	if (!ISSET($_SESSION['host'])) {
?>
	
	<script type="text/javascript">
		window.setTimeout(function(){ document.location.reload(true); }, 20000);
	</script>
<?php
	}
	else {
	?>
	<script type="text/javascript">
		window.setTimeout(function(){ document.location.reload(true); }, 30000);
	</script>
	<?php
	}
}
if (!ISSET($_SESSION['game_id'])) {
?>
	<p><font size='2'>Did you get here unexpectedly? If so, the game was closed or you were kicked from the queue.</font></p>
<?php
}

?>
</body>
</html>