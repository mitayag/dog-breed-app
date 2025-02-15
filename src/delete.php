<?php
$conn = new mysqli("db", "root", "password", "dog_breed_db");

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Delete the dog record
$id = $_GET['id'];
if ($conn->query("DELETE FROM dogs WHERE id=$id") === TRUE) {
    header("Location: index.php");
} else {
    echo "Error deleting record: " . $conn->error;
}
?>