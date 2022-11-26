<?php
// var_dump($_POST);
// echo "<hr>";

// Check required fields
if ( !isset($_POST['title']) || empty($_POST['title'])
    || !isset($_POST['maintainer-id']) || empty($_POST['maintainer-id']) ) {
    $error = "Missing required field(s).";
}
else{
    require 'config/config.php';
    $mysqli = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
    if ( $mysqli->connect_errno ) {
        echo $mysqli->connect_error;
        exit();
    }

    // Read title
    $title = $mysqli->real_escape_string($_POST['title']);
    // Read description
    if(isset($_POST["desc"]) && !empty($_POST["desc"])) {
		$desc = $mysqli->real_escape_string($_POST["desc"]);
	}
	else{
		$desc = null;
	}
    // Read maintainer(s)
    $maintainer_id = $_POST["maintainer-id"];
    // Read contributor(s)
    if(isset($_POST["contributor-id"]) && !empty($_POST["contributor-id"])) {
		$contributor_id = $_POST["contributor-id"];
	}
	else{
		$contributor_id = null;
	}


    // Insert new project
	$stmt = $mysqli->prepare("INSERT INTO projects(title, description) VALUES(?, ?)");
	$stmt->bind_param("ss", $title, $desc);
	$executed = $stmt->execute();
	if(!$executed){
		$error = $mysqli->error;
	}
	// Check added project successfully
	if($stmt->affected_rows == 1){
		$isInserted = true;
        $project_id = $stmt->insert_id; // Store id of inserted project
	}
    $stmt->close();

    // Add appropriate maintainer/contributor relationships
    // Loop through $maintainer_id
    foreach ($maintainer_id as $m_id){
        $stmt_m = $mysqli->prepare("INSERT INTO projects_has_maintainers(project_id, maintainer_id)
        VALUES(?, ?)");
        $stmt_m->bind_param("ii", $project_id, $m_id);
        $executed = $stmt_m->execute();
        if(!$executed){
            $error = $mysqli->error;
        }
        $stmt_m->close();
    };
    // Loop through $contributor_id
    foreach ($contributor_id as $c_id){
        $stmt_c = $mysqli->prepare("INSERT INTO projects_has_contributors(project_id, contributor_id)
        VALUES(?, ?)");
        $stmt_c->bind_param("ii", $project_id, $c_id);
        $executed = $stmt_c->execute();
        if(!$executed){
            $error = $mysqli->error;
        }
        $stmt_c->close();
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

        <div class="container-fluid">
            <div class="row mt-4">
                <div class="col-12">
                    <?php if ( isset($error) && !empty($error) ) : ?>
                        <div class="text-error">
                            <?php echo $error; ?>
                        </div>
                    <?php else : ?>
                        <div class="text-success">
                            <span class="font-italic">Project "<?php echo $_POST['title']; ?>"</span> was successfully created.
                        </div>
                    <?php endif; ?>
                </div>

                <div class="col-12 mt-4">
                    <a href="create_project.php" role="button" class="btn btn-dark">Back to Create New Project</a>
                </div>
            </div>
        </div>

      </div>
    </div>

    <?php include 'components/footer.php'; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.1/dist/js/bootstrap.bundle.min.js" integrity="sha384-/bQdsTh/da6pkI1MST/rWKFNjaCP5gBSY4sEBT38Q/9RBh9AH40zEOg7Hlq2THRZ" crossorigin="anonymous"></script>
</body>
</html>