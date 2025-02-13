<?php 
session_start();

// Check if the user is logged in
if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit();
}
include('../crud/headerpending.php');
// Include the database connection
include_once("../LoginRegisterAuthentication/connection.php");
include_once("functions.php");

// Check if the connection is successful
if (!$connection) {
    die("Connection failed: " . mysqli_connect_error());
}

// Query for users waiting for approval
$pendingUsersQuery = "SELECT userid, name, username FROM user WHERE status = 'pending'";
$pendingUsersResult = mysqli_query($connection, $pendingUsersQuery);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/css/all.min.css" rel="stylesheet">
    <style>
        body {
            font-family: 'Poppins', sans-serif;
            background-color: #f5f5f5;
        }

        .pending-users {
            margin-top: 20px; /* Added more top margin for better spacing */
            width: 90%;
            margin-left: auto;
            margin-right: auto;
        }

        .pending-users h3 {
            margin-bottom: 15px;
            color: #0047ab;
        }

        .pending-users table {
            width: 100%; /* Ensure the table takes full width of the container */
            border-collapse: collapse;
        }

        .pending-users table, .pending-users th, .pending-users td {
            border: 2px solid #ddd;
        }

        .pending-users th, .pending-users td {
            padding: 10px;
            text-align: left;
        }

        .pending-users th {
            background-color: #f4f4f4;
        }

        .btn-approve, .btn-disapprove {
            padding: 5px 10px;
            color: #fff;
            border: none;
            border-radius: 4px;
            text-decoration: none;
            margin: 0 5px;
        }

        .btn-approve {
            background-color: #28a745;
        }

        .btn-approve:hover {
            background-color: #218838;
        }

        .btn-disapprove {
            background-color: #dc3545;
        }

        .btn-disapprove:hover {
            background-color: #c82333;
        }

        /* Flexbox to align the button and title properly */
        .header-container {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 20px; /* Added padding for better spacing */
            background-color: #fff; /* Added background for the header section */
            border-radius: 8px;
            margin-bottom: 20px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1); /* Added shadow for better visual separation */
        }

        .header-container h3 {
            margin: 0;
        }

        .history-button {
            padding: 10px 20px; /* Adjusted padding for the button */
        }

    </style>
</head>
<body>
<div class="pending-users">
        <!-- Header container with button and title -->
        <div class="header-container">
            <h3>Pending User Approvals</h3>
            <a href="admin_history.php" class="btn btn-primary history-button">View Approval/Disapproval History</a>
        </div>

        <!-- Table for pending approvals -->
        <table class="table">
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Username</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
            <?php while ($row = mysqli_fetch_assoc($pendingUsersResult)): ?>
            <tr>
                <td><?php echo htmlspecialchars($row['name']); ?></td>
                <td><?php echo htmlspecialchars($row['username']); ?></td>
                <td>
                    <!-- Button for approval with modal for comments -->
                    <button type="button" class="btn-approve" onclick="showApproveModal(<?php echo htmlspecialchars($row['userid']); ?>)">Approve</button>

                    <!-- Button for disapproval with modal for comments -->
                    <button type="button" class="btn-disapprove" onclick="showDisapproveModal(<?php echo htmlspecialchars($row['userid']); ?>)">Disapprove</button>
                 
                </td>
            </tr>
            <?php endwhile; ?>
        </tbody>
    </table>
</div>


<!-- Approve Modal for Comments -->
<div class="modal fade" id="approveModal" tabindex="-1" aria-labelledby="approveModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <form method="POST" action="admin_approval.php?action=approve&userid=" id="approveForm">
        <div class="modal-header">
          <h5 class="modal-title" id="approveModalLabel">Approve User</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <div class="mb-3">
            <label for="approveComments" class="form-label">Reason for Approval</label>
            <textarea class="form-control" id="approveComments" name="comments" required></textarea>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
          <button type="submit" class="btn btn-success">Approve</button>
        </div>
      </form>
    </div>
  </div>
</div>

<!-- Disapprove Modal for Comments -->
<div class="modal fade" id="disapproveModal" tabindex="-1" aria-labelledby="disapproveModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <form method="POST" action="admin_approval.php?action=disapprove&userid=" id="disapproveForm">
        <div class="modal-header">
          <h5 class="modal-title" id="disapproveModalLabel">Disapprove User</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <div class="mb-3">
            <label for="disapproveComments" class="form-label">Reason for Disapproval</label>
            <textarea class="form-control" id="disapproveComments" name="comments" required></textarea>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
          <button type="submit" class="btn btn-danger">Disapprove</button>
        </div>
      </form>
    </div>
  </div>
</div>

<!-- Bootstrap JS for modal functionality -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<!-- SweetAlert -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@10"></script>

<!-- JavaScript to handle the approve and disapprove modals and form actions -->
<script>
  function showApproveModal(userid) {
    document.getElementById('approveForm').action = 'admin_approval.php?action=approve&userid=' + userid;
    var myModal = new bootstrap.Modal(document.getElementById('approveModal'));
    myModal.show();
  }

  function showDisapproveModal(userid) {
    document.getElementById('disapproveForm').action = 'admin_approval.php?action=disapprove&userid=' + userid;
    var myModal = new bootstrap.Modal(document.getElementById('disapproveModal'));
    myModal.show();
  }

  const urlParams = new URLSearchParams(window.location.search);
  const status = urlParams.get('status');
  const action = urlParams.get('action');

  if (status === 'success') {
    Swal.fire({
      icon: 'success',
      title: action === 'approve' ? 'User Approved!' : 'User Disapproved!',
      showConfirmButton: false,
      timer: 2000
    });
  } else if (status === 'failure') {
    Swal.fire({
      icon: 'error',
      title: 'Error!',
      text: 'There was an error processing the request.',
      showConfirmButton: false,
      timer: 2000
    });
  }
</script>
</body>
</html
