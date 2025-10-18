import React, { useState, useEffect } from 'react';
import './Sidebar.css';

const Sidebar = ({ isOpen, onToggle, onNewProject, onLoadProject, currentProject }) => {
  const [projects, setProjects] = useState([]);
  const [currentUser, setCurrentUser] = useState('User');

  // Load projects from localStorage
  useEffect(() => {
    const savedProjects = JSON.parse(localStorage.getItem('eolas-projects') || '[]');
    const savedUser = localStorage.getItem('eolas-current-user') || 'User';
    setProjects(savedProjects);
    setCurrentUser(savedUser);
  }, []);

  // Save user changes to localStorage
  const handleUserChange = (newUser) => {
    setCurrentUser(newUser);
    localStorage.setItem('eolas-current-user', newUser);
  };

  // Delete project
  const handleDeleteProject = (projectId, e) => {
    e.stopPropagation();
    const updatedProjects = projects.filter(p => p.id !== projectId);
    setProjects(updatedProjects);
    localStorage.setItem('eolas-projects', JSON.stringify(updatedProjects));
    
    // If deleting current project, clear current project
    if (currentProject && currentProject.id === projectId) {
      localStorage.removeItem('eolas-current-project');
    }
  };

  // Handle project creation
  const handleNewProject = () => {
    onNewProject();
    // Refresh projects list
    const updatedProjects = JSON.parse(localStorage.getItem('eolas-projects') || '[]');
    setProjects(updatedProjects);
  };

  const formatDate = (timestamp) => {
    const date = new Date(timestamp);
    const now = new Date();
    const diffTime = Math.abs(now - date);
    const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24));
    
    if (diffDays === 1) return 'Today';
    if (diffDays === 2) return 'Yesterday';
    if (diffDays <= 7) return `${diffDays} days ago`;
    return date.toLocaleDateString();
  };

  return (
    <>
      {/* Overlay for mobile */}
      {isOpen && <div className="sidebar-overlay" onClick={onToggle} />}
      
      <div className={`sidebar ${isOpen ? 'open' : ''}`}>
        <div className="sidebar-header">
          <div className="user-section">
            <div className="user-avatar">
              {currentUser.charAt(0).toUpperCase()}
            </div>
            <div className="user-info">
              <input
                type="text"
                value={currentUser}
                onChange={(e) => handleUserChange(e.target.value)}
                className="user-name-input"
                placeholder="Your name"
              />
              <span className="user-status">Online</span>
            </div>
          </div>
          
          <button 
            className="new-project-btn"
            onClick={handleNewProject}
            aria-label="Create new project"
          >
            <span className="plus-icon">+</span>
            New Chat
          </button>
        </div>

        <div className="sidebar-content">
          <div className="projects-section">
            <h3 className="section-title">Recent Chats</h3>
            
            {projects.length === 0 ? (
              <div className="empty-state">
                <div className="empty-icon">📝</div>
                <p>No projects yet</p>
                <small>Create your first project to get started</small>
              </div>
            ) : (
              <div className="projects-list">
                {projects.map((project) => (
                  <div
                    key={project.id}
                    className={`project-item ${currentProject?.id === project.id ? 'active' : ''}`}
                    onClick={() => onLoadProject(project)}
                  >
                    <div className="project-info">
                      <div className="project-name">{project.name}</div>
                      <div className="project-meta">
                        <span className="project-date">{formatDate(project.updatedAt)}</span>
                        <span className="project-messages">
                          {project.messages ? project.messages.length : 0} messages
                        </span>
                      </div>
                    </div>
                    <button
                      className="delete-project-btn"
                      onClick={(e) => handleDeleteProject(project.id, e)}
                      aria-label="Delete project"
                    >
                      ×
                    </button>
                  </div>
                ))}
              </div>
            )}
          </div>
        </div>

        <div className="sidebar-footer">
          <div className="app-info">
            <div className="app-name">Eolas AI</div>
            <div className="app-version">v1.0.0</div>
          </div>
        </div>
      </div>
    </>
  );
};

export default Sidebar;