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

	$code_error = "";
	$code = '';
	$error = 0;

	if ($_SERVER['REQUEST_METHOD'] === 'POST') {

		if (empty($_POST['code'])) {
			$code_error = 'Code field is required';
			$error++;
		}else{
			$code = clean_data($_POST['code']);

			$data = array(
				':code' => $code,
			);

			$query = "SELECT * FROM register WHERE code=:code";
			$statement = $connect->prepare($query);
			$statement->execute($data);
			//$result = $statement->fetchAll(PDO::FETCH_ASSOC);
			$count = $statement->rowCount();
			if ($count === 0) {
				$code_error = "Invalid code";
				$error++;
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
	<title>Code Check</title>
	<link rel="stylesheet" type="text/css" href="style.css">
</head>
<body>

	<div class="container">
		
		<form method="post" id="register" class="" name="regForm" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" onsubmit="return validateForm()">
			<input type="text" name="code" id="code" placeholder="Enter your code" value="<?php echo ($code) ? $code : ''; ?>" onkeypress="return isNumber(event)" autocomplete="off">
			<span id="code_error" class="error"><?php echo ($code_error) ? $code_error : ''; ?></span>

			<input type="submit" name="submit" value="Confirm">
		</form>

		<div style="overflow-x:auto;">
			<table>
				<?php  
					$code = $_POST['code'];
					$data = array(
						':code' => $code,
					);

					$query = "SELECT * FROM register WHERE code=:code";
					$statement = $connect->prepare($query);
					$statement->execute($data);
					$result = $statement->fetchAll(PDO::FETCH_ASSOC);
					$count = $statement->rowCount();
					if ($count > 0) {
				?>

				<tr>
					<th>Username</th>
					<th>Contact</th>
					<th>Email</th>
				</tr>

				<?php
						foreach ($result as $row) {
				?>
				<tr>
					<td><?php echo $row['username'] ?></td>
					<td><?php echo $row['contact'] ?></td>
					<td><?php echo $row['email'] ?></td>
				</tr>
				<?php
						}
					}
				?>
			</table>
		</div>

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

	function validateForm(){
		var code = document.forms["regForm"]["code"].value;

		if (username === "") {
			document.getElementById("code_error").innerHTML = "Code field is required";
			document.getElementById("code").focus();
			return false;
		}else{
			document.getElementById("code_error").innerHTML = "";
		}

		return true;

	}
</script>