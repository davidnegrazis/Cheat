<html>
<body>
<h2>Help</h2>
<p>Note: this was opened in a new tab</p>
<p>Remember, in cheat, you <i>have</i> to play a card, even if you don't hold the target rank.
You should to be discrete when you cheat, though!</p>
<p>Your input is in the format of the card abbreviation and subsequent cards separated by commas.
You <i>could</i> place as many cards as you'd like, but it'll be obvious that you cheated since it's not possible to hold more than four
cards of the same rank.</p>
<p><strong>Card abbreviations</strong> are in the format of the number/first letter of the rank name followed by the first letter of the suit, all in lowercase.
For example, a seven of hearts would be written as "7h", and an ace of diamonds would be written as "ad". Pretty simple, right?
If you wanted to place both these cards, all you'd have to write is "7h,ad" and send it! Make sure there's no space after/before the commas and no comma after the last entry.
</p>
Try inputting any card combination chosen from the entire deck and see if it's a valid entry!
<form method="POST" action="<?php echo $_SERVER['PHP_SELF']; ?>">
	<input type="text" name="input" autofocus>
	<input type="submit" name="submit" value="Go">
</form>

<?php
include("cardgame_functions.php");
$error = false;
$deck = "2h,3h,4h,5h,6h,7h,8h,9h,10h,jh,qh,kh,ah,2c,3c,4c,5c,6c,7c,8c,9c,10c,jc,qc,kc,ac,2s,3s,4s,5s,6s,7s,8s,9s,10s,js,qs,ks,as,2d,3d,4d,5d,6d,7d,8d,9d,10d,jd,qd,kd,ad";
$deck = explode(",", $deck);
if (ISSET($_POST['submit'])) {
	$input =  explode(",", $_POST['input']);
	foreach ($input as $card) {
		if (!in_array($card, $deck)) {
			$error = true;
			$msg = "<font color='red'><strong>You didn't make a valid input.</strong></font>";
			break 1;
		}
	}
	//check for dup
	if ($error == false) {
		$before = null;
		foreach ($input as $card) {
			if ($card == $before) {
				$error = true;
				$msg = "<font color='red'><strong>You can't send a duplicate card.</strong></font>";
				break 1;
			}
			$before = $card;
		}
	}
	if ($error == false) {
		$msg = "<font color='green'><strong>Valid!</strong></font>";
	}
	echo $msg;
	echo "<br /><br /><div>";
	shuffle($deck);
	showCards($deck);
	echo "</div>";
}
?>
</body>
</html>