<?php
session_start();

if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit();
}

include('../LoginRegisterAuthentication/connection.php');

// Get student ID from URL
$student_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// Fetch student data with error handling
$query = "SELECT * FROM encoded_learner_data WHERE id = ?";
$stmt = mysqli_prepare($connection, $query);
if (!$stmt) {
    die("Failed to prepare statement: " . mysqli_error($connection));
}

mysqli_stmt_bind_param($stmt, "i", $student_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$student = mysqli_fetch_assoc($result);

if (!$student) {
    die("Student record not found");
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Form 137 - <?php echo htmlspecialchars($student['first_name'] . ' ' . $student['last_name']); ?></title>
    <style>
        @page {
            size: 8.5in 13in;
            margin: 0.5in;
        }
        
        body {
            font-family: 'Times New Roman', serif;
            font-size: 12pt;
            line-height: 1.3;
            margin: 0;
            padding: 0;
            background-color: #f8f9fa;
        }

        .form-container {
            max-width: 8.5in;
            margin: 0 auto;
            padding: 0.5in;
            box-sizing: border-box;
            background-color: white;
            box-shadow: 0 0 20px rgba(0,0,0,0.1);
        }

        .header {
            text-align: center;
            margin-bottom: 20px;
            border-bottom: 2px solid #007bff;
            padding-bottom: 15px;
        }

        .deped-logo {
            width: 100px;
            height: auto;
            margin-bottom: 10px;
        }

        .form-title {
            text-align: center;
            font-size: 18pt;
            font-weight: bold;
            margin: 30px 0;
            text-transform: uppercase;
            color: #2c3e50;
            letter-spacing: 1px;
        }

        .section {
            margin: 20px 0;
            border: 1px solid #dee2e6;
            padding: 15px;
            border-radius: 5px;
            background-color: #fff;
        }

        .section h3 {
            color: #2c3e50;
            border-bottom: 2px solid #007bff;
            padding-bottom: 5px;
            margin-top: 0;
        }

        .info-grid {
            display: grid;
            grid-template-columns: auto 1fr;
            gap: 10px;
            align-items: center;
        }

        .label {
            font-weight: bold;
            padding-right: 15px;
            min-width: 200px;
            color: #2c3e50;
        }

        .value {
            border-bottom: 1px solid #dee2e6;
            padding: 5px 10px;
            min-height: 24px;
        }

        .two-columns {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 30px;
        }

        .signatures {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 100px;
            margin-top: 60px;
        }

        .signature-line {
            text-align: center;
            border-top: 1px solid #000;
            padding-top: 10px;
            margin-top: 40px;
        }

        .certification {
            margin: 40px 0;
            text-align: justify;
            line-height: 1.6;
            font-style: italic;
            padding: 20px;
            background-color: #f8f9fa;
            border-radius: 5px;
        }

        .badge {
            display: inline-block;
            padding: 3px 10px;
            border-radius: 15px;
            font-size: 0.9em;
            font-weight: normal;
            color: white;
            margin: 2px;
        }

        .badge-success {
            background-color: #28a745;
        }

        .badge-info {
            background-color: #17a2b8;
        }

        .back-button {
            position: fixed;
            bottom: 70px;
            left: 20px;
            padding: 12px 24px;
            background: linear-gradient(45deg, #2c3e50, #3498db);
            color: white;
            border: none;
            border-radius: 25px;
            cursor: pointer;
            text-decoration: none;
            font-size: 14px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.2);
            transition: all 0.3s ease;
        }

        .back-button:hover {
            background: linear-gradient(45deg, #3498db, #2c3e50);
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0,0,0,0.2);
        }

        .print-button {
            position: fixed;
            bottom: 20px;
            right: 20px;
            padding: 12px 24px;
            background: linear-gradient(45deg, #2980b9, #3498db);
            color: white;
            border: none;
            border-radius: 25px;
            cursor: pointer;
            font-size: 14px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.2);
            transition: all 0.3s ease;
        }

        .print-button:hover {
            background: linear-gradient(45deg, #3498db, #2980b9);
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0,0,0,0.2);
        }

        @media print {
            body {
                background-color: white;
            }

            .form-container {
                box-shadow: none;
                padding: 0;
            }

            .no-print {
                display: none;
            }

            .section {
                break-inside: avoid;
            }
        }
    </style>
</head>
<body>
    <div class="form-container">
        <!-- Header -->
        <div class="header">
            <img src="../crud/depedlogobgwhite.png" alt="DepEd Logo" class="deped-logo">
            <h3 style="margin: 5px 0;">Republic of the Philippines</h3>
            <h3 style="margin: 5px 0;">Department of Education</h3>
            <h3 style="margin: 5px 0;">Region <?php echo htmlspecialchars($student['region']); ?></h3>
            <h4 style="margin: 5px 0;">Division of <?php echo htmlspecialchars($student['division']); ?></h4>
            <h4 style="margin: 5px 0;">District of <?php echo htmlspecialchars($student['district']); ?></h4>
            <h4 style="margin: 5px 0;"><?php echo htmlspecialchars($student['school']); ?></h4>
        </div>

        <!-- Form Title -->
        <div class="form-title">
            LEARNER'S PERMANENT ACADEMIC RECORD<br>
            Form 137
        </div>

        <!-- Basic Information -->
        <div class="section">
            <h3>LEARNER'S INFORMATION</h3>
            <div class="info-grid">
                <span class="label">LEARNER REFERENCE NUMBER (LRN):</span>
                <span class="value"><?php echo htmlspecialchars($student['lrn']); ?></span>

                <span class="label">LAST NAME:</span>
                <span class="value"><?php echo htmlspecialchars($student['last_name']); ?></span>

                <span class="label">FIRST NAME:</span>
                <span class="value"><?php echo htmlspecialchars($student['first_name']); ?></span>

                <span class="label">MIDDLE NAME:</span>
                <span class="value"><?php echo htmlspecialchars($student['middle_name']); ?></span>

                <span class="label">NAME EXTENSION:</span>
                <span class="value"><?php echo htmlspecialchars($student['name_extension']); ?></span>

                <span class="label">BIRTHDATE:</span>
                <span class="value"><?php echo date('F d, Y', strtotime($student['birthdate'])); ?></span>

                <span class="label">SEX:</span>
                <span class="value"><?php echo htmlspecialchars($student['sex']); ?></span>
            </div>
        </div>

        <!-- School Information -->
        <div class="section">
            <h3>PREVIOUS SCHOOL INFORMATION</h3>
            <div class="info-grid">
                <span class="label">School Name:</span>
                <span class="value"><?php echo htmlspecialchars($student['elementary_school_name']); ?></span>

                <span class="label">School ID:</span>
                <span class="value"><?php echo htmlspecialchars($student['school_id']); ?></span>

                <span class="label">General Average:</span>
                <span class="value"><?php echo htmlspecialchars($student['general_average']); ?></span>

                <?php if ($student['citation']): ?>
                    <span class="label">Citations/Honors:</span>
                    <span class="value"><?php echo htmlspecialchars($student['citation']); ?></span>
                <?php endif; ?>
            </div>
        </div>

        <!-- Educational Status -->
        <div class="section">
            <h3>EDUCATIONAL QUALIFICATION STATUS</h3>
            <div class="two-columns">
                <!-- High School Status -->
                <div>
                    <div class="info-grid">
                        <span class="label">High School Completer:</span>
                        <span class="value">
                            <?php if ($student['high_school_completer']): ?>
                                <span class="badge badge-success">YES</span>
                            <?php else: ?>
                                <span class="badge badge-info">NO</span>
                            <?php endif; ?>
                        </span>
                    </div>
                </div>

                <!-- Alternative Credentials -->
                <div>
                    <?php if ($student['pept_passer']): ?>
                        <div class="info-grid">
                            <span class="label">PEPT Rating:</span>
                            <span class="value"><?php echo htmlspecialchars($student['pept_rating']); ?></span>
                        </div>
                    <?php endif; ?>

                    <?php if ($student['als_a_e_passer']): ?>
                        <div class="info-grid">
                            <span class="label">ALS A&E Rating:</span>
                            <span class="value"><?php echo htmlspecialchars($student['als_rating']); ?></span>
                        </div>
                    <?php endif; ?>

                    <?php if ($student['others_specify']): ?>
                        <div class="info-grid">
                            <span class="label">Other Qualifications:</span>
                            <span class="value">Yes</span>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Certification -->
        <div class="certification">
            I hereby certify that this is a true and correct record of 
            <strong><?php echo htmlspecialchars($student['first_name'] . ' ' . $student['last_name']); ?></strong> 
            and that the information contained herein is in accordance with the official records 
            on file in this school.
        </div>

        <!-- Signatures -->
        <div class="signatures">
            <div>
                <div class="signature-line">
                    <?php echo htmlspecialchars($student['adviser']); ?><br>
                    <span style="font-size: 11pt;">Adviser/Class Adviser</span>
                </div>
            </div>
            <div>
                <div class="signature-line">
                    <span style="font-size: 11pt;">School Principal</span><br>
                    <span style="font-size: 11pt;">Position/Designation</span>
                </div>
            </div>
        </div>

        <!-- Footer -->
        <div style="margin-top: 50px; font-size: 10pt; text-align: center; color: #666;">
            <p>Not valid without school seal</p>
            <p>Printed on: <?php echo date('F d, Y'); ?></p>
        </div>
    </div>

    <!-- Back Button -->
    <a href="form137.php" class="back-button no-print">
        <i class="fas fa-arrow-left"></i> Back to List
    </a>

    <!-- Print Button -->
    <button onclick="window.print()" class="print-button no-print">
        <i class="fas fa-print"></i> Print Form 137
    </button>
</body>
</html>