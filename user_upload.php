#!/usr/bin/php
<?php

// Parse command line arguments
$options = getopt("u:p:h:", ["file:", "create_table", "dry_run", "help"]);

// Define MySQL connection parameters
$db_username = isset($options['u']) ? $options['u'] : '';
$db_password = isset($options['p']) ? $options['p'] : '';
$db_host = isset($options['h']) ? $options['h'] : 'localhost';

// Handle --help directive
if (isset($options['help'])) {
    echo "Usage: user_upload.php --file [csv file name] --create_table --dry_run -u [MySQL username] -p [MySQL password] -h [MySQL host] --help\n";
    exit();
}

// Handle --create_table directive
if (isset($options['create_table'])) {
    createTable();
    exit();
}

// Handle --file directive
if (isset($options['file'])) {
    $csv_file = $options['file'];
    processCSV($csv_file);
}

// Function to create users table
function createTable() {
    global $db_username, $db_password, $db_host;
    // Connect to MySQL
    $conn = new mysqli($db_host, $db_username, $db_password);
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }
    // Create table
    $sql = "CREATE TABLE IF NOT EXISTS users (
        id INT AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(255) NOT NULL,
        surname VARCHAR(255) NOT NULL,
        email VARCHAR(255) UNIQUE NOT NULL
    )";
    if ($conn->query($sql) === TRUE) {
        echo "Table created successfully\n";
    } else {
        echo "Error creating table: " . $conn->error . "\n";
    }
    $conn->close();
}

// Function to process CSV file
function processCSV($csv_file) {
    global $db_username, $db_password, $db_host;
    // Connect to MySQL
    $conn = new mysqli($db_host, $db_username, $db_password, 'your_database_name');
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }
    // Open CSV file
    if (($handle = fopen($csv_file, "r")) !== FALSE) {
        while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
            $name = ucfirst(strtolower(trim($data[0])));
            $surname = ucfirst(strtolower(trim($data[1])));
            $email = strtolower(trim($data[2]));
            // Validate email
            if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
                // Insert data into table
                $sql = "INSERT INTO users (name, surname, email) VALUES ('$name', '$surname', '$email')";
                if ($conn->query($sql) !== TRUE) {
                    echo "Error: " . $sql . "\n" . $conn->error . "\n";
                }
            } else {
                echo "Error: Invalid email format - $email\n";
            }
        }
        fclose($handle);
    } else {
        echo "Error: Unable to open file - $csv_file\n";
    }
    $conn->close();
}

?>
