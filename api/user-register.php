<?php
// api/user-register.php
include '../config.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $input = json_decode(file_get_contents('php://input'), true);
    
    $name = $input['name'];
    $email = $input['email'];
    $phone = $input['phone'];
    $target_exam = $input['target_exam']; // JEE, NEET, Medical
    $language_preference = $input['language_preference']; // Hindi, English
    
    try {
        $stmt = $pdo->prepare("INSERT INTO users (name, email, phone, target_exam, language_preference) VALUES (?, ?, ?, ?, ?)");
        $stmt->execute([$name, $email, $phone, $target_exam, $language_preference]);
        
        $user_id = $pdo->lastInsertId();
        
        echo json_encode([
            'success' => true,
            'user_id' => $user_id,
            'message' => 'User registered successfully'
        ]);
        
    } catch(PDOException $e) {
        echo json_encode([
            'success' => false,
            'message' => 'Registration failed: ' . $e->getMessage()
        ]);
    }
}
?>