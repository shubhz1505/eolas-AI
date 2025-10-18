<?php
// api/ai-chat.php - Improved version with connection handling
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    exit(0);
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $input = json_decode(file_get_contents('php://input'), true);
    
    $query = $input['query'] ?? '';
    $language = $input['language'] ?? 'English';
    $subject = $input['subject'] ?? 'General';
    $user_id = $input['user_id'] ?? null;
    
    // Database connection function
    function getDatabaseConnection() {
        $host = 'localhost';
        $dbname = 'u515790361_master';     // Replace with your actual database name
        $username = 'u515790361_eolas';   // Replace with your actual username  
        $password = 'officePUNE@MH12';   // Replace with your actual password
        
        try {
            $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password, [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_PERSISTENT => false,
                PDO::ATTR_TIMEOUT => 5,
                PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4"
            ]);
            return $pdo;
        } catch(PDOException $e) {
            throw new Exception("Database connection failed: " . $e->getMessage());
        }
    }
    
    try {
        // Create fresh database connection
        $pdo = getDatabaseConnection();
        
        // First check cache
        $question_hash = md5(strtolower(trim($query)));
        
        $stmt = $pdo->prepare("SELECT ai_response, usage_count FROM cached_responses WHERE question_hash = ?");
        $stmt->execute([$question_hash]);
        $cached = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($cached) {
            // Return cached response
            $pdo = getDatabaseConnection(); // Fresh connection for update
            $update_stmt = $pdo->prepare("UPDATE cached_responses SET usage_count = usage_count + 1 WHERE question_hash = ?");
            $update_stmt->execute([$question_hash]);
            
            $response = json_decode($cached['ai_response'], true);
            $response['cached'] = true;
            $response['usage_count'] = $cached['usage_count'] + 1;
            
            echo json_encode($response);
            exit;
        }
        
        // Call AWS Lambda if not cached
        $aws_endpoint = 'https://2at1n9jc2h.execute-api.ap-south-1.amazonaws.com/prod/chat';
        
        $aws_data = [
            'query' => $query,
            'language' => $language,
            'subject' => $subject
        ];
        
        // Initialize cURL with timeout settings
        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_URL => $aws_endpoint,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => json_encode($aws_data),
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 45,          // Reduced timeout
            CURLOPT_CONNECTTIMEOUT => 10,   // Connection timeout
            CURLOPT_HTTPHEADER => [
                'Content-Type: application/json'
            ],
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_SSL_VERIFYPEER => false
        ]);
        
        $aws_response = curl_exec($ch);
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curl_error = curl_error($ch);
        curl_close($ch);
        
        if ($curl_error) {
            throw new Exception("AWS connection error: " . $curl_error);
        }
        
        if ($http_code == 200 && $aws_response) {
            $ai_result = json_decode($aws_response, true);
            
            if ($ai_result && $ai_result['success']) {
                // Cache the response with fresh connection
                try {
                    $pdo = getDatabaseConnection(); // Fresh connection for caching
                    $cache_stmt = $pdo->prepare("INSERT INTO cached_responses (question_hash, question, ai_response, response_type, created_at) VALUES (?, ?, ?, ?, NOW())");
                    $cache_stmt->execute([
                        $question_hash,
                        $query,
                        json_encode($ai_result),
                        'explanation'
                    ]);
                } catch (Exception $e) {
                    // If caching fails, continue without caching
                    error_log("Caching failed: " . $e->getMessage());
                }
                
                // Update user progress if user_id provided
                if ($user_id) {
                    try {
                        $pdo = getDatabaseConnection(); // Fresh connection for progress
                        $progress_stmt = $pdo->prepare("INSERT INTO user_progress (user_id, subject, topic, last_studied) VALUES (?, ?, ?, NOW()) ON DUPLICATE KEY UPDATE last_studied = NOW()");
                        $progress_stmt->execute([$user_id, $subject, substr($query, 0, 200)]);
                    } catch (Exception $e) {
                        // If progress update fails, continue
                        error_log("Progress update failed: " . $e->getMessage());
                    }
                }
                
                $ai_result['cached'] = false;
                $ai_result['processing_time'] = 'real-time';
                
                echo json_encode($ai_result);
            } else {
                echo json_encode([
                    'success' => false,
                    'message' => 'AI processing failed',
                    'error' => $ai_result['error'] ?? 'Invalid AI response',
                    'raw_response' => $aws_response
                ]);
            }
        } else {
            echo json_encode([
                'success' => false,
                'message' => 'AWS API connection failed',
                'http_code' => $http_code,
                'response' => substr($aws_response, 0, 500) // Truncate for debugging
            ]);
        }
        
    } catch(Exception $e) {
        echo json_encode([
            'success' => false,
            'message' => 'Server error: ' . $e->getMessage(),
            'type' => 'php_exception'
        ]);
    }
} else {
    echo json_encode([
        'success' => false,
        'message' => 'Only POST method allowed'
    ]);
}
?>