import React, { useState, useEffect } from 'react';
import './TerminalLoadingScreen.css';

const TerminalLoadingScreen = () => {
  const [lines, setLines] = useState([]);
  const [currentLine, setCurrentLine] = useState(0);

  const terminalLines = [
    "🚀 Initializing Eolas AI neural network...",
    "📊 Loading knowledge base modules...", 
    "🔍 Analyzing query context and intent...",
    "💭 Processing natural language patterns...",
    "🧠 Engaging reasoning capabilities...",
    "⚡ Generating intelligent response...",
    "✅ Preparing optimized output..."
  ];

  useEffect(() => {
    if (currentLine < terminalLines.length) {
      const timer = setTimeout(() => {
        setLines(prev => [...prev, terminalLines[currentLine]]);
        setCurrentLine(prev => prev + 1);
      }, 500 + Math.random() * 300); // Variable timing for realism

      return () => clearTimeout(timer);
    }
  }, [currentLine, terminalLines]);

  return (
    <div className="terminal-loading">
      <div className="terminal-content">
        <div className="terminal-line startup">$ eolas --process-query --verbose</div>
        <div className="terminal-line startup">Initializing AI processing pipeline...</div>
        <div className="terminal-separator">════════════════════════════════════</div>
        
        {lines.map((line, index) => (
          <div key={index} className="terminal-line process">
            <span className="prompt">❯</span>
            <span className="line-content">{line}</span>
            {index === lines.length - 1 && (
              <span className="cursor">▋</span>
            )}
          </div>
        ))}
        
        {currentLine === terminalLines.length && (
          <>
            <div className="terminal-separator">════════════════════════════════════</div>
            <div className="terminal-line success">
              <span className="success-icon">✅</span>
              <span>Processing complete! Response ready for delivery...</span>
            </div>
            <div className="terminal-line info">
              <span className="info-icon">ℹ️</span>
              <span>Execution time: {(2.5 + Math.random() * 1.5).toFixed(2)}s</span>
            </div>
          </>
        )}
        
        <div className="progress-bar">
          <div 
            className="progress-fill"
            style={{ 
              width: `${Math.min((currentLine / terminalLines.length) * 100, 100)}%` 
            }}
          ></div>
        </div>
      </div>
    </div>
  );
};

export default TerminalLoadingScreen;