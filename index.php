<?php  
	ini_set('display_errors', 1);
	error_reporting(E_ALL ^ E_NOTICE);

	$options = array(
		PDO::ATTR_PERSISTENT => true,
		PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
	);
	try {
		$connect = new PDO("mysql:host=localhost;dbname=register", "root", "", $options);
	} catch (Exception $e) {
		$error = $e->getMessage();
	}

	$user_error = $phone_error = $email_error = "";
	$username = '';
	$phone = '';
	$email = '';
	$error = 0;

	if ($_SERVER['REQUEST_METHOD'] === 'POST') {

		$code = mt_rand(1111111111, 9999999999);
		if (empty($_POST['username'])) {
			$user_error = 'Username field is required';
		}else{
			$username = clean_data($_POST['username']);
			if (!preg_match("/^[a-zA-Z0-9]+$/", $username)) {
				$user_error = "Only letters and number are allowed";
				$error++;
			}

			if (strlen($username) > 20) {
				$user_error = "Username character must not be more than 20";
				$error++;
			}
		}

		if (empty($_POST['phone'])) {
			$phone_error = 'Phone number field is required';
			$error++;
		}else{
			$phone = clean_data($_POST['phone']);

			if (strlen($phone) > 15 || strlen($phone) < 10) {
				$phone_error = "Phone digit must not be more than 15 and less than 10";
				$error++;
			}
		}

		if (empty($_POST['email'])) {
			$email_error = 'Email field is required';
			$error++;
		}else{
			$email = clean_data($_POST['email']);

			if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
				$email_error = "Invalid email format";
				$error++;
			}

			$data = array(
				':email' => $email,
			);

			$query = "SELECT email FROM register WHERE email=:email";
			$statement = $connect->prepare($query);
			$statement->execute($data);
			$count = $statement->rowCount();
			if ($count > 0) {
				$email_error = "Email already exist";
				$error++;
			}

		}

		if ($error === 0) {
			$data = array(
				':username' => $username,
				':phone' => $phone,
				':email' => $email,
				':code' => $code,
			);
			$query = "INSERT INTO register(username, contact, email, code) VALUES(:username, :phone, :email, :code)";
			$statement = $connect->prepare($query);
			if ($statement->execute($data)) {
				$to = $email;
				$subject = "Regsitration Code";
				$message = "
					<html>
					<head>
						<title>Registration Code</title>
					</head>
					<body>
						<p>This email contains the code that give u access to the site!</p>
					<table>
					<tr>
						<th>Code</th>
						<th>'".$code."'</th>
					</tr>
					
					</table>
					</body>
					</html>
				";

				$headers = "MIME-Version: 1.0" . "\r\n";
				$headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";

				$headers .= 'From: <tulbadex@gmail.com>' . "\r\n";
				//$headers .= 'Cc: myboss@example.com' . "\r\n";
				@mail($to, $subject, $message, $headers);
				header("Location: code_check.php");
			}

		}

	}

	function clean_data($value){
		$value = trim($value);
		$value = stripslashes($value);
		$value = htmlspecialchars($value);
		return $value;
	}
?>
<!DOCTYPE html>
<html>
<head>
	<title>Register</title>
	<link rel="stylesheet" type="text/css" href="style.css">
</head>
<body>

	<div class="container">
		
		<form method="post" id="register" class="" name="regForm" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" onsubmit="return validateForm()">
			<input type="text" name="username" id="username" placeholder="User Name" value="<?php echo ($username) ? $username : ''; ?>">
			<span id="username_error" class="error"><?php echo ($user_error) ? $user_error : ''; ?></span>

			<input type="text" name="phone" id="phone" placeholder="Phone Number" value="<?php echo ($phone) ? $phone : ''; ?>" onkeypress="return isNumber(event)">
			<span id="phone_error" class="error"><?php echo ($phone_error) ? $phone_error : ''; ?></span>

			<input type="email" name="email" id="email" placeholder="Email" value="<?php echo ($email) ? $email : ''; ?>">
			<span id="email_error" class="error"><?php echo ($email_error) ? $email_error : ''; ?></span>
			<input type="submit" name="submit" value="Register">
		</form>
	</div>

	<noscript>
	    Your browser does not support JavaScript!
	</noscript>
</body>
</html>

<script type="text/javascript">

	function isNumber(evt) {
		evt = (evt) ? evt : window.event;
		var charCode = (evt.which) ? evt.which : evt.keyCode;
		if (charCode > 31 && (charCode < 48 || charCode > 57)) {
			return false;
		}
		return true;
	}

	function validateEmail(email) {
	  var re = /^(([^<>()[\]\\.,;:\s@\"]+(\.[^<>()[\]\\.,;:\s@\"]+)*)|(\".+\"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/;
	  return re.test(email);
	}

	function validateForm(){
		var username = document.forms["regForm"]["username"].value;
		var phone = document.forms["regForm"]["phone"].value;
		var email = document.forms["regForm"]["email"].value;
		var reg = /^[a-zA-Z0-9]+$/;
		var invalid = reg.test(document.getElementById("username").value);

		if (username === "") {
			document.getElementById("username_error").innerHTML = "Username field is required";
			document.getElementById("username").focus();
			return false;
		}else if (username.length > 20) {
			document.getElementById("username_error").innerHTML = "Username character must not be more than 20";
			document.getElementById("username").focus();
			return false;
		}else if(!invalid){
			document.getElementById("username_error").innerHTML = "Only letters and number are allowed";
			document.getElementById("username").focus();
		}else{
			document.getElementById("username_error").innerHTML = "";
		}

		if (phone == "") {
			document.getElementById("phone_error").innerHTML = "Phone number field is required";
			document.getElementById("phone").focus();
			return false;
		}else if (phone.length > 15 || phone.length < 10) {
			document.getElementById("phone_error").innerHTML = "Phone digit must not be more than 15 and less than 10";
			document.getElementById("phone").focus();
			return false;
		}else{
			document.getElementById("phone_error").innerHTML = "";
		}

		if (email === "") {
			document.getElementById("email_error").innerHTML = "Email field is required";
			return false;
		}else if (!validateEmail(email)) {
			document.getElementById("email_error").innerHTML = "Invalid email format";
			return false;
		}else{
			document.getElementById("email_error").innerHTML = "";
		}

		return true;

	}
</script>