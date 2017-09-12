<?php
include("cardgame_functions.php");
session_start();
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
</head>
<?php
if (ISSET($_SESSION['previous_turn'])) {
	$o = 1;
	foreach ($_SESSION['players'] as $key => $hand) {
		$hand = $_SESSION["p" . $o];
		if (count($hand) == 0 and empty($_SESSION['cheat'])) {
			$_SESSION['win'] = true;
			if (!ISSET($_SESSION['winner'])) {
				$_SESSION['winner'] = $_SESSION['previous_turn'];
			}
			$_SESSION['do_win']++;
			break 1;
		}
		$o++;
	}
}
if (!ISSET($_SESSION['win'])) {
	$_SESSION['do_win'] = 0;
}

if (ISSET($_SESSION['maxturn'])) {
	if ($_SESSION['turn'] > $_SESSION['maxturn']) {
		$_SESSION['turn'] = 1;
	}
}
$msg = "";
if (ISSET($_POST['restart'])) {
	unset($_POST);
	unset($_SESSION);
	session_destroy();
}

if (!ISSET($_POST['start']) and !ISSET($_SESSION['play'])) {
?>

<fieldset>
<legend><h2><font color='red'>Cheat!</font></h2></legend>
<p><i>Cheat!</i> is a card-game where the goal is to be the first to rid of all your cards.
Each turn, the active player places 1-4 cards face-down so that the other players do not know what they are. The card rank of their turn (i.e. 10s, jacks, aces, etc.)
is what they're <i>supposed</i> to play. The rank of the goal card moves to the subsequent card rank
each round. The order is: numbers ascending, jacks, queens, kings, and then aces. The order loops after aces.<br>
The cards that are placed down do not necessarily match the round's card rank. For example, the goal rank may be
any 7 card, but the active player places down a king. If another player suspects a cheat, they can call the active player's bluff. If any of the cards just placed down were, in fact,
a cheat, then the player who placed them has to pick up all of the cards that have been placed in the pool. If not, the player who falsely
called the bluff needs to pick up all of those cards.<br>
You can only play cards that are in your hand. Sometimes, you'll be forced to cheat if none of your cards match the rank you're supposed to play. You can cheat at any time, though.<br />
Calling a cheat does not progress the turn to the next player. If it's your turn, you can still call cheat on the player that went previously. However, only the player that just placed down cards
can be called out.<br />
Once you're in the game, click the <a href="help.php" target="_blank">help message</a> at the top to see how to format your entries.<br />
All cards are dealt out initially.<br><br><br>

<form method="POST" action="<?php echo $_SERVER['PHP_SELF'] ?>">
	Players: 
	<select name="players">
		<?php
		for ($x=3 ; $x<=8 ; $x++) {
			echo "<option value=" . $x . ">" . $x . "</option>";
		}
		?>
	</select>
	<input type="submit" name="start" value="Go!">
</form>
</fieldset>
<embed src="cheatresources/snowpatrol.mp3" autostart="true" loop="true" hidden="true">

<?php
}
else {
	if (ISSET($_SESSION['win'])) {
		if (!empty($_SESSION['placed'])) {
			$_SESSION['cheat'] = array();
			if (ISSET($_POST['cheat'])) {
				array_push($_SESSION['cheat'], "p1");
			}
			for ($b=2 ; $b<=$_SESSION['maxturn'] ; $b++) {
				$prev = $_SESSION['placed'];
				$prev_turn = $_SESSION['previous_turn'];
				$prev_index = $_SESSION['previous_index'];
				$rank = $_SESSION['rank'][$_SESSION['previous_index']];
				$hand = $_SESSION['p' . $b];
				$prevhand = $_SESSION['p' . $_SESSION['previous_turn']];
				$cheat = ai_detectCheat($hand, $prev, $rank, $b, $prev_turn, $prevhand);
				if ($cheat != false) {
					array_push($_SESSION['cheat'], $cheat);
				}
				
			}
		}
		if (!empty($_SESSION['cheat'])) {
			if (ISSET($_SESSION['win'])) {
				$_SESSION['do_win'] = 0;
				unset($_SESSION['win']);
				unset($_SESSION['winner']);
			}
			shuffle($_SESSION['cheat']);
			reset($_SESSION['cheat']);
			$caller = current($_SESSION['cheat']);
			$accused = "p" . $_SESSION['previous_turn'];
			foreach ($_SESSION['placed'] as $card) {
				if (strpos($card, $_SESSION['rank'][$_SESSION['previous_index']]) === false) {
					$_SESSION['cheated'] = true;
					break 1;
				}
			}
		}
	}
	if (ISSET($_SESSION['previous_turn'])) {
		$t = $_SESSION['previous_turn'];
	}
	if (!ISSET($_POST['submit']) and !ISSET($_POST['cheat'])) {
		$_SESSION['do_win'] = false;
		$_SESSION['cheated'] = false;
		$_SESSION['rank'] = array('a', '2','3','4','5','6','7','8','9','10','j','q','k');
		$_SESSION['rank_names'] = array('Ace', 'Number 2', 'Number 3', 'Number 4', 'Number 5', 'Number 6', 'Number 7', 'Number 8', 'Number 9', 'Number 10', 'Jack', 'Queen', 'King');
		$_SESSION['rank_index'] = 0;
		$_SESSION['turn'] = 1;
		$_SESSION['dump'] = array();
		$_SESSION['play'] = true;
		$deck = "2h,3h,4h,5h,6h,7h,8h,9h,10h,jh,qh,kh,ah,2c,3c,4c,5c,6c,7c,8c,9c,10c,jc,qc,kc,ac,2s,3s,4s,5s,6s,7s,8s,9s,10s,js,qs,ks,as,2d,3d,4d,5d,6d,7d,8d,9d,10d,jd,qd,kd,ad";
		$deck = explode(",", $deck);
		shuffle($deck);
		$deal = deal($deck, $_POST['players']);
		$deal = $deal[1];
		
		$x=1;
		$_SESSION['players'] = array();
		foreach ($deal as $hand) {
			$_SESSION["p" . $x] = $hand;
			$key = $x;
			$_SESSION['players'][$key] = $_SESSION["p" . $x];
			$x++;
		}
		$_SESSION['maxturn'] = $x - 1;
	}
	else {
		if (ISSET($_SESSION['win'])) {
			$_SESSION['do_win']++;
		}
		else {
			$_SESSION['do_win'] = 0;
		}
		$error = false;
		//ai's move
		if (ISSET($_POST['submit']) and $_SESSION['turn'] != 1) {
			if ($_SESSION['turn'] != 1) {
				$_POST['input'] = aiPlay($_SESSION['p' . $_SESSION['turn']], $_SESSION['rank'][$_SESSION['rank_index']]);
			}
		}
		if (ISSET($_POST['input']) and empty($_SESSION['cheat']) and $_SESSION['cheated'] == false) {
			$input =  explode(",", $_POST['input']);
			foreach ($input as $card) {
				if (!in_array($card, $_SESSION['p' . $_SESSION['turn']])) {
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
						if ($_SESSION['turn'] == 1) {
							$msg = "<font color='red'><strong>You can't send a duplicate card.</strong></font>";
						}
						else {
							$msg = "</font><strong><font color='red' size='4'>The AI accidentally entered a duplicate card. Please resubmit their move.</strong></font>";
							print_r($input);
						}
						break 1;
					}
				}
			}
			if ($error == false) {
				$_SESSION['previous_turn'] = $_SESSION['turn'];
				$_SESSION['previous_index'] = $_SESSION['rank_index'];
				$player = "p" . $_SESSION['turn'];
				$_SESSION['placed'] = array();
				foreach ($input as $card) {
					array_push($_SESSION['dump'], $card);
					array_push($_SESSION['placed'], $card);
					$key = array_search($card, $_SESSION["p" . $_SESSION['turn']]);
					unset($_SESSION['p' . $_SESSION['turn']][$key]);
				}
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
			}
		}
		//ai's cheat
		if (!empty($_SESSION['placed']) and !ISSET($_SESSION['win'])) {
			$_SESSION['cheat'] = array();
			if (ISSET($_POST['cheat'])) {
				array_push($_SESSION['cheat'], "p1");
			}
			for ($b=2 ; $b<=$_SESSION['maxturn'] ; $b++) {
				$prev = $_SESSION['placed'];
				$prev_turn = $_SESSION['previous_turn'];
				$prev_index = $_SESSION['previous_index'];
				$rank = $_SESSION['rank'][$_SESSION['previous_index']];
				$hand = $_SESSION['p' . $b];
				$prevhand = $_SESSION['p' . $prev_turn];
				$cheat = ai_detectCheat($hand, $prev, $rank, $b, $prev_turn, $prevhand);
				if ($cheat != false) {
					array_push($_SESSION['cheat'], $cheat);
				}
				
			}
		}
		if (!empty($_SESSION['cheat'])) {
			if (ISSET($_SESSION['win'])) {
				$_SESSION['do_win'] = 0;
				unset($_SESSION['win']);
			}
			shuffle($_SESSION['cheat']);
			reset($_SESSION['cheat']);
			$caller = current($_SESSION['cheat']);
			$accused = "p" . $_SESSION['previous_turn'];
			foreach ($_SESSION['placed'] as $card) {
				if (strpos($card, $_SESSION['rank'][$_SESSION['previous_index']]) === false) {
					$_SESSION['cheated'] = true;
					break 1;
				}
			}
			if ($_SESSION['cheated'] == true) {
				$_SESSION[$accused] = array_merge($_SESSION[$accused], $_SESSION['dump']);
				$cheat_msg = "Player " . substr($accused, -1) . " cheated! They have to pick up all " . count($_SESSION['dump']) . " cards!";
			}
			else {
				$_SESSION[$caller] = array_merge($_SESSION[$caller], $_SESSION['dump']);
				$cheat_msg = "Player " . substr($accused, -1) . " didn't actually cheat! Player " . substr($caller, -1) . " therefore has to pick up all " . count($_SESSION['dump']) . " cards!";
				$_SESSION['cheated'] = true;
			}
		}
		
		if (count($_SESSION['p1']) == 0 and empty($_SESSION['cheat'])) {
			$_SESSION['win'] = true;
			$_SESSION['do_win'] = 2;
		}
	}
	echo "<div><font color='green' size='4'>You are Player 1</font>";
	?>
	 || Need help? Click: <strong><font size='4'><a href="help.php" target="_blank">Help</a></font></strong></div><br />
	<?php
	$o=1;
	foreach ($_SESSION['players'] as $key => $hand) {
		$hand = $_SESSION["p" . $o];
		if (count($hand) == 0 and empty($_SESSION['cheat'])) {
			$_SESSION['win'] = true;
			$_SESSION['winner'] = $_SESSION['previous_turn'];
			break 1;
		}
		$o++;
	}
	if ($_SESSION['do_win'] != 2) {
		echo "<fieldset><legend><h2>Rank: " . $_SESSION['rank_names'][$_SESSION['rank_index']] . " | Player " . $_SESSION['turn'] . "'s turn</h2></legend>";
		echo "<div style='float:right'>";
		$x=1;
		foreach ($_SESSION['players'] as $key => $hand) {
			$hand = $_SESSION["p" . $x];
			if ($x == $_SESSION['turn']) {
				echo "<font color='blue'><strong>";
			}
			echo "Player " . $key . "'s cards left: " . count($hand) . "<br />";
			if ($x == $_SESSION['turn']) {
				echo "</strong></font>";
			}
			$x++;
		}
		echo "<br /><font size='2'><font color='blue'>Blue</font> represents who is about to place cards!</font>";
		echo "</div><div>";
		echo "<font color='blue' size='5'><strong>Total cards placed in the pool: " . count($_SESSION['dump']) . "</strong></font></div>";
		if (!empty($_SESSION['placed'])) {
			if (ISSET($_SESSION['turn'])) {
				echo "<p><strong><font size='4'>Player " . $_SESSION['previous_turn'] . " placed " . count($_SESSION['placed']) . " " . strtolower($_SESSION['rank_names'][$_SESSION['previous_index']]) . "s!</font></strong></p>";
			}
			echo "<div>";
			foreach ($_SESSION['placed'] as $card) {
				echo "<img src='cheatresources/unknown.png' >";
			}
			echo "</div>";
		}
		if ($_SESSION['cheated'] == true) {
			$no_msg = true;
			echo "<hr size='5' color='red'>";
			echo "<h2>Player " . substr($caller, -1) . " called cheat on player " . substr($accused, -1) . "!</h2>";
			echo "<div>Here are the cards placed:<br />";
			showCards($_SESSION['placed']);
			echo "<p>" . $cheat_msg . "</p>";
			echo "</div>";
			$_SESSION['cheat'] = array();
			$_SESSION['placed'] = array();
			$_SESSION['dump'] = array();
		}
		
		echo "</fieldset>";
		
		if (ISSET($_SESSION['previous_turn'])) {
			$t = $_SESSION['previous_turn'];
		}
		
		if ($_SESSION['turn'] == 1) {
		?>
			<form method="POST" action="<?php echo $_SERVER['PHP_SELF'] ?>">
				<?php
				if ($_SESSION['cheated'] == false) {
					echo '<input type="text" name="input" type="text" placeholder="Cards to play. Ex: 3c,3d" autofocus>';
				}
				?>
				<input type="submit" name="submit" value="Play!" style="background-color: 71f788">
				<?php
				if (!empty($_SESSION['placed']) and $_SESSION['cheated'] == false) {
					echo '<input type="submit" name="cheat" value="Call cheat on player ' . $t . '!" style="background-color: ff7c81">';
				}
				?>
				<input type="submit" name="restart" value="Restart!" style="background-color: #ffcc00">
			</form>
		
		<?php
		}
		else {
		?>
			<form method="POST" action="<?php echo $_SERVER['PHP_SELF'] ?>">
				<input type="submit" name="submit" value="See player <?php echo $_SESSION['turn']; ?>'s move">
				<?php
				if ($_SESSION['turn'] != 2 and $_SESSION['cheated'] == false) {
					echo '<input type="submit" name="cheat" value="Call cheat on player ' . $t . '!" style="background-color: ff7c81">';
				}
				?>
				<input type="submit" name="restart" value="Restart!" style="background-color: #ffcc00">
			</form
		<?php
		}
		if ($_SESSION['cheated'] == true) {
			$_SESSION['cheated'] = false;
		}
		if (ISSET($no_msg)) {
			$msg = null;
		}
		if (ISSET($_SESSION['win'])) {
			$msg = "</font><font color='purple' size='4'><strong>Somebody is about to win! Call cheat?</font></strong>";
		}
		echo $msg . "<br /><br />";
	}
	elseif (ISSET($_SESSION['win']) and $_SESSION['do_win'] > 1) {
		$z = 1;
		$q = 0;
		foreach ($_SESSION['players'] as $key => $hand) {
			$hand = $_SESSION["p" . $z];
			if (empty($_SESSION['p' . $z]) and $q == 0) {
				$winner = $z;
				$q++;
			}
			echo "Player " . $key . "'s cards left: " . count($hand) . "<br />";
			$z++;
		}
		echo "<h1>Player " . $winner . " wins!</h1>";
		?>
		
		<form method="POST" action="<?php echo $_SERVER['PHP_SELF'] ?>">
			<input type="submit" name="restart" value="Restart!" style="background-color: #ffcc00">
		</form><br />
		<embed src="cheatresources/snowpatrol.mp3" autostart="true" loop="true" hidden="true">
		<?php
	}
	
	echo "<div><strong><font color='green'>My cards...</font></strong>";
	echo "<hr size='10' color='black'>";
	echo "<img src='cheatresources/arm.png' style='float:left'>  ";
	echo "  <img src='cheatresources/arm2.png' style='float:right'>";
	$_SESSION['p1'] = sortCards($_SESSION['p1']);
	showCards($_SESSION['p1']);
	echo "</div>";
	
}
?>
</body>
</html>