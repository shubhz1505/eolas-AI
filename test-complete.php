<?php
// test-complete.php - Complete end-to-end test
?>
<!DOCTYPE html>
<html>
<head>
    <title>🚀 Eolas Complete System Test</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; background: #f5f5f5; }
        .container { max-width: 800px; margin: 0 auto; background: white; padding: 20px; border-radius: 10px; }
        .test-section { margin: 20px 0; padding: 15px; border: 1px solid #ddd; border-radius: 5px; }
        .success { background: #d4edda; border-color: #c3e6cb; }
        .error { background: #f8d7da; border-color: #f5c6cb; }
        .loading { background: #fff3cd; border-color: #ffeaa7; }
        button { padding: 10px 20px; margin: 5px; cursor: pointer; background: #007bff; color: white; border: none; border-radius: 5px; }
        button:hover { background: #0056b3; }
        .response { margin: 10px 0; padding: 10px; background: #f8f9fa; border-left: 4px solid #007bff; white-space: pre-wrap; }
        input, select { padding: 8px; margin: 5px; border: 1px solid #ddd; border-radius: 3px; }
    </style>
</head>
<body>
    <div class="container">
        <h1>🎓 Eolas AI - Complete System Test</h1>
        
        <div class="test-section">
            <h3>🤖 AI Chat Test</h3>
            <div style="margin: 10px 0;">
                <input type="text" id="question" placeholder="Ask any physics/chemistry question..." style="width: 300px;">
                <select id="language">
                    <option value="English">English</option>
                    <option value="Hindi">Hindi</option>
                </select>
                <select id="subject">
                    <option value="Physics">Physics</option>
                    <option value="Chemistry">Chemistry</option>
                    <option value="Mathematics">Mathematics</option>
                    <option value="Biology">Biology</option>
                </select>
            </div>
            <button onclick="testAIChat()">🚀 Ask AI</button>
            <div id="ai-result"></div>
        </div>
        
        <div class="test-section">
            <h3>⚡ Quick Test Buttons</h3>
            <button onclick="quickTest('What is photosynthesis?', 'Biology')">Biology Test</button>
            <button onclick="quickTest('Explain quadratic equations', 'Mathematics')">Math Test</button>
            <button onclick="quickTest('परमाणु क्या है?', 'Chemistry', 'Hindi')">Hindi Test</button>
        </div>
    </div>

    <script>
        async function testAIChat() {
            const question = document.getElementById('question').value;
            const language = document.getElementById('language').value;
            const subject = document.getElementById('subject').value;
            
            if (!question.trim()) {
                alert('Please enter a question!');
                return;
            }
            
            const resultDiv = document.getElementById('ai-result');
            resultDiv.innerHTML = '<div class="loading">🤖 AI is thinking... Please wait (this may take 30-60 seconds)</div>';
            
            try {
                const response = await fetch('/api/ai-chat.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({
                        query: question,
                        language: language,
                        subject: subject,
                        user_id: 1
                    })
                });
                
                const result = await response.json();
                
                if (result.success) {
                    resultDiv.innerHTML = `
                        <div class="success">
                            <h4>✅ AI Response ${result.cached ? '(Cached)' : '(Fresh)'}</h4>
                            <div class="response">${result.response}</div>
                            <small>Model: ${result.model} | Language: ${result.language}</small>
                        </div>
                    `;
                } else {
                    resultDiv.innerHTML = `
                        <div class="error">
                            <h4>❌ Error</h4>
                            <p>${result.message}</p>
                            <pre>${JSON.stringify(result, null, 2)}</pre>
                        </div>
                    `;
                }
            } catch (error) {
                resultDiv.innerHTML = `
                    <div class="error">
                        <h4>❌ Connection Error</h4>
                        <p>${error.message}</p>
                    </div>
                `;
            }
        }
        
        function quickTest(question, subject, language = 'English') {
            document.getElementById('question').value = question;
            document.getElementById('subject').value = subject;
            document.getElementById('language').value = language;
            testAIChat();
        }
    </script>
</body>
</html>