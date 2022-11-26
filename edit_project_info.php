<?php
require 'config/config.php';
if( isset($_POST["project-id"]) && !empty($_POST["project-id"])) {
    $project_title = $_POST["project-title"];
    $project_desc = $_POST["project-description"];
}
else{
    $error = "Could not find project ID.";
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit | <?php echo $project_title; ?> | Intertext</title>
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
            <form action="edit_project_info_confirmation.php" method="POST" class="container-fluid create-form">
                <input type="hidden" name="project-id" value="<?php echo $_POST["project-id"]; ?>">
                <div id="info-container" class="pb-3">
                    <!-- Text Title -->
                    <input type="text" class="h1 form-control pt-3 mb-0 mt-3" id="title-id" name="title" value="<?php echo $project_title;?>">
                    <!-- Text Description -->
                    <div class="mb-0 m-3">
                        <label for="description-id" class="">Description: </label>
                        <textarea name="description" id="description-id" class="form-control"><?php echo $project_desc;?></textarea>
                    </div>
                </div>
                <a class="btn btn-danger col-2 m-2" href="project.php?project-id=<?php echo $_POST["project-id"];?>">Discard</a>
                <button type="submit" class="btn btn-dark">Save Changes</button>
            </form>
            
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