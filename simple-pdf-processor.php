<?php
// simple-pdf-processor.php - Basic PDF processor that works on Hostinger
error_reporting(E_ALL);
ini_set('display_errors', 1);

include 'config.php';
?>
<!DOCTYPE html>
<html>
<head>
    <title>📚 Simple PDF Text Extractor</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; background: #f5f5f5; }
        .container { max-width: 800px; margin: 0 auto; background: white; padding: 20px; border-radius: 10px; }
        .upload-area { border: 2px dashed #007bff; padding: 30px; text-align: center; margin: 20px 0; }
        .result { background: #f8f9fa; padding: 15px; margin: 10px 0; border-left: 4px solid #28a745; }
        .error { background: #f8d7da; padding: 15px; margin: 10px 0; border-left: 4px solid #dc3545; }
        button { padding: 12px 24px; background: #007bff; color: white; border: none; cursor: pointer; border-radius: 5px; }
        input[type="file"] { margin: 10px 0; }
        .extracted-text { max-height: 300px; overflow-y: auto; background: #f8f9fa; padding: 15px; border: 1px solid #ddd; }
    </style>
</head>
<body>
    <div class="container">
        <h1>📚 Eolas PDF Text Extractor</h1>
        
        <?php
        if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_FILES['pdf_file'])) {
            try {
                // Create upload directory if it doesn't exist
                $uploadDir = 'uploads/';
                if (!file_exists($uploadDir)) {
                    mkdir($uploadDir, 0777, true);
                }
                
                $uploadedFile = $uploadDir . basename($_FILES['pdf_file']['name']);
                
                if (move_uploaded_file($_FILES['pdf_file']['tmp_name'], $uploadedFile)) {
                    echo '<div class="result">';
                    echo '<h3>✅ File uploaded successfully!</h3>';
                    echo '<p><strong>File:</strong> ' . basename($_FILES['pdf_file']['name']) . '</p>';
                    echo '<p><strong>Size:</strong> ' . round($_FILES['pdf_file']['size'] / 1024, 2) . ' KB</p>';
                    
                    // Try to extract text using different methods
                    $extractedText = '';
                    
                    // Method 1: Check if pdftotext is available
                    $pdftotext_output = shell_exec("which pdftotext 2>/dev/null");
                    if ($pdftotext_output) {
                        echo '<p><strong>Method:</strong> Using pdftotext command</p>';
                        $extractedText = shell_exec("pdftotext '$uploadedFile' - 2>/dev/null");
                    }
                    
                    // Method 2: If no pdftotext, use online API (for demo)
                    if (empty($extractedText)) {
                        echo '<p><strong>Method:</strong> Manual text extraction needed</p>';
                        echo '<p><strong>Note:</strong> Please copy-paste text from your PDF below for processing</p>';
                    } else {
                        echo '<p><strong>Extracted Text Length:</strong> ' . strlen($extractedText) . ' characters</p>';
                        
                        // Process the extracted text
                        $questions = extractQuestions($extractedText);
                        echo '<p><strong>Questions Found:</strong> ' . count($questions) . '</p>';
                        
                        // Save to database
                        if (count($questions) > 0) {
                            $saved = saveQuestions($questions, $pdo, basename($_FILES['pdf_file']['name']));
                            echo '<p><strong>Questions Saved:</strong> ' . $saved . '</p>';
                        }
                        
                        // Show sample of extracted text
                        echo '<div class="extracted-text">';
                        echo '<h4>Sample Extracted Text:</h4>';
                        echo '<pre>' . htmlspecialchars(substr($extractedText, 0, 1000)) . '...</pre>';
                        echo '</div>';
                    }
                    
                    echo '</div>';
                    
                    // Clean up uploaded file
                    unlink($uploadedFile);
                    
                } else {
                    echo '<div class="error">❌ Failed to upload file</div>';
                }
                
            } catch (Exception $e) {
                echo '<div class="error">❌ Error: ' . $e->getMessage() . '</div>';
            }
        }
        ?>
        
        <div class="upload-area">
            <h3>📖 Upload PDF Book</h3>
            <form method="POST" enctype="multipart/form-data">
                <input type="file" name="pdf_file" accept=".pdf" required>
                <br><br>
                <button type="submit">🚀 Extract Text & Questions</button>
            </form>
        </div>
        
        <div class="upload-area" style="background: #fff3cd;">
            <h3>✍️ Manual Text Input</h3>
            <p>If PDF extraction doesn't work, copy-paste your book content here:</p>
            <form method="POST">
                <textarea name="manual_text" rows="10" cols="80" placeholder="Paste your book text here..."></textarea>
                <br><br>
                <input type="text" name="book_name" placeholder="Book name (e.g., NCERT Physics Class 12)" required>
                <br><br>
                <button type="submit" name="process_manual">🔄 Process Manual Text</button>
            </form>
        </div>
        
        <?php
        // Process manual text input
        if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['process_manual'])) {
            try {
                $manualText = $_POST['manual_text'];
                $bookName = $_POST['book_name'];
                
                if (!empty($manualText)) {
                    echo '<div class="result">';
                    echo '<h3>✅ Processing manual text!</h3>';
                    echo '<p><strong>Book:</strong> ' . htmlspecialchars($bookName) . '</p>';
                    echo '<p><strong>Text Length:</strong> ' . strlen($manualText) . ' characters</p>';
                    
                    $questions = extractQuestions($manualText);
                    echo '<p><strong>Questions Found:</strong> ' . count($questions) . '</p>';
                    
                    if (count($questions) > 0) {
                        $saved = saveQuestions($questions, $pdo, $bookName);
                        echo '<p><strong>Questions Saved:</strong> ' . $saved . '</p>';
                        
                        echo '<h4>Sample Questions Found:</h4>';
                        for ($i = 0; $i < min(5, count($questions)); $i++) {
                            echo '<p><strong>Q:</strong> ' . htmlspecialchars($questions[$i]['question']) . '</p>';
                        }
                    }
                    echo '</div>';
                }
            } catch (Exception $e) {
                echo '<div class="error">❌ Error: ' . $e->getMessage() . '</div>';
            }
        }
        
        // Function to extract questions from text
        function extractQuestions($text) {
            $questions = [];
            
            // Simple patterns to find questions
            $patterns = [
                '/(?:Question|Q\.?)\s*\d*[:\-]?\s*([^.!?]*\?)/i',
                '/(?:प्रश्न|प्र\.?)\s*\d*[:\-]?\s*([^.!?]*\?)/i',
                '/^\d+\.\s*([^.!?]*\?)/m',
                '/Example\s*\d*[:\-]?\s*([^.!?]*\?)/i'
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
            
            return array_unique($questions, SORT_REGULAR);
        }
        
        // Identify subject from text
        function identifySubject($text) {
            $text_lower = strtolower($text);
            
            if (strpos($text_lower, 'physics') !== false || strpos($text_lower, 'भौतिकी') !== false) return 'Physics';
            if (strpos($text_lower, 'chemistry') !== false || strpos($text_lower, 'रसायन') !== false) return 'Chemistry';
            if (strpos($text_lower, 'biology') !== false || strpos($text_lower, 'जीव विज्ञान') !== false) return 'Biology';
            if (strpos($text_lower, 'mathematics') !== false || strpos($text_lower, 'गणित') !== false) return 'Mathematics';
            
            return 'General';
        }
        
        // Get context around question
        function getContext($text, $question) {
            $pos = strpos($text, $question);
            if ($pos !== false) {
                $start = max(0, $pos - 200);
                $end = min(strlen($text), $pos + 300);
                return trim(substr($text, $start, $end - $start));
            }
            return '';
        }
        
        // Save questions to database
        function saveQuestions($questions, $pdo, $source) {
            // First, make sure the table exists
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
                    $stmt->execute([
                        $q['question'],
                        $q['subject'],
                        $q['context'],
                        $source
                    ]);
                    $count++;
                } catch (Exception $e) {
                    // Skip duplicates
                }
            }
            
            return $count;
        }
        ?>
        
        <div style="background: #e9ecef; padding: 15px; margin: 20px 0; border-radius: 5px;">
            <h4>📊 Instructions:</h4>
            <p><strong>For PDF Upload:</strong> Upload your NCERT/JEE/NEET books one by one</p>
            <p><strong>For Manual Input:</strong> Copy-paste 1-2 chapters at a time for better results</p>
            <p><strong>Supported:</strong> English and Hindi text</p>
        </div>
    </div>
</body>
</html>