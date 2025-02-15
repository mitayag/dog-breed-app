# **Walkthrough: Building the Dog Breed Web App**

This walkthrough will guide you step-by-step through creating a **Dog Breed Web App** using PHP, MySQL, Docker, and Bootstrap. The app allows users to manage dog breeds with images, including adding, editing, deleting, and viewing records. By the end of this tutorial, you’ll have a fully functional CRUD application running in Docker containers.

---

## **Overview**
1. **What You’ll Build**: A web app where users can:
   - View a list of dogs with their names, breeds, and images.
   - Add new dogs.
   - Edit existing dog records.
   - Delete dogs from the database.
2. **Technologies Used**:
   - **PHP**: Backend logic for handling CRUD operations.
   - **MySQL**: Database to store dog information.
   - **Docker**: Containerization for easy deployment.
   - **Bootstrap**: Styling for a clean and responsive UI.

---

## **Step 1: Set Up the Project Directory**
1. **Create the Project Folder**:
   ```bash
   mkdir dog-breed-app
   cd dog-breed-app
   ```

2. **Organize the File Structure**:
   Create the following directories and files:
   ```bash
   mkdir docker src src/uploads
   touch docker/Dockerfile docker/apache-config.conf src/index.php src/add.php src/edit.php src/delete.php database.sql insert_sample_data.sql docker-compose.yml
   ```

---

## **Step 2: Write the Docker Configuration**

### **1. `docker/Dockerfile`**
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

**Explanation**:
- Installs the `mysqli` extension for PHP to connect to MySQL.
- Copies an Apache configuration file to serve the app.
- Creates an `uploads/` directory for storing images and sets proper permissions.

---

### **2. `docker/apache-config.conf`**
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

**Explanation**:
- Sets the document root to `/var/www/html`.
- Allows `.htaccess` overrides for URL rewriting.

---

### **3. `docker-compose.yml`**
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

**Explanation**:
- The `web` service builds the PHP app using the `Dockerfile`.
- The `db` service uses the official MySQL 5.7 image.
- A health check ensures the database is ready before starting the web service.

---

## **Step 3: Write the PHP Code**

### **1. `src/index.php`**
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

**Explanation**:
- Connects to the MySQL database and fetches all dog records.
- Displays the data in a styled table using Bootstrap.

---

### **2. `src/add.php`, `src/edit.php`, and `src/delete.php`**
These files handle adding, editing, and deleting dog records. Refer to the earlier steps for their implementation.

---

### **3. `database.sql`**
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

**Explanation**:
- Creates a `dogs` table with columns for ID, name, breed, and image path.

---

### **4. `insert_sample_data.sql`**
SQL script to insert 20 sample records.

```sql
USE dog_breed_db;

INSERT INTO dogs (name, breed, image_path) VALUES
('Buddy', 'Golden Retriever', 'uploads/dog1.jpg'),
('Max', 'German Shepherd', 'uploads/dog2.jpg'),
...
('Molly', 'Bernese Mountain Dog', 'uploads/dog20.jpg');
```

**Explanation**:
- Inserts 20 sample dog records into the `dogs` table.

---

## **Step 4: Run the Application**
1. Start the containers:
   ```bash
   docker-compose up --build
   ```

2. Visit `http://localhost:8080` in your browser to see the app in action.

---

## **Step 5: Push to Docker Hub**
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

## **Conclusion**
You’ve successfully built a **Dog Breed Web App** with PHP, MySQL, Docker, and Bootstrap. The app is containerized, making it easy to deploy, and styled with Bootstrap for a modern look. You also learned how to push the Docker image to Docker Hub for sharing or deployment.

Marlon I. Tayag 🐶📦
