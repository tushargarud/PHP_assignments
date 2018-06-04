<html>
<head>
    <title>Message Board</title>
    <link rel="stylesheet" type="text/css" href="board.css">
</head>
<body>
<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors','On');

function clean_input($inputStr)
{
	$inputStr = trim($inputStr);
	$inputStr = stripslashes($inputStr);
	$inputStr = htmlspecialchars($inputStr);
	return $inputStr;
}

$db_host='127.0.0.1:3306' ;
$db_database='board' ;
$db_username='root';
$db_password='';

$usernameErr="";
$passwordErr="";
$genericErr="";

if ($_SERVER["REQUEST_METHOD"] == "GET")
{
	if(isset($_GET["auth"]) && $_GET["auth"]=="failed")
		$genericErr="Authentication failed.";
} 

if ($_SERVER["REQUEST_METHOD"] == "POST") 
{
	if (empty($_POST["username"])) 
		$usernameErr = "Username is required.";
	else 
		$c_username = clean_input($_POST["username"]);

	if (empty($_POST["password"])) 
		$passwordErr = "Password is required.";
	else 
		$c_password = md5(clean_input($_POST["password"]));
	
	if(empty($usernameErr) && empty($passwordErr))
	{	
		try 
		{
			$dbh = new PDO("mysql:host=$db_host;dbname=$db_database","$db_username","$db_password",array(PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION));
			$stmt = $dbh->prepare('SELECT * FROM users WHERE username=:c_username AND password=:c_password');
			$stmt->bindParam(':c_username', $c_username);
			$stmt->bindParam(':c_password', $c_password);
			$stmt->execute();
			$result = $stmt->fetch();
            $dbh=null;
			if (!$result)
			{
				session_regenerate_id(true);
				$genericErr = "Invalid username or password";
				header("Location: login.php?auth=failed"); 
				exit();
			}
			else
			{ 
				$_SESSION['login'] = true;
				$_SESSION['userName'] = $result["username"];
				header("Location: board.php");
			}

		} 
		catch (PDOException $e) 
		{
            $dbh=null;
			print "Error!: " . $e->getMessage() . "<br/>";
			die();
		}
	}
}

?>

<div class="login-box">
    <h3>LOGIN</h3>
	<form action="login.php" method="post">
			<input type="text" name="username" placeholder="Username" />
		<br/>
			<input type="password" name="password" placeholder="Password" />
		<br/>
        <span class="errortext">
            <?php echo $usernameErr . " " . $passwordErr . " " . $genericErr ?>
        </span>
        <br/>
        <button type="submit">Submit</button>

	</form>
</div>

</body>
</html>
