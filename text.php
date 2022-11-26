<?php
require 'config/config.php';

// echo $_GET["text-id"];

// Generate content if logged in
if( isset($_SESSION["username"]) && $_SESSION["username"]) {

    $mysqli = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
    if ( $mysqli->connect_errno ) {
        echo $mysqli->connect_error;
        exit();
    }
    $mysqli->set_charset("utf8");

    // Text Content
    $stmt = $mysqli->prepare("SELECT texts.title, texts.description, body, texts.project_id, projects.title AS project_title FROM texts
                    LEFT JOIN projects
                        ON projects.project_id = texts.project_id
                    WHERE text_id = ?");
	$stmt->bind_param("i", $_GET["text-id"]);
	$executed = $stmt->execute();
	if(!$executed){
		$error = $mysqli->error;
	}
    $stmt->bind_result($title, $desc, $body, $project_id, $project_title);
    $stmt->fetch();
    $stmt->close();

    // Maintainer Only Features - ID Validation
    $maintain_access = false;
    $stmt = $mysqli->prepare("SELECT * FROM projects_has_maintainers
                    WHERE projects_has_maintainers.maintainer_id = ? AND projects_has_maintainers.project_id = ?");
    $stmt->bind_param("ii", $_SESSION["maintainer_id"], $project_id);
    $executed = $stmt->execute();
    if(!$executed){
        $error = $mysqli->error;
    }
    $stmt->store_result();
    $numrows = $stmt->num_rows;
    if($numrows > 0){
        $maintain_access = true;
    }

    $stmt->close();
    $mysqli->close();
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $title; ?> | Intertext</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.1/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-F3w7mX95PdgyTmZZMECAngseQB83DfGTowi0iMjiWaeVhAn4FJkqJByhZMI3AhiU" crossorigin="anonymous">
    <link rel="stylesheet" href="intertext.css">
</head>
<body>
    <?php include 'components/nav.php'; ?>

    <div class="d-md-flex flex-1">

      <!-- IF NOT MOBILE -->
      <?php include 'components/sidebar.php'; ?>

      <div id="text-main" class="col-md-8 col-lg-9 d-md-flex flex-column">
        <!-- INFO -->
        <div id="info-container">
            <div class="container-fluid">

                <!-- Text Title -->
                <h1 class="pt-3 mb-0"><?php echo $title; ?></h1>
                <p class="mt-0 ps-4">
                    for <a class="text-dark" href="project.php?project-id=<?php echo $project_id;?>"><?php echo $project_title; ?></a>
                </p>
                <!-- Text Description -->
                <p><?php echo $desc; ?></p>
                <!-- Delete -->
                <?php if($maintain_access): ?>
                    <form action="delete_text.php" method="POST">
                        <input type="hidden" name="text-id" value="<?php echo $_GET["text-id"]; ?>">
                        <input type="hidden" name="text-title" value="<?php echo $title; ?>">
                        <input type="hidden" name="project-id" value="<?php echo $project_id; ?>">
                        <input type="hidden" name="project-title" value="<?php echo $project_title; ?>">
                        <button type="submit" class="btn-sm btn-danger m-2">Delete Text</button>
                    </form>
                <?php endif;?>
            </div>
        </div>
        <!-- END INFO -->

        <!-- BODY -->
        <div id="work-container" class="d-flex">
            <div class="container-fluid bg-light m-3 p-5 flex-1">
                <p class="">
                    <?php echo $body; ?></a>
                </p>
            </div>
        </div>
        <!-- END BODY -->

        <!-- MOBILE ONLY -->
        <?php include 'components/sidebar_mobile.php'; ?>

      </div>
    </div>

    <?php include 'components/footer.php'; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.1/dist/js/bootstrap.bundle.min.js" integrity="sha384-/bQdsTh/da6pkI1MST/rWKFNjaCP5gBSY4sEBT38Q/9RBh9AH40zEOg7Hlq2THRZ" crossorigin="anonymous"></script>
</body>
</html>