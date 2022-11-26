<?php
require 'config/config.php';

// Check not logged in yet
if( !isset($_SESSION["logged_in"]) || !$_SESSION["logged_in"]) {
	// Check $_POST['username]
	if ( isset($_POST['username']) && isset($_POST['password']) ) {
		// Checking username & password filled
		if ( empty($_POST['username']) || empty($_POST['password']) ) {

			$error = "Please enter username and password.";

		}
		else {
			// Check credentials in database
			$mysqli = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

			if($mysqli->connect_errno) {
				echo $mysqli->connect_error;
				exit();
			}

			$passwordInput = hash("sha256", $_POST["password"]);

            // Query the database
            $statement_registered = $mysqli->prepare("SELECT user_id FROM users WHERE username = ? AND password = ?");
            $statement_registered->bind_param("ss", $_POST["username"], $passwordInput);
            $executed_registered = $statement_registered->execute();
            if(!$executed_registered) {
                echo $mysqli->error;
            }
            $statement_registered->store_result();
            $numrows = $statement_registered->num_rows;
			$statement_registered->bind_result($user_id);
			$statement_registered->fetch();
            $statement_registered->close();

			// echo "The user id is: " . $user_id;

			// Check username/pw combo match
			if($numrows > 0) {
				// Get access ids
				$statement_get_contrib = $mysqli->prepare("SELECT contributor_id FROM contributors WHERE user_id = ?");
				$statement_get_contrib->bind_param("i", $user_id);
				$executed = $statement_get_contrib->execute();
				if(!$executed) {
					echo $mysqli->error;
				}
				$statement_get_contrib->bind_result($contrib_id);
				$statement_get_contrib->fetch();
				$statement_get_contrib->close();
				$statement_get_maintain = $mysqli->prepare("SELECT maintainer_id FROM maintainers WHERE user_id = ?");
				$statement_get_maintain->bind_param("i", $user_id);
				$executed = $statement_get_maintain->execute();
				if(!$executed) {
					echo $mysqli->error;
				}
				$statement_get_maintain->bind_result($maintain_id);
				$statement_get_maintain->fetch();
				$statement_get_maintain->close();

				// Store session info
				$_SESSION["logged_in"] = true;
				$_SESSION["username"] = $_POST["username"];
				$_SESSION["contributor_id"] = $contrib_id;
				$_SESSION["maintainer_id"] = $maintain_id;

				

				// Redirect the user to the home page
				header("Location: intertext.php");
			}
			else {
				// header('Location:'.$_SERVER['HTTP_REFERER'].'?login=0');
				// die();
				header("Location: intertext.php?login=0");
			}

			$mysqli->close();
		}
	}
}
else{
	// User is logged in already
	header("Location: intertext.php");
}
?>