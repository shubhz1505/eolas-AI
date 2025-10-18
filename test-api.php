<?php
// test-api.php
?>
<!DOCTYPE html>
<html>
<head>
    <title>🧪 Eolas API Test</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        .test-section { margin: 20px 0; padding: 15px; border: 1px solid #ddd; }
        .success { background: #d4edda; }
        .error { background: #f8d7da; }
        button { padding: 10px 15px; margin: 5px; cursor: pointer; }
    </style>
</head>
<body>
    <h1>🚀 Eolas API Testing Dashboard</h1>
    
    <div class="test-section">
        <h3>Test 1: User Registration API</h3>
        <button onclick="testUserRegistration()">Test User Registration</button>
        <div id="register-result"></div>
    </div>
    
    <div class="test-section">
        <h3>Test 2: Cache Check API</h3>
        <button onclick="testCacheCheck()">Test Cache Check</button>
        <div id="cache-result"></div>
    </div>

    <script>
        async function testUserRegistration() {
            const testData = {
                name: "Test Student",
                email: "test@example.com",
                phone: "9876543210",
                target_exam: "JEE",
                language_preference: "English"
            };
            
            try {
                const response = await fetch('/api/user-register.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify(testData)
                });
                
                const result = await response.json();
                document.getElementById('register-result').innerHTML = 
                    `<div class="${result.success ? 'success' : 'error'}">
                        <strong>Result:</strong> ${JSON.stringify(result, null, 2)}
                    </div>`;
                    
            } catch (error) {
                document.getElementById('register-result').innerHTML = 
                    `<div class="error"><strong>Error:</strong> ${error.message}</div>`;
            }
        }
        
        async function testCacheCheck() {
            const testData = {
                question: "What is Newton's first law?"
            };
            
            try {
                const response = await fetch('/api/check-cache.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify(testData)
                });
                
                const result = await response.json();
                document.getElementById('cache-result').innerHTML = 
                    `<div class="${result.cached ? 'success' : 'error'}">
                        <strong>Result:</strong> ${JSON.stringify(result, null, 2)}
                    </div>`;
                    
            } catch (error) {
                document.getElementById('cache-result').innerHTML = 
                    `<div class="error"><strong>Error:</strong> ${error.message}</div>`;
            }
        }
    </script>
</body>
</html>