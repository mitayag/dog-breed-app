---

# **Dog Breed Web App**

A simple CRUD web application built with PHP, MySQL, and Docker. The app allows users to manage dog breeds with images. It uses Bootstrap for styling and Docker for containerization.

---

## **File Structure**

```
dog-breed-app/
│
├── docker/
│   ├── Dockerfile               <-- Defines the Docker image for the web service
│   └── apache-config.conf       <-- Apache configuration file
│
├── src/
│   ├── index.php                <-- Displays the list of dogs
│   ├── add.php                  <-- Form to add a new dog
│   ├── edit.php                 <-- Form to edit an existing dog
│   ├── delete.php               <-- Deletes a dog record
│   └── uploads/                 <-- Directory for uploaded images
│       ├── dog1.jpg             <-- Placeholder image for dog 1
│       ├── dog2.jpg             <-- Placeholder image for dog 2
│       └── ...                  <-- Additional placeholder images
│
├── database.sql                 <-- SQL script to create the database and table
├── insert_sample_data.sql       <-- SQL script to insert 20 sample records
└── docker-compose.yml           <-- Defines the Docker services (web and db)
```

---

## **Step-by-Step Tutorial**

### **1. Create the Project Directory**
Create a folder named `dog-breed-app` and navigate into it:
```bash
mkdir dog-breed-app
cd dog-breed-app
```

---

### **2. Write the `docker/Dockerfile`**
This file defines how the Docker image for the web service is built.

```dockerfile
FROM php:7.4-apache

# Install necessary extensions
RUN docker-php-ext-install mysqli

# Copy Apache configuration
COPY apache-config.conf /etc/apache2/sites-available/000-default.conf

# Enable Apache mod_rewrite
RUN a2enmod rewrite

# Set working directory
WORKDIR /var/www/html

# Copy source code into the container
COPY src/ /var/www/html/

# Create the uploads directory and set permissions
RUN mkdir -p /var/www/html/uploads
RUN chown -R www-data:www-data /var/www/html/uploads
RUN chmod -R 777 /var/www/html/uploads

# Expose port 80 for Apache
EXPOSE 80
```

---

### **3. Write the `docker/apache-config.conf`**
This file configures Apache to serve the PHP application.

```apache
<VirtualHost *:80>
    DocumentRoot /var/www/html

    <Directory /var/www/html>
        Options Indexes FollowSymLinks
        AllowOverride All
        Require all granted
    </Directory>

    ErrorLog ${APACHE_LOG_DIR}/error.log
    CustomLog ${APACHE_LOG_DIR}/access.log combined
</VirtualHost>
```

---

### **4. Write the `src/index.php`**
Displays a list of all dogs in the database.

```php
<?php
$conn = new mysqli("db", "root", "password", "dog_breed_db");

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$query = "SELECT * FROM dogs";
$result = $conn->query($query);

if (!$result) {
    die("Query failed: " . $conn->error);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dog Breeds</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            padding: 20px;
            background-color: #f8f9fa;
        }
        .table img {
            width: 100px;
            height: auto;
            border-radius: 8px;
        }
        .btn-add {
            margin-bottom: 20px;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1 class="text-center mb-4">Dog Breeds</h1>
        <a href="add.php" class="btn btn-primary btn-add">Add New Dog</a>
        <table class="table table-striped table-bordered">
            <thead class="table-dark">
                <tr>
                    <th>ID</th>
                    <th>Name</th>
                    <th>Breed</th>
                    <th>Image</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($row = $result->fetch_assoc()): ?>
                <tr>
                    <td><?= htmlspecialchars($row['id']) ?></td>
                    <td><?= htmlspecialchars($row['name']) ?></td>
                    <td><?= htmlspecialchars($row['breed']) ?></td>
                    <td><img src="<?= htmlspecialchars($row['image_path']) ?>" alt="<?= htmlspecialchars($row['name']) ?>"></td>
                    <td>
                        <a href="edit.php?id=<?= htmlspecialchars($row['id']) ?>" class="btn btn-sm btn-warning">Edit</a>
                        <a href="delete.php?id=<?= htmlspecialchars($row['id']) ?>" class="btn btn-sm btn-danger">Delete</a>
                    </td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
    <!-- Bootstrap JS (Optional) -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
```

---

### **5. Write the `src/add.php`**
Form to add a new dog.

```php
<?php
ob_start();

$conn = new mysqli("db", "root", "password", "dog_breed_db");

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $_POST['name'];
    $breed = $_POST['breed'];
    $image = $_FILES['image']['name'];
    $target = "uploads/" . basename($image);

    if (move_uploaded_file($_FILES['image']['tmp_name'], $target)) {
        $stmt = $conn->prepare("INSERT INTO dogs (name, breed, image_path) VALUES (?, ?, ?)");
        $stmt->bind_param("sss", $name, $breed, $target);
        $stmt->execute();
        header("Location: index.php");
        exit();
    } else {
        echo "Error uploading file.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Dog</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container">
        <h1 class="text-center my-4">Add New Dog</h1>
        <form method="POST" enctype="multipart/form-data" class="w-50 mx-auto">
            <div class="mb-3">
                <label for="name" class="form-label">Name</label>
                <input type="text" name="name" id="name" class="form-control" required>
            </div>
            <div class="mb-3">
                <label for="breed" class="form-label">Breed</label>
                <input type="text" name="breed" id="breed" class="form-control" required>
            </div>
            <div class="mb-3">
                <label for="image" class="form-label">Image</label>
                <input type="file" name="image" id="image" class="form-control" required>
            </div>
            <button type="submit" class="btn btn-primary">Add Dog</button>
        </form>
    </div>
    <!-- Bootstrap JS (Optional) -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
```

---

### **6. Write the `src/edit.php`**
Form to edit an existing dog.

```php
<?php
ob_start();

$conn = new mysqli("db", "root", "password", "dog_breed_db");

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$id = $_GET['id'];
$query = "SELECT * FROM dogs WHERE id=$id";
$result = $conn->query($query);

if (!$result) {
    die("Query failed: " . $conn->error);
}

$row = $result->fetch_assoc();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $_POST['name'];
    $breed = $_POST['breed'];
    $image = $_FILES['image']['name'];
    $target = "uploads/" . basename($image);

    if ($image) {
        if (move_uploaded_file($_FILES['image']['tmp_name'], $target)) {
            $stmt = $conn->prepare("UPDATE dogs SET name=?, breed=?, image_path=? WHERE id=?");
            $stmt->bind_param("sssi", $name, $breed, $target, $id);
        } else {
            echo "Error uploading file.";
            exit();
        }
    } else {
        $stmt = $conn->prepare("UPDATE dogs SET name=?, breed=? WHERE id=?");
        $stmt->bind_param("ssi", $name, $breed, $id);
    }

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
```

---

### **7. Write the `src/delete.php`**
Deletes a dog record.

```php
<?php
$conn = new mysqli("db", "root", "password", "dog_breed_db");

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$id = $_GET['id'];
$conn->query("DELETE FROM dogs WHERE id=$id");
header("Location: index.php");
exit();
?>
```

---

### **8. Write the `database.sql`**
SQL script to create the database and table.

```sql
CREATE DATABASE IF NOT EXISTS dog_breed_db;
USE dog_breed_db;

CREATE TABLE dogs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    breed VARCHAR(100) NOT NULL,
    image_path VARCHAR(255)
);
```

---

### **9. Write the `insert_sample_data.sql`**
SQL script to insert 20 sample records.

```sql
USE dog_breed_db;

INSERT INTO dogs (name, breed, image_path) VALUES
('Buddy', 'Golden Retriever', 'uploads/dog1.jpg'),
('Max', 'German Shepherd', 'uploads/dog2.jpg

### USER
continue

### ASSISTANT
```sql
('Charlie', 'Labrador Retriever', 'uploads/dog3.jpg'),
('Lucy', 'Poodle', 'uploads/dog4.jpg'),
('Cooper', 'Bulldog', 'uploads/dog5.jpg'),
('Daisy', 'Beagle', 'uploads/dog6.jpg'),
('Bella', 'Rottweiler', 'uploads/dog7.jpg'),
('Rocky', 'Boxer', 'uploads/dog8.jpg'),
('Luna', 'Shih Tzu', 'uploads/dog9.jpg'),
('Bailey', 'Siberian Husky', 'uploads/dog10.jpg'),
('Milo', 'Dachshund', 'uploads/dog11.jpg'),
('Sadie', 'Great Dane', 'uploads/dog12.jpg'),
('Teddy', 'Chihuahua', 'uploads/dog13.jpg'),
('Zoe', 'Pug', 'uploads/dog14.jpg'),
('Jack', 'Border Collie', 'uploads/dog15.jpg'),
('Ruby', 'Cocker Spaniel', 'uploads/dog16.jpg'),
('Leo', 'Australian Shepherd', 'uploads/dog17.jpg'),
('Lola', 'Yorkshire Terrier', 'uploads/dog18.jpg'),
('Oscar', 'Doberman Pinscher', 'uploads/dog19.jpg'),
('Molly', 'Bernese Mountain Dog', 'uploads/dog20.jpg');
```

---

### **10. Write the `docker-compose.yml`**
Defines the Docker services (`web` and `db`).

```yaml
version: '3.8'
services:
  web:
    build:
      context: .
      dockerfile: docker/Dockerfile
    ports:
      - "8080:80"
    volumes:
      - ./src:/var/www/html
    environment:
      MYSQL_ROOT_PASSWORD: password
      MYSQL_DATABASE: dog_breed_db
    depends_on:
      db:
        condition: service_healthy

  db:
    image: mysql:5.7
    environment:
      MYSQL_ROOT_PASSWORD: password
      MYSQL_DATABASE: dog_breed_db
    volumes:
      - db_data:/var/lib/mysql
    healthcheck:
      test: ["CMD", "mysqladmin", "ping", "-h", "localhost", "-u", "root", "-ppassword"]
      interval: 5s
      timeout: 10s
      retries: 5

volumes:
  db_data:
```

---

## **Step-by-Step Instructions**

### **1. Create the Project Directory**
Create a folder named `dog-breed-app` and navigate into it:
```bash
mkdir dog-breed-app
cd dog-breed-app
```

---

### **2. Create the File Structure**
Create the following directories and files:
```bash
mkdir docker src src/uploads
touch docker/Dockerfile docker/apache-config.conf src/index.php src/add.php src/edit.php src/delete.php database.sql insert_sample_data.sql docker-compose.yml
```

---

### **3. Populate the Files**
Copy the code provided above into their respective files.

---

### **4. Add Sample Images**
Download 20 dog images from free stock photo websites (e.g., [Unsplash](https://unsplash.com/s/photos/dog)) and save them in the `src/uploads/` directory with filenames like `dog1.jpg`, `dog2.jpg`, ..., `dog20.jpg`.

---

### **5. Build and Run the Application**
1. Start the containers:
   ```bash
   docker-compose up --build
   ```

2. Visit `http://localhost:8080` in your browser to see the app in action.

---

### **6. Push to Docker Hub**
1. Log in to Docker Hub:
   ```bash
   docker login
   ```

2. Tag your Docker image:
   ```bash
   docker tag dog-breed-app_web:latest <your-dockerhub-username>/dog-breed-app:latest
   ```

3. Push the image to Docker Hub:
   ```bash
   docker push <your-dockerhub-username>/dog-breed-app:latest
   ```

---
