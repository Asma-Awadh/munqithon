<?php
include("conn.php");

// Check if the reviews table exists
$table_check_query = "SHOW TABLES LIKE 'reviews'";
$table_result = mysqli_query($db, $table_check_query);

if (mysqli_num_rows($table_result) == 0) {
    // Create reviews table if it doesn't exist
    $create_table_query = "CREATE TABLE reviews (
        id INT AUTO_INCREMENT PRIMARY KEY,
        appointment_id INT NOT NULL,
        user_id INT NOT NULL,
        doctor_id INT NOT NULL,
        rating INT NOT NULL,
        review TEXT,
        created_at DATETIME NOT NULL,
        updated_at DATETIME,
        UNIQUE KEY unique_review (appointment_id, user_id)
    )";
    
    if (mysqli_query($db, $create_table_query)) {
        echo "Reviews table created successfully.<br>";
    } else {
        echo "Error creating reviews table: " . mysqli_error($db) . "<br>";
    }
} else {
    echo "Reviews table already exists.<br>";
    
    // Describe the table structure
    $describe_query = "DESCRIBE reviews";
    $describe_result = mysqli_query($db, $describe_query);
    
    echo "<h3>Reviews Table Structure:</h3>";
    echo "<table border='1'>";
    echo "<tr><th>Field</th><th>Type</th><th>Null</th><th>Key</th><th>Default</th><th>Extra</th></tr>";
    
    while ($row = mysqli_fetch_assoc($describe_result)) {
        echo "<tr>";
        echo "<td>" . $row['Field'] . "</td>";
        echo "<td>" . $row['Type'] . "</td>";
        echo "<td>" . $row['Null'] . "</td>";
        echo "<td>" . $row['Key'] . "</td>";
        echo "<td>" . $row['Default'] . "</td>";
        echo "<td>" . $row['Extra'] . "</td>";
        echo "</tr>";
    }
    
    echo "</table>";
}

// Check if there are any reviews in the table
$count_query = "SELECT COUNT(*) as count FROM reviews";
$count_result = mysqli_query($db, $count_query);
$count_row = mysqli_fetch_assoc($count_result);

echo "<p>Number of reviews in the table: " . $count_row['count'] . "</p>";

// Close the connection
mysqli_close($db);
?>
