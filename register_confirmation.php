<?php
require 'config/config.php';

// Server-side validation
if (   !isset($_POST['username']) || empty($_POST['username'])
	|| !isset($_POST['password']) || empty($_POST['password']) ) {
	$error = "Username and Password are required.";
}
else{
	// Connect to the database
	$mysqli = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
	if($mysqli->connect_errno) {
		echo $mysqli->connect_error;
		exit();
	}

	// Query the database if this username already exists in the users table
	$statement_registered = $mysqli->prepare("SELECT * FROM users WHERE username = ?");
	$statement_registered->bind_param("s", $_POST["username"]);
	$executed_registered = $statement_registered->execute();
	if(!$executed_registered) {
		echo $mysqli->error;
	}

	// Getting number of results with prepared statements
	$statement_registered->store_result();
	$numrows = $statement_registered->num_rows;

	$statement_registered->close();

	// If we get ANY result back, it means this username or email is taken!
	if( $numrows > 0) {
		$error = "Username taken. Please choose another.";
	}
	else {
		// Hash password
		$password = hash("sha256", $_POST["password"]);

		// Add user record to users table
		$statement = $mysqli->prepare("INSERT INTO users(username, password) VALUES(?,?)");
		$statement->bind_param("ss", $_POST["username"], $password);
		$executed = $statement->execute();
		if(!$executed) {
			echo $mysqli->error;
		}

        // 1. Get user_id of newly registered user
        $statement_get = $mysqli->prepare("SELECT user_id FROM users WHERE username = ?");
        $statement_get->bind_param("s", $_POST["username"]);
        $executed = $statement_get->execute();
        if(!$executed) {
			echo $mysqli->error;
		}
        $statement_get->bind_result($user_id);
        $statement_get->fetch();
        $statement_get->close();
        // 2. Assign user_id to new record in contributors
        $statement = $mysqli->prepare("INSERT INTO contributors(user_id) VALUES(?)");
		$statement->bind_param("i", $user_id);
		$executed = $statement->execute();
		if(!$executed) {
			echo $mysqli->error;
		}
        // 3. Assign user_id to new record in maintainers
        $statement = $mysqli->prepare("INSERT INTO maintainers(user_id) VALUES(?)");
		$statement->bind_param("i", $user_id);
		$executed = $statement->execute();
		if(!$executed) {
			echo $mysqli->error;
		}

		$statement->close();
	}

	$mysqli->close();
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign Up | Intertext</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.1/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-F3w7mX95PdgyTmZZMECAngseQB83DfGTowi0iMjiWaeVhAn4FJkqJByhZMI3AhiU" crossorigin="anonymous">
    <link rel="stylesheet" href="intertext.css">
</head>
<body>
    <?php include 'components/nav.php'; ?>

    <div class="d-flex flex-1">

      <!-- IF NOT MOBILE -->
      <?php include 'components/sidebar.php'; ?>

      <div id="create-main" class="col-md-9 col-lg-10">

        <div class="container-fluid">
            <h1 class="pt-3">New User Registration</h1>
        </div>

        <div class="container-fluid">
            <div class="row mt-4">
                <div class="col-12">
                    <?php if ( isset($error) && !empty($error) ) : ?>
                        <div class="text-danger"><?php echo $error; ?></div>
                    <?php else : ?>
                        <div class="text-success">User "<?php echo $_POST['username']; ?>" was successfully registered.</div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
      </div>
    </div>

    <?php include 'components/footer.php'; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.1/dist/js/bootstrap.bundle.min.js" integrity="sha384-/bQdsTh/da6pkI1MST/rWKFNjaCP5gBSY4sEBT38Q/9RBh9AH40zEOg7Hlq2THRZ" crossorigin="anonymous"></script>
</body>
</html>