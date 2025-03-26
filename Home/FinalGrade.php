<?php
session_start();

if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit();
}

include("../LoginRegisterAuthentication/connection.php");
include("../crud/header.php");

$userid = $_SESSION['userid'];

// Fetch distinct school years
$school_years_query = "SELECT DISTINCT school_year FROM students WHERE user_id=".$userid." ORDER BY school_year DESC";
$school_years_result = mysqli_query($connection, $school_years_query);

// Fetch distinct grade & sections
$grade_sections_query = "SELECT DISTINCT `grade & section` FROM students WHERE user_id=".$userid." ORDER BY `grade & section`";
$grade_sections_result = mysqli_query($connection, $grade_sections_query);

// Fetch subjects

$subjectsQuery = "SELECT DISTINCT description as name,subject_id as id FROM student_subjects WHERE student_id IN (SELECT id FROM students WHERE user_id = ?) ORDER BY description";
$stmt = mysqli_prepare($connection, $subjectsQuery);
mysqli_stmt_bind_param($stmt, 'i', $userid);
mysqli_stmt_execute($stmt);
$subjects_result = mysqli_stmt_get_result($stmt);


// Check for query execution errors
if (!$school_years_result || !$grade_sections_result || !$subjects_result) {
    die("Query failed: " . mysqli_error($connection));
}

// Get current filter values
$current_school_year = isset($_GET['school_year']) ? $_GET['school_year'] : '';
$current_grade_section = isset($_GET['grade_section']) ? $_GET['grade_section'] : '';
$current_subject = isset($_GET['subject']) ? $_GET['subject'] : '';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Final Grades</title>
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        
        :root {
            --primary-color: #2c3e50;
            --secondary-color: #3498db;
            --success-color: #27ae60;
            --danger-color: #e74c3c;
            --background-color: #f5f6fa;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: var(--background-color);
            color: var(--primary-color);
            line-height: 1.6;
            padding-top: 20px;
        }

        .container {
            max-width: 1400px;
            padding: 20px;
            margin: 0 auto;
        }

        /* Header Styling */
        .page-header {
            background-color: #4a69bd;
            color: white;
            padding: 2rem;
            border-radius: 15px;
            margin-bottom: 2rem;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            text-align: center;
        }

        .page-header h2 {
            margin: 0;
            font-size: 2rem;
            font-weight: 600;
        }

        /* Form Styling */
        #filterForm {
            background: white;
            padding: 2rem;
            border-radius: 15px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            margin-bottom: 2rem;
        }

        .form-control {
            border-radius: 8px;
            border: 2px solid #e9ecef;
            padding: 0.75rem;
            transition: all 0.3s ease;
            height: 60px;
        }

        .form-control:focus {
            border-color: var(--secondary-color);
            box-shadow: 0 0 0 0.2rem rgba(52, 152, 219, 0.25);
        }

        label {
            font-weight: 600;
            color: var(--primary-color);
            margin-bottom: 0.5rem;
            display: block;
        }

        /* Button Styling */
        .btn-primary {
            background: #3f51b5;
            border: none;
            padding: 0.75rem 2rem;
            border-radius: 8px;
            font-weight: 600;
            transition: all 0.3s ease;
        }

        .btn-primary:hover {
            background: #2980b9;
            transform: translateY(-2px);
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }

        /* Table Styling */
        .table-container {
            background: white;
            padding: 1.5rem;
            border-radius: 15px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            overflow-x: auto;
        }

        .table {
            margin-bottom: 0;
            width: 100%;
            border-collapse: separate;
            border-spacing: 0;
        }

        .table th {
            background: #f8f9fa;
            color: var(--primary-color);
            font-weight: 600;
            padding: 1rem;
            text-transform: uppercase;
            font-size: 0.9rem;
            border: none;
            border-bottom: 2px solid #dee2e6;
        }

        .table td {
            padding: 1rem;
            vertical-align: middle;
            border: 1px solid #dee2e6;
        }

        .table tbody tr:hover {
            background-color: #f8f9fa;
        }

        /* Grades Styling */
        .grade-cell {
            font-weight: 600;
            font-size: 1.1rem;
        }

        .final-grade {
            font-weight: 700;
            font-size: 1.2rem;
            color: var(--primary-color);
        }

        .remarks {
            font-weight: 600;
            padding: 0.5rem 1rem;
            border-radius: 20px;
            display: inline-block;
            min-width: 100px;
            text-align: center;
        }

        .remarks.passed {
            background-color: rgba(39, 174, 96, 0.1);
            color: var(--success-color);
        }

        .remarks.failed {
            background-color: rgba(231, 76, 60, 0.1);
            color: var(--danger-color);
        }

        /* Loading Message */
        #loadingMessage {
            position: fixed;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            background: white;
            padding: 2rem;
            border-radius: 15px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            z-index: 1000;
        }

        .spinner-border {
            width: 3rem;
            height: 3rem;
            color: var(--secondary-color);
        }

        /* Responsive Design */
        @media (max-width: 768px) {
            .container {
                padding: 10px;
            }
            
            .page-header {
                padding: 1.5rem;
            }

            #filterForm {
                padding: 1rem;
            }

            .btn-primary {
                width: 100%;
                margin-top: 1rem;
            }

            .table {
                font-size: 0.9rem;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <!-- Page Header -->
        <div class="page-header">
            <h2>
                <i class="fas fa-graduation-cap me-2"></i>
                Summary of Quarterly Grades
            </h2>
        </div>

        <!-- Filter Form -->
        <form id="filterForm">
            <div class="row">
                <div class="col-md-4 mb-3">
                    <label for="school_year">
                        <i class="fas fa-calendar-alt me-2"></i>School Year
                    </label>
                    <select id="school_year" name="school_year" class="form-control" onchange="fetchSections(this.value)">
                        <option value="">All School Years</option>
                        <?php while ($row = mysqli_fetch_assoc($school_years_result)): ?>
                            <option value="<?php echo htmlspecialchars($row['school_year']); ?>"
                                <?php echo ($current_school_year == $row['school_year']) ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($row['school_year']); ?>
                            </option>
                        <?php endwhile; ?>
                    </select>
                </div>
                <div class="col-md-4 mb-3">
                    <label for="grade_section">
                        <i class="fas fa-chalkboard me-2"></i>Grade & Section
                    </label>
                    <select id="grade_section" name="grade_section" class="form-control" onchange="fetchSubjects(this.value)">
                        <option value="">All Grade & Section</option>
                        <?php while ($row = mysqli_fetch_assoc($grade_sections_result)): ?>
                            <option value="<?php echo htmlspecialchars($row['grade & section']); ?>"
                                <?php echo ($current_grade_section == $row['grade & section']) ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($row['grade & section']); ?>
                            </option>
                        <?php endwhile; ?>
                    </select>
                </div>
                <div class="col-md-4 mb-3">
                    <label for="subject">
                        <i class="fas fa-book me-2"></i>Subject
                    </label>
                    <select id="subject" name="subject" class="form-control">
                        <option value="">All Subjects</option>
                        <?php while ($row = mysqli_fetch_assoc($subjects_result)): ?>
                            <option value="<?php echo $row['id']; ?>"
                                <?php echo ($current_subject == $row['id']) ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($row['name']); ?>
                            </option>
                        <?php endwhile; ?>
                    </select>
                </div>
            </div>
            <div class="text-center">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-filter me-2"></i>Apply Filters
                </button>
            </div>
        </form>

        <!-- Grades Table -->
        <div class="table-container">
            <table id="gradesTable" class="table table-bordered table-striped">
                <thead>
                    <tr>
                        <th>LEARNERS' NAMES</th>
                        <th>1st Quarter</th>
                        <th>2nd Quarter</th>
                        <th>3rd Quarter</th>
                        <th>4th Quarter</th>
                        <th>Final Grade</th>
                        <th>Remarks</th>
                    </tr>
                </thead>
                <tbody>
                    <!-- Data will be dynamically populated here -->
                </tbody>
            </table>
        </div>

        <!-- Loading Message -->
        <div id="loadingMessage" class="text-center" style="display: none;">
            <div class="spinner-border" role="status">
                <span class="sr-only">Loading...</span>
            </div>
            <p class="mb-0">Loading grades...</p>
        </div>
    </div>

    <!-- Scripts -->
    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.2/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
    <script>
    $(document).ready(function() {
        $('#filterForm').on('submit', function(e) {
            e.preventDefault();
            loadGrades();
        });

        function formatGrade(grade) {
            return grade !== null ? parseFloat(grade).toFixed(2) : 'N/A';
        }

        function loadGrades() {
            $('#loadingMessage').show();
            $.ajax({
                url: 'load_grades.php',
                type: 'GET',
                data: $('#filterForm').serialize(),
                dataType: 'json',
                success: function(data) {
                    console.log(data);  // For debugging
                    var tbody = $('#gradesTable tbody');
                    tbody.empty();
                    if (data.length === 0) {
                        tbody.append('<tr><td colspan="7" class="text-center">No records found</td></tr>');
                    } else {
                        $.each(data, function(i, grade) {
                            var row = '<tr>' +
                                '<td>' + grade.learners_name + '</td>' +
                                '<td class="grade-cell">' + formatGrade(grade.quarter_1) + '</td>' +
                                '<td class="grade-cell">' + formatGrade(grade.quarter_2) + '</td>' +
                                '<td class="grade-cell">' + formatGrade(grade.quarter_3) + '</td>' +
                                '<td class="grade-cell">' + formatGrade(grade.quarter_4) + '</td>' +
                                '<td class="final-grade">' + formatGrade(grade.final_grade) + '</td>' +
                                '<td class="remarks ' + (grade.remarks === 'Passed' ? 'passed' : 'failed') + '">' + 
                                    grade.remarks + '</td>' +
                                '</tr>';
                            tbody.append(row);
                        });
                    }
                    $('#loadingMessage').hide();
                },
                error: function(jqXHR, textStatus, errorThrown) {
                    console.error("AJAX error: " + textStatus + ' : ' + errorThrown);
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: 'An error occurred while loading the data.'
                    });
                    $('#loadingMessage').hide();
                }
            });
        }

        // Load grades on page load if filters are set
        if ($('#school_year').val() || $('#grade_section').val() || $('#subject').val()) {
            loadGrades();
        }
    });

    function fetchSections(schoolYear) {
        // Create AJAX request
        var xhr = new XMLHttpRequest();
        xhr.open('GET', '../crud/fetch_sections.php?school_year=' + schoolYear, true);
        xhr.onreadystatechange = function () {
            if (xhr.readyState == 4 && xhr.status == 200) {
                console.log(xhr.responseText)
                document.getElementById('grade_section').innerHTML = xhr.responseText;
            }
        };
        xhr.send();
    }

    function fetchSubjects(gradeSection) {
        var subjectDropdown = document.getElementById('subject');
        subjectDropdown.innerHTML = '<option value="">Loading...</option>';

        if (gradeSection !== '') {
            var xhr = new XMLHttpRequest();
            xhr.open('GET', '../crud/fetch_subjects_new.php?grade_section=' + encodeURIComponent(gradeSection), true);
            
            xhr.onreadystatechange = function () {
                if (xhr.readyState == 4 && xhr.status == 200) {
                    subjectDropdown.innerHTML = xhr.responseText;
                }
            };
            
            xhr.send();
        } else {
            subjectDropdown.innerHTML = '<option value="">All Subjects</option>';
        }
    }
    </script>
</body>
</html>
<?php 
include("../crud/footer.php");
?>