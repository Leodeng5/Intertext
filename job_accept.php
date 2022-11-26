<?php
// var_dump($_POST);
// echo "<hr>";

// Check required fields
if ( !isset($_POST['job-id']) || empty($_POST['job-id']) ) {
    $error = "job ID not found.";
}
else{
    require 'config/config.php';
    $mysqli = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
    if ( $mysqli->connect_errno ) {
        echo $mysqli->connect_error;
        exit();
    }
    $mysqli->set_charset("utf8");

    // POST info
    $job_id = $_POST["job-id"];
    $job_title = $_POST["job-title"];
    $project_id = $_POST["project-id"];
    $project_title = $_POST["project-title"];

    // Get All Relevant Revisions
    $stmt = $mysqli->prepare("SELECT revised_title, revised_description, revised_body, revisions.text_id, jobs.project_id, revisions.timestamp FROM revisions
                    LEFT JOIN jobs
                        ON jobs.job_id = revisions.job_id
                    LEFT JOIN projects
                        ON projects.project_id = jobs.project_id
                    WHERE revisions.job_id = ?");
    $stmt->bind_param("i", $job_id);
    $executed = $stmt->execute();
    if(!$executed){
        $error = $mysqli->error;
    }
    else{
        $results_revisions = $stmt->get_result();
    }

    // Merge Changes to Project
    $numChanged = 0;
    $numErrors = 0;
    while($row_revision = $results_revisions->fetch_assoc()){
        $title = $row_revision["revised_title"];
        $description = $row_revision["revised_description"];
        $body = $row_revision["revised_body"];
        // Add New Text
        if( $row_revision["text_id"] == NULL ){
            $stmt = $mysqli->prepare("INSERT INTO texts(title, description, body, project_id)
                            VALUES(?, ?, ?, ?)");
            $stmt->bind_param("sssi", $title, $description, $body, $project_id);
            $executed = $stmt->execute();
            if(!$executed){
                $error = $mysqli->error;
            }
            if($stmt->affected_rows == 1){
                $numChanged++;
            }
            else{
                $numErrors++;
            }
        }
        // Replace Original Text
        else{
            $text_id = $row_revision["text_id"];
            $stmt = $mysqli->prepare("UPDATE texts SET title = ?, description = ?, body = ?
                            WHERE text_id = ?");
            $stmt->bind_param("sssi", $title, $description, $body, $text_id);
            $executed = $stmt->execute();
            if(!$executed){
                $error = $mysqli->error;
            }
            // Check edited revision successfully
            if($stmt->affected_rows == 1){
                $numChanged++;
            }
            else{
                $numErrors++;
            }
        }
    }

    // Mark Job as Accepted, update timestamp
    $stmt = $mysqli->prepare("UPDATE jobs SET job_status_id = 4, last_updated = DEFAULT
                    WHERE job_id = ?");
    $stmt->bind_param("i", $job_id);
    $executed = $stmt->execute();
    if(!$executed){
        $error = $mysqli->error;
    }
    // Check Accepted
    if($stmt->affected_rows == 1){
        $isAccepted = true;
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
    <title>Accept | <?php echo $job_title;?> | Intertext</title>
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
            <h1 class="pt-3">Revision Submitted</h1>
        </div>

        <div class="container-fluid">
            <div class="row mt-4">
                <div class="col-12">
                    <?php if ( isset($error) && !empty($error) ) : ?>
                        <div class="text-error">
                            <?php echo $error; ?>
                        </div>
                    <?php elseif ( $isAccepted == true ) : ?>
                        <div class="text-success">
                            <span class="font-italic">Job "<?php echo $job_title;?>"</span> was accepted.
                            <?php echo $numChanged;?> texts were revised/added in <strong><?php echo $project_title?></strong>.
                        </div>
                    <?php endif; ?>
                </div>

                <div class="col-12 mt-4">
                    <a href="job.php?job-id=<?php echo $job_id; ?>" role="button" class="btn btn-dark">Back to Job</a>
                </div>
            </div>
        </div>

      </div>
    </div>

    <?php include 'components/footer.php'; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.1/dist/js/bootstrap.bundle.min.js" integrity="sha384-/bQdsTh/da6pkI1MST/rWKFNjaCP5gBSY4sEBT38Q/9RBh9AH40zEOg7Hlq2THRZ" crossorigin="anonymous"></script>
</body>
</html>