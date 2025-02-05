<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Form 137 - Learner's Permanent Record</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            padding: 20px;
            margin: 0;
        }
        .container {
            max-width: 1000px;
            margin: 0 auto;
            background: white;
            padding: 30px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }
        h1, h2, h3 {
            text-align: center;
            margin-bottom: 20px;
        }
        .form-container {
            width: 100%;
            margin-bottom: 20px;
        }
        .form-container td {
            padding: 8px;
            border: 1px solid #ddd;
        }
        .form-container td:first-child {
            font-weight: bold;
            width: 40%;
        }
        .signature-section {
            text-align: center;
            margin-top: 50px;
        }
        .print-btn {
            background-color: #4CAF50;
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            margin: 20px auto;
            display: block;
        }
        @media print {
            .print-btn {
                display: none;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Learner's Permanent Record</h1>
        <h3>(Formerly Form 137)</h3>

        <h2>Learner's Information</h2>
        <table class="form-container">
            <tr>
                <td>LAST NAME</td>
                <td><?php echo htmlspecialchars($this->learnerData['last_name']); ?></td>
            </tr>
            <tr>
                <td>FIRST NAME</td>
                <td><?php echo htmlspecialchars($this->learnerData['first_name']); ?></td>
            </tr>
            <!-- Add other learner information fields -->
        </table>

        <h2>Scholastic Record</h2>
        <table class="form-container">
            <thead>
                <tr>
                    <th rowspan="2">Learning Areas</th>
                    <th colspan="4">Quarterly Rating</th>
                    <th rowspan="2">Final Rating</th>
                    <th rowspan="2">Remarks</th>
                </tr>
                <tr>
                    <th>1</th>
                    <th>2</th>
                    <th>3</th>
                    <th>4</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $grades = $this->fetchGrades();
                while ($grade = $grades->fetch_assoc()) {
                    ?>
                    <tr>
                        <td><?php echo htmlspecialchars($grade['subject']); ?></td>
                        <td><?php echo htmlspecialchars($grade['quarter1']); ?></td>
                        <td><?php echo htmlspecialchars($grade['quarter2']); ?></td>
                        <td><?php echo htmlspecialchars($grade['quarter3']); ?></td>
                        <td><?php echo htmlspecialchars($grade['quarter4']); ?></td>
                        <td><?php echo htmlspecialchars($grade['final_rating']); ?></td>
                        <td><?php echo htmlspecialchars($grade['remarks']); ?></td>
                    </tr>
                    <?php
                }
                ?>
            </tbody>
        </table>

        <div class="signature-section">
            <p>____________________</p>
            <p><?php echo htmlspecialchars($this->learnerData['adviser']); ?></p>
            <p>Adviser</p>
            <p>School Year: <?php echo htmlspecialchars($this->learnerData['school_year']); ?></p>
            
            <div class="certification-section">
                <h3>CERTIFICATION</h3>
                <p class="certification-text">
                    I hereby certify that this is a true record of 
                    <?php echo htmlspecialchars($this->learnerData['first_name'] . ' ' . 
                          $this->learnerData['middle_name'] . ' ' . 
                          $this->learnerData['last_name']); ?>
                    and that he/she is eligible for admission to Grade _____.
                </p>

                <div class="signature-lines">
                    <div class="signature-block">
                        <div class="signature-line"></div>
                        <p><?php echo htmlspecialchars($this->learnerData['signature']); ?></p>
                        <p class="signatory-title">Division Records Officer</p>
                    </div>

                    <div class="signature-block">
                        <div class="signature-line"></div>
                        <p>Date</p>
                    </div>
                </div>

                <div class="school-details">
                    <p>School: <?php echo htmlspecialchars($this->learnerData['school']); ?></p>
                    <p>School ID: <?php echo htmlspecialchars($this->learnerData['school_id']); ?></p>
                    <p>Address: <?php echo htmlspecialchars($this->learnerData['school_address']); ?></p>
                    <p>Division: <?php echo htmlspecialchars($this->learnerData['division']); ?></p>
                    <p>Region: <?php echo htmlspecialchars($this->learnerData['region']); ?></p>
                </div>
            </div>

            <div class="eligibility-section">
                <h3>ELIGIBILITY FOR ADMISSION TO SECONDARY SCHOOL</h3>
                <table class="eligibility-table">
                    <tr>
                        <td>
                            <input type="checkbox" <?php echo $this->learnerData['elementary_completer'] ? 'checked' : ''; ?> disabled>
                            Elementary School Completer
                        </td>
                        <td>General Average: <?php echo htmlspecialchars($this->learnerData['general_average']); ?></td>
                    </tr>
                    <tr>
                        <td colspan="2">
                            Citations (if any): <?php echo htmlspecialchars($this->learnerData['citation']); ?>
                        </td>
                    </tr>
                    <tr>
                        <td>
                            <input type="checkbox" <?php echo $this->learnerData['pept_passer'] ? 'checked' : ''; ?> disabled>
                            PEPT Passer
                        </td>
                        <td>Rating: <?php echo htmlspecialchars($this->learnerData['pept_rating']); ?></td>
                    </tr>
                    <tr>
                        <td>
                            <input type="checkbox" <?php echo $this->learnerData['als_a_e_passer'] ? 'checked' : ''; ?> disabled>
                            ALS A&E Passer
                        </td>
                        <td>Rating: <?php echo htmlspecialchars($this->learnerData['als_rating']); ?></td>
                    </tr>
                    <tr>
                        <td>Others: <?php echo htmlspecialchars($this->learnerData['others_specify']); ?></td>
                        <td>Date of Examination/Assessment: <?php echo htmlspecialchars($this->learnerData['exam_date']); ?></td>
                    </tr>
                    <tr>
                        <td colspan="2">
                            Name and Address of Testing Center: <?php echo htmlspecialchars($this->learnerData['testing_center']); ?>
                        </td>
                    </tr>
                </table>
            </div>

            <div class="remarks-section">
                <h3>REMARKS / RECOMMENDATIONS</h3>
                <div class="remarks-box">
                    <!-- Add any specific remarks or leave blank for manual entry -->
                </div>
            </div>
        </div>

        <button class="print-btn" onclick="window.print()">Print Form 137</button>
        <a href="student_records.php" class="back-btn">Back to Records</a>
    </div>

    <style>
        /* Additional styles specific to Form 137 */
        .certification-section {
            margin-top: 40px;
            text-align: left;
            padding: 20px;
        }

        .certification-text {
            margin: 20px 0;
            line-height: 1.6;
            text-align: justify;
        }

        .signature-lines {
            display: flex;
            justify-content: space-between;
            margin: 40px 0;
        }

        .signature-block {
            text-align: center;
            width: 45%;
        }

        .signature-line {
            border-bottom: 1px solid #000;
            width: 100%;
            margin-bottom: 5px;
        }

        .signatory-title {
            font-style: italic;
            margin-top: 5px;
        }

        .school-details {
            margin: 30px 0;
        }

        .school-details p {
            margin: 5px 0;
        }

        .eligibility-table {
            width: 100%;
            border-collapse: collapse;
            margin: 20px 0;
        }

        .eligibility-table td {
            padding: 10px;
            border: 1px solid #ddd;
        }

        .remarks-section {
            margin: 30px 0;
        }

        .remarks-box {
            border: 1px solid #ddd;
            min-height: 100px;
            padding: 10px;
            margin-top: 10px;
        }

        .back-btn {
            display: inline-block;
            padding: 10px 20px;
            background-color: #6c757d;
            color: white;
            text-decoration: none;
            border-radius: 5px;
            margin-top: 20px;
        }

        .back-btn:hover {
            background-color: #5a6268;
        }

        @media print {
            .back-btn {
                display: none;
            }
            
            body {
                padding: 0;
                margin: 0;
            }

            .container {
                box-shadow: none;
            }

            .remarks-box {
                min-height: 200px;
            }
        }

        @page {
            size: portrait;
            margin: 0.5in;
        }
    </style>
</body>
</html>