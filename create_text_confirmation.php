<?php
// var_dump($_POST);
// echo "<hr>";

// Check required fields
if ( !isset($_POST['title']) || empty($_POST['title']) ) {
    $error = "Title must not be empty.";
}
else{
    require 'config/config.php';
    $mysqli = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
    if ( $mysqli->connect_errno ) {
        echo $mysqli->connect_error;
        exit();
    }
    $mysqli->set_charset("utf8");

    // Project ID
    $project_id = $_POST["project-id"];
    // Read title
    $title = $mysqli->$_POST['title'];
    // Read description
    if(isset($_POST["description"]) && !empty($_POST["description"])) {
        $description = $_POST["description"];
    }
    else{
        $description = null;
    }
    // Read Body
    if(isset($_POST["body"]) && !empty($_POST["body"])) {
        $body = $_POST["body"];
    }
    else{
        $body = null;
    }

    
    // Insert New Text into Database
    $stmt = $mysqli->prepare("INSERT INTO texts(title, description, body, project_id) VALUES(?, ?, ?, ?)");
    $stmt->bind_param("sssi", $title, $description, $body, $project_id);
    $executed = $stmt->execute();
    if(!$executed){
        $error = $mysqli->error;
    }
    // Check added revision successfully
    if($stmt->affected_rows == 1){
        $isAdded = true;
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
    <title>New Text | <?php echo $title; ?> | Intertext</title>
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
            <h1 class="pt-3">New Text</h1>
        </div>

        <div class="container-fluid">
            <div class="row mt-4">
                <div class="col-12">
                    <?php if ( isset($error) && !empty($error) ) : ?>
                        <div class="text-error">
                            <?php echo $error; ?>
                        </div>
                    <?php elseif ( $isAdded == true ) : ?>
                        <div class="text-success">
                            <span class="font-italic">Added to Project: "<?php echo $_POST['title']; ?>"</span>.
                        </div>
                    <?php endif; ?>
                </div>

                <div class="col-12 mt-4">
                    <a href="project.php?project-id=<?php echo $project_id; ?>" role="button" class="btn btn-dark">Back to Project</a>
                </div>
            </div>
        </div>

      </div>
    </div>

    <?php include 'components/footer.php'; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.1/dist/js/bootstrap.bundle.min.js" integrity="sha384-/bQdsTh/da6pkI1MST/rWKFNjaCP5gBSY4sEBT38Q/9RBh9AH40zEOg7Hlq2THRZ" crossorigin="anonymous"></script>
</body>
</html>