<?php
	require 'config/config.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Coming Soon | Intertext</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.1/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-F3w7mX95PdgyTmZZMECAngseQB83DfGTowi0iMjiWaeVhAn4FJkqJByhZMI3AhiU" crossorigin="anonymous">
    <link rel="stylesheet" href="intertext.css">
</head>
<body>
    <?php include 'components/nav.php'; ?>

    <div class="d-flex flex-1">

      <!-- IF NOT MOBILE -->
      <?php include 'components/sidebar.php'; ?>

      <div id="coming-soon" class="col-sm-12 col-md-8 col-lg-9">

        <div class="container-fluid">
            <h1 class="pt-3">Coming Soon</h1>
        </div>
        
      </div>

    </div>

    <div class="d-flex flex-column">
      <!-- MOBILE ONLY -->
      <?php include 'components/sidebar_mobile.php'; ?>
    </div>

    <?php include 'components/footer.php'; ?>
</body>
</html>