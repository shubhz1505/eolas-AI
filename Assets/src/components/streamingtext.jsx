import React, { useState, useEffect } from 'react';
import './StreamingText.css';

const StreamingText = ({ text, wordsToType = 1, speed = 50, onComplete }) => {
  const [displayedText, setDisplayedText] = useState('');
  const [currentIndex, setCurrentIndex] = useState(0);
  const [isComplete, setIsComplete] = useState(false);

  useEffect(() => {
    const words = text.split(' ');
    const wordsToDisplay = words.slice(0, wordsToType).join(' ');
    const stopIndex = wordsToDisplay.length;

    if (currentIndex < stopIndex) {
      const timer = setTimeout(() => {
        setDisplayedText(prev => prev + wordsToDisplay[currentIndex]);
        setCurrentIndex(prev => prev + 1);
      }, speed);

      return () => clearTimeout(timer);
    } else if (!isComplete) {
      setIsComplete(true);
      if (onComplete) onComplete();
    }
  }, [currentIndex, text, wordsToType, speed, onComplete, isComplete]);

  return (
    <span className="streaming-text">
      {displayedText}
      {!isComplete && <span className="typing-cursor">|</span>}
    </span>
  );
};

export default StreamingText;