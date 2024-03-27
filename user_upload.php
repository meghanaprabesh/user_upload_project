<?php

// Parse command line arguments
$options = getopt("u:p:h:", ["file:", "create_table", "dry_run", "help"]);

// Define MySQL connection parameters
$db_username = isset($options['u']) ? $options['u'] : 'root';
$db_password = isset($options['p']) ? $options['p'] : '';
$db_host = isset($options['h']) ? $options['h'] : 'localhost';

// Handle --help directive
if (isset($options['help'])) {
    echo "Usage: user_upload.php --file [users.csv] --create_table --dry_run -u [root] -p [''] -h [localhost] --help\n";
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
    $conn = new mysqli($db_host, $db_username, $db_password, 'user');
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
    $conn = new mysqli($db_host, $db_username, $db_password, 'user');
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }
    $query = "SHOW TABLES LIKE 'users'";
                $result = $conn->query($query);
                // print_r($result);exit;
                if($result->num_rows == 0) {
                    // print_r($result);exit;
                    createTable();
                }
    // Open CSV file
    if (($handle = fopen($csv_file, "r")) !== FALSE) {
        while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
            $name = ucfirst(strtolower(trim($data[0])));
            $name = preg_replace('/[^a-zA-Z0-9_ %\[\]\.\(\)%&-]/s', '', $name);
            $surname = ucfirst(strtolower(trim($data[1])));
            $surname = preg_replace('/[^a-zA-Z0-9_ %\[\]\.\(\)%&-]/s', '', $surname);
            $email = strtolower(trim($data[2]));
            $email = str_replace( array( '\'', '"',',' , ';', '<', '>' ), ' ', $email);
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
