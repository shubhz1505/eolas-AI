<?php
// api/check-cache.php
include '../config.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $input = json_decode(file_get_contents('php://input'), true);
    $question = $input['question'];
    
    // Create hash of question for caching
    $question_hash = md5(strtolower(trim($question)));
    
    try {
        $stmt = $pdo->prepare("SELECT ai_response, usage_count FROM cached_responses WHERE question_hash = ?");
        $stmt->execute([$question_hash]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($result) {
            // Update usage count
            $update_stmt = $pdo->prepare("UPDATE cached_responses SET usage_count = usage_count + 1 WHERE question_hash = ?");
            $update_stmt->execute([$question_hash]);
            
            echo json_encode([
                'cached' => true,
                'response' => json_decode($result['ai_response'], true),
                'usage_count' => $result['usage_count'] + 1
            ]);
        } else {
            echo json_encode([
                'cached' => false,
                'message' => 'No cached response found'
            ]);
        }
        
    } catch(PDOException $e) {
        echo json_encode([
            'success' => false,
            'message' => 'Cache check failed: ' . $e->getMessage()
        ]);
    }
}
?>