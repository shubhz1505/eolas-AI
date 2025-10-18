<?php
// working-text-processor.php - Fixed version
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Database connection with error handling
try {
    $host = 'localhost';
    $dbname = 'u515790361_master';     // Replace with actual
    $username = 'u515790361_eolas';   // Replace with actual  
    $password = 'officePUNE@MH12';   // Replace with actual

    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    die("Database connection failed. Please check your credentials.");
}

// Define functions FIRST
function extractQuestions($text) {
    $questions = [];
    $patterns = [
        '/(?:Question|Q\.?)\s*\d*[:\-]?\s*([^.!?]*\?)/i',
        '/(?:प्रश्न|प्र\.?)\s*\d*[:\-]?\s*([^.!?]*\?)/i',
        '/^\d+\.\s*([^.!?]*\?)/m'
    ];
    
    foreach ($patterns as $pattern) {
        if (preg_match_all($pattern, $text, $matches)) {
            foreach ($matches[1] as $question) {
                $question = trim($question);
                if (strlen($question) > 10 && strlen($question) < 500) {
                    $questions[] = [
                        'question' => $question,
                        'subject' => identifySubject($text),
                        'context' => getContext($text, $question)
                    ];
                }
            }
        }
    }
    return $questions;
}

function identifySubject($text) {
    $text_lower = strtolower($text);
    if (strpos($text_lower, 'physics') !== false) return 'Physics';
    if (strpos($text_lower, 'chemistry') !== false) return 'Chemistry';
    if (strpos($text_lower, 'biology') !== false) return 'Biology';
    if (strpos($text_lower, 'mathematics') !== false) return 'Mathematics';
    return 'General';
}

function getContext($text, $question) {
    $pos = strpos($text, $question);
    if ($pos !== false) {
        $start = max(0, $pos - 200);
        $end = min(strlen($text), $pos + 300);
        return trim(substr($text, $start, $end - $start));
    }
    return '';
}

function saveQuestions($questions, $pdo, $source) {
    $createTable = "CREATE TABLE IF NOT EXISTS extracted_questions (
        id INT AUTO_INCREMENT PRIMARY KEY,
        question TEXT,
        subject VARCHAR(100),
        context TEXT,
        source VARCHAR(200),
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )";
    $pdo->exec($createTable);
    
    $stmt = $pdo->prepare("INSERT INTO extracted_questions (question, subject, context, source) VALUES (?, ?, ?, ?)");
    
    $count = 0;
    foreach ($questions as $q) {
        try {
            $stmt->execute([$q['question'], $q['subject'], $q['context'], $source]);
            $count++;
        } catch (Exception $e) {
            // Skip duplicates
        }
    }
    return $count;
}

// Process form submission
$result_message = '';
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['process_manual'])) {
    try {
        $manualText = $_POST['manual_text'] ?? '';
        $bookName = $_POST['book_name'] ?? '';
        
        if (!empty($manualText) && !empty($bookName)) {
            $questions = extractQuestions($manualText);
            $saved = 0;
            
            if (count($questions) > 0) {
                $saved = saveQuestions($questions, $pdo, $bookName);
            }
            
            $result_message = "
                <div style='background: #d4edda; padding: 15px; margin: 10px 0; border-left: 4px solid #28a745;'>
                    <h3>✅ Processing Complete!</h3>
                    <p><strong>Book:</strong> " . htmlspecialchars($bookName) . "</p>
                    <p><strong>Text Length:</strong> " . strlen($manualText) . " characters</p>
                    <p><strong>Questions Found:</strong> " . count($questions) . "</p>
                    <p><strong>Questions Saved:</strong> " . $saved . "</p>
                </div>";
        } else {
            $result_message = "<div style='background: #f8d7da; padding: 15px; margin: 10px 0; border-left: 4px solid #dc3545;'>Please fill both fields.</div>";
        }
    } catch (Exception $e) {
        $result_message = "<div style='background: #f8d7da; padding: 15px; margin: 10px 0; border-left: 4px solid #dc3545;'>Error: " . $e->getMessage() . "</div>";
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Eolas Text Processor</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; background: #f5f5f5; }
        .container { max-width: 800px; margin: 0 auto; background: white; padding: 20px; border-radius: 10px; }
        .form-area { border: 2px dashed #007bff; padding: 30px; text-align: center; margin: 20px 0; }
        button { padding: 12px 24px; background: #007bff; color: white; border: none; cursor: pointer; border-radius: 5px; }
        textarea { width: 95%; height: 200px; padding: 10px; }
        input[type="text"] { width: 95%; padding: 8px; margin: 5px 0; }
    </style>
</head>
<body>
    <div class="container">
        <h1>📚 Eolas Text Processor</h1>
        
        <?php echo $result_message; ?>
        
        <div class="form-area">
            <h3>✍️ Process Your Book Content</h3>
            <form method="POST">
                <textarea name="manual_text" placeholder="Paste your NCERT book content here..."></textarea>
                <br><br>
                <input type="text" name="book_name" placeholder="Book name (e.g., NCERT Physics Class 12)" required>
                <br><br>
                <button type="submit" name="process_manual">🔄 Process Text</button>
            </form>
        </div>
        
        <div style="background: #e9ecef; padding: 15px; margin: 20px 0; border-radius: 5px;">
            <h4>📋 Test Sample:</h4>
            <pre style="background: white; padding: 10px; font-size: 12px;">Chapter 4: Motion in a Plane
Question 1: What is scalar quantity?
Question 2: What is vector quantity?  
Question 3: How do you add vectors?
Question 4: What is projectile motion?</pre>
        </div>
    </div>
</body>
</html>