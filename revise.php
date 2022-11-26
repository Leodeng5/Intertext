<?php
require 'config/config.php';

// echo $_GET["text-id"];
// echo $_GET["job-id"];

// Generate content if logged in
if( isset($_SESSION["username"]) && !empty($_SESSION["username"])) {

    $mysqli = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
    if ( $mysqli->connect_errno ) {
        echo $mysqli->connect_error;
        exit();
    }
    $mysqli->set_charset("utf8");

    // Contributor ID Validation
    $stmt = $mysqli->prepare("SELECT * FROM contributors
                    LEFT JOIN jobs
                        ON jobs.contributor_id = contributors.contributor_id
                    WHERE jobs.contributor_id = ? AND jobs.job_id = ?");
    $stmt->bind_param("ii", $_SESSION["contributor_id"], $_GET["job-id"]);
	$executed = $stmt->execute();
	if(!$executed){
		$error = $mysqli->error;
	}
    $stmt->store_result();
    $numrows = $stmt->num_rows;

    if(!($numrows > 0)){
        $error = "Access Denied - Only the assigned contributor can make revisions.";
    }

    // Text Content
    if(isset($_GET["text-id"]) && !empty($_GET["text-id"])){
        // Check if continuing old revision
        $stmt = $mysqli->prepare("SELECT * FROM revisions
                WHERE revisions.text_id = ? AND revisions.job_id = ?");
        $stmt->bind_param("ii", $_GET["text-id"], $_GET["job-id"]);
        $executed = $stmt->execute();
        if(!$executed){
            $error = $mysqli->error;
        }
        $results_revised_texts = $stmt->get_result();
        $numrows = $results_revised_texts->num_rows;

        // Get original text info
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

        // Get old revisions info as needed
        if($numrows > 0){
            $row_revised_text = $results_revised_texts->fetch_assoc();
            $title = $row_revised_text["revised_title"];
            $desc = $row_revised_text["revised_description"];
            $body = $row_revised_text["revised_body"];
            $revision_id = $row_revised_text["revision_id"];
        }
    }
    else{
        // Create Revision with text_id = NULL (New Text)
        $stmt = $mysqli->prepare("SELECT projects.title AS project_title FROM jobs
                        LEFT JOIN projects
                            ON projects.project_id = jobs.project_id
                        WHERE job_id = ?");
        $stmt->bind_param("i", $_GET["job-id"]);
        $executed = $stmt->execute();
        if(!$executed){
            $error = $mysqli->error;
        }
        $stmt->bind_result($project_title);
        $stmt->fetch();
        $stmt->close();
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
    <title>Revise | <?php echo $title; ?> | Intertext</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.1/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-F3w7mX95PdgyTmZZMECAngseQB83DfGTowi0iMjiWaeVhAn4FJkqJByhZMI3AhiU" crossorigin="anonymous">
    <link rel="stylesheet" href="intertext.css">
</head>
<body>
    <?php include 'components/nav.php'; ?>

    <div class="d-md-flex flex-1">

      <!-- IF NOT MOBILE -->
      <?php include 'components/sidebar.php'; ?>

      <div id="text-main" class="col-md-8 col-lg-9 d-md-flex flex-column">

        <?php if( isset($error) && !empty($error)) : ?>
            <div class="text-error font-italic">
                <?php echo $error; ?>
            </div>
        <?php else: ?>
            <form action="revise_submit.php" method="POST" class="container-fluid create-form">
                <input type="hidden" name="job-id" value="<?php echo $_GET["job-id"]; ?>">

                <?php if(isset($_GET["text-id"]) && !empty($_GET["text-id"])): ?>
                    <!-- REVISE EXISTING TEXT -->
                    <?php if(isset($revision_id) && !empty($revision_id)): ?>
                        <input type="hidden" name="revision-id" value="<?php echo $revision_id; ?>">
                    <?php endif; ?>
                    <input type="hidden" name="text-id" value="<?php echo $_GET["text-id"]; ?>">
                    <div id="info-container" class="pb-3">
                        <!-- Text Title -->
                        <input type="text" class="h1 form-control pt-3 mb-0 mt-3" id="title-id" name="title" value="<?php echo $title; ?>">
                        <p class="mt-0 ps-4">
                            for <a class="text-dark" href="project.php?project-id=<?php echo $project_id;?>"><?php echo $project_title; ?></a>
                        </p>
                        <!-- Text Description -->
                        <div class="mb-0 m-3">
                            <label for="description-id" class="">Description: </label>
                            <textarea name="description" id="description-id" class="form-control"><?php echo $desc; ?></textarea>
                        </div>
                    </div>
                    <div id="work-container" class="d-flex mb-2">
                        <div class="container-fluid bg-light m-3 p-5 flex-1">
                            <textarea name="body" id="body-id" class="form-control" rows=20><?php echo $body; ?></textarea>
                        </div>
                    </div>
                <?php else: ?>
                    <!-- CREATE NEW TEXT -->
                    <div id="info-container" class="pb-3">
                        <!-- Text Title -->
                        <input type="text" class="h1 form-control pt-3 mb-0 mt-3" id="title-id" name="title" placeholder="Text Title">
                        <p class="mt-0 ps-4">
                            for <a class="text-dark" href="project.php?project-id=<?php echo $project_id;?>"><?php echo $project_title; ?></a>
                        </p>
                        <!-- Text Description -->
                        <div class="mb-0 m-3">
                            <label for="description-id" class="">Description: </label>
                            <textarea name="description" id="description-id" class="form-control"></textarea>
                        </div>
                    </div>
                    <div id="work-container" class="d-flex mb-2">
                        <div class="container-fluid bg-light m-3 p-5 flex-1">
                            <textarea name="body" id="body-id" class="form-control" rows=20></textarea>
                        </div>
                    </div>
                <?php endif; ?>

                <button type="submit" class="btn btn-dark">Save Changes</button>

            </form>
            <a class="btn btn-dark col-2 p-2 m-2" href="job.php?job-id=<?php echo $_GET["job-id"];?>">Discard</a>
        
            

        <?php endif; ?>

        <!-- MOBILE ONLY -->
        <?php include 'components/sidebar_mobile.php'; ?>

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
            return ( !document.querySelectorAll('.is-invalid').length > 0 );
        }
    </script>
</body>
</html>