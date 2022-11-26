<?php
require 'config/config.php';

// Generate items if logged in
if( isset($_SESSION["username"]) && $_SESSION["username"]) {

    $mysqli = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
    if ( $mysqli->connect_errno ) {
        echo $mysqli->connect_error;
        exit();
    }
    $mysqli->set_charset("utf8");

    // Activity Items
    $stmt = $mysqli->prepare("SELECT jobs.title, projects.title AS project_title, jobs.last_updated AS date FROM jobs
            LEFT JOIN projects
              ON projects.project_id = jobs.project_id
            LEFT JOIN projects_has_maintainers
              ON projects_has_maintainers.project_id = projects.project_id
            LEFT JOIN projects_has_contributors
              ON projects_has_contributors.project_id = projects.project_id
            WHERE jobs.job_status_id = 4 AND (projects_has_maintainers.maintainer_id = ? OR projects_has_contributors.contributor_id = ?)
            GROUP BY jobs.title
            ORDER BY jobs.last_updated DESC
            LIMIT 10");
    $stmt->bind_param("ii", $_SESSION["maintainer_id"], $_SESSION["contributor_id"]);
    $executed = $stmt->execute();
    if(!$executed){
      $error = $mysqli->error;
    }
    else{
      $results_activity = $stmt->get_result();
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
    <title>Intertext - Home</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.1/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-F3w7mX95PdgyTmZZMECAngseQB83DfGTowi0iMjiWaeVhAn4FJkqJByhZMI3AhiU" crossorigin="anonymous">
    <link rel="stylesheet" href="intertext.css">
</head>
<body>
    <?php include 'components/nav.php'; ?>

    <div class="d-md-flex flex-1">

      <!-- IF NOT MOBILE -->
      <?php include 'components/sidebar.php'; ?>

      <div id="main" class="col-md-8 col-lg-9">
        <!-- ACTIVITY -->
        <div class="container-fluid" id="activity-container">
          <h2 class="pt-3">Activity</h2>

          <div id="activity-items">

            <?php if(!isset($_SESSION["logged_in"]) || !$_SESSION["logged_in"]) :?>
                <!-- DISPLAY SAMPLE ACTIVITY -->
                <?php include 'placeholders/activity_item.php' ?>
            <?php else: ?>
                <!-- GET & DISPLAY UPDATES -->
                <?php if(!$results_activity->num_rows > 0):?>
                  <div>No activity!</div>
                <?php endif;?>
                <?php while($row_activity = $results_activity->fetch_assoc()) : ?>
                  <div class="activity-item d-flex border-bottom">
                    <div class="d-flex flex-column w-100">
                    <div class="info-box p-2">
                        <span class="change">Job <?php echo $row_activity["title"];?> merged with <?php echo $row_activity["project_title"];?></span>
                        <span class="date">on <?php echo $row_activity["date"];?></span>
                    </div>
                    </div>
                  </div>
                <?php endwhile; ?>
            <?php endif;?>

          </div>

        </div>
        <!-- END ACTIVITY -->

        <!-- MOBILE ONLY -->
        <?php include 'components/sidebar_mobile.php'; ?>

      </div>
    </div>

    <?php include 'components/footer.php'; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.1/dist/js/bootstrap.bundle.min.js" integrity="sha384-/bQdsTh/da6pkI1MST/rWKFNjaCP5gBSY4sEBT38Q/9RBh9AH40zEOg7Hlq2THRZ" crossorigin="anonymous"></script>
</body>
</html>