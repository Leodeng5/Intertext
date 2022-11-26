<?php
require 'config/config.php';

// echo $_GET["job-id"];

// Generate items if logged in
if( isset($_SESSION["username"]) && $_SESSION["username"]) {

    $mysqli = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
    if ( $mysqli->connect_errno ) {
        echo $mysqli->connect_error;
        exit();
    }
    $mysqli->set_charset("utf8");

    // Job Info
    $stmt = $mysqli->prepare("SELECT jobs.title, jobs.details, status, projects.title AS project_title, jobs.project_id FROM jobs
                    LEFT JOIN projects
                        ON projects.project_id = jobs.project_id
                    LEFT JOIN job_status
                        ON jobs.job_status_id = job_status.job_status_id
                    WHERE job_id = ?");
	$stmt->bind_param("i", $_GET["job-id"]);
	$executed = $stmt->execute();
	if(!$executed){
		$error = $mysqli->error;
	}
    $stmt->bind_result($title, $details, $status, $project_title, $project_id);
    $stmt->fetch();
    $stmt->close();

    // Revision Items
    $stmt = $mysqli->prepare("SELECT texts.title, revisions.timestamp, revisions.revised_title, texts.text_id FROM revisions
                    LEFT JOIN texts
                        ON texts.text_id = revisions.text_id
                    WHERE revisions.job_id = ?
                    ORDER BY revisions.timestamp DESC");
	$stmt->bind_param("i", $_GET["job-id"]);
	$executed = $stmt->execute();
	if(!$executed){
		$error = $mysqli->error;
	}
    else{
        $results_revisions = $stmt->get_result();
    }

    // Texts from Project to Revise
    $stmt = $mysqli->prepare("SELECT text_id, texts.title, texts.description FROM texts
                    LEFT JOIN projects
                        ON projects.project_id = texts.project_id
                    WHERE texts.project_id = ?");
    $stmt->bind_param("i", $project_id);
    $executed = $stmt->execute();
    if(!$executed){
        $error = $mysqli->error;
    }
    else{
        $results_texts = $stmt->get_result();
    }


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
    <title><?php echo $title;?> | Intertext</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.1/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-F3w7mX95PdgyTmZZMECAngseQB83DfGTowi0iMjiWaeVhAn4FJkqJByhZMI3AhiU" crossorigin="anonymous">
    <link rel="stylesheet" href="intertext.css">
</head>
<body>
    <?php include 'components/nav.php'; ?>

    <div class="d-md-flex flex-1">

      <!-- IF NOT MOBILE -->
      <?php include 'components/sidebar.php'; ?>

      <div id="job-main" class="col-md-8 col-lg-9 d-md-flex flex-column">
        <!-- INFO -->
        <div id="info-container">
            <div class="container-fluid">
                <!-- Title -->
                <div class="d-flex">
                    <h1 class="pt-3 mb-0"><?php echo $title;?></h1>
                    <?php
                        if($status=="open"){
                            $status_color = "btn-secondary";
                        }
                        else if($status=="working"){
                            $status_color = "btn-primary";
                        }
                        else if($status=="submitted"){
                            $status_color = "btn-warning";
                        }
                        else if($status=="accepted"){
                            $status_color = "btn-success";
                        }
                        else{
                            $status_color = "btn-danger";
                        }
                    ?>
                    <div class="btn-sm <?php echo $status_color; ?> mt-4 ms-4">
                        <?php echo $status; ?>
                    </div>

                    <?php if($status=="submitted" && $maintain_access): ?>
                        <form action="coming_soon.php" method="POST">
                            <input type="hidden" name="job-id" value="<?php echo $_GET["job-id"]; ?>">
                            <input type="hidden" name="job-title" value="<?php echo $title; ?>">
                            <input type="hidden" name="project-id" value="<?php echo $project_id; ?>">
                            <input type="hidden" name="project-title" value="<?php echo $project_title; ?>">
                            <button type="submit" class="btn-sm btn-primary mt-4 ms-4">Review</button>
                        </form>
                        <form action="job_accept.php" method="POST">
                            <input type="hidden" name="job-id" value="<?php echo $_GET["job-id"]; ?>">
                            <input type="hidden" name="job-title" value="<?php echo $title; ?>">
                            <input type="hidden" name="project-id" value="<?php echo $project_id; ?>">
                            <input type="hidden" name="project-title" value="<?php echo $project_title; ?>">
                            <button type="submit" class="btn-sm btn-success mt-4 ms-4">Accept</button>
                        </form>
                    <?php endif;?>
                </div>
                <p class="mt-0 ps-4">
                    for <a class="text-dark" href="project.php?project-id=<?php echo $project_id;?>"><?php echo $project_title; ?></a>
                </p>
                <!-- Details -->
                <p><?php echo $details;?></p>
            </div>

            <!-- REVISIONS -->
            <div class="container-fluid" id="revisions-container">
                <h2>Changes</h2>

                <div id="revision-items">
                    
                    <?php if(!isset($_SESSION["logged_in"]) || !$_SESSION["logged_in"]) :?>
                        <!-- DISPLAY SAMPLE REVISIONS -->
                        <?php include "placeholders/revision_item.php"; ?>
                    <?php else: ?>
                        <!-- GET & DISPLAY REVISIONS -->
                        <?php if(!$results_revisions->num_rows > 0):?>
                            <div>No revisions!</div>
                        <?php endif; ?>
                        <?php while($row_revision = $results_revisions->fetch_assoc()) : ?>
                            
                            <div class="revision-item d-flex border-bottom">
                            <div class="d-flex flex-column w-100">
                                <div class="info-box p-2">
                                <span class="revision">
                                    <?php
                                        // Revised
                                        if( isset($row_revision["text_id"]) && !empty($row_revision["text_id"])){
                                            echo "Revised " . $row_revision["title"];
                                        }
                                        else{
                                            echo "Created " . $row_revision["revised_title"];
                                        }
                                    ?>
                                </span>
                                <span class="date">on <?php echo $row_revision["timestamp"];?></span>
                                </div>
                            </div>
                            </div>

                        <?php endwhile; ?>
                    <?php endif;?>

                </div>
            </div>
            <!-- END REVISIONS -->
        </div>
        <!-- END INFO -->

        <!-- WORK -->
        <div id="work-container" class="pb-3 d-flex flex-column">

            <!-- REVISE OPTIONS -->
            <div class="container-fluid pb-3 flex-grow" id="revise-container">
                <div id="revise-header" class="row pt-3">
                    <h2 class="pt-2 col-9">Revise</h2>
                    <?php if($status=="open" || $status=="working"): ?>
                        <a class="btn btn-dark col-2 p-2 m-2" href="revise.php?job-id=<?php echo $_GET["job-id"];?>">New Text</a>
                    <?php endif;?>
                </div>
                <div id="revise-items" class="row row-cols-2 row-cols-md-3 row-cols-lg-4 g-4">

                    <?php if(!isset($_SESSION["logged_in"]) || !$_SESSION["logged_in"]) :?>
                        <!-- DISPLAY SAMPLE TEXTS -->
                        <?php include 'placeholders/text_item.php' ?>
                    <?php else: ?>
                        <!-- GET & DISPLAY TEXTS -->
                        <?php if(!$results_texts->num_rows > 0):?>
                            <div>No texts to revise!</div>
                        <?php endif; ?>
                        <?php while($row_text = $results_texts->fetch_assoc()) : ?>
                            <?php 
                                // Check for previous revision to this text
                                $mysqli = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
                                if ( $mysqli->connect_errno ) {
                                    echo $mysqli->connect_error;
                                    exit();
                                }
                                $mysqli->set_charset("utf8");

                                $stmt = $mysqli->prepare("SELECT * FROM revisions
                                                WHERE revisions.text_id = ? AND revisions.job_id = ?");
                                $stmt->bind_param("ii", $row_text["text_id"], $_GET["job-id"]);
                                $executed = $stmt->execute();
                                if(!$executed){
                                    $error = $mysqli->error;
                                }
                                $results_revised_texts = $stmt->get_result();
                                $numrows = $results_revised_texts->num_rows;
                                if($numrows > 0){
                                    $row_revised_text = $results_revised_texts->fetch_assoc();
                                }
                                $stmt->close();
                                $mysqli->close();
                            ?>
                            
                            <?php
                                if($status=="open" || $status=="working"){
                                    echo "<a href=revise.php?job-id=" . $_GET["job-id"] . "&text-id=" . $row_text["text_id"] . " class='text-item col'>";
                                }
                                else{
                                    echo "<div class='text-item col'>";
                                }
                            ?>
                                <div class="card col-12 p-2">
                                    <div class="card-body">
                                        <?php if($numrows > 0):?>
                                            <!-- GET FROM REVISIONS -->
                                            <h5 class="card-title">
                                                <?php
                                                    $in = $row_revised_text["revised_title"];
                                                    $out = strlen($in) > 26 ? substr($in,0,26)."..." : $in;
                                                    echo $out;
                                                ?>
                                            </h5>
                                            <p class="card-text">
                                                <?php
                                                    $in = $row_revised_text["revised_description"];
                                                    $out = strlen($in) > 60 ? substr($in,0,60)."..." : $in;
                                                    echo $out;
                                                ?>
                                            </p>
                                        <?php else: ?>
                                            <!-- GET FROM TEXTS -->
                                            <h5 class="card-title">
                                                <?php
                                                    $in = $row_text["title"];
                                                    $out = strlen($in) > 26 ? substr($in,0,26)."..." : $in;
                                                    echo $out;
                                                ?>
                                            </h5>
                                            <p class="card-text">
                                                <?php
                                                    $in = $row_text["description"];
                                                    $out = strlen($in) > 60 ? substr($in,0,60)."..." : $in;
                                                    echo $out;
                                                ?>
                                            </p>
                                        <?php endif;?>
                                    </div>
                                </div>
                            <?php
                                if($status=="open" || $status=="working"){
                                    echo "</a>";
                                }
                                else{
                                    echo "</div>";
                                }
                            ?>
                        <?php endwhile; ?>
                    <?php endif;?>
                    
                </div>
            </div>
            <!-- END REVISE OPTIONS -->

            <?php if($status=="open" || $status=="working"): ?>
                <div class="container">
                    <a class="btn btn-dark col-12 p-2 m-2" href="job_submit.php?job-id=<?php echo $_GET["job-id"]; ?>">Submit for Review</a>
                </div>
            <?php endif; ?>
        </div>
        <!-- END WORK -->

        <!-- MOBILE ONLY -->
        <?php include 'components/sidebar_mobile.php'; ?>

      </div>
    </div>

    <?php include 'components/footer.php'; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.1/dist/js/bootstrap.bundle.min.js" integrity="sha384-/bQdsTh/da6pkI1MST/rWKFNjaCP5gBSY4sEBT38Q/9RBh9AH40zEOg7Hlq2THRZ" crossorigin="anonymous"></script>
</body>
</html>