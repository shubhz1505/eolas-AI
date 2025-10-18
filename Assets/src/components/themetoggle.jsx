// components/ThemeToggle.jsx
import React from 'react';
import { useTheme } from '../contexts/themecontext.jsx'
import './ThemeToggle.css';

const ThemeToggle = () => {
  const { theme, toggleTheme } = useTheme();

  return (
    <button 
      className="theme-toggle"
      onClick={toggleTheme}
      aria-label={`Switch to ${theme === 'light' ? 'dark' : 'light'} mode`}
      title={`Switch to ${theme === 'light' ? 'dark' : 'light'} mode`}
    >
      <div className={`toggle-track ${theme}`}>
        <div className="toggle-thumb">
          <div className="toggle-icon">
            {theme === 'light' ? '💡' :'💡'}
          </div>
        </div>
      </div>
    </button>
  );
};

export default ThemeToggle;