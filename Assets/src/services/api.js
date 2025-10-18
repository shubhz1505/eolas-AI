const API_BASE_URL = import.meta.env.VITE_API_BASE_URL || 'https://eolas.epictechglobal.com/api';

class EolasAPI {
  async makeRequest(endpoint, data) {
    try {
      const url = `${API_BASE_URL}/${endpoint}`;
      console.log("🔍 Calling API:", url);
      console.log("📦 Payload:", data);

      const response = await fetch(url, {
        method: 'POST',
        headers: { 
          'Content-Type': 'application/json',
          'Accept': 'application/json'
        },
        body: JSON.stringify(data),
      });
      const convertFileToBase64 = (file) => {
        return new Promise((resolve, reject) => {
          const reader = new FileReader();
          reader.readAsDataURL(file);
          reader.onload = () => resolve(reader.result);
          reader.onerror = error => reject(error);
        });
      };
      
      // Update chatWithAI function
      chatWithAI: async (query, language, subject, files = []) => {
        const fileData = await Promise.all(
          files.map(async (f) => ({
            name: f.name,
            type: f.type,
            data: await convertFileToBase64(f.file)
          }))
        );
      
        const response = await fetch(`${API_BASE_URL}/chat`, {
          method: 'POST',
          headers: { 'Content-Type': 'application/json' },
          body: JSON.stringify({ 
            query, 
            language, 
            subject,
            files: fileData 
          })
        });
        
        return response.json();
      }

      console.log("📊 HTTP status:", response.status);
      console.log("📋 Response headers:", Object.fromEntries(response.headers.entries()));

      // Check if response is ok
      if (!response.ok) {
        throw new Error(`HTTP ${response.status}: ${response.statusText}`);
      }

      // Get response text first to see raw content
      const responseText = await response.text();
      console.log("📄 Raw response:", responseText);

      // Try to parse as JSON
      let responseJson;
      try {
        responseJson = JSON.parse(responseText);
        console.log("✅ Parsed JSON:", responseJson);
      } catch (parseError) {
        console.error("❌ JSON parse error:", parseError);
        console.error("Raw response was:", responseText);
        throw new Error(`Invalid JSON response: ${responseText.substring(0, 200)}`);
      }

      return responseJson;

    } catch (err) {
      console.error("🚨 API error:", err);
      throw err;
    }
  }

  async chatWithAI(message, language = 'English', subject = 'Physics') {
    return this.makeRequest('ai-chat.php', { 
      query: message, 
      language, 
      subject, 
      user_id: 1 
    });
  }

  async registerUser(userData) {
    return this.makeRequest('user-register.php', userData);
  }

  async testConnection() {
    return this.makeRequest('test-complete.php', {});
  }
}

export default new EolasAPI();