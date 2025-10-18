<?php
// view-questions.php - View extracted questions
include 'config.php';

echo "<h2>📊 Extracted Questions Database</h2>";

try {
    $stmt = $pdo->query("SELECT * FROM extracted_questions ORDER BY created_at DESC LIMIT 50");
    $questions = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if ($questions) {
        echo "<p><strong>Total Questions Found:</strong> " . count($questions) . "</p>";
        
        foreach ($questions as $q) {
            echo "<div style='border: 1px solid #ddd; padding: 10px; margin: 10px 0;'>";
            echo "<strong>Subject:</strong> " . htmlspecialchars($q['subject']) . "<br>";
            echo "<strong>Question:</strong> " . htmlspecialchars($q['question']) . "<br>";
            echo "<strong>Source:</strong> " . htmlspecialchars($q['source']) . "<br>";
            echo "<small><strong>Added:</strong> " . $q['created_at'] . "</small>";
            echo "</div>";
        }
    } else {
        echo "<p>No questions found in database yet.</p>";
    }
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
?>