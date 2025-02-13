<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Test Form</title>
</head>
<body>
    <form method="POST" action="savegradingscale.php">
        <input type="hidden" name="row_id" value="1">
        <input type="text" name="subject" value="Math">
        <input type="text" name="written" value="40%">
        <input type="text" name="performance_task" value="50%">
        <input type="text" name="quarterly_exams" value="10%">
        <button type="submit">Submit Test Data</button>
    </form>
</body>
</html>
