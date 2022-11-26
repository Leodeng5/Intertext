<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign Up | Intertext</title>
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
            <h1 class="pt-3">New User Registration</h1>
        </div>

        <form id="register-form" action="register_confirmation.php" method="POST" class="container-fluid create-form">

            <div class="form-group row">
				<label for="username-id-reg" class="col-sm-3 col-form-label text-sm-right">Username: <span class="text-danger">*</span></label>
				<div class="col-sm-9">
					<input type="text" class="form-control" id="username-id-reg" name="username">
					<small id="username-error" class="invalid-feedback">Username is required.</small>
				</div>
			</div>

            <div class="form-group row">
				<label for="password-id-reg" class="col-sm-3 col-form-label text-sm-right">Password: <span class="text-danger">*</span></label>
				<div class="col-sm-9">
					<input type="password" class="form-control" id="password-id-reg" name="password">
					<small id="password-error" class="invalid-feedback">Password is required.</small>
				</div>
			</div>

            <div class="form-group row">
                <div class="col-sm-9"></div>
                <div class="col-sm-3 mt-2">
                    <button type="submit" class="btn btn-dark">Register</button>
                </div>
            </div>

        </form>
      </div>
    </div>

    <?php include 'components/footer.php'; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.1/dist/js/bootstrap.bundle.min.js" integrity="sha384-/bQdsTh/da6pkI1MST/rWKFNjaCP5gBSY4sEBT38Q/9RBh9AH40zEOg7Hlq2THRZ" crossorigin="anonymous"></script>

    <script>
        // JS Validation
        document.querySelector('#register-form').onsubmit = function(){
            if ( document.querySelector('#username-id-reg').value.trim().length == 0 ) {
                document.querySelector('#username-id-reg').classList.add('is-invalid');
            } else {
                document.querySelector('#username-id-reg').classList.remove('is-invalid');
            }

            if ( document.querySelector('#password-id-reg').value.trim().length == 0 ) {
                document.querySelector('#password-id-reg').classList.add('is-invalid');
            } else {
                document.querySelector('#password-id-reg').classList.remove('is-invalid');
            }

            return ( !document.querySelectorAll('.is-invalid').length > 0 );
        }
    </script>
</body>
</html>