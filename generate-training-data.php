<?php
// generate-training-data.php - Convert extracted questions to training format
$host = 'localhost';
$dbname = 'u515790361_master';     // Replace with actual
$username = 'u515790361_eolas';   // Replace with actual  
$password = 'officePUNE@MH12';   // Replace with actual

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    die("Database connection failed: " . $e->getMessage());
}

// Fetch all extracted questions
$stmt = $pdo->query("SELECT * FROM extracted_questions ORDER BY subject, created_at DESC");
$questions = $stmt->fetchAll(PDO::FETCH_ASSOC);

$training_data = [];
$jsonl_content = "";

foreach ($questions as $q) {
    // Create educational prompt-completion pairs
    $prompt = "Subject: {$q['subject']}
Source: {$q['source']}
Student Question: {$q['question']}

Provide detailed educational response for Indian JEE/NEET students:";

    // Generate comprehensive answer using context
    $completion = "📚 **Topic**: {$q['subject']} Concept

🎯 **Answer**: 
{$q['context']}

📝 **Key Points**:
- Based on NCERT curriculum
- Essential for JEE/NEET preparation
- Practice similar problems for mastery

📅 **Study Strategy**:
- Review fundamental concepts
- Solve numerical problems
- Connect to real-world applications

💡 **Exam Tip**: This topic frequently appears in competitive exams. Focus on understanding the underlying principles.";

    // Create training pair
    $training_pair = [
        "prompt" => $prompt,
        "completion" => $completion
    ];
    
    $training_data[] = $training_pair;
    $jsonl_content .= json_encode($training_pair) . "\n";
    
    // Also create Hindi version for bilingual support
    $hindi_prompt = "विषय: {$q['subject']}
स्रोत: {$q['source']}
छात्र प्रश्न: {$q['question']}

भारतीय JEE/NEET छात्रों के लिए विस्तृत शैक्षणिक उत्तर प्रदान करें:";

    $hindi_completion = "📚 **विषय**: {$q['subject']} अवधारणा

🎯 **उत्तर**: 
{$q['context']}

📝 **मुख्य बिंदु**:
- NCERT पाठ्यक्रम पर आधारित
- JEE/NEET तैयारी के लिए आवश्यक
- दक्षता के लिए समान समस्याओं का अभ्यास करें

📅 **अध्ययन रणनीति**:
- मूलभूत अवधारणाओं की समीक्षा करें
- संख्यात्मक समस्याओं को हल करें
- वास्तविक दुनिया के अनुप्रयोगों से जोड़ें";

    $hindi_pair = [
        "prompt" => $hindi_prompt,
        "completion" => $hindi_completion
    ];
    
    $training_data[] = $hindi_pair;
    $jsonl_content .= json_encode($hindi_pair) . "\n";
}

// Save training data
file_put_contents('eolas-training-data.jsonl', $jsonl_content);
file_put_contents('eolas-training-sample.json', json_encode(array_slice($training_data, 0, 10), JSON_PRETTY_PRINT));

echo "<h2>Training Data Generated Successfully!</h2>";
echo "<p><strong>Total Questions:</strong> " . count($questions) . "</p>";
echo "<p><strong>Total Training Examples:</strong> " . count($training_data) . " (English + Hindi)</p>";
echo "<p><strong>File Size:</strong> " . round(filesize('eolas-training-data.jsonl') / 1024, 2) . " KB</p>";
echo "<p><strong>Files Created:</strong></p>";
echo "<ul>";
echo "<li>eolas-training-data.jsonl (for AWS training)</li>";
echo "<li>eolas-training-sample.json (for review)</li>";
echo "</ul>";

// Show sample training data
echo "<h3>Sample Training Data:</h3>";
echo "<pre>" . json_encode($training_data[0], JSON_PRETTY_PRINT) . "</pre>";

// Provide download links
echo "<p><a href='eolas-training-data.jsonl' download>Download Training Data (JSONL)</a></p>";
echo "<p><a href='eolas-training-sample.json' download>Download Sample (JSON)</a></p>";
?>