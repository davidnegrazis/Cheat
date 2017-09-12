<?php
session_start();
?>
<html>
<head>
<title>Cheat | Login</title>
</head>
<body>
<fieldset>
<legend><h2><font color='blue'>Login to Cheat</font></h2></legend>
<form method="POST" action="<?php echo $_SERVER['PHP_SELF']; ?>">
	<input type="text" name="username" placeholder="Username" autofocus><br />
	<input type="password" name="pwd" placeholder="Password"><br />
	<input type="submit" name="submit" value="Login!">
</form>

<?php
$msg = null;
if (ISSET($_POST['submit'])) {
	if ($_POST['username'] != null and $_POST['pwd'] != null) {
		$error = true;
		require("db_connect.php");
		$select = "SELECT * FROM `users`";
		$result = mysqli_query($dbc, $select) or DIE("Query error!");
		while ($row = mysqli_fetch_array($result)) {
			if ($_POST['username'] == $row['username']) {
				if (md5($_POST['pwd']) == $row['pwd']) {
					$_SESSION['user_id'] = $row['user_id'];
					$_SESSION['username'] = $row['username'];
					header("Location:index.php");
				}
			}
		}
		if ($error == true) {
			$msg = "<font color='red'><strong>Username and password did not match.</strong></font>";
		}
	}
	else {
		$msg = "<font color='red'><strong>Please enter all fields.</strong></font>";
	}
}
echo $msg;
?>
</fieldset>
<a href="cheat_signup.php">Signup</a>
</body>
</html>