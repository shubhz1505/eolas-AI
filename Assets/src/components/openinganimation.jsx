// components/OpeningAnimation.jsx
import React, { useState, useEffect } from 'react';
import './OpeningAnimation.css';

const OpeningAnimation = ({ onAnimationComplete }) => {
  const [currentPhase, setCurrentPhase] = useState('initial'); // initial, logo, text, fade
  const [isVisible, setIsVisible] = useState(true);

  useEffect(() => {
    const timeline = [
      { phase: 'logo', delay: 500 },
      { phase: 'text', delay: 2000 },
      { phase: 'fade', delay: 3500 },
      { phase: 'complete', delay: 4500 }
    ];

    timeline.forEach(({ phase, delay }) => {
      setTimeout(() => {
        if (phase === 'complete') {
          setIsVisible(false);
          setTimeout(() => onAnimationComplete(), 300);
        } else {
          setCurrentPhase(phase);
        }
      }, delay);
    });
  }, [onAnimationComplete]);

  if (!isVisible) return null;

  return (
    <div className={`opening-animation ${currentPhase}`}>
      <div className="animation-content">
        {/* Geometric Background Elements */}
        <div className="geometric-bg">
          <div className="geo-element geo1"></div>
          <div className="geo-element geo2"></div>
          <div className="geo-element geo3"></div>
          <div className="geo-element geo4"></div>
        </div>

        {/* Main Logo Animation */}
        <div className="logo-container">
          <div className="logo-glow"></div>
          <div className="logo-main">
            <span className="logo-letter">E</span>
            <span className="logo-letter">O</span>
            <span className="logo-letter">L</span>
            <span className="logo-letter">A</span>
            <span className="logo-letter">S</span>
          </div>
          
          {/* Subtitle Animation */}
          <div className="subtitle-container">
            <div className="subtitle-line"></div>
            <span className="subtitle-text">by EPITEG</span>
            <div className="subtitle-line"></div>
          </div>
        </div>

        {/* Particle Effects */}
        <div className="particles">
          {[...Array(20)].map((_, i) => (
            <div key={i} className={`particle particle-${i + 1}`}></div>
          ))}
        </div>

        {/* Loading Progress */}
        <div className="loading-progress">
          <div className="progress-bar">
            <div className="progress-fill"></div>
          </div>
          <span className="loading-text">Initializing AI System...</span>
        </div>
      </div>
    </div>
  );
};

export default OpeningAnimation;