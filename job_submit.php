<?php
require 'config/config.php';
// echo $_GET["job-id"];
// echo $_SESSION["contributor_id"];

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
    $error = "Access Denied - Only the assigned contributor can submit job for revision.";
}

// Submit job to project
$stmt = $mysqli->prepare("UPDATE jobs SET job_status_id = 3, last_updated = DEFAULT
                WHERE job_id = ?");
$stmt->bind_param("i", $_GET["job-id"]);
$executed = $stmt->execute();
if(!$executed){
    $error = $mysqli->error;
}
// Check submitted successfully
if($stmt->affected_rows == 1){
    $isSubmitted = true;
}

// Get job title
$stmt = $mysqli->prepare("SELECT jobs.title, projects.project_id, projects.title AS project_title FROM jobs
                LEFT JOIN projects
                    ON projects.project_id = jobs.project_id
                WHERE job_id = ?");
$stmt->bind_param("i", $_GET["job-id"]);
$executed = $stmt->execute();
if(!$executed){
    $error = $mysqli->error;
}
$stmt->bind_result($title, $project_id, $project_title);
$stmt->fetch();

$stmt->close();
$mysqli->close();

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Submit Job | Intertext</title>
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
            <h1 class="pt-3">Submit Job</h1>
            <p class="mt-0 ps-4">
                to <a class="text-dark" href="project.php?project-id=<?php echo $project_id;?>"><?php echo $project_title; ?></a>
            </p>
        </div>

        <div class="container-fluid">
            <div class="row mt-4">
                <div class="col-12">
                    <?php if ( isset($error) && !empty($error) ) : ?>
                        <div class="text-error">
                            <?php echo $error; ?>
                        </div>
                    <?php elseif ( $isSubmitted == true ) : ?>
                        <div class="text-success">
                            <span class="font-italic">"<?php echo $title; ?>"</span> was submitted for review.
                        </div>
                    <?php endif; ?>
                </div>

                <div class="col-12 mt-4">
                    <a href="job.php?job-id=<?php echo $_GET("job-id"); ?>" role="button" class="btn btn-dark">Back to Job</a>
                </div>
            </div>
        </div>

      </div>
    </div>

    <?php include 'components/footer.php'; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.1/dist/js/bootstrap.bundle.min.js" integrity="sha384-/bQdsTh/da6pkI1MST/rWKFNjaCP5gBSY4sEBT38Q/9RBh9AH40zEOg7Hlq2THRZ" crossorigin="anonymous"></script>
</body>
</html>