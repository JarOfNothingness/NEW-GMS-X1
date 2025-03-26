<?php
session_start();

if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit();
}
include("classrecordheader.php");
include("../LoginRegisterAuthentication/connection.php");
// include("LoginRegisterAuthentication/connection.php");

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
    <title>Statistics Overview</title>
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #f8f9fa;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        .container {
            max-width: 1200px;
            padding: 2rem;
        }

        .page-header {
            background: linear-gradient(135deg, #007bff, #0056b3);
            color: white;
            padding: 2rem 0;
            margin-bottom: 2rem;
            border-radius: 10px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }

        .filter-card {
            background: white;
            padding: 1.5rem;
            border-radius: 10px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
            margin-bottom: 2rem;
        }

        .form-control {
            border-radius: 8px;
            border: 1px solid #e0e0e0;
            padding: 0;
            transition: all 0.3s ease;
        }

        .form-control:focus {
            box-shadow: 0 0 0 0.2rem rgba(0, 123, 255, 0.15);
            border-color: #80bdff;
        }

        .btn-primary {
            padding: 0.75rem 2rem;
            border-radius: 8px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            transition: all 0.3s ease;
            background: #007bff;
            border: none;
            box-shadow: 0 2px 4px rgba(0, 123, 255, 0.2);
        }

        .btn-primary:hover {
            transform: translateY(-1px);
            box-shadow: 0 4px 6px rgba(0, 123, 255, 0.3);
            background: #0056b3;
        }

        .stats-container {
            display: flex;
            gap: 2rem;
            margin-top: 2rem;
        }

        .stats-bucket {
            flex: 1;
            padding: 1.5rem;
            border-radius: 12px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.05);
            transition: all 0.3s ease;
        }

        .stats-bucket:hover {
            transform: translateY(-5px);
            box-shadow: 0 6px 12px rgba(0, 0, 0, 0.1);
        }

        .passed-bucket {
            background: linear-gradient(145deg, #e3f2fd, #bbdefb);
            border: 1px solid #90caf9;
        }

        .failed-bucket {
            background: linear-gradient(145deg, #ffebee, #ffcdd2);
            border: 1px solid #ef9a9a;
        }

        .stats-bucket h3 {
            color: #2c3e50;
            font-size: 1.5rem;
            margin-bottom: 1rem;
            padding-bottom: 0.5rem;
            border-bottom: 2px solid rgba(0, 0, 0, 0.1);
        }

        .stats-bucket p {
            font-size: 1.1rem;
            color: #34495e;
            margin-bottom: 0.5rem;
        }

        .stats-total {
            font-size: 1.25rem;
            font-weight: 600;
            margin: 1rem 0;
            padding: 0.5rem;
            border-radius: 6px;
            text-align: center;
            background: rgba(255, 255, 255, 0.5);
        }

        .student-list {
            max-height: 300px;
            overflow-y: auto;
            margin-top: 1rem;
            padding: 0.5rem;
            background: rgba(255, 255, 255, 0.7);
            border-radius: 8px;
        }

        .student-item {
            padding: 0.75rem;
            border-bottom: 1px solid rgba(0, 0, 0, 0.05);
            display: flex;
            justify-content: space-between;
            align-items: center;
            transition: background-color 0.2s ease;
        }

        .student-item:hover {
            background-color: rgba(255, 255, 255, 0.9);
        }

        .student-item:last-child {
            border-bottom: none;
        }

        .loading-overlay {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(255, 255, 255, 0.8);
            display: flex;
            justify-content: center;
            align-items: center;
            z-index: 1000;
        }

        .loading-content {
            text-align: center;
            background: white;
            padding: 2rem;
            border-radius: 12px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
        }

        .spinner-border {
            width: 3rem;
            height: 3rem;
            color: #007bff;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="page-header text-center">
            <h1><i class="fas fa-chart-pie me-2"></i> Statistics Overview</h1>
            <p class="lead">Analyze student performance and demographics</p>
        </div>
        
        <div class="filter-card">
            <form id="filterForm">
                <div class="row">
                    <div class="col-md-4">
                        <label for="school_year" class="form-label">
                            <i class="fas fa-calendar me-2"></i>School Year
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
                    <div class="col-md-4">
                        <label for="grade_section" class="form-label">
                            <i class="fas fa-users me-2"></i>Grade & Section
                        </label>
                        <select id="grade_section" name="grade_section" class="form-control" onchange="fetchSubjects(this.value)">
                            <option value="">All Grade & Section</option>
                            <!-- <?php while ($row = mysqli_fetch_assoc($grade_sections_result)): ?>
                                <option value="<?php echo htmlspecialchars($row['grade & section']); ?>"
                                    <?php echo ($current_grade_section == $row['grade & section']) ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($row['grade & section']); ?>
                                </option>
                            <?php endwhile; ?> -->
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label for="subject" class="form-label">
                            <i class="fas fa-book me-2"></i>Subject
                        </label>
                        <select id="subject" name="subject" class="form-control">
                            <option value="">All Subjects</option>
                            <!-- <?php while ($row = mysqli_fetch_assoc($subjects_result)): ?>
                                <option value="<?php echo $row['id']; ?>"
                                    <?php echo ($current_subject == $row['id']) ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($row['name']); ?>
                                </option>
                            <?php endwhile; ?> -->
                        </select>
                    </div>
                </div>
                <div class="row mt-3">
                    <div class="col-12 text-center">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-filter me-2"></i>Apply Filters
                        </button>
                    </div>
                </div>
            </form>
        </div>

        <div id="statisticsResults"></div>

        <div id="loadingMessage" class="loading-overlay" style="display: none;">
            <div class="loading-content">
                <div class="spinner-border" role="status">
                    <span class="sr-only">Loading...</span>
                </div>
                <p class="mt-3 mb-0">Loading statistics...</p>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.2/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
    <script>
        $(document).ready(function() {
            $('#filterForm').on('submit', function(e) {
                e.preventDefault();
                loadStatistics();
            });

            function loadStatistics() {
                $('#loadingMessage').fadeIn(300);
                $.ajax({
                    url: 'load_statistics.php',
                    type: 'GET',
                    data: $('#filterForm').serialize(),
                    dataType: 'json',
                    success: function(data) {
                        var statsHtml = `
                            <div class="stats-container">
                                <div class="stats-bucket passed-bucket">
                                    <h3><i class="fas fa-check-circle me-2"></i>Passed Students</h3>
                                    <p><i class="fas fa-male me-2"></i>Male: ${data.male_passed}</p>
                                    <p><i class="fas fa-female me-2"></i>Female: ${data.female_passed}</p>
                                    <div class="stats-total">
                                        <i class="fas fa-users me-2"></i>Total Passed: ${data.total_passed}
                                    </div>
                                    <div class="student-list">
                                        ${data.passed_students.map(student => `
                                            <div class="student-item">
                                                <span><i class="fas fa-user me-2"></i>${student.name} (${student.gender})</span>
                                                <span class="badge badge-success">${student.final_grade}</span>
                                            </div>
                                        `).join('')}
                                    </div>
                                </div>
                                <div class="stats-bucket failed-bucket">
                                    <h3><i class="fas fa-times-circle me-2"></i>Failed Students</h3>
                                    <p><i class="fas fa-male me-2"></i>Male: ${data.male_failed}</p>
                                    <p><i class="fas fa-female me-2"></i>Female: ${data.female_failed}</p>
                                    <div class="stats-total">
                                        <i class="fas fa-users me-2"></i>Total Failed: ${data.total_failed}
                                    </div>
                                    <div class="student-list">
                                        ${data.failed_students.map(student => `
                                            <div class="student-item">
                                                <span><i class="fas fa-user me-2"></i>${student.name} (${student.gender})</span>
                                                <span class="badge badge-danger">${student.final_grade}</span>
                                            </div>
                                        `).join('')}
                                    </div>
                                </div>
                            </div>
                        `;
                        $('#statisticsResults').html(statsHtml).hide().fadeIn(500);
                        $('#loadingMessage').fadeOut(300);
                    },
                    error: function(jqXHR, textStatus, errorThrown) {
                        console.error("AJAX error: " + textStatus + ' : ' + errorThrown);
                        alert('An error occurred while loading the statistics.'+ textStatus);
                        $('#loadingMessage').fadeOut(300);
                    }
                });
            }

            if ($('#school_year').val() || $('#grade_section').val() || $('#subject').val()) {
                loadStatistics();
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