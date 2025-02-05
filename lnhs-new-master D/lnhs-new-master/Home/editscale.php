<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Grading Scale</title>
</head>
<body>
<h4>Edit Grading Scale</h4>
<form method="POST" action="savegradingscale.php">
    <input type="hidden" name="row_id" value="<?php echo htmlspecialchars($row_id); ?>">
    <table class="table-Scale table-bordered">
        <thead>
            <tr>
                <th>Subject</th>
                <th>Written</th>
                <th>Performance Task</th>
                <th>Quarterly Exams</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td><input type="text" name="subject" value="<?php echo htmlspecialchars($data['subject']); ?>"></td>
                <td><input type="text" name="written" value="<?php echo htmlspecialchars($data['written']); ?>"></td>
                <td><input type="text" name="performance_task" value="<?php echo htmlspecialchars($data['performance_task']); ?>"></td>
                <td><input type="text" name="quarterly_exams" value="<?php echo htmlspecialchars($data['quarterly_exams']); ?>"></td>
            </tr>
        </tbody>
    </table>
    <button type="submit">Save Changes</button>
</form> 

</body>
</html>
