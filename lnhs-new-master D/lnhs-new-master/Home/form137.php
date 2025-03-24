<?php
session_start();

$success_msg = '';
if (isset($_SESSION['form_success'])) {
    $success_msg = $_SESSION['form_success'];
    unset($_SESSION['form_success']);
}

$userid = $_SESSION['userid'];

if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit();
}

include('../LoginRegisterAuthentication/connection.php');

// Updated query with better formatting and complete data
$query = "SELECT 
            e.*,
            YEAR(e.birthdate) as batch_year,
            s.`grade & section` as grade_section,
            CASE 
                WHEN e.high_school_completer = 1 THEN 'Yes'
                ELSE 'No'
            END as high_school_status,
            CASE 
                WHEN e.pept_passer = 1 THEN 'Yes'
                ELSE 'No'
            END as pept_status,
            CASE 
                WHEN e.als_a_e_passer = 1 THEN 'Yes'
                ELSE 'No'
            END as als_status
          FROM encoded_learner_data e
          INNER JOIN students s on s.id = e.learner_id
          ORDER BY e.last_name ASC, e.first_name ASC";

$result = mysqli_query($connection, $query);

if (!$result) {
    die("Query failed: " . mysqli_error($connection));
}

// Get statistics
$total_records = mysqli_num_rows($result);
$male_students = 0;
$female_students = 0;
$high_school_completers = 0;
$pept_passers = 0;
$als_passers = 0;

mysqli_data_seek($result, 0);
while($row = mysqli_fetch_assoc($result)) {
    if($row['sex'] == 'Male') $male_students++;
    if($row['sex'] == 'Female') $female_students++;
    if($row['high_school_completer'] == 1) $high_school_completers++;
    if($row['pept_passer'] == 1) $pept_passers++;
    if($row['als_a_e_passer'] == 1) $als_passers++;
}
mysqli_data_seek($result, 0);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Form 137 - Learner's Permanent Records</title>
    
    <!-- CSS Dependencies -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.datatables.net/1.13.7/css/dataTables.bootstrap5.min.css" rel="stylesheet">
    <link href="https://cdn.datatables.net/responsive/2.5.0/css/responsive.bootstrap5.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    
    <style>

        body {
            background-color: #f8f9fa;
            padding: 20px;
        }
        
        .main-container {
            max-width: 1400px;
            margin: 0 auto;
        }

        .page-title {
            background-color: #4a69bd;
            padding: 20px;
            color: #fff;
            font-size: 43px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            width: 100%;
            border-radius: 15px;
            font-weight: 700;
            text-transform: uppercase;
        }

        .stats-card {
            background: white;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
            text-align: center;
            transition: transform 0.3s ease;
            margin-bottom: 20px;
        }

        .stats-card:hover {
            transform: translateY(-5px);
        }

        .stats-icon {
            font-size: 2.5rem;
            margin-bottom: 15px;
            color: #fff;
            width: 60px;
            height: 60px;
            line-height: 60px;
            border-radius: 50%;
            margin: 0 auto 15px;
        }

        .stats-card.total {
            background: linear-gradient(45deg, #1abc9c, #16a085);
        }

        .stats-card.male {
            background: linear-gradient(45deg, #3498db, #2980b9);
        }

        .stats-card.female {
            background: linear-gradient(45deg, #e91e63, #c2185b);
        }

        .stats-card.high-school {
            background: linear-gradient(45deg, #2ecc71, #27ae60);
        }

        .stats-number {
            font-size: 2rem;
            font-weight: bold;
            color: white;
            margin-bottom: 5px;
        }

        .stats-label {
            color: white;
            font-size: 0.9rem;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        .badge-custom {
            padding: 0.4rem 0.8rem;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: 500;
            margin: 0 2px;
        }

        .badge-high-school {
            background-color: #2ecc71;
            color: white;
        }

        .badge-pept {
            background-color: #3498db;
            color: white;
        }

        .badge-als {
            background-color: #e67e22;
            color: white;
        }

        .table-container {
            background: white;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
            margin-top: 30px;
        }

        .filter-section {
            background: white;
            padding: 15px;
            border-radius: 10px;
            margin-bottom: 20px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.05);
        }

        .back-button {
            display: inline-block;
            padding: 10px 20px;
            background-color: #3f51b5;
            color: #fff;
            text-decoration: none;
            border-radius: 25px;
            transition: all 0.3s ease;
            margin-bottom: 20px;
            border: none;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }

        .back-button:hover {
            background-color: #2980b9;
            color: #fff;
            transform: translateY(-2px);
            box-shadow: 0 4px 6px rgba(0,0,0,0.2);
        }

        .btn-print {
            background: linear-gradient(45deg, #3498db, #2980b9);
            color: white;
            border: none;
            padding: 0.5rem 1rem;
            border-radius: 20px;
            transition: all 0.3s ease;
        }

        .btn-print:hover {
            background: linear-gradient(45deg, #2980b9, #3498db);
            transform: translateY(-2px);
            color: white;
        }

        .dataTables_wrapper .form-control {
            border-radius: 20px;
            border: 1px solid #ddd;
            padding: 8px 15px;
        }

        .table thead th {
            background-color: #f8f9fa;
            border-bottom: 2px solid #dee2e6;
            color: #2c3e50;
            font-weight: 600;
        }

        .table tbody tr:hover {
            background-color: #f8f9fa;
            transition: background-color 0.3s ease;
        }
    </style>
</head>
<body>

<div class="main-container">
    <h1 class="page-title text-center">Form 137 - Learner's Permanent Records</h1>

    <?php if ($success_msg): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <?php echo htmlspecialchars($success_msg); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>

    <a href="view_attendance.php" class="back-button mt-3">
        <i class="fas fa-arrow-left"></i> Back to Attendance
    </a>

    <!-- Statistics Cards -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="stats-card total">
                <div class="stats-icon">
                    <i class="fas fa-users"></i>
                </div>
                <div class="stats-number"><?php echo $total_records; ?></div>
                <div class="stats-label">Total Records</div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stats-card male">
                <div class="stats-icon">
                    <i class="fas fa-male"></i>
                </div>
                <div class="stats-number"><?php echo $male_students; ?></div>
                <div class="stats-label">Male Students</div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stats-card female">
                <div class="stats-icon">
                    <i class="fas fa-female"></i>
                </div>
                <div class="stats-number"><?php echo $female_students; ?></div>
                <div class="stats-label">Female Students</div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stats-card high-school">
                <div class="stats-icon">
                    <i class="fas fa-graduation-cap"></i>
                </div>
                <div class="stats-number"><?php echo $high_school_completers; ?></div>
                <div class="stats-label">High School Completers</div>
            </div>
        </div>
    </div>

    <div class="table-container">
        <!-- Filters Section -->
        <div class="filter-section">
            <div class="row">
                <div class="col-md-4">
                    <select class="form-select" id="schoolYearFilter">
                        <option value="">All School Years</option>
                        <?php
                        $years_query = "SELECT DISTINCT school_year FROM encoded_learner_data ORDER BY school_year DESC";
                        $years_result = mysqli_query($connection, $years_query);
                        while ($year = mysqli_fetch_assoc($years_result)) {
                            echo "<option value='" . htmlspecialchars($year['school_year']) . "'>" . 
                                 htmlspecialchars($year['school_year']) . "</option>";
                        }
                        ?>
                    </select>
                </div>
                <div class="col-md-4">
                    <select class="form-select" id="genderFilter">
                        <option value="">All Genders</option>
                        <option value="Male">Male</option>
                        <option value="Female">Female</option>
                    </select>
                </div>
                <div class="col-md-4">
                    <select class="form-select" id="gradeSectionFilter">
                        <option value="">All Grade & Section</option>
                        <?php
                            $sectionsQuery = "SELECT DISTINCT `grade & section` FROM students WHERE user_id = ? ORDER BY `grade & section`";
                            $stmt = mysqli_prepare($connection, $sectionsQuery);
                            mysqli_stmt_bind_param($stmt, 'i', $userid);
                            mysqli_stmt_execute($stmt);
                            $sectionsResult = mysqli_stmt_get_result($stmt);
                            while ($section = mysqli_fetch_assoc($sectionsResult)):
                                $selected = (isset($_GET['section']) && $_GET['section'] == $section['grade & section']) ? 'selected' : '';
                        ?>
                                <option value="<?= htmlspecialchars($section['grade & section']) ?>" <?= $selected ?>>
                                    <?= htmlspecialchars($section['grade & section']) ?>
                                </option>
                        <?php endwhile; ?>
                    </select>
                </div>
            </div>
        </div>

        <!-- Data Table -->
        <div class="table-responsive">
            <table id="learnersTable" class="table table-striped table-hover">
                <thead>
                    <tr>
                        <th>Full Name</th>
                        <th>LRN</th>
                        <th>Sex</th>
                        <th>Birth Date</th>
                        <th>School</th>
                        <th>School Year</th>
                        <th hidden>Grade and  Section</th>
                        <th>Status</th>
                        <th class="no-print">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($data = mysqli_fetch_assoc($result)): 
                        $fullName = trim(implode(' ', array_filter([
                            $data['last_name'],
                            $data['first_name'],
                            $data['name_extension'],
                            $data['middle_name']
                        ])));
                    ?>
                        <tr>
                            <td><?php echo htmlspecialchars($fullName); ?></td>
                            <td><?php echo htmlspecialchars($data['lrn']); ?></td>
                            <td><?php echo htmlspecialchars($data['sex']); ?></td>
                            <td><?php echo date('M d, Y', strtotime($data['birthdate'])); ?></td>
                            <td><?php echo htmlspecialchars($data['elementary_school_name']); ?></td>
                            <td><?php echo htmlspecialchars($data['school_year']); ?></td>
                            <td hidden><?php echo htmlspecialchars($data['grade_section']); ?></td>
                            <td>
                                <?php if ($data['high_school_completer'] == 1): ?>
                                    <span class="badge-custom badge-high-school">High School</span>
                                <?php endif; ?>
                                <?php if ($data['pept_passer'] == 1): ?>
                                    <span class="badge-custom badge-pept">PEPT</span>
                                <?php endif; ?>
                                <?php if ($data['als_a_e_passer'] == 1): ?>
                                    <span class="badge-custom badge-als">ALS</span>
                                <?php endif; ?>
                            </td>
                            <td class="no-print">
                                <a href="print_form.php?id=<?php echo $data['id']; ?>" 
                                   class="btn btn-print btn-sm">
                                    <i class="fas fa-print me-1"></i>Print
                                </a>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- JavaScript Dependencies -->
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.7/js/dataTables.bootstrap5.min.js"></script>
<script src="https://cdn.datatables.net/responsive/2.5.0/js/dataTables.responsive.min.js"></script>
<script src="https://cdn.datatables.net/responsive/2.5.0/js/responsive.bootstrap5.min.js"></script>

<script>
$(document).ready(function() {
    // Initialize DataTable with enhanced features
    const table = $('#learnersTable').DataTable({
        responsive: true,
        dom: '<"top"lf>rt<"bottom"ip><"clear">',
        language: {
            search: "_INPUT_",
            searchPlaceholder: "Search learners...",
            lengthMenu: "Show _MENU_ records per page",
            info: "Showing _START_ to _END_ of _TOTAL_ learners",
            infoEmpty: "No learners found",
            infoFiltered: "(filtered from _MAX_ total records)",
            zeroRecords: "No matching learners found"
        },
        order: [[0, 'asc']], // Sort by full name by default
        pageLength: 10,
        lengthMenu: [[10, 25, 50, -1], [10, 25, 50, "All"]],
        columnDefs: [
            {
                targets: -1,
                orderable: false,
                className: 'text-center'
            },
            {
                targets: 6, // Status column
                orderable: true,
                className: 'text-center'
            }
        ],
        initComplete: function() {
            // Add custom styling to the DataTable controls
            $('.dataTables_length select').addClass('form-select form-select-sm');
            $('.dataTables_filter input').addClass('form-control form-control-sm');
            
            // Add tooltips to action buttons
            $('.btn-print').tooltip({
                title: 'Print Form 137',
                placement: 'top'
            });
        }
    });

    // Enhanced filter functionality
    $('#schoolYearFilter').on('change', function() {
        const searchTerm = this.value;
        table.column(5) // School Year column
            .search(searchTerm)
            .draw();
        
        // Update URL with filter
        updateUrlParams('school_year', searchTerm);
    });

    $('#gradeSectionFilter').on('change', function() {
        const searchTerm = this.value;
        table.column(6) // School Year column
            .search(searchTerm)
            .draw();
        
        // Update URL with filter
        updateUrlParams('grade_section', searchTerm);
    });

    $('#genderFilter').on('change', function() {
        const searchTerm = this.value;
        table.column(2) // Sex column
            .search(searchTerm)
            .draw();
        
        // Update URL with filter
        updateUrlParams('gender', searchTerm);
    });

    // Function to update URL parameters
    function updateUrlParams(key, value) {
        const url = new URL(window.location.href);
        if (value) {
            url.searchParams.set(key, value);
        } else {
            url.searchParams.delete(key);
        }
        window.history.pushState({}, '', url);
    }

    // Apply filters from URL on page load
    function applyUrlFilters() {
        const url = new URL(window.location.href);
        const schoolYear = url.searchParams.get('school_year');
        const gender = url.searchParams.get('gender');
        const gradeSection = url.searchParams.get('grade_section');

        if (schoolYear) {
            $('#schoolYearFilter').val(schoolYear).trigger('change');
        }
        if (gender) {
            $('#genderFilter').val(gender).trigger('change');
        }
        if (gradeSection) {
            $('#gradeSectionFilter').val(gradeSection).trigger('change');
        }
    }

    // Call function on load
    applyUrlFilters();

    // Add animation to stats cards
    $('.stats-card').each(function(index) {
        $(this).delay(100 * index).animate({
            opacity: 1,
            top: 0
        }, 500);
    });

    // Add hover effect to table rows
    $('#learnersTable tbody').on('mouseenter', 'tr', function() {
        $(this).addClass('hover-highlight');
    }).on('mouseleave', 'tr', function() {
        $(this).removeClass('hover-highlight');
    });

    // Add print functionality
    $('.btn-print').on('click', function(e) {
        e.preventDefault();
        const printUrl = $(this).attr('href');
        
        // Show loading state
        $(this).html('<i class="fas fa-spinner fa-spin me-1"></i>Preparing...');
        
        // Open print window
        window.open(printUrl, '_blank');
        
        // Reset button state after a delay
        const btn = $(this);
        setTimeout(() => {
            btn.html('<i class="fas fa-print me-1"></i>Print');
        }, 1000);
    });

    // Add responsive behavior to stats cards
    function adjustStatsCards() {
        if (window.innerWidth < 768) {
            $('.stats-card').addClass('mb-3');
        } else {
            $('.stats-card').removeClass('mb-3');
        }
    }

    // Call on load and resize
    adjustStatsCards();
    $(window).resize(adjustStatsCards);

    // Add success message auto-hide
    $('.alert-success').delay(3000).fadeOut(500);
});
</script>

</body>
</html>