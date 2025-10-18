<?php
// pdf-processor.php - Extract text from PDFs and prepare training data
set_time_limit(300); // 5 minutes for large PDFs

// You'll need to install these via composer or manual upload
// For now, let's use a simple text extraction approach

class PDFProcessor {
    private $pdo;
    
    public function __construct($database) {
        $this->pdo = $database;
    }
    
    // Extract text from PDF using different methods
    public function extractPDFText($pdfPath) {
        $text = '';
        
        // Method 1: Try pdftotext (if available on Hostinger)
        $output = shell_exec("pdftotext '$pdfPath' -");
        if ($output) {
            return $output;
        }
        
        // Method 2: Use online PDF to text API (backup)
        return $this->extractUsingAPI($pdfPath);
    }
    
    // Extract using online API (free tier)
    private function extractUsingAPI($pdfPath) {
        // Using ConvertAPI or similar service
        $api_key = 'YOUR_API_KEY'; // You can get free key
        
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, 'https://v2.convertapi.com/convert/pdf/to/txt');
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Authorization: Bearer ' . $api_key,
            'Content-Type: multipart/form-data'
        ]);
        curl_setopt($ch, CURLOPT_POSTFIELDS, [
            'File' => new CURLFile($pdfPath)
        ]);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        
        $response = curl_exec($ch);
        curl_close($ch);
        
        return json_decode($response, true)['Files'][0]['FileData'] ?? '';
    }
    
    // Process NCERT books
    public function processNCERTBooks($bookDir) {
        $books = glob($bookDir . "/*.pdf");
        $processed_data = [];
        
        foreach ($books as $bookPath) {
            echo "Processing: " . basename($bookPath) . "\n";
            
            $text = $this->extractPDFText($bookPath);
            $bookInfo = $this->identifyBook($bookPath);
            
            // Split into chapters/topics
            $chapters = $this->splitIntoChapters($text, $bookInfo['subject']);
            
            foreach ($chapters as $chapter) {
                $questions = $this->extractQuestionsFromChapter($chapter);
                $processed_data = array_merge($processed_data, $questions);
            }
        }
        
        return $processed_data;
    }
    
    // Identify book details from filename
    private function identifyBook($filePath) {
        $filename = basename($filePath);
        
        // Extract subject and class from filename
        $subject = 'General';
        $class = 'Unknown';
        
        if (stripos($filename, 'physics') !== false) $subject = 'Physics';
        elseif (stripos($filename, 'chemistry') !== false) $subject = 'Chemistry';
        elseif (stripos($filename, 'biology') !== false) $subject = 'Biology';
        elseif (stripos($filename, 'math') !== false) $subject = 'Mathematics';
        
        preg_match('/(\d{1,2})/', $filename, $matches);
        if ($matches) {
            $class = 'Class ' . $matches[1];
        }
        
        return [
            'subject' => $subject,
            'class' => $class,
            'book_type' => stripos($filename, 'ncert') !== false ? 'NCERT' : 'Reference'
        ];
    }
    
    // Split text into chapters
    private function splitIntoChapters($text, $subject) {
        $chapters = [];
        
        // Look for chapter patterns
        $patterns = [
            '/Chapter\s+\d+[:\s]+([^\n]+)/i',
            '/अध्याय\s+\d+[:\s]+([^\n]+)/i',
            '/CHAPTER\s+\d+[:\s]+([^\n]+)/i'
        ];
        
        foreach ($patterns as $pattern) {
            if (preg_match_all($pattern, $text, $matches, PREG_OFFSET_CAPTURE)) {
                for ($i = 0; $i < count($matches[0]); $i++) {
                    $start = $matches[0][$i][1];
                    $end = isset($matches[0][$i + 1]) ? $matches[0][$i + 1][1] : strlen($text);
                    
                    $chapter_text = substr($text, $start, $end - $start);
                    $chapter_title = trim($matches[1][$i][0]);
                    
                    $chapters[] = [
                        'title' => $chapter_title,
                        'content' => $chapter_text,
                        'subject' => $subject
                    ];
                }
                break; // Use first matching pattern
            }
        }
        
        return $chapters;
    }
    
    // Extract questions from chapter content
    private function extractQuestionsFromChapter($chapter) {
        $questions = [];
        $content = $chapter['content'];
        
        // Patterns to find questions
        $question_patterns = [
            '/(?:Question|प्रश्न)\s*\d*[:.]\s*([^?]+\?)/i',
            '/(?:Q|प्र)\s*\d*[:.]\s*([^?]+\?)/i',
            '/^\d+\.\s*([^?]+\?)/m',
            '/Example\s*\d*[:.]\s*([^?]+\?)/i'
        ];
        
        foreach ($question_patterns as $pattern) {
            if (preg_match_all($pattern, $content, $matches)) {
                foreach ($matches[1] as $question) {
                    $question = trim($question);
                    if (strlen($question) > 10) { // Filter out very short matches
                        $questions[] = [
                            'question' => $question,
                            'subject' => $chapter['subject'],
                            'topic' => $chapter['title'],
                            'source' => 'NCERT',
                            'context' => $this->getQuestionContext($content, $question)
                        ];
                    }
                }
            }
        }
        
        return $questions;
    }
    
    // Get context around question for better answers
    private function getQuestionContext($content, $question) {
        $pos = strpos($content, $question);
        if ($pos !== false) {
            $start = max(0, $pos - 500);
            $end = min(strlen($content), $pos + 1000);
            return substr($content, $start, $end - $start);
        }
        return '';
    }
    
    // Save processed data to database
    public function saveToDatabase($processed_data) {
        $stmt = $this->pdo->prepare("
            INSERT INTO knowledge_base (subject, topic, content, source, question, context, difficulty, created_at) 
            VALUES (?, ?, ?, ?, ?, ?, ?, NOW())
        ");
        
        $count = 0;
        foreach ($processed_data as $item) {
            $stmt->execute([
                $item['subject'],
                $item['topic'],
                $item['context'],
                $item['source'],
                $item['question'],
                $item['context'],
                'NCERT'
            ]);
            $count++;
        }
        
        return $count;
    }
}

// Usage
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_FILES['pdf'])) {
    include 'config.php';
    
    $processor = new PDFProcessor($pdo);
    
    // Handle file upload
    $uploadDir = 'uploads/books/';
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0777, true);
    }
    
    $uploadedFile = $uploadDir . basename($_FILES['pdf']['name']);
    if (move_uploaded_file($_FILES['pdf']['tmp_name'], $uploadedFile)) {
        $text = $processor->extractPDFText($uploadedFile);
        echo "Extracted " . strlen($text) . " characters from PDF\n";
        
        // Process the extracted text
        $bookInfo = $processor->identifyBook($uploadedFile);
        echo "Book: " . json_encode($bookInfo) . "\n";
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>📚 Eolas PDF Data Processor</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        .upload-area { border: 2px dashed #ccc; padding: 20px; text-align: center; margin: 20px 0; }
        .book-info { background: #f8f9fa; padding: 15px; margin: 10px 0; border-left: 4px solid #007bff; }
        button { padding: 10px 20px; background: #007bff; color: white; border: none; cursor: pointer; }
    </style>
</head>
<body>
    <h1>📚 Eolas Book Processing System</h1>
    
    <div class="upload-area">
        <h3>Upload NCERT/JEE/NEET Books (PDF)</h3>
        <form method="POST" enctype="multipart/form-data">
            <input type="file" name="pdf" accept=".pdf" required>
            <br><br>
            <button type="submit">📖 Process Book</button>
        </form>
    </div>
    
    <div class="book-info">
        <h4>📊 Processing Status</h4>
        <p><strong>Supported Formats:</strong> NCERT Books, JEE Books, NEET Books</p>
        <p><strong>Supported Classes:</strong> 8th to 12th</p>
        <p><strong>Languages:</strong> English & Hindi</p>
    </div>
</body>
</html>