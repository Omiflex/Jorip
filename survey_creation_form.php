<?php
ob_start();
include "component/head.php";
include "component/header.php";
include "config/db_connect.php";

if (isset($_POST["submit"])) {
    $client_id = $_SESSION['id'];

    $survey_title = $_POST['survey_title'];
    $cost_per_row = $_POST['cost_per_row'];
    $required_entries = $_POST['required_entries'];
    $questions = $_POST['question'];
    $data_types = $_POST['data_type'];

    $survey_title = sanitizeInput($survey_title);

    $sql = "INSERT INTO survey_list (client_id, title, required_entries, cost_per_row) VALUES (?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("isii", $client_id, $survey_title, $required_entries, $cost_per_row);
    $stmt->execute();

    $stmt->close();

    $survey_id = $conn->insert_id;

    $sql = "INSERT INTO survey_questions (survey_id, question_text, question_type) VALUES (?, ?, ?)";
    $stmt = $conn->prepare($sql);

    for ($i = 0; $i < count($questions); $i++) {
        $question_text = sanitizeInput($questions[$i]);
        $data_type = sanitizeInput($data_types[$i]);

        $stmt->bind_param("iss", $survey_id, $question_text, $data_type);
        $stmt->execute();
    }

    $stmt->close();
    $conn->close();

    header("Location: index.php");
    exit();
}
?>

<body>

    <main style="background-image: url(assets/img/login-bgp.jpg); 
                 background-size: cover; 
                 background-position: center center;">
        <main>
            <div class="container">

                <section class="section register min-vh-100 d-flex flex-column align-items-center justify-content-center py-4">
                    <div class="container">
                        <div class="row justify-content-center">
                            <div class="col-lg-10 col-md-12 d-flex flex-column align-items-center justify-content-center">

                                <div>
                                    <a href="index.php" class="logo d-flex align-items-center w-auto">
                                        <span class="d-none d-lg-block">JORIP</span>
                                    </a>
                                </div><!-- End Logo -->

                                <div class="card mb-5" style="width: 100%; max-width: 700px; margin-top: 35px;">
                                    <div class="card-body">
                                        <div class="pt-4 pb-2">
                                            <h5 class="card-title text-center pb-0 fs-4">Create Your Survey</h5>
                                            <p class="text-center small">Enter your survey title and questions</p>
                                        </div>

                                        <form id="surveyForm" class="row g-4 needs-validation" novalidate method="POST" action="<?php htmlspecialchars($_SERVER["PHP_SELF"]); ?>">

                                            <!-- Survey Title Field -->
                                            <div class="col-12">
                                                <label for="survey_title" class="form-label">Survey Title</label>
                                                <input type="text" name="survey_title" class="form-control form-control-lg" id="survey_title" required>
                                                <div class="invalid-feedback">Please enter a survey title.</div>
                                            </div>

                                            <!-- Price per Data Row -->
                                            <div class="col-12 mt-3">
                                                <label for="cost_per_row" class="form-label">Price per Data Row (Minimum 10 Taka)</label>
                                                <input type="number" name="cost_per_row" class="form-control form-control-lg" id="price_per_data_row" min="10" required>
                                                <div class="invalid-feedback">Please enter a price of at least 10 Taka.</div>
                                            </div>

                                            <!-- Number of Entries Expected -->
                                            <div class="col-12 mt-3">
                                                <label for="required_entries" class="form-label">Number of Entries Expected</label>
                                                <input type="number" name="required_entries" class="form-control form-control-lg" id="required_entries" required>
                                                <div class="invalid-feedback">Please specify the number of entries expected for this survey.</div>
                                            </div>

                                            <!-- Container to hold dynamic fields -->
                                            <div id="dynamic-fields-container" class="col-12 mt-4">
                                                <div class="row g-3 dynamic-field">
                                                    <div class="col-lg-1 field-number d-flex align-items-end">
                                                        <label>1.</label>
                                                    </div>
                                                    <div class="col-lg-8">
                                                        <textarea name="question[]" class="form-control form-control-lg" id="question" rows="2" style="resize:vertical; min-height: 60px;" required></textarea>
                                                        <div class="invalid-feedback">Please enter your survey question.</div>
                                                    </div>
                                                    <div class="col-lg-3 d-flex align-items-end">
                                                        <select name="data_type[]" class="form-control form-control-lg" id="data_type" required>
                                                            <option value="">Select a data type</option>
                                                            <option value="String">Text</option>
                                                            <option value="Float">Numeric</option>
                                                            <option value="Boolean">Yes/No</option>
                                                        </select>
                                                    </div>
                                                </div>
                                            </div>

                                            <!-- Add Button -->
                                            <div class="col-12 text-center mt-3">
                                                <button type="button" class="btn btn-success" id="add-field-button">Add Another Question</button>
                                            </div>

                                            <div class="col-12 text-center mt-3">
                                                <button class="btn btn-primary" type="submit" name="submit" style="background-color: #FF885B; border-color: #FF885B;">Submit Survey</button>
                                            </div>
                                        </form>
                                    </div>
                                </div>

                                <div class="credits">
                                    <?php include "component/footer.php" ?>
                                </div>

                            </div>
                        </div>
                    </div>

                </section>

            </div>
        </main><!-- End #main -->

        <a href="#" class="back-to-top d-flex align-items-center justify-content-center"><i class="bi bi-arrow-up-short"></i></a>

        <!-- Vendor JS Files -->
        <script src="assets/vendor/bootstrap/js/bootstrap.bundle.min.js"></script>

        <!-- Dynamic Add/Remove Fields Script -->
        <script>
            // Function to update field numbers
            function updateFieldNumbers() {
                let fieldNumbers = document.querySelectorAll('.field-number label');
                fieldNumbers.forEach((label, index) => {
                    label.textContent = (index + 1) + ".";
                });
            }

            // Function to add a new dynamic field
            function addField() {
                let container = document.getElementById('dynamic-fields-container');
                let newField = document.createElement('div');
                newField.classList.add('row', 'g-3', 'dynamic-field', 'mt-3');

                newField.innerHTML = `
                <div class="col-lg-1 field-number d-flex align-items-end">
                  <label></label>
                </div>
                <div class="col-lg-8"> 
                  <textarea name="question[]" class="form-control form-control-lg" rows="2" style="resize:vertical; min-height: 60px;" required></textarea>
                  <div class="invalid-feedback">Please enter your survey question.</div>
                </div>
                <div class="col-lg-3 d-flex align-items-end">
                  <select name="data_type[]" class="form-control form-control-lg" required>
                    <option value="">Select a data type</option>
                    <option value="String">Text</option>
                    <option value="Float">Numeric</option>
                    <option value="Boolean">Yes/No</option>
                  </select>
                  <button type="button" class="btn btn-danger ms-2 delete-field-button">Delete</button>
                </div>
              `;

                // Append new field to container
                container.appendChild(newField);

                // Add event listener to the delete button of the new field
                newField.querySelector('.delete-field-button').addEventListener('click', function() {
                    newField.remove();
                    updateFieldNumbers();
                });

                // Update field numbers
                updateFieldNumbers();
            }

            // Add event listener to Add button
            document.getElementById('add-field-button').addEventListener('click', addField);

            // Initial numbering of the first field
            updateFieldNumbers();
        </script>

</body>

</html>
<?php ob_end_flush() ?>
