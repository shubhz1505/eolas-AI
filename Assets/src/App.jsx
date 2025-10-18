import React, { useState, useRef, useEffect } from 'react';
import EolasAPI from './services/api';
import TerminalLoadingScreen from './components/terminalloadingscreen';
import Sidebar from './components/sidebar';
import { ThemeProvider } from './contexts/themecontext'; 
import ThemeToggle from './components/themetoggle';
import OpeningAnimation from './components/openinganimation';
import StreamingText from './components/streamingtext'; 
import './App.css';
import MarkdownViewer from './components/markdown';

function App() {
  const [showOpeningAnimation, setShowOpeningAnimation] = useState(true);
  const [isPreparing, setIsPreparing] = useState(false);
  const [showTerminal, setShowTerminal] = useState(false);
  const [sidebarOpen, setSidebarOpen] = useState(false);
  const [currentProject, setCurrentProject] = useState(null);
  const [isFirstMessage, setIsFirstMessage] = useState(true);
  const [messages, setMessages] = useState([
    {
      id: 1,
      type: 'assistant',
      content: 'Hello! I\'m ready to assist you with anything you need.',
      isWelcome: true
    }
  ]);
  const [inputValue, setInputValue] = useState('');
  const [uploadedFiles, setUploadedFiles] = useState([]);
  const [copiedMessageId, setCopiedMessageId] = useState(null);
  const messagesEndRef = useRef(null);
  const inputRef = useRef(null);
  const fileInputRef = useRef(null);

  const handleAnimationComplete = () => {
    setShowOpeningAnimation(false);
  };

  useEffect(() => {
    const savedSidebarState = localStorage.getItem('eolas-sidebar-open');
    const savedProject = localStorage.getItem('eolas-current-project');
    
    if (savedSidebarState !== null) {
      setSidebarOpen(JSON.parse(savedSidebarState));
    }
    
    if (savedProject) {
      const project = JSON.parse(savedProject);
      setCurrentProject(project);
      if (project.messages) {
        setMessages(project.messages);
        setIsFirstMessage(false);
      }
    }
  }, []);

  useEffect(() => {
    if (messages.length > 0) {
      const lastMessage = messages[messages.length - 1];
      
      if (lastMessage.type === 'assistant' && !isPreparing) {
        setTimeout(() => {
          const messageElements = document.querySelectorAll('.assistant-message');
          const lastAIMessage = messageElements[messageElements.length - 1];
          if (lastAIMessage) {
            lastAIMessage.scrollIntoView({ 
              behavior: 'smooth', 
              block: 'start'
            });
          }
        }, 100);
      } else if (lastMessage.type === 'user') {
        messagesEndRef.current?.scrollIntoView({ behavior: 'smooth' });
      }
    }
  }, [messages, isPreparing]);

  useEffect(() => {
    inputRef.current?.focus();
  }, []);

  useEffect(() => {
    if (currentProject && messages.length > 0) {
      const updatedProject = { ...currentProject, messages, updatedAt: Date.now() };
      localStorage.setItem('eolas-current-project', JSON.stringify(updatedProject));
    }
  }, [messages, currentProject]);

  const handleFileUpload = (event) => {
    const files = Array.from(event.target.files);
    const newFiles = files.map(file => ({
      id: Date.now() + Math.random(),
      name: file.name,
      size: file.size,
      type: file.type,
      file: file
    }));
    setUploadedFiles(prev => [...prev, ...newFiles]);
  };

  const removeFile = (fileId) => {
    setUploadedFiles(prev => prev.filter(f => f.id !== fileId));
  };

  const copyToClipboard = async (text, messageId) => {
    try {
      await navigator.clipboard.writeText(text);
      setCopiedMessageId(messageId);
      setTimeout(() => setCopiedMessageId(null), 2000);
    } catch (err) {
      console.error('Failed to copy:', err);
    }
  };

  const getFileIcon = (type) => {
    if (type.startsWith('image/')) return '🖼';
    if (type.includes('pdf')) return '📄';
    if (type.includes('doc')) return '📝';
    return '📎';
  };

  const handleSendMessage = async () => {
    if ((!inputValue.trim() && uploadedFiles.length === 0) || isPreparing) return;
  
    let messageContent = inputValue.trim();
    
    if (uploadedFiles.length > 0) {
      const fileNames = uploadedFiles.map(f => f.name).join(', ');
      messageContent += `\n\nAttached files: ${fileNames}`;
    }

    const userMessage = {
      id: Date.now(),
      type: 'user',
      content: messageContent,
      files: uploadedFiles
    };
  
    setMessages(prev => [...prev, userMessage]);
    setInputValue('');
    setUploadedFiles([]);
    setIsPreparing(true);
    setShowTerminal(true);

    const shouldStream = isFirstMessage;
    if (isFirstMessage) {
      setIsFirstMessage(false);
    }
  
    try {
      const response = await EolasAPI.chatWithAI(inputValue, 'English', 'General');
  
      const aiMessage = {
        id: Date.now() + 1,
        type: 'assistant',
        content: response?.response || 'No response from backend.',
        streaming: shouldStream
      };
  
      setMessages(prev => [...prev, aiMessage]);
    } catch (err) {
      const errorMsg = {
        id: Date.now() + 1,
        type: 'assistant',
        content: 'Error: Could not reach backend. ' + err.message,
        streaming: false
      };
      setMessages(prev => [...prev, errorMsg]);
      console.error('Backend error:', err);
    } finally {
      setIsPreparing(false);
      setTimeout(() => setShowTerminal(false), 2000);
      setTimeout(() => inputRef.current?.focus(), 100);
    }
  };

  const handleKeyDown = (e) => {
    if (e.key === 'Enter') {
      if (e.shiftKey) {
        return;
      } else {
        e.preventDefault();
        handleSendMessage();
      }
    }
  };

  const toggleSidebar = () => {
    const newState = !sidebarOpen;
    setSidebarOpen(newState);
    localStorage.setItem('eolas-sidebar-open', JSON.stringify(newState));
  };

  const createNewProject = () => {
    const newProject = {
      id: Date.now(),
      name: `Project ${new Date().toLocaleDateString()}`,
      messages: [
        {
          id: Date.now(),
          type: 'assistant',
          content: 'Hello! I\'m ready to assist you with your new project.',
          isWelcome: true
        }
      ],
      createdAt: Date.now(),
      updatedAt: Date.now()
    };
    
    const existingProjects = JSON.parse(localStorage.getItem('eolas-projects') || '[]');
    existingProjects.unshift(newProject);
    localStorage.setItem('eolas-projects', JSON.stringify(existingProjects));
    
    setCurrentProject(newProject);
    setMessages(newProject.messages);
    setIsFirstMessage(true);
    localStorage.setItem('eolas-current-project', JSON.stringify(newProject));
  };

  const loadProject = (project) => {
    setCurrentProject(project);
    setMessages(project.messages || []);
    setIsFirstMessage(false);
    localStorage.setItem('eolas-current-project', JSON.stringify(project));
  };

  const clearChat = () => {
    if (isPreparing) return;
    const clearedMessages = [
      {
        id: Date.now(),
        type: 'assistant',
        content: 'Chat cleared! How can I assist you today?',
        isWelcome: true
      }
    ];
    setMessages(clearedMessages);
    setIsFirstMessage(true);
    
    if (currentProject) {
      const updatedProject = { ...currentProject, messages: clearedMessages, updatedAt: Date.now() };
      setCurrentProject(updatedProject);
      localStorage.setItem('eolas-current-project', JSON.stringify(updatedProject));
    }
  };

  return (
    <ThemeProvider>
      <div className="app-container">
        {showOpeningAnimation && (
          <OpeningAnimation onAnimationComplete={handleAnimationComplete} />
        )}
        
        <Sidebar 
          isOpen={sidebarOpen}
          onToggle={toggleSidebar}
          onNewProject={createNewProject}
          onLoadProject={loadProject}
          currentProject={currentProject}
        />
        
        <div className={`main-content ${sidebarOpen ? 'sidebar-open' : ''}`}>
          <div className="chat-container">
            <div className="chat-header">
              <div className="header-left">
                <button 
                  className="sidebar-toggle"
                  onClick={toggleSidebar}
                  aria-label="Toggle sidebar"
                  aria-expanded={sidebarOpen}
                >
                  <span className="hamburger-icon">
                    <span></span>
                    <span></span>
                    <span></span>
                  </span>
                </button>
                <div className="logo">
                  <span className="logo-icon">⚡</span>
                  <h2>Eolas AI</h2>
                </div>
                <div className="status-indicator">
                  <span className={`status-dot ${isPreparing ? 'processing' : 'ready'}`}></span>
                  {isPreparing ? 'Processing...' : 'Ready'}
                </div>
              </div>
              <div className="header-actions">
                <ThemeToggle />
                <button 
                  className="action-btn clear-btn"
                  onClick={clearChat}
                  disabled={isPreparing}
                >
                  Clear Chat
                </button>
              </div>
            </div>
            
            <div className="chat-messages" role="log" aria-live="polite">
              {messages.map((message) => (
                <div key={message.id} className={`message ${message.type}-message`}>
                  <div className="message-content">
                    <div className="message-bubble">
                      {message.type === 'assistant' && (message.streaming || message.isWelcome) ? (
                        <StreamingText 
                          text={message.content}
                          speed={50}
                          onComplete={() => {
                            setMessages(prev => 
                              prev.map(msg => 
                                msg.id === message.id 
                                  ? { ...msg, streaming: false, isWelcome: false }
                                  : msg
                              )
                            );
                          }}
                        />
                      ) : (
                        <>
                          <MarkdownViewer content={message.content} />
                          {message.files && message.files.length > 0 && (
                            <div className="message-files">
                              {message.files.map(file => (
                                <div key={file.id} className="file-tag">
                                  {getFileIcon(file.type)} {file.name}
                                </div>
                              ))}
                            </div>
                          )}
                        </>
                      )}
                    </div>
                    <div className="message-actions">
                      <div className="message-timestamp">
                        {new Date(message.id).toLocaleTimeString()}
                      </div>
                      {message.type === 'assistant' && !message.streaming && !message.isWelcome && (
                        <button 
                          className="copy-btn"
                          onClick={() => copyToClipboard(message.content, message.id)}
                          title="Copy to clipboard"
                        >
                          {copiedMessageId === message.id ? (
                            <span className="copy-icon">✓ Copied</span>
                          ) : (
                            <span className="copy-icon">Copy</span>
                          )}
                        </button>
                      )}
                    </div>
                  </div>
                </div>
              ))}
              
              {isPreparing && (
                <div className="message assistant-message">
                  <div className="message-content">
                    <div className="thinking-indicator" role="status" aria-label="AI is thinking">
                      <span>Eolas is thinking</span>
                      <div className="typing-dots">
                        <span></span>
                        <span></span>
                        <span></span>
                      </div>
                    </div>
                  </div>
                </div>
              )}
              <div ref={messagesEndRef} />
            </div>
            
            <div className="chat-input-container">
              {uploadedFiles.length > 0 && (
                <div className="uploaded-files-preview">
                  {uploadedFiles.map(file => (
                    <div key={file.id} className="file-preview">
                      <span className="file-icon">
                        {getFileIcon(file.type)}
                      </span>
                      <span className="file-name">{file.name}</span>
                      <button 
                        className="remove-file-btn"
                        onClick={() => removeFile(file.id)}
                        aria-label="Remove file"
                      >
                        ×
                      </button>
                    </div>
                  ))}
                </div>
              )}

              <div className="chat-input-wrapper">
                <div className="input-decoration">
                  <span className="input-prompt">❯</span>
                  <textarea 
                    ref={inputRef}
                    className="chat-input" 
                    placeholder="Ask Eolas anything..."
                    value={inputValue}
                    onChange={(e) => setInputValue(e.target.value)}
                    onKeyDown={handleKeyDown}
                    disabled={isPreparing}
                    aria-label="Chat input"
                    rows={1}
                    style={{
                      resize: 'none',
                      minHeight: '24px',
                      maxHeight: '120px',
                      overflowY: 'auto'
                    }}
                  />
                  
                  <input
                    ref={fileInputRef}
                    type="file"
                    multiple
                    accept="image/*,.pdf,.doc,.docx,.txt"
                    style={{ display: 'none' }}
                    onChange={handleFileUpload}
                  />
                  
                  <button 
                    className="upload-btn"
                    onClick={() => fileInputRef.current?.click()}
                    disabled={isPreparing}
                    aria-label="Upload files"
                    title="Upload files"
                  >
                    <svg width="16" height="16" viewBox="0 0 16 16" fill="none" xmlns="http://www.w3.org/2000/svg">
                      <path d="M7.5 11V4.5M7.5 4.5L4.5 7.5M7.5 4.5L10.5 7.5" stroke="currentColor" strokeWidth="1.5" strokeLinecap="round" strokeLinejoin="round"/>
                      <path d="M3.5 13.5H11.5" stroke="currentColor" strokeWidth="1.5" strokeLinecap="round"/>
                    </svg>
                  </button>
                </div>
                <button 
                  className="send-button"
                  onClick={handleSendMessage}
                  disabled={isPreparing || (!inputValue.trim() && uploadedFiles.length === 0)}
                  aria-label="Send message"
                >
                  <span className="send-icon">↑</span>
                  Send
                </button>
              </div>
              <div className="input-hint">
                Press Enter to send • Shift+Enter for new line
              </div>
            </div>
          </div>
        </div>

        <div className={`terminal-modal ${showTerminal ? 'visible' : ''}`}>
          <div className="terminal-overlay" onClick={() => setShowTerminal(false)} />
          <div className="terminal-window">
            <div className="terminal-header">
              <div className="terminal-tabs">
                <button className="tab active">Processing</button>
                <button className="tab">Logs</button>
                <button className="tab">Analysis</button>
              </div>
              <div className="terminal-controls">
                <span className="control-dot red"></span>
                <span className="control-dot yellow"></span>
                <span className="control-dot green"></span>
                <button 
                  className="close-terminal"
                  onClick={() => setShowTerminal(false)}
                  aria-label="Close terminal"
                >
                  ×
                </button>
              </div>
            </div>
            
            <div className="terminal-content">
              {isPreparing ? (
                <TerminalLoadingScreen />
              ) : (
                <div className="terminal-complete">
                  <div className="completion-message">
                    <div className="terminal-icon">✅</div>
                    <h3>Processing Complete</h3>
                    <p>Response generated successfully</p>
                    <small>Click anywhere outside to close terminal</small>
                  </div>
                </div>
              )}
            </div>
          </div>
        </div>
      </div>
    </ThemeProvider>
  );
}

export default App;