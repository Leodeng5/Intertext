<?php
require 'config/config.php';

$mysqli = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
if ( $mysqli->connect_errno ) {
	echo $mysqli->connect_error;
	exit();
}
$mysqli->set_charset('utf8');

// Users
$sql_users = "SELECT * FROM users
                LEFT JOIN contributors
                    ON users.user_id = contributors.user_id
                LEFT JOIN maintainers
                    ON users.user_id = maintainers.user_id;";
$results_users = $mysqli->query($sql_users);
$results_contribs = $mysqli->query($sql_users);
if ( $results_users == false || $results_contribs == false ) {
	echo $mysqli->error;
	exit();
}

$mysqli->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Project | Intertext</title>
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
            <h1 class="pt-3">Create a New Project</h1>
        </div>

        <?php if( !isset($_SESSION["username"]) || !$_SESSION["username"]):?>

            <div class="container text-error">Must be logged in first.</div>

        <?php else: ?>

            <form action="create_project_confirmation.php" method="POST" class="container-fluid create-form">

                <!-- TITLE -->
                <div class="form-group row">
                    <label for="title-id" class="col-sm-3 col-form-label text-sm-right">Project Title: <span class="text-danger">*</span></label>
                    <div class="col-sm-9">
                        <input type="text" class="form-control" id="title-id" name="title">
                        <small id="username-error" class="invalid-feedback">Title is required.</small>
                    </div>
                </div>

                <!-- DESCRIPTION -->
                <div class="form-group row">
                    <label for="desc-id" class="col-sm-3 col-form-label text-sm-right">Project Description:</label>
                    <div class="col-sm-9">
                        <textarea name="desc" id="desc-id" class="form-control"></textarea>
                    </div>
                </div>

                <!-- MAINTAINERS -->
                <div class="form-group row">
                    <div class="col-sm-3 col-form-label text-sm-right">
                        <label for="maintainer-id">Maintainers:</label>
                        <div>(creator required by default)</div>
                    </div>
                    <div class="col-sm-9">
                        <select name="maintainer-id[]" multiple id="maintainer-id" class="form-control">
                            <option value=<?php echo $_SESSION["maintainer_id"];?> selected><?php echo $_SESSION["username"]?></option>

                            <?php while( $row = $results_users->fetch_assoc() ): ?>
                                <?php if( $row['maintainer_id'] != $_SESSION["maintainer_id"]):?>
                                    <option value="<?php echo $row['maintainer_id']; ?>">
                                        <?php echo $row['username']; ?>
                                    </option>
                                <?php endif;?>
                            <?php endwhile; ?>

                        </select>
                    </div>
                </div>

                <!-- CONTRIBUTORS -->
                <div class="form-group row">
                    <label for="contributor-id" class="col-sm-3 col-form-label text-sm-right">Contributors:</label>
                    <div class="col-sm-9">
                        <select name="contributor-id[]" multiple id="contributor-id" class="form-control">
                            <option value="<?php echo $_SESSION['contributor_id']; ?>" selected>-- Add --</option>

                            <?php while( $row = $results_contribs->fetch_assoc() ): ?>
                                <?php if( $row['contributor_id'] != $_SESSION["contributor_id"]):?>
                                    <option value="<?php echo $row['contributor_id']; ?>">
                                        <?php echo $row['username']; ?>
                                    </option>
                                <?php endif;?>
                            <?php endwhile; ?>

                        </select>
                    </div>
                </div>

                <div class="form-group row">
                    <div class="col-sm-3"></div>
                    <div class="col-sm-9 mt-2">
                        <button type="submit" class="btn btn-dark">Create</button>
                    </div>
                </div>

            </form>
        <?php endif;?>
      </div>
    </div>

    <?php include 'components/footer.php'; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.1/dist/js/bootstrap.bundle.min.js" integrity="sha384-/bQdsTh/da6pkI1MST/rWKFNjaCP5gBSY4sEBT38Q/9RBh9AH40zEOg7Hlq2THRZ" crossorigin="anonymous"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js" integrity="sha256-/xUj+3OJU5yExlq6GSYGSHk7tPXikynS7ogEvDej/m4=" crossorigin="anonymous"></script>
    <script>
        $('select option').mousedown(function(e) {
            e.preventDefault();
            $(this).prop('selected', !$(this).prop('selected'));
            return false;
        });

        document.querySelector('.create-form').onsubmit = function(){
            if ( document.querySelector('#title-id').value.trim().length == 0 ) {
                document.querySelector('#title-id').classList.add('is-invalid');
            } else {
                document.querySelector('#title-id').classList.remove('is-invalid');
            }

            return ( !document.querySelectorAll('.is-invalid').length > 0 );
        }
    </script>
</body>
</html>