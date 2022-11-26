<?php
require 'config/config.php';

$mysqli = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
if ( $mysqli->connect_errno ) {
	echo $mysqli->connect_error;
	exit();
}
$mysqli->set_charset('utf8');

// GET optionally passes project id
if( isset($_GET["project_id"]) && $_GET["project_id"]){
    // Query for project title
    $stmt = $mysqli->prepare("SELECT title FROM projects WHERE project_id = ?");
    $stmt->bind_param("i", $_GET["project_id"]);
    $executed = $stmt->execute();
    if(!$stmt) {
        echo $mysqli->error;
    }
    $stmt->bind_result($title);
    $stmt->fetch();
    $stmt->close();
    $project_title = $title;
    
    // Query for contributors to assign the job to
    $stmt = $mysqli->prepare("SELECT * FROM users
                    LEFT JOIN contributors
                        ON users.user_id = contributors.user_id
                    LEFT JOIN projects_has_contributors
                        ON projects_has_contributors.contributor_id=contributors.contributor_id
                    WHERE projects_has_contributors.project_id = ?");
    $stmt->bind_param("i", $_GET["project_id"]);
    $executed = $stmt->execute();
    if(!$stmt) {
        echo $mysqli->error;
    }
    else{
        $results_contributors = $stmt->get_result();
    }
    $stmt->close();
}
else{
    // Projects
    $stmt = $mysqli->prepare("SELECT * FROM projects 
                    LEFT JOIN projects_has_contributors ON projects.project_id=projects_has_contributors.project_id
                    WHERE contributor_id=?");
    $stmt->bind_param("i", $_SESSION["contributor_id"]);
    $executed = $stmt->execute();
    if(!$stmt) {
        echo $mysqli->error;
    }
    else{
        $results_for_project = $stmt->get_result();
    }
    $stmt->close();

    // TODO: Upon change of "For Project" field, clear & repopulate "Assign Job To" options

    // All users as potential contributors
    $sql_users = "SELECT * FROM users
    LEFT JOIN contributors
        ON users.user_id = contributors.user_id;";
    $results_contributors = $mysqli->query($sql_users);
    if ( $results_contributors == false ) {
        echo $mysqli->error;
        exit();
    }
}

$mysqli->close();

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Job | Intertext</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.1/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-F3w7mX95PdgyTmZZMECAngseQB83DfGTowi0iMjiWaeVhAn4FJkqJByhZMI3AhiU" crossorigin="anonymous">
    <link rel="stylesheet" href="intertext.css">
</head>
<body>
    <?php include 'components/nav.php'; ?>

    <div class="d-md-flex flex-1">

      <!-- IF NOT MOBILE -->
      <?php include 'components/sidebar.php'; ?>

      <div id="create-main" class="col-md-9 col-lg-10">

        <div class="container-fluid">
            <h1 class="pt-3">Create a New Job</h1>
        </div>

        <?php if( !isset($_SESSION["username"]) || !$_SESSION["username"]):?>

            <div class="container text-error">Must be logged in first.</div>

        <?php else: ?>

            <form action="create_job_confirmation.php" method="POST" class="container-fluid create-form">

                <?php if( !isset($_GET["project_id"]) || !$_GET["project_id"]):?>
                    <!-- ARRIVED FROM SIDEBAR -->
                    <div class="form-group row">
                        <label for="project-id" class="col-sm-3 col-form-label text-sm-right">For Project: <span class="text-danger">*</span></label>
                        <div class="col-sm-9">
                            <select name="project-id" id="project-id" class="form-control">
                                <option value="" selected disabled>-- Select --</option>
                                <?php while( $row = $results_for_project->fetch_assoc() ): ?>
                                    <option value="<?php echo $row['project_id']; ?>">
                                        <?php echo $row['title']; ?>
                                    </option>
                                <?php endwhile; ?>
                            </select>
                            <small class="invalid-feedback">Project is required.</small>
                        </div>
                    </div>

                <?php else: ?>
                    <!-- ARRIVED FROM PROJECT PAGE -->
                    <div class="row">
                        <div class="col-sm-3">For Project: </div>
                        <div class="col-sm-9"><?php echo $project_title; ?></div>
                    </div>
                    <input type="hidden" name="project-id" value="<?php echo $_GET['project_id']; ?>">
                <?php endif;?>
            
                <!-- TITLE -->
                <div class="form-group row">
                    <label for="title-id" class="col-sm-3 col-form-label text-sm-right">Job Title: <span class="text-danger">*</span></label>
                    <div class="col-sm-9">
                        <input type="text" class="form-control" id="title-id" name="title">
                        <small class="invalid-feedback">Title is required.</small>
                    </div>
                </div>

                <!-- DETAILS -->
                <div class="form-group row">
                    <label for="details-id" class="col-sm-3 col-form-label text-sm-right">Job Details:</label>
                    <div class="col-sm-9">
                        <textarea name="details" id="details-id" class="form-control"></textarea>
                    </div>
                </div>

                <!-- CONTRIBUTOR ASSIGNED -->
                <div class="form-group row">
                    <label for="contributor-id" class="col-sm-3 col-form-label text-sm-right">Assign Job To:</label>
                    <div class="col-sm-9">
                        <select name="contributor-id" id="contributor-id" class="form-control">
                            <?php while( $row = $results_contributors->fetch_assoc() ): ?>
                                <?php if( $row['contributor_id'] != $_SESSION["contributor_id"]):?>
                                    <option value="<?php echo $row['contributor_id']; ?>">
                                        <?php echo $row['username']; ?>
                                    </option>
                                <?php else:?>
                                    <!-- SELF SELECTED BY DEFAULT -->
                                    <option value="<?php echo $row['contributor_id']; ?>" selected>
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
                        <button type="submit" class="btn btn-dark">Assign</button>
                    </div>
                </div>

            </form>
        <?php endif;?>
      </div>
    </div>

    <?php include 'components/footer.php'; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.1/dist/js/bootstrap.bundle.min.js" integrity="sha384-/bQdsTh/da6pkI1MST/rWKFNjaCP5gBSY4sEBT38Q/9RBh9AH40zEOg7Hlq2THRZ" crossorigin="anonymous"></script>
    <script>
        document.querySelector('.create-form').onsubmit = function(){
            if ( document.querySelector('#title-id').value.trim().length == 0 ) {
                document.querySelector('#title-id').classList.add('is-invalid');
            } else {
                document.querySelector('#title-id').classList.remove('is-invalid');
            }

            if ( document.querySelector('#project-id').value.trim().length == 0 ) {
                document.querySelector('#project-id').classList.add('is-invalid');
            } else {
                document.querySelector('#project-id').classList.remove('is-invalid');
            }

            return ( !document.querySelectorAll('.is-invalid').length > 0 );
        }
    </script>
</body>
</html>