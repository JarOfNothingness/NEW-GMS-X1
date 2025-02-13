<?php
session_start();
if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit();
}

require_once("../LoginRegisterAuthentication/connection.php");

class Form137 {
    private $connection;
    private $learner_id;
    private $learnerData;

    public function __construct($connection, $learner_id) {
        $this->connection = $connection;
        $this->learner_id = $learner_id;
        $this->fetchLearnerData();
    }

    private function fetchLearnerData() {
        $query = "SELECT * FROM encoded_learner_data WHERE learner_id = ?";
        $stmt = $this->connection->prepare($query);
        $stmt->bind_param("i", $this->learner_id);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $this->learnerData = $result->fetch_assoc();
        } else {
            die("Learner not found.");
        }
    }

    public function fetchGrades() {
        $query = "SELECT * FROM student_grades WHERE learner_id = ?";
        $stmt = $this->connection->prepare($query);
        $stmt->bind_param("i", $this->learner_id);
        $stmt->execute();
        return $stmt->get_result();
    }

    public function getLearnerData() {
        return $this->learnerData;
    }

    public function render() {
        include('form137_template.php');
    }
}

// Initialize form
$learner_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
$form137 = new Form137($connection, $learner_id);
$form137->render();
?>
