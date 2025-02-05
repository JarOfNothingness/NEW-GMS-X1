<?php
session_start(); // Start the session at the very top

if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit();
}
?>

<?php include('headeradmin.php'); ?>
<?php include("../LoginRegisterAuthentication/connection.php"); ?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

<body>
<style>
    /* Style for tables */
table.table-bordered {
    width: 100%;
    border-collapse: collapse;
    margin: 25px 0;
    font-size: 18px;
    text-align: left;
    box-shadow: 0 0 15px rgba(0, 0, 0, 0.1);
}

/* Styling table headers */
table.table-bordered thead th {
    background-color: #343a40;
    color: white;
    font-weight: bold;
    font-size: 20px;
    text-transform: uppercase;
    padding: 12px 15px;
    border: 1px solid #dddddd;
    text-align: center;
}

/* Table body styling */
table.table-bordered tbody td {
    padding: 10px 15px;
    border: 1px solid #dddddd;
    text-align: center;
}

/* Alternating row colors */
table.table-bordered tbody tr:nth-child(even) {
    background-color: #f9f9f9;
}

table.table-bordered tbody tr:hover {
    background-color: #f1f1f1;
}

/* Increase the space between two tables */
h4 {
    margin-top: 40px;
    margin-bottom: 15px;
}

/* Margin between tables */
table + h4 {
    margin-top: 50px;
}

</style>

    <h4>Grading Scale for Junior High School (JHS)</h4>
    <form method="POST" action="savegradingscale.php">
    <table class="table-Scale table-bordered">
        <thead>
            <tr>
                <th>Subject</th>
                <th>Written</th>
                <th>Performance Task</th>
                 <th>Quarterly Exams</th>
                 <th>Action</th>
            </tr>
        </thead>
        <tbody id="gradingTableBody">

        <tr>
        <td contenteditable="true" data-id="1" data-column="subject">English</td>
        <td contenteditable="true" data-id="1" data-column="written">30%</td>
        <td contenteditable="true" data-id="1" data-column="performance_task">50%</td>
        <td contenteditable="true" data-id="1" data-column="quarterly_exams">20%</td>
        <td><button class="save-btn" data-id="1">Save</button></td>
    </tr>
    <tr>
        <td contenteditable="true" data-id="2" data-column="subject">Math</td>
        <td contenteditable="true" data-id="2" data-column="written">40%</td>
        <td contenteditable="true" data-id="2" data-column="performance_task">40%</td>
        <td contenteditable="true" data-id="2" data-column="quarterly_exams">20%</td>
        <td><button class="save-btn" data-id="2">Save</button></td>
    </tr>
         



    <script>
document.addEventListener('DOMContentLoaded', () => {
    // Save when cell loses focus (use fetch to save content directly)
    document.querySelectorAll('[contenteditable]').forEach(cell => {
        cell.addEventListener('blur', () => {
            let id = cell.getAttribute('data-id'); // row ID from the cell
            let column = cell.getAttribute('data-column'); // column name from the cell
            let value = cell.textContent; // cell value

            fetch('update_cell.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({
                    id: id,
                    column: column,
                    value: value
                })
            }).then(response => response.text())
              .then(result => console.log(result))
              .catch(error => console.error('Error:', error));
        });
    });

    // Save the entire row when the save button is clicked
    document.querySelectorAll('.save-btn').forEach(button => {
        button.addEventListener('click', () => {
            let row = button.parentElement.parentElement;
            let id = button.getAttribute('data-id'); // get the row ID
            let data = {};

            // Collect all contenteditable data from the row
            row.querySelectorAll('[contenteditable]').forEach(cell => {
                let column = cell.getAttribute('data-column'); // the column to be updated
                data[column] = cell.textContent;
            });

            // Send all the updated data for that row via fetch
            fetch('update_cell.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({
                    id: id,
                    ...data
                })
            }).then(response => response.text())
              .then(result => console.log(result))
              .catch(error => console.error('Error:', error));
        });
    });
});


</script>


    
</body>
</html>
<?php include('../crud/footer.php'); ?>