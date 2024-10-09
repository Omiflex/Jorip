<?php
session_start();
include "component/head.php";
include "config/db_connect.php";

checkIfLoggedIn();

$loginError = false;

if (isset($_POST["submit"])) {
  $username = $_POST['username'];
  $password = $_POST['password'];
  $user = loginUser($username, $password);

  if ($user) {
    // Store user info in session
    $_SESSION['user_id'] = $user['id'];
    $_SESSION['user_role'] = $user['role'];
    header('Location: index.php');
    exit();
  } else {
    $loginError = true;
  }
}
?>

<body>

  <main style="background-image: url(assets/img/login-bg.png); 
               background-size: cover; 
               background-position: center center;">

    <div class="container">

      <section class="section register min-vh-100 d-flex flex-column align-items-center justify-content-center py-4">
        <div class="container">
          <div class="row justify-content-center">
            <div class="col-lg-4 col-md-6 d-flex flex-column align-items-center justify-content-center">

              <div class="d-flex justify-content-center py-4">
                <a href="index.html" class="logo d-flex align-items-center w-auto">
                  <img src="assets2/img/jorip-logo.png" alt="">
                  <span class="d-none d-lg-block" style="font-size : 30px">JORIP</span>
                </a>
              </div><!-- End Logo -->

              <!-- Error Message Container (Initially Hidden) -->
              <div id="errorMessage" class="error-message"></div>

              <div class="card mb-3">

                <div class="card-body">

                  <div class="pt-4 pb-2">
                    <h5 class="card-title text-center pb-0 fs-4">Login to Your Account</h5>
                    <p class="text-center small">Enter your username & password to login</p>
                  </div>

                  <form class="row g-3 needs-validation" novalidate method="POST" action="<?php htmlspecialchars($_SERVER["PHP_SELF"]); ?>">

                    <div class="col-12">
                      <label for="yourUsername" class="form-label">Username</label>
                      <div class="input-group has-validation">
                        <span class="input-group-text" id="inputGroupPrepend">@</span>
                        <input type="text" name="username" class="form-control" id="yourUsername" required>
                        <div class="invalid-feedback">Please enter your username.</div>
                      </div>
                    </div>

                    <div class="col-12">
                      <label for="yourPassword" class="form-label">Password</label>
                      <input type="password" name="password" class="form-control" id="yourPassword" required>
                      <div class="invalid-feedback">Please enter your password!</div>
                    </div>

                    <div class="col-12">
                      <div class="form-check">
                        <input class="form-check-input" type="checkbox" name="remember" value="true" id="rememberMe">
                        <label class="form-check-label" for="rememberMe">Remember me</label>
                      </div>
                    </div>
                    <div class="col-12">
                      <button class="btn btn-primary w-100" type="submit" name="submit" style="background-color: #FF885B; color: white; border: 2px solid #FF885B;">Login</button>
                    </div>
                    <div class="col-12">
                      <p class="small mb-0">Don't have account? <a href="pages-register.php">Create an account</a></p>
                    </div>
                  </form>

                </div>
              </div>

            </div>
          </div>
        </div>

      </section>

    </div>
  </main><!-- End #main -->

  <a href="#" class="back-to-top d-flex align-items-center justify-content-center"><i class="bi bi-arrow-up-short"></i></a>

  <!-- Vendor JS Files -->
  <script src="assets/vendor/apexcharts/apexcharts.min.js"></script>
  <script src="assets/vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
  <script src="assets/vendor/chart.js/chart.umd.js"></script>
  <script src="assets/vendor/echarts/echarts.min.js"></script>
  <script src="assets/vendor/quill/quill.js"></script>
  <script src="assets/vendor/simple-datatables/simple-datatables.js"></script>
  <script src="assets/vendor/tinymce/tinymce.min.js"></script>
  <script src="assets/vendor/php-email-form/validate.js"></script>

  <!-- Template Main JS File -->
  <!-- <script src="assets/js/main.js"></script> -->

  <!-- Error Message Styles -->
  <style>
    .error-message {
      display: none; /* Initially hidden */
      position: fixed;
      top: 20px;
      left: 50%;
      transform: translateX(-50%);
      background-color: rgba(255, 0, 0, 0.9); /* Red with transparency */
      color: white;
      padding: 10px 20px;
      border-radius: 5px;
      box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
      z-index: 1000; /* Ensure it’s above other elements */
    }

    .error-message.show {
      display: block; /* Show when adding .show class */
    }
  </style>

  <!-- JavaScript for Error Message Display -->
  <script>
    function showError(message) {
      const errorMessage = document.getElementById('errorMessage');
      errorMessage.textContent = message;
      errorMessage.classList.add('show');

      // Hide the message after 3 seconds
      setTimeout(() => {
        errorMessage.classList.remove('show');
      }, 3000);
    }

    // Trigger error message if login fails
    <?php if ($loginError): ?>
      showError('Invalid password. Please try again!');
    <?php endif; ?>
  </script>

</body>
</html>
