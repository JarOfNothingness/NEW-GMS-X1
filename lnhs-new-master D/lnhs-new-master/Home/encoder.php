<?php
session_start();
if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit();
}

include('../LoginRegisterAuthentication/connection.php');

// include('LoginRegisterAuthentication/connection.php');

// Fetch learners from the students table
$learnersQuery = "SELECT id, learners_name FROM students WHERE user_id = " .$_SESSION['userid'];
$learnersResult = mysqli_query($connection, $learnersQuery);

// Fetch adviser name
$userId = $_SESSION['userid'];
$userQuery = "SELECT name FROM user WHERE userid = ?";
$stmt = mysqli_prepare($connection, $userQuery);
mysqli_stmt_bind_param($stmt, 'i', $userId);
mysqli_stmt_execute($stmt);
$userResult = mysqli_stmt_get_result($stmt);
$adviserName = '';
if ($userResult && mysqli_num_rows($userResult) > 0) {
    $user = mysqli_fetch_assoc($userResult);
    $adviserName = $user['name'];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Encode Learner Data</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css" rel="stylesheet">    
    
    <style>
        body {
            background-color: #f8f9fa;
            padding: 20px 0;
        }
        .form-container {
            background-color: white;
            border-radius: 10px;
            box-shadow: 0 0 15px rgba(0,0,0,0.1);
            padding: 30px;
            margin-bottom: 30px;
        }
        .form-section {
            display: none;
            animation: fadeIn 0.5s;
        }
        .form-section.active {
            display: block;
        }
        .section-header {
            background-color: #f8f9fa;
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 5px;
            color: #0d6efd;
            font-weight: bold;
        }
        .nav-pills .nav-link {
            margin: 5px;
            padding: 10px 20px;
        }
        .nav-pills .nav-link.active {
            background-color: #0d6efd;
        }
        .form-group {
            margin-bottom: 20px;
        }
        .checkbox-group {
            padding: 15px;
            background-color: #f8f9fa;
            border-radius: 5px;
            margin-bottom: 20px;
        }
        .form-control:read-only {
            background-color: #e9ecef;
        }
        .btn-navigation {
            margin-top: 20px;
        }
        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }
        .progress {
            height: 10px;
            margin-bottom: 20px;
        }
        .required-field::after {
            content: "*";
            color: red;
            margin-left: 4px;
        }
               /* Back button styling */
               .back-button {
            display: inline-block;
            padding: 10px 20px;
            background-color: darkblue;
            color: #fff;
            text-decoration: none;
            border-radius: 4px;
            transition: background-color 0.3s;
            margin-bottom: 20px;
        }
        .back-button:hover {
            background-color: #2980b9;
        }
    </style>
</head>
<body>

<div class="container">
<a href="view_attendance.php" class="back-button">
        <i class="fas fa-arrow-left"></i> Back 
    </a>
    <div class="row justify-content-center">
        <div class="col-md-10">
            <div class="form-container">
                <h2 class="text-center mb-4">Learner Information Form</h2>
                
                <!-- Progress bar -->
                <div class="progress">
                    <div class="progress-bar" role="progressbar" style="width: 25%"></div>
                </div>

                <!-- Navigation Pills -->
                <ul class="nav nav-pills nav-justified mb-4">
                    <li class="nav-item">
                        <a class="nav-link active" data-section="1">
                            <i class="fas fa-user"></i> Basic Info
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" data-section="2">
                            <i class="fas fa-school"></i> Educational Background
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" data-section="3">
                            <i class="fas fa-file-alt"></i> Assessment Details
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" data-section="4">
                            <i class="fas fa-building"></i> School Information
                        </a>
                    </li>
                </ul>

                <form action="encode_process.php" method="POST" id="learnerForm">
                    <!-- Section 1: Basic Information -->
                    <div class="form-section active" id="section1">
                        <div class="section-header">
                            <i class="fas fa-user"></i> Basic Information
                        </div>
                        
                        <div class="form-group">
                            <label class="required-field">Select Learner</label>
                            <select class="form-select" name="learner_id" id="learner_id" required onchange="fetchLearnerDetails(this.value)">
                                <option value="">Choose a learner</option>
                                <?php while ($learner = mysqli_fetch_assoc($learnersResult)): ?>
                                    <option value="<?php echo $learner['id']; ?>"><?php echo htmlspecialchars($learner['learners_name']); ?></option>
                                <?php endwhile; ?>
                            </select>
                        </div>

                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>Last Name</label>
                                    <input type="text" class="form-control" name="last_name" id="last_name" readonly>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>First Name</label>
                                    <input type="text" class="form-control" name="first_name" id="first_name" readonly>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>Middle Name</label>
                                    <input type="text" class="form-control" name="middle_name" id="middle_name" readonly>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="required-field">LRN</label>
                                    <input type="text" class="form-control" name="lrn" maxlength="12" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="required-field">Birthdate</label>
                                    <input type="date" class="form-control" name="birthdate" required>
                                </div>
                            </div>
                        </div>

                        <div class="form-group">
                            <label class="required-field">Sex</label>
                            <select class="form-select" name="sex" required>
                                <option value="">Select gender</option>
                                <option value="Male">Male</option>
                                <option value="Female">Female</option>
                            </select>
                        </div>
                    </div>

                    <!-- Section 2: Educational Background -->
                    <div class="form-section" id="section2">
                        <div class="section-header">
                            <i class="fas fa-school"></i> Educational Background
                        </div>

                        <div class="checkbox-group">
                            <div class="form-check">
                                <input type="checkbox" class="form-check-input" name="elementary_completer" id="elementary_completer" value="1">
                                <label class="form-check-label">HighsSchool Completer</label>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                            <div class="form-group">
                                <label class="required-field">General Average</label>
                                <div class="input-group">
                                    <input type="text" class="form-control" name="general_average" required readonly>
                                </div>
                                <small class="form-text text-muted">Automatically computed from assessment and quiz grades</small>
                            </div>

                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Citation (If Any)</label>
                                    <input type="text" class="form-control" name="citation">
                                </div>
                            </div>
                        </div>

                        <div class="form-group">
                            <label>Elementary School Name</label>
                            <input type="text" class="form-control" name="elementary_school_name">
                        </div>
                    </div>

                    <!-- Section 3: Assessment Information -->
                    <div class="form-section" id="section3">
                        <div class="section-header">
                            <i class="fas fa-file-alt"></i> Assessment Information
                        </div>

                        <div class="checkbox-group">
                            <div class="form-check mb-2">
                                <input type="checkbox" class="form-check-input" name="pept_passer" id="pept_passer" value="1">
                                <label class="form-check-label">PEPT Passer</label>
                            </div>
                            <div class="form-check mb-2">
                                <input type="checkbox" class="form-check-input" name="als_a_e_passer" id="als_a_e_passer" value="1">
                                <label class="form-check-label">ALS A & E Passer</label>
                            </div>
                            <div class="form-check">
                                <input type="checkbox" class="form-check-input" name="others_specify" id="others_specify" value="1">
                                <label class="form-check-label">Others</label>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>PEPT Rating</label>
                                    <input type="text" class="form-control" name="pept_rating">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>ALS Rating</label>
                                    <input type="text" class="form-control" name="als_rating">
                                </div>
                            </div>
                        </div>

                        <div class="form-group">
                            <label>Date of Examination</label>
                            <input type="date" class="form-control" name="exam_date">
                        </div>

                        <div class="form-group">
                            <label>Testing Center</label>
                            <input type="text" class="form-control" name="testing_center">
                        </div>
                    </div>

                    <!-- Section 4: School Information -->
                    <div class="form-section" id="section4">
                        <div class="section-header">
                            <i class="fas fa-building"></i> School Information
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Adviser</label>
                                    <input type="text" class="form-control" name="adviser" value="<?php echo htmlspecialchars($adviserName); ?>" readonly>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>School Year</label>
                                    <input type="text" class="form-control" name="school_year" id="school_year" readonly>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>School</label>
                                    <input type="text" class="form-control" name="school" value="Lanao National High School" readonly>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>School ID</label>
                                    <input type="text" class="form-control" name="school_id" value="303031" readonly>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>District</label>
                                    <input type="text" class="form-control" name="district" value="Pilar" readonly>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>Division</label>
                                    <input type="text" class="form-control" name="division" value="Cebu" readonly>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>Region</label>
                                    <input type="text" class="form-control" name="region" value="VII" readonly>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Navigation Buttons -->
                    <div class="d-flex justify-content-between mt-4">
                        <button type="button" class="btn btn-secondary" id="prevBtn" onclick="navigate(-1)">Previous</button>
                        <button type="button" class="btn btn-primary" id="nextBtn" onclick="navigate(1)">Next</button>
                           <!-- Back Button -->
   
                        <button type="submit" class="btn btn-success" id="submitBtn" style="display: none;">
    Submit and View Record
</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<!-- Add SweetAlert2 JS -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    
// Form state management
const formState = {
    currentSection: 1,
    totalSections: 4,
    isSubmitting: false
};

// Function to validate form
function validateForm() {
    const currentSection = document.getElementById(`section${formState.currentSection}`);
    if (!currentSection) return true;

    const requiredFields = currentSection.querySelectorAll('[required]');
    let isValid = true;

    requiredFields.forEach(field => {
        if (!field.value.trim()) {
            field.classList.add('is-invalid');
            isValid = false;
            
            let errorDiv = field.nextElementSibling;
            if (!errorDiv || !errorDiv.classList.contains('invalid-feedback')) {
                errorDiv = document.createElement('div');
                errorDiv.className = 'invalid-feedback';
                errorDiv.textContent = 'This field is required';
                field.parentNode.insertBefore(errorDiv, field.nextSibling);
            }
        } else {
            field.classList.remove('is-invalid');
            const errorDiv = field.nextElementSibling;
            if (errorDiv && errorDiv.classList.contains('invalid-feedback')) {
                errorDiv.remove();
            }
        }
    });

    if (!isValid) {
        Swal.fire({
            icon: 'error',
            title: 'Required Fields Empty',
            text: 'Please fill in all required fields before proceeding.',
            confirmButtonColor: '#3085d6'
        });
    }

    return isValid;
}

// Navigation function
function navigate(direction) {
    if (direction === 1) {
        if (!validateForm()) {
            return;
        }
    }

    const newSection = formState.currentSection + direction;
    if (newSection >= 1 && newSection <= formState.totalSections) {
        // Hide current section
        document.querySelector(`#section${formState.currentSection}`).classList.remove('active');
        // Show new section
        document.querySelector(`#section${newSection}`).classList.add('active');
        
        // Update navigation pills
        document.querySelector(`[data-section="${formState.currentSection}"]`).classList.remove('active');
        document.querySelector(`[data-section="${newSection}"]`).classList.add('active');
        
        formState.currentSection = newSection;
        updateProgressBar();
        updateButtons();
    }
}

// Update progress bar
function updateProgressBar() {
    const progress = (formState.currentSection / formState.totalSections) * 100;
    const progressBar = document.querySelector('.progress-bar');
    progressBar.style.width = `${progress}%`;
    progressBar.setAttribute('aria-valuenow', progress);
}

// Update button visibility
function updateButtons() {
    const prevBtn = document.getElementById('prevBtn');
    const nextBtn = document.getElementById('nextBtn');
    const submitBtn = document.getElementById('submitBtn');

    prevBtn.style.display = formState.currentSection === 1 ? 'none' : 'block';
    nextBtn.style.display = formState.currentSection === formState.totalSections ? 'none' : 'block';
    submitBtn.style.display = formState.currentSection === formState.totalSections ? 'block' : 'none';
}

// Fetch learner details
function fetchLearnerDetails(learnerId) {
    if (!learnerId) {
        clearLearnerFields();
        return;
    }

    try {
        // First fetch general learner details
        fetch(`get_learner_details.php?id=${learnerId}`)
            .then(response => {
                if (!response.ok) throw new Error('Failed to fetch learner details');
                return response.json();
            })
            .then(learner => {
                populateLearnerFields(learner);
                // After populating basic details, fetch the final grade
                return fetch(`get_final_grade.php?student_id=${learnerId}&subject_id=2`); // Assuming subject_id 2 for now
            })
            .then(response => {
                if (!response.ok) throw new Error('Failed to fetch grade data');
                return response.json();
            })
            .then(gradeData => {
                if (gradeData.success && gradeData.final_grade) {
                    // Populate the general average field
                    document.querySelector('input[name="general_average"]').value = gradeData.final_grade;
                    // Make the field readonly since it's automatically computed
                    document.querySelector('input[name="general_average"]').readOnly = true;
                }
            })
            .catch(error => {
                console.error('Error:', error);
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'Failed to fetch learner details or grades'
                });
            });
    } catch (error) {
        console.error('Error:', error);
        Swal.fire({
            icon: 'error',
            title: 'Error',
            text: 'Failed to fetch learner details'
        });
    }
}

// Clear learner fields
function clearLearnerFields() {
    ['last_name', 'first_name', 'middle_name', 'school_year'].forEach(fieldId => {
        document.getElementById(fieldId).value = '';
    });
}

// Populate learner fields
function populateLearnerFields(learner) {
    const names = learner.learners_name.split(' ');
    
    if (names.length > 0) {
        document.getElementById('last_name').value = names[names.length - 1].trim();
        document.getElementById('first_name').value = names[0].trim();
        document.getElementById('middle_name').value = names.length > 2 ? 
            names.slice(1, names.length - 1).join(' ').trim() : '';
    }

    document.getElementById('school_year').value = learner.school_year || '';
}

// Form submission handling
document.getElementById('learnerForm').addEventListener('submit', async function(e) {
    e.preventDefault();
    
    // Validate all sections before submission
    let isValid = true;
    for (let i = 1; i <= formState.totalSections; i++) {
        formState.currentSection = i;
        if (!validateForm()) {
            isValid = false;
            break;
        }
    }
    
    if (!isValid) {
        return;
    }

    try {
        const formData = new FormData(this);
        const response = await fetch('encode_process.php', {
            method: 'POST',
            body: formData
        });

        if (!response.ok) {
            throw new Error('Network response was not ok');
        }

        const result = await response.json();
        
        if (result.success) {
            await Swal.fire({
                icon: 'success',
                title: 'Success!',
                text: 'Form submitted successfully',
                timer: 1500,
                showConfirmButton: false
            });
            window.location.href = 'form137.php';
        } else {
            throw new Error(result.message || 'Submission failed');
        }
    } catch (error) {
        Swal.fire({
            icon: 'error',
            title: 'Error',
            text: error.message || 'An error occurred while submitting the form'
        });
    }
});

// Initialize form on page load
document.addEventListener('DOMContentLoaded', () => {
    updateProgressBar();
    updateButtons();
});
</script>