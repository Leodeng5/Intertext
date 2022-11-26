<?php
// Generate items if logged in
if( isset($_SESSION["username"]) && $_SESSION["username"]) {

    $mysqli = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
    if ( $mysqli->connect_errno ) {
        echo $mysqli->connect_error;
        exit();
    }
    $mysqli->set_charset("utf8");

    // Project Items
    $stmt = $mysqli->prepare("SELECT projects.project_id, projects.title FROM projects
                    LEFT JOIN projects_has_contributors
                        ON projects_has_contributors.project_id = projects.project_id
                    WHERE projects_has_contributors.contributor_id = ?");
	$stmt->bind_param("i", $_SESSION["contributor_id"]);
	$executed = $stmt->execute();
	if(!$executed){
		$error = $mysqli->error;
	}
    else{
        $results_projects = $stmt->get_result();
    }

    // Job Items
    $stmt = $mysqli->prepare("SELECT jobs.job_id, projects.title AS project_title, jobs.title, job_status.status, job_status.status_abbrev FROM jobs
                    LEFT JOIN job_status
                        ON job_status.job_status_id = jobs.job_status_id
                    LEFT JOIN projects
                        ON jobs.project_id = projects.project_id
                    WHERE jobs.contributor_id = ?");
    $stmt->bind_param("i", $_SESSION["contributor_id"]);
    $executed = $stmt->execute();
    if(!$executed){
        $error = $mysqli->error;
    }
    else{
        $results_jobs = $stmt->get_result();
    }
    $stmt->close();

    $mysqli->close();
}
?>
<!-- PROJECTS-CONTAINER-SM -->
<div class="container-fluid mobile-only" id="projects-container-sm">
    <h2>My Projects</h2>
    <div class="project-items">
        <?php if(!isset($_SESSION["logged_in"]) || !$_SESSION["logged_in"]) :?>
            <!-- DISPLAY SAMPLE ITEMS -->
            <?php include 'placeholders/project_item.php' ?>
        <?php else: ?>
            <!-- GET & DISPLAY ITEMS -->
            <?php if(!$results_projects->num_rows > 0):?>
                <div>You have no projects!</div>
            <?php endif; ?>
            <?php while($row_project = $results_projects->fetch_assoc()) : ?>
                <a href="project.php?project-id=<?php echo $row_project["project_id"];?>" class="row project-item">
                    <div class="row project-details">
                        <div>
                            <span class="project-link col-10"><?php echo $row_project["title"];?></span>
                            <span class="project-role col-2 mobile-only">
                                <?php
                                    if(is_maintainer($row_project["project_id"], $_SESSION["maintainer_id"])){
                                        echo "Maintainer";
                                    }
                                    else{
                                        echo "Contributor";
                                    }
                                ?>
                            </span>
                            <span class="project-role col-2 not-mobile">
                                <?php
                                    if(is_maintainer($row_project["project_id"], $_SESSION["maintainer_id"])){
                                        echo "M";
                                    }
                                    else{
                                        echo "C";
                                    }
                                ?>
                            </span>
                        </div>
                        <!-- <span class="project-owner">by Owner One</span> -->
                    </div>
                </a>
            <?php endwhile; ?>
        <?php endif;?>
    </div>

    <a class="col-12 btn btn-dark" href="create_project.php">Create New Project</a>
</div>
<!-- END PROJECTS-CONTAINER-SM-->

<!-- JOBS-CONTAINER-SM -->
<div class="container-fluid mobile-only" id="jobs-container-sm">
    <h2>My Jobs</h2>
    <div class="job-items">
        <?php if(!isset($_SESSION["logged_in"]) || !$_SESSION["logged_in"]) :?>
            <!-- DISPLAY SAMPLE ITEMS -->
            <?php include 'placeholders/job_item.php' ?>
        <?php else: ?>
            <!-- GET & DISPLAY ITEMS -->
            <?php if(!$results_jobs->num_rows > 0):?>
                <div>You have no jobs!</div>
            <?php endif; ?>
            <?php while($row_job = $results_jobs->fetch_assoc()) : ?>
                <a href="job.php?job-id=<?php echo $row_job["job_id"];?>" class="row job-item">
                    <div class="row job-details">
                        <div>
                            <span class="job-link col-10"><?php echo $row_job["title"];?></span>
                            <span class="job-status col-2 mobile-only"><?php echo $row_job["status"];?></span>
                            <span class="job-status col-2 not-mobile"><?php echo $row_job["status_abbrev"];?></span>
                        </div>
                        <span class="job-project"><?php echo $row_job["project_title"];?></span>
                    </div>
                </a>
            <?php endwhile; ?>
        <?php endif;?>
    </div>
    <a class="col-12 btn btn-dark" href="create_job.php">Create New Job</a>
</div>
<!-- END JOBS-CONTAINER-SM-->