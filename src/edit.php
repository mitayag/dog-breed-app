<?php
ob_start();

$conn = new mysqli("db", "root", "password", "dog_breed_db");

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Fetch the dog record for editing
$id = $_GET['id'];
$query = "SELECT * FROM dogs WHERE id=$id";
$result = $conn->query($query);

if (!$result) {
    die("Query failed: " . $conn->error);
}

$row = $result->fetch_assoc();

if (!$row) {
    die("No record found for ID: $id");
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $_POST['name'];
    $breed = $_POST['breed'];
    $image = $_FILES['image']['name'];
    $target = "uploads/" . basename($image);

    if ($image) {
        // Move uploaded file
        if (move_uploaded_file($_FILES['image']['tmp_name'], $target)) {
            // Update with image
            $stmt = $conn->prepare("UPDATE dogs SET name=?, breed=?, image_path=? WHERE id=?");
            $stmt->bind_param("sssi", $name, $breed, $target, $id);
        } else {
            echo "Error uploading file.";
            exit();
        }
    } else {
        // Update without image
        $stmt = $conn->prepare("UPDATE dogs SET name=?, breed=? WHERE id=?");
        $stmt->bind_param("ssi", $name, $breed, $id);
    }

    // Execute the update query
    if ($stmt->execute()) {
        header("Location: index.php");
        exit();
    } else {
        echo "Error updating record: " . $stmt->error;
        exit();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Dog</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container">
        <h1 class="text-center my-4">Edit Dog</h1>
        <form method="POST" enctype="multipart/form-data" class="w-50 mx-auto">
            <div class="mb-3">
                <label for="name" class="form-label">Name</label>
                <input type="text" name="name" id="name" class="form-control" value="<?= htmlspecialchars($row['name']) ?>" required>
            </div>
            <div class="mb-3">
                <label for="breed" class="form-label">Breed</label>
                <input type="text" name="breed" id="breed" class="form-control" value="<?= htmlspecialchars($row['breed']) ?>" required>
            </div>
            <div class="mb-3">
                <label for="image" class="form-label">Image</label>
                <input type="file" name="image" id="image" class="form-control">
            </div>
            <button type="submit" class="btn btn-success">Update</button>
        </form>
    </div>
    <!-- Bootstrap JS (Optional) -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>