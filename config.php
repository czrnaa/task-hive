<!-- NOTES
- databse connection
- this is a shared file -->

<?php
$host = "localhost";
$user = "root";
$pass = "";
$dbname = "task_hive_db"; // project db name

// connection
$conn = new mysqli($host, $user, $pass, $dbname);

// check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>