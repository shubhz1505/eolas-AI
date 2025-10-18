<?php
// api/test-aws.php - Simple AWS connectivity test
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $aws_endpoint = 'https://2at1n9jc2h.execute-api.ap-south-1.amazonaws.com/prod/chat';
    
    // Simple test payload
    $test_data = [
        'query' => 'Test question',
        'language' => 'English',
        'subject' => 'Physics'
    ];
    
    $ch = curl_init();
    curl_setopt_array($ch, [
        CURLOPT_URL => $aws_endpoint,
        CURLOPT_POST => true,
        CURLOPT_POSTFIELDS => json_encode($test_data),
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT => 60,
        CURLOPT_CONNECTTIMEOUT => 10,
        CURLOPT_HTTPHEADER => [
            'Content-Type: application/json'
        ],
        CURLOPT_VERBOSE => true,
        CURLOPT_SSL_VERIFYPEER => false,
        CURLOPT_USERAGENT => 'Eolas-AI/1.0'
    ]);
    
    $response = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $curl_info = curl_getinfo($ch);
    $curl_error = curl_error($ch);
    curl_close($ch);
    
    echo json_encode([
        'endpoint' => $aws_endpoint,
        'http_code' => $http_code,
        'curl_error' => $curl_error,
        'response' => $response,
        'connection_info' => [
            'total_time' => $curl_info['total_time'],
            'connect_time' => $curl_info['connect_time'],
            'namelookup_time' => $curl_info['namelookup_time']
        ]
    ]);
}
?>