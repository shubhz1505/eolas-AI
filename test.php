<?php
// test.php - Upload this to your eolas.epictechglobal.com folder
echo "<h1>🚀 Eolas AI Backend Test</h1>";

// Test PHP version
echo "<h2>PHP Version: " . phpinfo(INFO_GENERAL) . "</h2>";

// Test database connection
$host = 'localhost';
$dbname = 'u515790361_master';     // Replace with your actual database name
$username = 'u515790361_eolas';   // Replace with your actual username  
$password = 'officePUNE@MH12';   // Replace with your actual password

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    echo "<p>✅ Database connection successful!</p>";
    
    // Test if your tables exist
    $tables = ['users', 'user_progress', 'cached_responses'];
    foreach($tables as $table) {
        $stmt = $pdo->query("SHOW TABLES LIKE '$table'");
        if($stmt->rowCount() > 0) {
            echo "<p>✅ Table '$table' exists</p>";
        } else {
            echo "<p>❌ Table '$table' missing</p>";
        }
    }
    
} catch(PDOException $e) {
    echo "<p>❌ Database error: " . $e->getMessage() . "</p>";
}

// Test file permissions
if(is_writable('.')) {
    echo "<p>✅ Directory is writable</p>";
} else {
    echo "<p>❌ Directory not writable</p>";
}
?>