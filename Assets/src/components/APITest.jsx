import React, { useState } from 'react';
import EolasAPI from '../services/api';

export default function APITest() {
  const [result, setResult] = useState('');
  const [loading, setLoading] = useState(false);

  const testAPI = async () => {
    setLoading(true);
    try {
      const res = await EolasAPI.testConnection();
      setResult(JSON.stringify(res, null, 2));
    } catch (err) {
      setResult('Error: ' + err.message);
    } finally {
      setLoading(false);
    }
  };

  return (
    <div style={{ padding: '20px' }}>
      <h2>API Connection Test</h2>
      <button onClick={testAPI} disabled={loading}>
        {loading ? 'Testing...' : 'Test Backend Connection'}
      </button>
      <pre style={{ background: '#f5f5f5', padding: '10px' }}>{result}</pre>
    </div>
  );
}