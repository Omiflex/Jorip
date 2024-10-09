<?php
ob_start();
session_start();
include "component/head.php";
include "config/db_connect.php";
checkIfLoggedIn();
?>

<body>
  <main style="background-image: url(assets/img/login-bg.png); 
               background-size: cover; 
               background-position: center center;">

    <div class="container">

      <section class="section register min-vh-100 d-flex flex-column align-items-center justify-content-center py-4">
        <div class="container">
          <div class="row justify-content-center">
            <div class="col-lg-6 col-md-8 d-flex flex-column align-items-center justify-content-center">

              <div class="d-flex justify-content-center py-4">
                <a href="index.html" class="logo d-flex align-items-center w-auto">
                  <img src="assets2/img/jorip-logo.png" alt="">
                  <span class="d-none d-lg-block">JORIP</span>
                </a>
              </div><!-- End Logo -->

              <div class="card mb-3">
                <div class="card-body">

                  <!-- Registration Form Section -->
                  <div id="registrationForm">
                    <h5 class="card-title text-center pb-0 fs-4">Create an Account</h5>

                    <form class="row g-3 needs-validation" method="POST" action="pages-register.php" enctype="multipart/form-data">
                      <!-- Hidden input to store role -->
                      <input type="hidden" name="role" id="role" value="surveyor" required>

                      <!-- Role Selection Buttons -->
                      <div class="col-12">
                        <label for="role" class="form-label">Select Role</label>
                        <div class="d-flex justify-content-around my-3">
                          <button class="btn btn-secondary w-100 role-btn" type="button" id="btnSurveyor" onclick="selectRole('surveyor')" style="background-color: blue;">Surveyor</button>
                          <button class="btn btn-secondary w-100 ms-2 role-btn" type="button" id="btnClient" onclick="selectRole('client')">Client</button>
                        </div>
                        <div class="invalid-feedback" id="roleError">Please select a role!</div>
                      </div>

                      <!-- Additional Fields based on Role -->
                      <div id="additionalFields" class="col-12"></div>

                      <!-- input field for email -->
                      <div class="col-12">
                        <label for="yourEmail" class="form-label">Your Email</label>
                        <input type="email" name="email" class="form-control" id="yourEmail" required>
                        <div class="invalid-feedback">Please enter a valid Email address!</div>
                      </div>

                      <!-- input field for phone -->
                      <div class="col-12">
                        <label for="Phone" class="form-label">Your Phone Number</label>
                        <input type="tel" name="phone" class="form-control" id="Phone" required pattern="[0-9]{11}">
                        <div class="invalid-feedback">Please enter a valid phone number!</div>
                      </div>

                      <!-- input field for address -->
                      <div class="col-12">
                        <label for="address" class="form-label">Your Address</label>
                        <input type="text" name="address" class="form-control" id="address" required>
                        <div class="invalid-feedback">Please enter your address</div>
                      </div>

                      <!-- input field for image -->
                      <div class="col-12">
                        <label for="profilePicture" class="form-label">Profile Picture</label>
                        <input type="file" name="profile_picture" class="form-control" id="profilePicture" required>
                        <div class="invalid-feedback">Please upload a profile picture!</div>
                      </div>

                      <!-- input field for username -->
                      <div class="col-12">
                        <label for="yourUsername" class="form-label">Username</label>
                        <div class="input-group has-validation">
                          <span class="input-group-text" id="inputGroupPrepend">@</span>
                          <input type="text" name="username" class="form-control" id="yourUsername" required>
                          <div class="invalid-feedback">Please choose a username.</div>
                        </div>
                      </div>

                      <!-- input field for password -->
                      <div class="col-12">
                        <label for="yourPassword" class="form-label">Password</label>
                        <input type="password" name="password" class="form-control" id="yourPassword" required>
                        <div class="invalid-feedback">Please enter your password!</div>
                      </div>

                      <div class="col-12">
                      <button class="btn w-100" type="submit" name="submit" style="background-color: #FF885B; color: white; border: 2px solid #FF885B;">Create</button>

                      </div>

                      <div class="col-12">
                        <p class="small mb-0">Already have an account? <a href="pages-login.php">Log in</a></p>
                      </div>
                    </form>
                    <?php signupUser(); ?>
                  </div>
                </div>
              </div>

              <?php include "component/footer.php"; ?>

            </div>
          </div>
        </div>
      </section>
    </div>
  </main>

  <a href="#" class="back-to-top d-flex align-items-center justify-content-center"><i class="bi bi-arrow-up-short"></i></a>

  <!-- Vendor JS Files -->
  <script src="assets/vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
  <script src="assets/js/main.js"></script>

  <script>
  function selectRole(role) {
    document.getElementById('role').value = role;
    document.getElementById('roleError').style.display = 'none';

    let additionalFields = document.getElementById('additionalFields');
    additionalFields.innerHTML = '';

    const surveyorBtn = document.getElementById('btnSurveyor');
    const clientBtn = document.getElementById('btnClient');

    // Reset styles for both buttons
    surveyorBtn.style.backgroundColor = '';
    surveyorBtn.style.color = '';
    surveyorBtn.style.border = '';
    clientBtn.style.backgroundColor = '';
    clientBtn.style.color = '';
    clientBtn.style.border = '';

    // Apply selected styles
    if (role === 'surveyor') {
      surveyorBtn.style.backgroundColor = '#FF885B';
      surveyorBtn.style.color = 'white';
      surveyorBtn.style.border = '2px solid #FF885B';
      
      additionalFields.innerHTML = `
        <div class="col-12">
          <label for="firstName" class="form-label">First Name</label>
          <input type="text" name="firstName" class="form-control" id="firstName" required>
          <div class="invalid-feedback">Please, enter your first name!</div>
        </div>
        
        <div class="col-12">
          <label for="lastName" class="form-label">Last Name</label>
          <input type="text" name="lastName" class="form-control" id="lastName" required>
          <div class="invalid-feedback">Please, enter your last name!</div>
        </div>`;
      
    } else if (role === 'client') {
      clientBtn.style.backgroundColor = '#FF885B';
      clientBtn.style.color = 'white';
      clientBtn.style.border = '2px solid #FF885B';

      additionalFields.innerHTML = `
        <div class="col-12">
          <label for="companyName" class="form-label">Company Name</label>
          <input type="text" name="companyName" class="form-control" id="companyName" required>
          <div class="invalid-feedback">Please enter your company name!</div>
        </div>`;
    }
  }

  window.onload = function() {
    selectRole('surveyor');
  };
</script>

</body>
<?php ob_end_flush() ?>