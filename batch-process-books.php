<?php
// batch-process-books.php - Process multiple PDFs at once
include 'config.php';

$booksDirectory = 'uploads/books/';
$processor = new PDFProcessor($pdo);

// Process all PDFs in directory
$pdfs = glob($booksDirectory . '*.pdf');
$totalQuestions = 0;

foreach ($pdfs as $pdfPath) {
    echo "Processing: " . basename($pdfPath) . "\n";
    
    try {
        $bookData = $processor->processNCERTBooks($pdfPath);
        $saved = $processor->saveToDatabase($bookData);
        
        echo "✅ Processed: " . basename($pdfPath) . " - {$saved} questions extracted\n";
        $totalQuestions += $saved;
        
    } catch (Exception $e) {
        echo "❌ Error processing " . basename($pdfPath) . ": " . $e->getMessage() . "\n";
    }
}

echo "\n🎉 Total questions extracted: {$totalQuestions}\n";

// Generate training data from processed books
echo "Generating training data...\n";
$generateTrainingData();

function generateTrainingData() {
    global $pdo;
    
    $stmt = $pdo->query("
        SELECT subject, topic, question, context, difficulty 
        FROM knowledge_base 
        ORDER BY subject, topic
    ");
    
    $training_data = [];
    
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        // Create training prompt
        $prompt = "Subject: {$row['subject']}\nTopic: {$row['topic']}\nDifficulty: {$row['difficulty']}\n\nStudent Question: {$row['question']}\n\nProvide detailed educational response:";
        
        // Create response from context
        $completion = "📚 **Topic**: {$row['topic']}\n\n🎯 **Explanation**:\n{$row['context']}\n\n📝 **Key Points**: Based on NCERT curriculum\n📅 **Study Tip**: Practice similar questions from your textbook";
        
        $training_data[] = [
            'prompt' => $prompt,
            'completion' => $completion
        ];
    }
    
    // Save as JSONL for AWS training
    $jsonl_content = "";
    foreach ($training_data as $item) {
        $jsonl_content .= json_encode($item) . "\n";
    }
    
    file_put_contents('eolas-ncert-training.jsonl', $jsonl_content);
    echo "✅ Training data saved: eolas-ncert-training.jsonl (" . count($training_data) . " examples)\n";
}
?>