<?php
require("db_connect.php");
require("cardgame_functions.php");
session_start();
$game_id = $_SESSION['game_id'];
//get my data
$user_id = $_SESSION['user_id'];

//echo '<embed src="cheatresources/snowpatrol.mp3" autostart="true" loop="true" hidden="true">';
//echo "<h2><font color='red'>It glitched... it made me pick up all the cards...</font></h2>";
$msg4 = null;
if (ISSET($_POST['send_msg']) and $_POST['send_msg'] != "false") {
	if (strlen($_POST['message']) > 2 and strlen($_POST['message']) < 151) {
		$call_msg = "<strong>" . $_SESSION['username'] . ":</strong><font color='3a3fc4'> " . $_POST['message'] . "</font>";
		$call_msg = mysqli_real_escape_string($dbc, $call_msg);
		$insert_msg = "INSERT INTO `cheat`.`messages` (`msg_id`, `game_id`, `message`, `time`) VALUES ('NULL', '" . $_SESSION['game_id'] . "', '" . $call_msg . "', NOW())";
		mysqli_query($dbc, $insert_msg) or DIE("could not insert chat msg");
		$header = true;
		header("Location:cheat_mp.php");
		exit("You sent a message.");
	}
	else {
		$msg4 = "Invalid length of message. Must be 1-150 characters.";
	}
}


if (!ISSET($_SESSION['ai_play'])) {
	$select = "SELECT `game_data`.`singleplayer`, `game_data`.`game_status`, `game_data`.`rank_index`, `game_data`.`turn`, `game_data`.`placed`, `game_data`.`pool`,`game_data`.`cheat`,`game_data`.`cheated`, `game_data`.`show_cheat`, `game_data`.`cheat_caller`, `game_data`.`cheat_accused`, `game_data`.`winner`, `game_data`.`ai_checked`, `game_hands`.`player_id`, `game_hands`.`player_hand` FROM `cheat`.`game_data` INNER JOIN `cheat`.`game_hands` ON `game_data`.`game_id`=`game_hands`.`game_id` WHERE `game_hands`.`user_id`=" . $_SESSION['user_id'] . " AND `game_hands`.`game_id`=" . $_SESSION['game_id'];
	$result = mysqli_query($dbc, $select) or DIE("First query error.");
	while ($row = mysqli_fetch_array($result)) {
		$_SESSION['game_status'] = $row['game_status'];
		$_SESSION['rank_index'] = $row['rank_index'];
		$_SESSION['turn'] = $row['turn'];
		$_SESSION['placed'] = $row['placed'];
		$_SESSION['dump'] = $row['pool'];
		$_SESSION['cheat'] = $row['cheat'];
		$_SESSION['cheated'] = $row['cheated'];
		$_SESSION['winner'] = $row['winner'];
		$_SESSION['show_cheat'] = $row['show_cheat'];
		$_SESSION['cheat_caller'] = $row['cheat_caller'];
		$_SESSION['cheat_accused'] = $row['cheat_accused'];
		$_SESSION['hand'] = $row['player_hand'];
		$_SESSION['my_turn'] = $row['player_id'];
		$_SESSION['ai_checked'] = $row['ai_checked'];
		$_SESSION['sp'] = $row['singleplayer'];
	}

	if ($_SESSION['show_cheat'] == 0) {
		unset($_SESSION['update_cont']);
	}

	//explode arrays
	$_SESSION['placed'] = explode(",", $_SESSION['placed']);
	foreach ($_SESSION['placed'] as $key => $value) {
		if ($value == null) {
			unset($_SESSION['placed'][$key]);
		}
	}
	if ($_SESSION['placed'] == null) {
		$_SESSION['placed'] = array();
	}
	$_SESSION['dump'] = explode(",", $_SESSION['dump']);
	foreach ($_SESSION['dump'] as $key => $value) {
		if ($value == null) {
			unset($_SESSION['dump'][$key]);
		}
	}
	if ($_SESSION['dump'] == null) {
		$_SESSION['dump'] = array();
	}
	$_SESSION['hand'] = explode(",", $_SESSION['hand']);
	foreach ($_SESSION['hand'] as $key => $value) {
		if ($value == null) {
			unset($_SESSION['hand'][$key]);
		}
	}
	if ($_SESSION['hand'] == null) {
		$_SESSION['hand'] = array();
	}
}

$get = "SELECT `ai_checked` FROM `game_data` WHERE `game_id`=" . $_SESSION['game_id'];
$result = mysqli_query($dbc, $get) or DIE("died hard");
while ($row = mysqli_fetch_array($result)) {
	$_SESSION['ai_checked'] = $row['ai_checked'];
}

//ai move choice. no submit yet
$find = "SELECT `users`.`ai`, `game_hands`.`player_hand`, `game_hands`.`player_id`, `game_hands`.`user_id` FROM `users` INNER JOIN `game_hands` ON `users`.`user_id`=`game_hands`.`user_id` WHERE `game_id`=" . $_SESSION['game_id'] . " AND `player_id`=" . $_SESSION['turn'];
$result = mysqli_query($dbc, $find) or DIE("AI find error");
while ($row = mysqli_fetch_array($result)) {
	if ($row['ai'] == 1) {
		$row['player_hand'] = explode(",", $row['player_hand']);
		$_POST['input'] = aiPlay($row['player_hand'], $_SESSION['rank'][$_SESSION['rank_index']]);
		break 1;
	}
}

if (ISSET($_POST['restart'])) {
	$call_msg = "<font color='blue'><strong>" . $_SESSION['username'] . " has left the game.</strong></font>";
	$call_msg = mysqli_real_escape_string($dbc, $call_msg);
	$insert_msg = "INSERT INTO `cheat`.`messages` (`msg_id`, `game_id`, `message`, `time`) VALUES ('NULL', '" . $_SESSION['game_id'] . "', '" . $call_msg . "', NOW())";
	mysqli_query($dbc, $insert_msg) or DIE("could not insert chat msg");
	header("Location:nuke_session.php");
}
	
?>

<html>
<body>
<head>
<style>
html *
{
	font-family: Tahoma, Geneva, sans-serif;
}
</style>
<title>Cheat</title>
<link rel="shortcut icon" href="000.ico" />
</head>
<?php

if (ISSET($_SESSION['host']) and $_SESSION['game_status'] != 'win') {
//check win
	$check_win = "SELECT `user_id`, `continue`, `ai` FROM `game_hands` WHERE `game_id`=" . $_SESSION['game_id'];
	$result = mysqli_query($dbc, $check_win) or DIE("Fucked up checking all cont data");
	$cont = true;
	while ($row = mysqli_fetch_array($result)) {
		if ($row['continue'] != 'win' and $row['ai'] != 1) {
			$cont = false;
			break 1;
		}
	}
	if ($cont == true) {
		
		$change_cheat = "UPDATE `game_data` SET `placed`='', `pool`='', `cheat`='0', `cheated`='0', `show_cheat`='0', `cheat_caller`='', `cheat_accused`='', `game_status`='win' WHERE `game_id`=" . $_SESSION['game_id'];
		mysqli_query($dbc, $change_cheat) or DIE("Could not change cheat.");
		unset($_SESSION['update_cont']);
	}
}

if (ISSET($_SESSION['game_status'])) {
	//set previous turn
	if ($_SESSION['turn'] == 1) {
		$_SESSION['previous_turn'] = $_SESSION['maxturn'];
	}
	else {
		$_SESSION['previous_turn'] = $_SESSION['turn'] - 1;
	}

	//set previous rank index
	$max_rank = count($_SESSION['rank']) - 1;
	if ($_SESSION['rank_index'] == 0) {
		$_SESSION['previous_index'] = $max_rank;
	}
	else {
		$prev = $_SESSION['rank_index'] - 1;
		$_SESSION['previous_index'] = $prev;
	}

	//stuff the host does

	$msg = "";

	if (ISSET($_SESSION['previous_turn'])) {
		$t = $_SESSION['previous_turn'];
	}

	$error = false;


	//might need to include and $_SESSION['cheated'] == false
	if (ISSET($_POST['input']) and $_SESSION['cheat'] == 0 and $_SESSION['turn'] == $_SESSION['my_turn'] and $_SESSION['show_cheat'] == 0 and $_SESSION['cheat'] == 0) {
		$input =  explode(",", strtolower($_POST['input']));
		foreach ($input as $card) {
			if (!in_array($card, $_SESSION['hand'])) {
				$error = true;
				$msg = "<font color='red'><strong>You didn't make a valid input.</strong></font>";
				break 1;
			}
		}
		//check for dup
		if ($error == false and !ISSET($_SESSION['ai_play'])) {
			$count_vals = array_count_values($input);
			foreach ($count_vals as $sum) {
				if ($sum > 1) {
					$error = true;
					$msg = "<font color='red'><strong>You can't send a duplicate card.</strong></font>";
					break 1;
				}
			}
		}
		if ($error == false) {
			$dump = $_SESSION['dump'];
			$placed = $_SESSION['placed'];
			$placed = array();
			foreach ($input as $card) {
				array_push($dump, $card);
				array_push($placed, $card);
				$key = array_search($card, $_SESSION['hand']);
				unset($_SESSION['hand'][$key]);
			}
			$placed = implode(",", $placed);
			$dump = implode(",", $dump);
			if ($_SESSION['turn'] == $_SESSION['maxturn']) {
				$_SESSION['turn'] = 1;
			}
			else {
				$_SESSION['turn']++;
			}
			$end = count($_SESSION['rank']) - 1;
			if ($_SESSION['rank_index'] == $end) {
				$_SESSION['rank_index'] = 0;
			}
			else {
				$_SESSION['rank_index']++;
			}
			$_SESSION['hand'] = implode(",", $_SESSION['hand']);
			$update_gamedata = "UPDATE `cheat`.`game_data` SET `placed`='" . $placed . "', `pool`='" . $dump . "', `turn`='" . $_SESSION['turn'] . "', `rank_index`='" . $_SESSION['rank_index'] . "' WHERE `game_id`=" . $_SESSION['game_id'];
			mysqli_query($dbc, $update_gamedata) or DIE("Could not update game data while trying to submit cards.");
			$update_hand = "UPDATE `cheat`.`game_hands` SET `player_hand`='" . $_SESSION['hand'] . "' WHERE `game_id`=" . $_SESSION['game_id'] . " AND `user_id`=" . $_SESSION['user_id'];
			mysqli_query($dbc, $update_hand) or DIE("Could not update hand.");
		}
	}
	
	$_SESSION['detect_counter'] = 0;
	if ($_SESSION['ai_checked'] == 1) {
		$_SESSION['detect_counter']++;
	}

	//cheat
	$get_cheatinfo = "SELECT `cheat`, `show_cheat`, `cheated`, `ai_checked` FROM `game_data` WHERE `game_id`=" . $_SESSION['game_id'];
	$result = mysqli_query($dbc, $get_cheatinfo) or DIE("could not do secondary cheat check");
	while ($row = mysqli_fetch_array($result)) {
		$_SESSION['cheat'] = $row['cheat'];
		$_SESSION['show_cheat'] = $row['show_cheat'];
		$_SESSION['cheated'] = $row['cheated'];
		$_SESSION['ai_checked'] = $row['ai_checked'];
	}
	$_SESSION['ai_call'] = false;
	if (!empty($_SESSION['placed'])) {
		if ($_SESSION['show_cheat'] == 0 and $_SESSION['detect_counter'] < 2) {
			if (!ISSET($_POST['input'])) {
				$update = "UPDATE `game_data` SET `ai_checked`='1' WHERE `game_id`=" . $_SESSION['game_id'];
				mysqli_query($dbc, $update) or DIE("could not update AI checked");
			}
			//ai cheat detect
			$get_prevhand = "SELECT `player_hand` FROM `game_hands` WHERE `player_id`=" . $_SESSION['previous_turn'] . " AND `game_id`=" . $_SESSION['game_id'];
			$result = mysqli_query($dbc, $get_prevhand) or DIE("could not get prev hand");
			while ($row = mysqli_fetch_array($result)) {
				$prev_hand = $row['player_hand'];
			}
			$row['player_hand'] = explode(",", $row['player_hand']);
			$get_ais = "SELECT `player_hand`, `player_id`, `ai` FROM `game_hands` WHERE `game_id`=" . $_SESSION['game_id'];
			$result = mysqli_query($dbc, $get_ais) or DIE("did not start cheat detect");
			while ($row = mysqli_fetch_array($result)) {
				if ($row['ai'] == 1) {
					$row['player_hand'] = explode(",", $row['player_hand']);
					$cheat = ai_detectCheat($row['player_hand'], $_SESSION['placed'], $_SESSION['rank'][$_SESSION['previous_index']], $row['player_id'], $_SESSION['previous_turn'], $prev_hand);
					if ($cheat != false) {
						$_POST['cheat'] = true;
						$_SESSION['ai_call'] = true;
						$_SESSION['my_turn'] = $row['player_id'];
						break 1;
					}
				}
			}
		}
		
		if (ISSET($_POST['cheat']) and $_SESSION['cheat'] == 0 and $_SESSION['show_cheat'] == 0) {
				if ($_SESSION['do_win'] < 2) {
				$insert_mycheat = "UPDATE `cheat`.`game_data` SET `cheat`='1', `cheat_caller`='" . $_SESSION['my_turn'] . "', `cheat_accused`='" . $_SESSION['previous_turn'] . "' WHERE `game_id`=" . $_SESSION['game_id'];
				mysqli_query($dbc, $insert_mycheat) or DIE("Do cheat error!");
				$_SESSION['cheat'] = 1;
			}
			else {
				$msg = "It's too late to call cheat. Maybe somebody else will...";
			}
		}
	}
	if ($_SESSION['cheat'] == 1 and $_SESSION['show_cheat'] == 0) {
		if (ISSET($_SESSION['win'])) {
			$_SESSION['do_win'] = 0;
			unset($_SESSION['win']);
			unset($_SESSION['winner']);
		}
		foreach ($_SESSION['placed'] as $card) {
			if (strpos($card, $_SESSION['rank'][$_SESSION['previous_index']]) === false) {
				$_SESSION['cheated'] = 1;
				break 1;
			}
		}

		if ($_SESSION['cheated'] == 1) {
			$select_conts = "SELECT `player_id` FROM `game_hands` WHERE `game_id`=" . $_SESSION['game_id'];
			$result = mysqli_query($dbc, $select_conts) or DIE("could not select conts");
			while ($row = mysqli_fetch_array($result)) {
				$update = "UPDATE `game_hands` SET `continue`='' WHERE `player_id`=" . $row['player_id'] . " AND `game_id`=" . $_SESSION['game_id'];
				mysqli_query($dbc, $update) or DIE("could not reset game hands cont");
			}
			$update = "UPDATE `game_data` SET `winner`='' WHERE `game_id`=" . $_SESSION['game_id'];
			mysqli_query($dbc, $update) or DIE("could not empty winner");
			//$_SESSION['previous_turn'] = 1;
			$select_accused = "SELECT `player_hand` FROM `game_hands` WHERE `game_id`=" . $_SESSION['game_id'] . " AND `player_id`=" . $_SESSION['previous_turn'];
			$result = mysqli_query($dbc, $select_accused) or DIE("could not get accused");
			while ($row = mysqli_fetch_array($result)) {
				$accused = $row['player_hand'];
			}
			$accused = explode(",", $accused);
			$accused = array_merge($accused, $_SESSION['dump']);
			$get_names = "SELECT `game_data`.`cheat_accused`, `game_hands`.`player_id`, `users`.`username` FROM `cheat`.`game_data` INNER JOIN `cheat`.`game_hands` ON `game_data`.`game_id`=`game_hands`.`game_id` INNER JOIN `cheat`.`users` ON `game_hands`.`user_id` = `users`.`user_id` WHERE `game_data`.`game_id`=" . $_SESSION['game_id'];
			$result = mysqli_query($dbc, $get_names) or DIE("could not get accused name.");
			while ($row = mysqli_fetch_array($result)) {
				if ($row['player_id'] == $_SESSION['my_turn']) {
					$caller_name = $row['username'];
					$caller_id = $row['player_id'];
				}
				if ($row['player_id'] == $_SESSION['previous_turn']) {
					$accused_name = $row['username'];
					$accused_id = $row['player_id'];
				}
			}
			$accused = implode(",", $accused);
			$update_accused = "UPDATE `game_hands` SET `player_hand`='" . $accused . "' WHERE `game_id`=" . $_SESSION['game_id'] . " AND `player_id`=" . $accused_id;
			mysqli_query($dbc, $update_accused) or DIE("Sheeeit. COuld not update accused's hand.");
			$_SESSION['cheat_msg'] = $accused_name . " cheated! They have to pick up all " . count($_SESSION['dump']) . " cards!";
		}
		else {
			$select_caller = "SELECT `player_hand` FROM `game_hands` WHERE `game_id`=" . $_SESSION['game_id'] . " AND `player_id`=" . $_SESSION['my_turn'];
			$result = mysqli_query($dbc, $select_caller) or DIE("could not get caller");
			while ($row = mysqli_fetch_array($result)) {
				$caller = $row['player_hand'];
			}
			$caller = explode(",", $caller);
			$caller = array_merge($caller, $_SESSION['dump']);
			if ($_SESSION['winner'] != null) {
				$make_win = "UPDATE `game_data` SET `game_status`='win' WHERE `game_id`=" . $_SESSION['game_id'];
				mysqli_query($dbc, $make_win) or DIE("could not make win");
			}
			$_SESSION['dump'] = null;
			$get_names = "SELECT `game_data`.`cheat_accused`, `game_hands`.`player_id`, `users`.`username` FROM `cheat`.`game_data` INNER JOIN `cheat`.`game_hands` ON `game_data`.`game_id`=`game_hands`.`game_id` INNER JOIN `cheat`.`users` ON `game_hands`.`user_id` = `users`.`user_id` WHERE `game_data`.`game_id`=" . $_SESSION['game_id'];
			$result = mysqli_query($dbc, $get_names) or DIE("could not get accused name.");
			while ($row = mysqli_fetch_array($result)) {
				if ($row['player_id'] == $_SESSION['my_turn']) {
					$caller_name = $row['username'];
					$caller_id = $row['player_id'];
				}
				if ($row['player_id'] == $_SESSION['previous_turn']) {
					$accused_name = $row['username'];
					$accused_id = $row['player_id'];
				}
			}
			$caller = implode(",", $caller);
			$_SESSION['cheat_msg'] = $accused_name . " (player " . $accused_id . ") did not actually cheat! " . $caller_name . " therefore has to pick up all cards!";
			$update_caller = "UPDATE `game_hands` SET `player_hand`='" . $caller . "' WHERE `game_id`=" . $_SESSION['game_id'] . " AND `player_id`=" . $caller_id;
			mysqli_query($dbc, $update_caller) or DIE("Sheeeit. COuld not update caller's hand.");
		}
		$call_msg = "<font color=\"purple\"><strong>" . $caller_name . " (player " . $caller_id . ") called cheat on " . $accused_name . " (player " . $accused_id . ")!</strong></font>";
		$insert_msg = "INSERT INTO `cheat`.`messages` (`msg_id`, `game_id`, `message`, `time`) VALUES ('NULL', '" . $_SESSION['game_id'] . "', '" . $call_msg . "', NOW())";
		mysqli_query($dbc, $insert_msg) or DIE("could not insert cheat msg1");
		$cheat_msg = "<font color=\"red\"><strong>" . $_SESSION['cheat_msg'] . "</strong></font>";
		$insert_msg = "INSERT INTO `cheat`.`messages` (`msg_id`, `game_id`, `message`, `time`) VALUES ('NULL', '" . $_SESSION['game_id'] . "', '" . $cheat_msg . "', NOW())";
		mysqli_query($dbc, $insert_msg) or DIE("could not insert cheat msg");
		$update_dumppool = "UPDATE `cheat`.`game_data` SET `show_cheat`='1', `cheated`=" . $_SESSION['cheated'] . " WHERE `game_id`=" . $_SESSION['game_id'];
		mysqli_query($dbc, $update_dumppool) or DIE("Could not update dump and pool.");
	}
	
	//ai submit move
	if (ISSET($_POST['input']) and $_SESSION['cheat'] == 0 and ISSET($_SESSION['ai_play']) and $_SESSION['show_cheat'] == 0) {

		$input =  explode(",", $_POST['input']);
		foreach ($input as $card) {
			if (!in_array($card, $_SESSION['ai_hand'])) {
				$error = true;
				$msg = "<font color='red'><strong>You didn't make a valid input.</strong></font>";
				break 1;
			}
		}
		//check for dup
		if ($error == false) {
			$count_vals = array_count_values($input);
			foreach ($count_vals as $sum) {
				if ($sum > 1) {
					$error = true;
					$msg = "<font color='red'><strong>You can't send a duplicate card.</strong></font>";
					break 1;
				}
			}
		}
		if ($error == false) {
			$dump = $_SESSION['dump'];
			$placed = $_SESSION['placed'];
			$placed = array();
			foreach ($input as $card) {
				array_push($dump, $card);
				array_push($placed, $card);
				$key = array_search($card, $_SESSION['ai_hand']);
				unset($_SESSION['ai_hand'][$key]);
			}
			$placed = implode(",", $placed);
			$dump = implode(",", $dump);
			if ($_SESSION['turn'] == $_SESSION['maxturn']) {
				$_SESSION['turn'] = 1;
			}
			else {
				$_SESSION['turn']++;
			}
			$end = count($_SESSION['rank']) - 1;
			if ($_SESSION['rank_index'] == $end) {
				$_SESSION['rank_index'] = 0;
			}
			else {
				$_SESSION['rank_index']++;
			}
			$_SESSION['ai_hand'] = implode(",", $_SESSION['ai_hand']);
			$update_gamedata = "UPDATE `cheat`.`game_data` SET `placed`='" . $placed . "', `pool`='" . $dump . "', `turn`='" . $_SESSION['turn'] . "', `rank_index`='" . $_SESSION['rank_index'] . "', `ai_checked`='0' WHERE `game_id`=" . $_SESSION['game_id'];
			mysqli_query($dbc, $update_gamedata) or DIE("Could not update game data while trying to submit cards.");
			$update_hand = "UPDATE `cheat`.`game_hands` SET `player_hand`='" . $_SESSION['ai_hand'] . "' WHERE `game_id`=" . $_SESSION['game_id'] . " AND `player_id`=" . $_SESSION['ai_turn'];
			mysqli_query($dbc, $update_hand) or DIE("Could not update hand.");
		}
	}
}

$select = "SELECT `game_data`.`singleplayer`, `game_data`.`game_status`, `game_data`.`rank_index`, `game_data`.`turn`, `game_data`.`placed`, `game_data`.`pool`,`game_data`.`cheat`,`game_data`.`cheated`, `game_data`.`show_cheat`, `game_data`.`cheat_caller`, `game_data`.`cheat_accused`, `game_data`.`winner`, `game_data`.`ai_checked`, `game_hands`.`player_id`, `game_hands`.`player_hand` FROM `cheat`.`game_data` INNER JOIN `cheat`.`game_hands` ON `game_data`.`game_id`=`game_hands`.`game_id` WHERE `game_hands`.`user_id`=" . $_SESSION['user_id'] . " AND `game_hands`.`game_id`=" . $_SESSION['game_id'];
$result = mysqli_query($dbc, $select) or DIE("First query error.");
while ($row = mysqli_fetch_array($result)) {
	$_SESSION['game_status'] = $row['game_status'];
	$_SESSION['rank_index'] = $row['rank_index'];
	$_SESSION['turn'] = $row['turn'];
	$_SESSION['placed'] = $row['placed'];
	$_SESSION['dump'] = $row['pool'];
	$_SESSION['cheat'] = $row['cheat'];
	$_SESSION['cheated'] = $row['cheated'];
	$_SESSION['winner'] = $row['winner'];
	$_SESSION['show_cheat'] = $row['show_cheat'];
	$_SESSION['cheat_caller'] = $row['cheat_caller'];
	$_SESSION['cheat_accused'] = $row['cheat_accused'];
	$_SESSION['hand'] = $row['player_hand'];
	$_SESSION['my_turn'] = $row['player_id'];
	$_SESSION['ai_checked'] = $row['ai_checked'];
	$_SESSION['sp'] = $row['singleplayer'];
}

if ($_SESSION['show_cheat'] == 0) {
	unset($_SESSION['update_cont']);
}

//explode arrays
$_SESSION['placed'] = explode(",", $_SESSION['placed']);
foreach ($_SESSION['placed'] as $key => $value) {
	if ($value == null) {
		unset($_SESSION['placed'][$key]);
	}
}
if ($_SESSION['placed'] == null) {
	$_SESSION['placed'] = array();
}
$_SESSION['dump'] = explode(",", $_SESSION['dump']);
foreach ($_SESSION['dump'] as $key => $value) {
	if ($value == null) {
		unset($_SESSION['dump'][$key]);
	}
}
if ($_SESSION['dump'] == null) {
	$_SESSION['dump'] = array();
}
$_SESSION['hand'] = explode(",", $_SESSION['hand']);
foreach ($_SESSION['hand'] as $key => $value) {
	if ($value == null) {
		unset($_SESSION['hand'][$key]);
	}
}
if ($_SESSION['hand'] == null) {
	$_SESSION['hand'] = array();
}

//check win
$select = "SELECT `game_data`.`game_status`, `game_data`.`turn`, `game_data`.`show_cheat`, `game_data`.`winner`, `game_hands`.`user_id`, `game_hands`.`player_id`, `game_hands`.`player_hand` FROM `cheat`.`game_data` INNER JOIN `cheat`.`game_hands` ON `game_data`.`game_id`=`game_hands`.`game_id` WHERE `game_data`.`game_id`=" . $_SESSION['game_id'];
$result2 = mysqli_query($dbc, $select) or DIE("Host check win error.");
while ($row = mysqli_fetch_array($result2)) {
	$hand = $row['player_hand'];
	$hand = explode(",", $hand);
	$winner = $row['winner'];
	foreach ($hand as $key => $value) {
		if ($value == null) {
			unset($hand[$key]);
		}
	}
	if ($hand == null) {
		$hand = array();
	}
	if (count($hand) == 0) {
		if ($winner == null) {
			$update = "UPDATE `game_data` SET `winner`=" . $row['player_id'] . " WHERE `game_id`=" . $_SESSION['game_id'];
			mysqli_query($dbc, $update) or DIE("could not set winner.");
		}
		break 1;
	}
}

//check if there is a winner
if ($_SESSION['winner'] != null and $_SESSION['winner'] != $_SESSION['my_turn']) {
	$_SESSION['do_win']++;
}
else {
	$_SESSION['do_win'] = 0;
}

if ($_SESSION['do_win']  > 1) {
	$update = "UPDATE `game_hands` SET `continue`='win' WHERE `game_id`=" . $_SESSION['game_id'];
	mysqli_query($dbc, $update) or DIE("could not update win continue thing.");
}

if ($_SESSION['turn'] == 1) {
	$_SESSION['previous_turn'] = $_SESSION['maxturn'];
}
else {
	$_SESSION['previous_turn'] = $_SESSION['turn'] - 1;
}

//set previous rank index
$max_rank = count($_SESSION['rank']) - 1;
if ($_SESSION['rank_index'] == 0) {
	$_SESSION['previous_index'] = $max_rank;
}
else {
	$prev = $_SESSION['rank_index'] - 1;
	$_SESSION['previous_index'] = $prev;
}

$get_currentplayer = "SELECT `users`.`username` FROM `users` INNER JOIN `game_hands` ON `users`.`user_id`=`game_hands`.`user_id` WHERE `game_hands`.`game_id`=" . $_SESSION['game_id'] . " AND `game_hands`.`player_id`=" . $_SESSION['turn'];
$result = mysqli_query($dbc, $get_currentplayer) or DIE("Could not get current player's username.");
while ($row = mysqli_fetch_array($result)) {
	$_SESSION['cur_username'] = $row['username'];
}
if (ISSET($_SESSION['previous_turn'])) {
	$get_previousplayer = "SELECT `users`.`username` FROM `users` INNER JOIN `game_hands` ON `users`.`user_id`=`game_hands`.`user_id` WHERE `game_hands`.`game_id`=" . $_SESSION['game_id'] . " AND `game_hands`.`player_id`=" . $_SESSION['previous_turn'];
	$result = mysqli_query($dbc, $get_previousplayer) or DIE("Could not get previous player's username.");
	while ($row = mysqli_fetch_array($result)) {
		$_SESSION['pre_username'] = $row['username'];
	}
}

echo "<div><font color='green' size='4'>You are player " . $_SESSION['my_turn'] . "</font>";
?>
 || Need help? Click: <strong><font size='4'><a href="help.php" target="_blank">Help</a></font></strong> || <strong><font size='4'><a href="about.html" target="_blank">About cheat</a></font></strong> || <strong><font size='4'><a href="music.html" target="_blank">Music</a></font></strong></div></div><br />
<?php
$okay = true;
if ($_SESSION['previous_turn'] == $_SESSION['my_turn']) {
	$okay = false;
}
if ($_SESSION['sp'] == 1) {
	$okay = true;
}
unset($_SESSION['ai_play']);
if ($_SESSION['game_status'] != "win") {
	if ($_SESSION['show_cheat'] == 0 and $okay == true) {
		//ai move
		$find = "SELECT `users`.`ai`, `game_hands`.`player_hand`, `game_hands`.`player_id`, `game_hands`.`user_id` FROM `users` INNER JOIN `game_hands` ON `users`.`user_id`=`game_hands`.`user_id` WHERE `game_id`=" . $_SESSION['game_id'] . " AND `player_id`=" . $_SESSION['turn'];
		$result = mysqli_query($dbc, $find) or DIE("AI find error");
		while ($row = mysqli_fetch_array($result)) {
			if ($row['ai'] == 1) {
				$row['player_hand'] = explode(",", $row['player_hand']);
				$_POST['input'] = aiPlay($row['player_hand'], $_SESSION['rank'][$_SESSION['rank_index']]);
				$_SESSION['ai_turn'] = $row['player_id'];
				$_SESSION['ai_hand'] = $row['player_hand'];
				$_SESSION['ai_play'] = true;
				$_SESSION['ai_id'] = $row['user_id'];
				break 1;
			}
		}
	}

	echo "<fieldset><legend><h2>Rank: " . $_SESSION['rank_names'][$_SESSION['rank_index']] . " | Player " . $_SESSION['turn'] . "'s turn</h2></legend>";
	echo "<div style='float:right'>";
	$get_players = "SELECT `game_hands`.`player_hand`, `game_hands`.`player_id`, `users`.`username` FROM `cheat`.`game_hands` INNER JOIN `cheat`.`users` ON `game_hands`.`user_id`=`users`.`user_id` WHERE `game_hands`.`game_id`=" . $_SESSION['game_id'];
	$result = mysqli_query($dbc, $get_players) or DIE("Crap... couldn't fetch player hands!");
	while ($row = mysqli_fetch_array($result)) {
		$hand = $row['player_hand'];
		$hand = explode(",", $hand);
		foreach ($hand as $key => $value) {
			if ($value == null) {
				unset($hand[$key]);
			}
		}
		if ($hand == null) {
			$hand = array();
		}
		if ($_SESSION['turn'] == $row['player_id']) {
			echo "<font color='blue'><strong>";
		}
		echo $row['username'] . "'s (player " . $row['player_id'] . ") cards left: " . count($hand) . "<br />";
		if ($_SESSION['turn'] == $row['player_id']) {
			echo "</strong></font>";
		}	
	}
	echo "<br /><font size='2'><font color='blue'>Blue</font> represents who is about to place cards!</font>";
	echo "</div><div>";
	echo "<font color='blue' size='5'><strong>Total cards placed in the pool: " . count($_SESSION['dump']) . "</strong></font></div>";

	if (!empty($_SESSION['placed'])) {
		if (ISSET($_SESSION['turn'])) {
			echo "<p><strong><font size='4'>" . $_SESSION['pre_username'] . " placed " . count($_SESSION['placed']) . " " . strtolower($_SESSION['rank_names'][$_SESSION['previous_index']]) . "s!</font></strong></p>";
		}
		echo "<div>";
		foreach ($_SESSION['placed'] as $card) {
			echo "<img src='cheatresources/unknown.png' >";
		}
		echo "</div>";
	}
	if ($_SESSION['cheat'] == 1) {
		$get_names = "SELECT `game_data`.`cheat_accused`, `game_data`.`cheat_caller`, `game_hands`.`player_id`, `users`.`username` FROM `cheat`.`game_data` INNER JOIN `cheat`.`game_hands` ON `game_data`.`game_id`=`game_hands`.`game_id` INNER JOIN `cheat`.`users` ON `game_hands`.`user_id` = `users`.`user_id` WHERE `game_data`.`game_id`=" . $_SESSION['game_id'];
		$result = mysqli_query($dbc, $get_names) or DIE("could not get accused name.");
		while ($row = mysqli_fetch_array($result)) {
			if ($row['player_id'] == $row['cheat_caller']) {
				$caller_name = $row['username'];
				$caller_id = $row['player_id'];
			}
			if ($row['player_id'] == $row['cheat_accused']) {
				$accused_name = $row['username'];
				$accused_id = $row['player_id'];
			}
		}
		if ($_SESSION['cheated'] == 1) {
			$_SESSION['cheat_msg'] = $accused_name . " (player " . $accused_id . ") cheated! They have to pick up all " . count($_SESSION['dump']) . " cards!";
		}
		else {
			$_SESSION['cheat_msg'] = $accused_name . " (player " . $accused_id . ") didn't actually cheat! " . $caller_name . " therefore has to pick up all " . count($_SESSION['dump']) . " cards!";
		}
		$no_msg = true;
		echo "<hr size='5' color='red'>";
		echo "<h2>" . $caller_name . " (player " . $caller_id . ") called cheat on " . $accused_name . " (player " . $accused_id . ")!</h2>";
		echo "<div>Here are the cards placed:<br />";
		showCards($_SESSION['placed']);
		echo "<p>" . $_SESSION['cheat_msg'] . "</p>";
		echo "</div>";
		$_SESSION['cheat'] = 0;
		$_SESSION['placed'] = null;
		$_SESSION['dump'] = null;
	}
	
	echo "</fieldset>";
	
	if (ISSET($_SESSION['previous_turn'])) {
		$t = $_SESSION['previous_turn'];
	}
if ($_SESSION['show_cheat'] == 1) {
	if (!ISSET($_SESSION['update_cont'])) {
		$update_cont = "UPDATE `game_hands` SET `continue`='yes' WHERE `game_id`=" . $_SESSION['game_id'] . " AND `user_id`=" . $_SESSION['user_id'];
		mysqli_query($dbc, $update_cont) or DIE("Fuck");
		$_SESSION['update_cont'] = true;
	}
	$check_cont = "SELECT `user_id`, `continue`, `ai` FROM `game_hands` WHERE `game_id`=" . $_SESSION['game_id'];
	$result = mysqli_query($dbc, $check_cont) or DIE("Fucked up checking all cont data");
	$cont = true;
	while ($row = mysqli_fetch_array($result)) {
		if ($row['continue'] == null and $row['ai'] != 1) {
			$cont = false;
			break 1;
		}
	}
	if ($cont == true) {
		
		$get_players = "SELECT `user_id` FROM `game_hands` WHERE `game_id`=" . $_SESSION['game_id'];
		$result = mysqli_query($dbc, $get_players) or DIE("could not get players 4747474774");
		while ($row = mysqli_fetch_array($result)) {
			$update = "UPDATE `game_hands` SET `continue`='' WHERE `game_id`=" . $_SESSION['game_id'] . " AND `user_id`=" . $row['user_id'];
			mysqli_query($dbc, $update) or DIE("could not do the update query within the fetch query.");
		}
		$change_cheat = "UPDATE `game_data` SET `placed`='', `pool`='', `cheat`='0', `cheated`='0', `show_cheat`='0', `cheat_caller`='', `cheat_accused`='' WHERE `game_id`=" . $_SESSION['game_id'];
		mysqli_query($dbc, $change_cheat) or DIE("Could not change cheat.");
		unset($_SESSION['update_cont']);
	}
	
}
	
	if ($_SESSION['turn'] == $_SESSION['my_turn']) {
	?>
		<form method="POST" action="<?php echo $_SERVER['PHP_SELF'] ?>">
			<?php
			if ($_SESSION['cheated'] == 0) {
				echo '<input type="text" name="input" type="text" placeholder="Cards to play. Ex: 3c,3d" autofocus>';
			}
			?>
			<input type="submit" name="submit" value="Play!" style="background-color: 71f788">
			<?php
			if (!empty($_SESSION['placed']) and $_SESSION['cheated'] == 0) {
				echo '<input type="submit" name="cheat" value="Call cheat on player ' . $t . '!" style="background-color: ff7c81">';
			}
			?>
			<!--<input type="submit" name="restart" value="Restart!" style="background-color: #ffcc00">-->
		</form>
	
	<?php
	}
	else {
	?>
		<form method="POST" action="<?php echo $_SERVER['PHP_SELF'] ?>">
			<input type="submit" name="submit" value="See player <?php echo $_SESSION['turn']; ?>'s move">
			<p>Note: if you refresh and nothing happens, then the player is still thinking.</p>
			<?php
			if ($_SESSION['previous_turn'] != $_SESSION['my_turn'] and $_SESSION['cheated'] == 0 and !empty($_SESSION['placed'])) {
				echo '<input type="submit" name="cheat" value="Call cheat on player ' . $t . '!" style="background-color: ff7c81">';
			}
			?>
			<!--<input type="submit" name="restart" value="Restart!" style="background-color: #ffcc00">-->
		</form
	<?php
	}
	if (ISSET($no_msg)) {
		$msg = null;
	}
	
	if (ISSET($_POST['submit']) and $_SESSION['show_cheat'] == 1) {
		$msg = "<font color='purple' size='4'><strong>Waiting for everybody to see the cheat message.</strong></font>";
	}
	if (ISSET($_SESSION['win'])) {
		$msg = "</font><font color='purple' size='4'><strong>Somebody is about to win! Call cheat?</font></strong>";
	}
	echo $msg . "<br /><br />";
}
else {
	$get_name = "SELECT `users`.`username` FROM `users` INNER JOIN `game_hands` ON `users`.`user_id`=`game_hands`.`user_id` WHERE `game_hands`.`player_id`=" . $_SESSION['winner'] . " AND `game_id`=" . $_SESSION['game_id'];
	$result = mysqli_query($dbc, $get_name) or DIE("Winner username error!");
	while ($row = mysqli_fetch_array($result)) {
		$username = $row['username'];
	}
	echo "<h1>" . $username . " (player " . $_SESSION['winner'] . ") wins!</h1><br /><br />";
	
		$get_players = "SELECT `game_hands`.`player_hand`, `game_hands`.`player_id`, `users`.`username` FROM `cheat`.`game_hands` INNER JOIN `cheat`.`users` ON `game_hands`.`user_id`=`users`.`user_id` WHERE `game_hands`.`game_id`=" . $_SESSION['game_id'] . " ORDER BY `game_hands`.`player_id` ASC";
		$result = mysqli_query($dbc, $get_players) or DIE("Crap... couldn't fetch player hands!");
		while ($row = mysqli_fetch_array($result)) {
		$hand = $row['player_hand'];
		$hand = explode(",", $hand);
		foreach ($hand as $key => $value) {
			if ($value == null) {
				unset($hand[$key]);
			}
		}
		if ($hand == null) {
			$hand = array();
		}
		if ($_SESSION['turn'] == $row['player_id']) {
			echo "<font color='blue'><strong>";
		}
		echo $row['username'] . "'s (player " . $row['player_id'] . ") cards left: " . count($hand) . "<br />";
		if ($_SESSION['turn'] == $row['player_id']) {
			echo "</strong></font>";
		}	
	}
	
	echo "<br /><br />";
	?>
		<form method="POST" action="<?php echo $_SERVER['PHP_SELF'] ?>">
			<input type="submit" name="restart" value="Restart!" style="background-color: #ffcc00">
		</form>
	<?php
}

//check all win continues
if ($_SESSION['winner'] != null) {
	$check = "SELECT `player_id`, `continue`, `ai` FROM `game_hands` WHERE `game_id`=" . $_SESSION['game_id'];
	$result = mysqli_query($dbc, $check) or DIE("could not check win conts");
	$win = true;
	while ($row = mysqli_fetch_array($result)) {
		if ($row['continue'] != "win" and $row['ai'] == 0) {
			echo $row['player_id'];
			$win = false;
			break 1;
		}
	}
	if ($win == true) {
		$update = "UPDATE `game_hands` SET `continue`='win' WHERE `game_id`=" . $_SESSION['game_id'];
		mysqli_query($dbc, $update) or DIE("You know where the script failed.");
	}
}

?>
<form method="POST" action="<?php echo $_SERVER['PHP_SELF'] ?>">
	<textarea name="message" spellcheck="true"></textarea><br />
	<input type="submit" name="send_msg" value="Send message">
</form>

<?php
echo "<p>" . $msg4 . "</p>";

echo "<font color='green'><strong>My cards...</strong></font><br /><br />";
$_SESSION['hand'] = sortCards($_SESSION['hand']);
showCards($_SESSION['hand']);

if ($_SESSION['show_cheat'] == 1) {
	$_SESSION['counter']++;
	if ($_SESSION['counter'] == 4) {
		if (!ISSET($_SESSION['update_cont'])) {
			$update_cont = "UPDATE `game_hands` SET `continue`='yes' WHERE `game_id`=" . $_SESSION['game_id'] . " AND `user_id`=" . $_SESSION['user_id'];
			mysqli_query($dbc, $update_cont) or DIE("Fuck");
			$_SESSION['update_cont'] = true;
		}
	}
}
else {
	$_SESSION['counter'] = 0;
}

if ($_SESSION['turn'] != $_SESSION['my_turn'] or $_SESSION['show_cheat'] == 1) {
?>

	<script type="text/javascript">
		window.setTimeout(function()
		{ 
			
			document.location.reload(true); 
		}, 20000);
	</script>
<?php
}

$c = 0;
$get_msg = "SELECT `message`, `time` FROM `messages` WHERE `game_id`=" . $_SESSION['game_id'] . " ORDER BY `msg_id` DESC";
$result = mysqli_query($dbc, $get_msg) or DIE("could not fetch messages");
echo "<fieldset><legend>Messages</legend>";
echo "<table border='1'>";
while ($row = mysqli_fetch_array($result)) {
	echo "<tr><td><font size='2'>[" . substr($row['time'], -8) . "] " . $row['message'] . "</font></td></tr>";
	$c++;
	if ($c == 10) {
		break 1;
	}
}
echo "</table></fieldset>";

if (ISSET($_POST['cheat']) and !ISSET($header)) {
	header("Location:cheat_mp.php");
	exit();
}
	
?>
</body>
</html>