<?php
session_start();

if (!isset($_SESSION['userid'])) {
    header("Location: login.php");
    exit();
}

include("../LoginRegisterAuthentication/connection.php");
include("../crud/header.php");

$userid = $_SESSION['userid'];

// Updated query to properly fetch approval status and action logs
$query = "
    SELECT 
        asm.id,
        asm.approval_status,
        asm.updated_at,
        s.learners_name,
        subj.name AS subject_name,
        asm.quarter,
        asm.quarterly_grade,
        DATE(asm.created_at) AS request_date,
        CASE 
            WHEN asm.approval_status = 'pending' THEN 1
            WHEN asm.approval_status = 'approve' THEN 2
            WHEN asm.approval_status = 'reject' THEN 3
        END as status_order,
        gal.action as latest_action,
        gal.approval_date as action_date
    FROM assessment_summary asm
    JOIN students s ON asm.student_id = s.id
    JOIN subjects subj ON asm.subject_id = subj.id
    LEFT JOIN (
        SELECT 
            student_id, 
            subject_id, 
            quarter,
            action,
            approval_date,
            ROW_NUMBER() OVER (PARTITION BY student_id, subject_id, quarter ORDER BY approval_date DESC) as rn
        FROM grade_approval_log
    ) gal ON asm.student_id = gal.student_id 
        AND asm.subject_id = gal.subject_id 
        AND asm.quarter = gal.quarter 
        AND gal.rn = 1
    WHERE asm.user_id = ? 
    ORDER BY 
        status_order ASC,
        asm.updated_at DESC,
        asm.created_at DESC
";

$stmt = mysqli_prepare($connection, $query);
mysqli_stmt_bind_param($stmt, "i", $userid);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

$requests = [];
while ($row = mysqli_fetch_assoc($result)) {
    $requests[] = $row;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Record Request</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/css/all.min.css" rel="stylesheet">
    <style>
        .record-card {
            background-color: #f8f9fa;
            padding: 20px;
            border-radius: 8px;
            margin-bottom: 20px;
            border-left: 4px solid #4a69bd;
            transition: transform 0.2s;
            position: relative;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }

        .record-card:hover {
            transform: translateX(5px);
        }

        .grade-info {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-top: 10px;
            padding-top: 10px;
            border-top: 1px solid #dee2e6;
        }

        .grade-box {
            background-color: #e9ecef;
            padding: 8px 15px;
            border-radius: 5px;
            font-weight: bold;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .empty-state {
            text-align: center;
            padding: 40px;
            color: #6c757d;
        }

        .back-button {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            text-decoration: none;
            color: #4a69bd;
            margin-bottom: 1rem;
            font-weight: 500;
            padding: 8px 16px;
            border-radius: 4px;
            background-color: #f8f9fa;
            transition: all 0.2s;
        }

        .back-button:hover {
            color: #334c8c;
            background-color: #e9ecef;
        }

        .header {
            margin-bottom: 2rem;
            padding: 1rem 0;
            border-bottom: 1px solid #dee2e6;
        }

        .header h1 {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            color: #2c3e50;
            font-size: 1.75rem;
            margin: 0;
        }

        /* Enhanced status badge styles */
        .status-badge {
            display: inline-block;
            padding: 8px 16px;
            border-radius: 20px;
            font-weight: 600;
            text-transform: uppercase;
            font-size: 0.75rem;
            letter-spacing: 0.5px;
            margin: 0 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }

        .status-approve, .status-approved {
            background-color: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }

        .status-reject, .status-rejected {
            background-color: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }

        .status-pending {
            background-color: #fff3cd;
            color: #856404;
            border: 1px solid #ffeeba;
        }

        .status-info {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 1rem;
            margin-top: 1rem;
            padding-top: 1rem;
            border-top: 1px solid #dee2e6;
        }

        .timestamp-group {
            display: flex;
            flex-direction: column;
            gap: 0.5rem;
        }

        .timestamp {
            font-size: 0.813rem;
            color: #6c757d;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .action-info {
            font-weight: 500;
            color: #2c3e50;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .action-info i {
            font-size: 1rem;
        }

        .status-indicator {
            position: absolute;
            top: 1rem;
            right: 1rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
            background-color: white;
            padding: 8px 16px;
            border-radius: 20px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }

        .status-dot {
            width: 10px;
            height: 10px;
            border-radius: 50%;
            display: inline-block;
        }

        .status-dot.pending { 
            background-color: #ffc107;
            box-shadow: 0 0 0 2px rgba(255, 193, 7, 0.2);
        }
        .status-dot.approve, .status-dot.approved { 
            background-color: #28a745;
            box-shadow: 0 0 0 2px rgba(40, 167, 69, 0.2);
        }
        .status-dot.reject, .status-dot.rejected { 
            background-color: #dc3545;
            box-shadow: 0 0 0 2px rgba(220, 53, 69, 0.2);
        }

        @media (max-width: 768px) {
            .grade-info, .status-info {
                flex-direction: column;
                align-items: flex-start;
            }

            .status-indicator {
                position: static;
                margin-bottom: 1rem;
            }

            .record-card {
                padding: 15px;
            }
        }

        /* Animation styles */
        @keyframes fadeInGreen {
            from { background-color: rgba(40, 167, 69, 0.1); }
            to { background-color: #f8f9fa; }
        }

        @keyframes fadeInRed {
            from { background-color: rgba(220, 53, 69, 0.1); }
            to { background-color: #f8f9fa; }
        }
    </style>
</head>
<body>
    <div class="container">
        <a href="newdashboard.php" class="back-button">
            <i class="fas fa-arrow-left"></i> Back to Dashboard
        </a>

        <div class="header">
            <h1><i class="fas fa-file-alt"></i> Grade Assessment Records</h1>
        </div>

        <div class="content">
            <div class="record-section">
                <?php if (empty($requests)): ?>
                    <div class="empty-state">
                        <i class="fas fa-folder-open fa-3x mb-3"></i>
                        <h3>No Grade Records Found</h3>
                        <p>There are currently no grade assessment records to display.</p>
                    </div>
                <?php else: ?>
                    <?php foreach ($requests as $request): ?>
                        <div class="record-card">
                            <div class="status-indicator">
                                <span class="status-dot <?php echo strtolower($request['approval_status']); ?>"></span>
                                <span class="status-badge status-<?php echo strtolower($request['approval_status']); ?>">
                                    <?php 
                                    $status = $request['approval_status'];
                                    if ($status === 'approve') {
                                        echo 'Approved';
                                    } elseif ($status === 'reject') {
                                        echo 'Rejected';
                                    } else {
                                        echo ucfirst($status);
                                    }
                                    ?>
                                </span>
                            </div>

                            <h3><?php echo htmlspecialchars($request['subject_name']); ?> - <?php echo htmlspecialchars($request['quarter']); ?> Quarter</h3>
                            <p><strong>Student:</strong> <?php echo htmlspecialchars($request['learners_name']); ?></p>
                            <p><strong>Date Submitted:</strong> <?php echo date('F d, Y', strtotime($request['request_date'])); ?></p>
                            
                            <div class="grade-info">
                                <div class="grade-box">
                                    <i class="fas fa-chart-line"></i>
                                    Grade: <?php echo number_format($request['quarterly_grade'], 2); ?>
                                </div>
                            </div>

                            <div class="status-info">
                                <div class="timestamp-group">
                                    <?php if ($request['latest_action']): ?>
                                        <div class="action-info">
                                            <i class="fas <?php echo $request['latest_action'] === 'approve' ? 'fa-check-circle' : 'fa-times-circle'; ?>"></i>
                                            <span>
                                                <?php echo ucfirst($request['latest_action']); ?>d on 
                                                <?php echo date('F d, Y h:i A', strtotime($request['action_date'])); ?>
                                            </span>
                                        </div>
                                    <?php endif; ?>
                                    
                                    <?php if ($request['updated_at']): ?>
                                        <div class="timestamp">
                                            <i class="fas fa-clock"></i>
                                            <span>
                                                Last Updated: <?php echo date('F d, Y h:i A', strtotime($request['updated_at'])); ?>
                                            </span>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const cards = document.querySelectorAll('.record-card');
            cards.forEach(card => {
                const status = card.querySelector('.status-badge');
                if (status.classList.contains('status-approved')) {
                    card.style.animation = 'fadeInGreen 0.5s ease-in-out';
                } else if (status.classList.contains('status-rejected')) {
                    card.style.animation = 'fadeInRed 0.5s ease-in-out';
                }
            });
        });
    </script>
</body>
</html>