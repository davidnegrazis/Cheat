<html>
<head>
	<title>Cheat | Signup</title>
</head>
<body>
<fieldset>
<legend><h2><font color='blue'>Signup for Cheat</font></h2></legend>
<form method="POST" action="<?php echo $_SERVER['PHP_SELF']; ?>">
	<input type="text" name="username" placeholder="Username" autofocus maxlength="30"><br />
	<input type="password" name="pwd" placeholder="Password"><br />
	<input type="password" name="confirm_pwd" placeholder="Confirm password" maxlength="30"><br />
	<input type="submit" name="submit" value="Sign up!">
	<p>*note* Your passwords are encrypted with the PHP md5() function. However, I recommend you still <strong><font color='red'>do not enter a password you use for other sites.</font></strong></p>
</form>

<?php
$msg = null;
if (ISSET($_POST['submit'])) {
	if ($_POST['username'] != null and $_POST['pwd'] != null and $_POST['confirm_pwd'] != null) {
		if ($_POST['pwd'] == $_POST['confirm_pwd']) {
			$error = false;
			require("db_connect.php");
			$select = "SELECT `username` FROM `users`";
			$result = mysqli_query($dbc, $select) or DIE("Query error!");
			while ($row = mysqli_fetch_array($result)) {
				if ($_POST['username'] == $row['username']) {
					$error = true;
					$msg = "<font color='red'><strong>That username is unavailable! Please choose another one.</strong></font>";
					break 1;
				}
			}
			if ($error == false) {
				$msg = "success";
				$_POST['pwd'] = md5($_POST['pwd']);
				$query = "INSERT INTO `cheat`.`users` (`user_id`, `username`, `pwd`) VALUES (NULL, '" . $_POST['username'] . "', '" . $_POST['pwd'] . "')";
				mysqli_query($dbc, $query) or DIE("Dang! Query error2, bro");
				header("Location:cheat_login.php");
			}
		}
		else {
			$msg = "<font color='red'><strong>Passwords do not match.</strong></font>";
		}
	
	}
	else {
		$msg = "<font color='red'><strong>Please enter all fields.</strong></font>";
	}
}
echo $msg;
?>

</fieldset>
<a href="cheat_login.php">Login</a>

<h2><font color='blue'>This was created by David Negrazis</font></h2>
<img src='http://i.imgur.com/IFeeqfa.jpg'>
</body>
</html>