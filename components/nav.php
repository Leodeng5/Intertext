<?php
if(isset($_GET["login"]) && $_GET["login"] == false){
    $login_error = "invalid username/password";
}
?>


<!-- NAVBAR -->
<nav class="navbar navbar-expand-md navbar-light">
    <div class="container-fluid">
        <a href="intertext.php" class="navbar-brand">Intertext</a>
        <?php if(!isset($_SESSION["logged_in"]) || !$_SESSION["logged_in"]) :?>
            <div id="intertext-nav-md" class="not-mobile justify-content-end">
                <!-- Login Form -->
                <form id="nav-login-form" action="login.php" class="form-inline" method="POST">
                    <input id="username-id" class="form-control" type="text" name="username" placeholder="Username">
                    <small id="username-error" class=""></small>

                    <input id="password-id" class="form-control" type="password" name="password" placeholder="Password">
                    <small id="password-error" class=""></small>

                    <button class="btn btn-dark" type="submit">Login</button>
                    <a class="btn btn-outline-light" href="register_form.php">Sign Up</a>
                </form>
        <?php else: ?>
            <div id="intertext-nav-md" class="justify-content-end">
                <div class="d-flex">
                    <!-- Username -->
                    <div class="mt-3">Welcome, <?php echo $_SESSION["username"];?>!</div>
                    <!-- Link to Logout -->
                    <a class="btn btn-dark p-2" href="logout.php">Logout</a>
                </div>
        <?php endif;?>
        <?php if(!isset($_SESSION["logged_in"]) || !$_SESSION["logged_in"]) :?>
            <!-- Link to Register -->
            <a class="btn btn-outline-light mobile-only" href="register_form.php">Sign Up</a>
        <?php endif;?>
            </div>
    </div>
</nav>

<?php if(isset($_GET["login"]) && $_GET["login"] == false):?>
    <div id="login-error" class="container-fluid text-light text-center"><?php echo $login_error;?></div>
<?php endif;?>

<div id="intertext-nav-sm" class="container-fluid mobile-only">
    <div class="row justify-content-center">
        <?php if(!isset($_SESSION["logged_in"]) || !$_SESSION["logged_in"]) :?>
            <!-- Login Form -->
            <form id="nav-login-form-sm" action="login.php" class="col-6 row pd10 justify-content-center form-inline" method="POST">
                <div>
                    <input id="username-id-sm" class="form-control mt10" type="text" name="username" placeholder="Username">
                    <small id="username-error" class="invalid-feedback">Username is required.</small>
                </div>
                <div>
                    <input id="password-id-sm" class="form-control mt10" type="password" name="password" placeholder="Password">
                    <small id="password-error" class="invalid-feedback">Password is required.</small>
                </div>
                <div id="nav-item-buttons" class="col-12 row pt10">
                    <!-- Login Button -->
                    <div class="col-2"></div>
                    <button class="col-8 btn btn-dark" type="submit">Login</button>
                    <div class="col-2"></div>
                </div>
            </form>
        <?php endif;?>
    </div>
</div>
<!-- END NAVBAR -->

<script src="https://code.jquery.com/jquery-3.6.0.min.js" integrity="sha256-/xUj+3OJU5yExlq6GSYGSHk7tPXikynS7ogEvDej/m4=" crossorigin="anonymous"></script>
<script>
    // Input field shared
    $("#username-id").on("change", function(){
        $("#username-id-sm").val($(this).val());
    });
    $("#username-id-sm").on("change", function(){
        $("#username-id").val($(this).val());
    });

    $("#password-id").on("change", function(){
        $("#password-id-sm").val($(this).val());
    });
    $("#password-id-sm").on("change", function(){
        $("#password-id").val($(this).val());
    });


    // JS Validation
    let login_form = document.querySelector('#nav-login-form');
    if(login_form){
        document.querySelector('#nav-login-form').onsubmit = function(){
            // console.log("submitted");
            if ( document.querySelector('#username-id').value.trim().length == 0 ) {
                document.querySelector('#username-id-sm').classList.add('is-invalid');
                document.querySelector('#username-id').classList.add('is-invalid');
            } else {
                document.querySelector('#username-id-sm').classList.remove('is-invalid');
                document.querySelector('#username-id').classList.remove('is-invalid');
            }

            if ( document.querySelector('#password-id').value.trim().length == 0 ) {
                document.querySelector('#password-id-sm').classList.add('is-invalid');
                document.querySelector('#password-id').classList.add('is-invalid');
            } else {
                document.querySelector('#password-id-sm').classList.remove('is-invalid');
                document.querySelector('#password-id').classList.remove('is-invalid');
            }

            return ( !document.querySelectorAll('.is-invalid').length > 0 );
        }
    }

    let login_form_sm = document.querySelector('#nav-login-form');
    if(login_form_sm){
        document.querySelector('#nav-login-form-sm').onsubmit = function(){
            // console.log("submitted");
            if ( document.querySelector('#username-id-sm').value.trim().length == 0 ) {
                document.querySelector('#username-id-sm').classList.add('is-invalid');
                document.querySelector('#username-id').classList.add('is-invalid');
            } else {
                document.querySelector('#username-id-sm').classList.remove('is-invalid');
                document.querySelector('#username-id').classList.remove('is-invalid');
            }

            if ( document.querySelector('#password-id-sm').value.trim().length == 0 ) {
                document.querySelector('#password-id-sm').classList.add('is-invalid');
                document.querySelector('#password-id').classList.add('is-invalid');
            } else {
                document.querySelector('#password-id-sm').classList.remove('is-invalid');
                document.querySelector('#password-id').classList.remove('is-invalid');
            }

            return ( !document.querySelectorAll('.is-invalid').length > 0 );
        }
    }
</script>