import React, { useState } from 'react';
import ReactMarkdown from 'react-markdown';
import remarkMath from 'remark-math';
import rehypeKatex from 'rehype-katex';
import remarkGfm from 'remark-gfm';
import { Prism as SyntaxHighlighter } from 'react-syntax-highlighter';
import { oneDark } from 'react-syntax-highlighter/dist/esm/styles/prism';
import 'katex/dist/katex.min.css';

const CodeBlock = ({ language, value }) => {
  const [copied, setCopied] = useState(false);

  const handleCopy = async () => {
    try {
      await navigator.clipboard.writeText(value);
      setCopied(true);
      setTimeout(() => setCopied(false), 2000);
    } catch (err) {
      console.error('Failed to copy code:', err);
    }
  };

  return (
    <div className="code-block-wrapper">
      <div className="code-block-header">
        <span className="code-language">{language || 'text'}</span>
        <button 
          className="copy-code-btn"
          onClick={handleCopy}
          aria-label="Copy code"
        >
          {copied ? (
            <>
              <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2">
                <polyline points="20 6 9 17 4 12"></polyline>
              </svg>
              Copied
            </>
          ) : (
            <>
              <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2">
                <rect x="9" y="9" width="13" height="13" rx="2" ry="2"></rect>
                <path d="M5 15H4a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h9a2 2 0 0 1 2 2v1"></path>
              </svg>
              Copy code
            </>
          )}
        </button>
      </div>
      <SyntaxHighlighter
        language={language}
        style={oneDark}
        customStyle={{
          margin: 0,
          borderRadius: '0 0 8px 8px',
          fontSize: '0.875rem',
          padding: '1rem'
        }}
        showLineNumbers={false}
      >
        {value}
      </SyntaxHighlighter>
    </div>
  );
};

const MarkdownViewer = ({ content }) => {
  // Pre-process content to ensure proper LaTeX formatting
  const processedContent = content
    // Convert inline LaTeX wrapped in \( \) to $...$
    .replace(/\\\((.*?)\\\)/g, '$$$1$$')
    // Convert display LaTeX wrapped in \[ \] to $$...$$
    .replace(/\\\[(.*?)\\\]/g, '\n$$$$\n$1\n$$$$\n')
    // Ensure single $ for inline math
    .replace(/\$\$([^\$\n]+?)\$\$/g, (match, p1) => {
      // If it's already display math (contains newlines or is on its own line), keep it
      if (p1.includes('\n') || match.startsWith('\n')) {
        return match;
      }
      // Otherwise convert to inline
      return `$${p1}$`;
    });

  return (
    <div className="markdown-content">
      <ReactMarkdown
        remarkPlugins={[remarkGfm, remarkMath]}
        rehypePlugins={[rehypeKatex]}
        components={{
          code({ node, inline, className, children, ...props }) {
            const match = /language-(\w+)/.exec(className || '');
            const codeString = String(children).replace(/\n$/, '');
            
            return !inline && match ? (
              <CodeBlock
                language={match[1]}
                value={codeString}
              />
            ) : (
              <code className={className} {...props}>
                {children}
              </code>
            );
          },
          // Ensure proper rendering of paragraphs with math
          p({ children }) {
            return <p>{children}</p>;
          }
        }}
      >
        {processedContent}
      </ReactMarkdown>
    </div>
  );
};

export default MarkdownViewer;