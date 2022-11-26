<?php
require 'config/config.php';

// echo $_GET["project-id"];

// Generate items if logged in
if( isset($_SESSION["username"]) && $_SESSION["username"]) {

    $mysqli = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
    if ( $mysqli->connect_errno ) {
        echo $mysqli->connect_error;
        exit();
    }
    $mysqli->set_charset("utf8");

    // Project Info
    $stmt = $mysqli->prepare("SELECT title, description FROM projects
                    WHERE project_id = ?");
	$stmt->bind_param("i", $_GET["project-id"]);
	$executed = $stmt->execute();
	if(!$executed){
		$error = $mysqli->error;
	}
    $stmt->bind_result($title, $desc);
    $stmt->fetch();
    $p_title = $title;
    $p_desc = $desc;
    $stmt->close();

    // Update Items
    $stmt = $mysqli->prepare("SELECT jobs.title, status, last_updated AS date FROM jobs
                    LEFT JOIN job_status
                        ON jobs.job_status_id = job_status.job_status_id
                    WHERE jobs.project_id = ?
                    ORDER BY jobs.last_updated DESC
                    LIMIT 5");
	$stmt->bind_param("i", $_GET["project-id"]);
	$executed = $stmt->execute();
	if(!$executed){
		$error = $mysqli->error;
	}
    else{
        $results_updates = $stmt->get_result();
    }

    // Text Items
    $stmt = $mysqli->prepare("SELECT text_id, texts.title, texts.description FROM texts
                    LEFT JOIN projects
                        ON projects.project_id = texts.project_id
                    WHERE texts.project_id = ?");
    $stmt->bind_param("i", $_GET["project-id"]);
    $executed = $stmt->execute();
    if(!$executed){
        $error = $mysqli->error;
    }
    else{
        $results_texts = $stmt->get_result();
    }

    // Project Job Items
    $stmt = $mysqli->prepare("SELECT jobs.job_id, jobs.title, status, users.username AS contributor FROM jobs
                    LEFT JOIN projects
                        ON projects.project_id = jobs.project_id
                    LEFT JOIN job_status
                        ON job_status.job_status_id = jobs.job_status_id
                    LEFT JOIN contributors
                        ON contributors.contributor_id = jobs.contributor_id
                    LEFT JOIN users
                        ON contributors.user_id = users.user_id
                    WHERE jobs.project_id = ?");
    $stmt->bind_param("i", $_GET["project-id"]);
    $executed = $stmt->execute();
    if(!$executed){
        $error = $mysqli->error;
    }
    else{
        $results_project_jobs = $stmt->get_result();
    }

    // Maintainer Only Features - Edit Title/Description
    $maintain_access = false;
    $stmt = $mysqli->prepare("SELECT * FROM projects_has_maintainers
                    WHERE projects_has_maintainers.maintainer_id = ? AND projects_has_maintainers.project_id = ?");
    $stmt->bind_param("ii", $_SESSION["maintainer_id"], $_GET["project-id"]);
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
    <title><?php echo $p_title; ?> | Intertext</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.1/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-F3w7mX95PdgyTmZZMECAngseQB83DfGTowi0iMjiWaeVhAn4FJkqJByhZMI3AhiU" crossorigin="anonymous">
    <link rel="stylesheet" href="intertext.css">
</head>
<body>
    <?php include 'components/nav.php'; ?>

    <div class="d-md-flex flex-1">

      <!-- IF NOT MOBILE -->
      <?php include 'components/sidebar.php'; ?>

      <div id="project-main" class="col-md-8 col-lg-9 d-md-flex flex-column">
        <!-- INFO -->
        <div id="info-container">
            <div class="container-fluid">
                <!-- Project Title -->
                <h1 class="pt-3"><?php echo $p_title; ?></h1>
                <!-- Project Description -->
                <p><?php echo $p_desc; ?></p>
                <?php if($maintain_access): ?>
                    <form action="edit_project_info.php" method="POST">
                        <input type="hidden" name="project-id" value="<?php echo $_GET["project-id"]; ?>">
                        <input type="hidden" name="project-title" value="<?php echo $p_title;?>">
                        <input type="hidden" name="project-description" value="<?php echo $p_desc; ?>">
                        <button type="submit" class="btn-sm btn-dark mb-2">Edit Project Info</button>
                    </form>
                <?php endif;?>
            </div>

            <!-- Updates -->
            <div class="container-fluid" id="updates-container">
                <h2>Updates</h2>
                <div id="updates-items" class="mb-3">

                    <?php if(!isset($_SESSION["logged_in"]) || !$_SESSION["logged_in"]) :?>
                        <!-- DISPLAY SAMPLE UPDATES -->
                        <?php include 'placeholders/update_item.php' ?>
                    <?php else: ?>
                        <?php if(!$results_updates->num_rows > 0):?>
                            <div>No updates!</div>
                        <?php endif; ?>
                        <!-- GET & DISPLAY UPDATES -->
                        <?php while($row_update = $results_updates->fetch_assoc()) : ?>
                            <div class="update-item d-flex border-bottom">
                            <div class="d-flex flex-column w-100">
                                <div class="info-box p-2">
                                <span class="update"><strong><?php echo $row_update["title"];?>:</strong> status changed to <strong><?php echo $row_update["status"]?></strong></span>
                                <span class="date">on <?php echo $row_update["date"];?></span>
                                </div>
                            </div>
                            </div>
                        <?php endwhile; ?>
                    <?php endif;?>

                </div>
            </div>
            <!-- END UPDATES -->
        </div>
        <!-- END INFO -->

        <!-- WORK -->
        <div id="work-container" class="pb-3">

            <!-- TEXTS -->
            <div class="container-fluid" id="text-container">
                <div id="text-header" class="row pt-2">
                    <h2 class="pt-2 col-9">Texts</h2>
                    <a class="btn btn-dark col-2 p-2 m-2" href="create_text.php?project-id=<?php echo $_GET['project-id'];?>">New Text</a>
                </div>

                <div id="text-items" class="row row-cols-2 row-cols-md-3 row-cols-lg-4 g-4">
                    <?php if(!isset($_SESSION["logged_in"]) || !$_SESSION["logged_in"]) :?>
                        <!-- DISPLAY SAMPLE TEXTS -->
                        <?php include 'placeholders/text_item.php' ?>
                    <?php else: ?>
                        <!-- GET & DISPLAY TEXTS -->
                        <?php if(!$results_texts->num_rows > 0):?>
                            <div>No texts!</div>
                        <?php endif; ?>
                        <?php while($row_text = $results_texts->fetch_assoc()) : ?>
                            <a href="text.php?text-id=<?php echo $row_text["text_id"];?>" class="text-item col">
                                <div class="card col-12 p-2">
                                    <!-- <img src="#" class="card-img-top"> -->
                                    <div class="card-body">

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

                                    </div>
                                </div>
                            </a>
                        <?php endwhile; ?>
                    <?php endif;?>
                </div>
            </div>
            <!-- END TEXTS -->
                
            <!-- JOBS -->
            <div class="container-fluid" id="project-jobs-container">
                <div id="project-jobs-header" class="row pt-3">
                    <h2 class="pt-2 col-9">Jobs</h2>
                    <a class="btn btn-dark col-2 p-2 m-2" href="create_job.php?project_id=<?php echo $_GET['project-id'];?>">New Job</a>
                </div>
                <div id="project-job-items" class="row row-cols-2 row-cols-md-3 row-cols-lg-4 g-4 mb-3">

                    <?php if(!isset($_SESSION["logged_in"]) || !$_SESSION["logged_in"]) :?>
                        <!-- DISPLAY SAMPLE JOBS -->
                        <?php include 'placeholders/project_job_item.php'; ?>
                    <?php else: ?>
                        <!-- GET & DISPLAY JOBS -->
                        <?php if(!$results_project_jobs->num_rows > 0):?>
                            <div>No jobs!</div>
                        <?php endif; ?>                       
                        <?php while($row_job = $results_project_jobs->fetch_assoc()) : ?>
                            <a href="job.php?job-id=<?php echo $row_job["job_id"];?>" class="col project-job-item">
                                <div class="card col-12 p-2">
                                    <?php
                                        if($row_job["status"]=="open"){
                                            $status_color = "bg-secondary";
                                        }
                                        else if($row_job["status"]=="working"){
                                            $status_color = "bg-primary";
                                        }
                                        else if($row_job["status"]=="submitted"){
                                            $status_color = "bg-warning";
                                        }
                                        else if($row_job["status"]=="accepted"){
                                            $status_color = "bg-success";
                                        }
                                        else{
                                            $status_color = "bg-danger";
                                        }
                                    ?>
                                    <div class="project-job-status card-header <?php echo $status_color; ?>">
                                        <p class="card-text"><?php echo $row_job["status"];?></p>
                                    </div>
                                    <div class="card-body">
                                        <h5 class="card-title"><?php echo $row_job["title"];?></h5>
                                        <p class="card-text"><?php echo $row_job["contributor"];?></p>
                                    </div>
                                </div>
                            </a>
                        <?php endwhile; ?>
                    <?php endif;?>

                </div>
            </div>
            <!-- END JOBS -->
        </div>
        <!-- END WORK -->

        <!-- MOBILE ONLY -->
        <?php include 'components/sidebar_mobile.php'; ?>

      </div>
    </div>

    <?php include 'components/footer.php'; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.1/dist/js/bootstrap.bundle.min.js" integrity="sha384-/bQdsTh/da6pkI1MST/rWKFNjaCP5gBSY4sEBT38Q/9RBh9AH40zEOg7Hlq2THRZ" crossorigin="anonymous"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js" integrity="sha256-/xUj+3OJU5yExlq6GSYGSHk7tPXikynS7ogEvDej/m4=" crossorigin="anonymous"></script>
    <script>
        $('.card').mouseenter(function() {
            $(this).css("background-color", "#4B6858");
            $(this).css("color", "#fff");
        });
        $('.card').mouseleave(function() {
            $(this).css("background-color", "#fff");
            $(this).css("color", "#000");
        });
    </script>
</body>
</html>