import { gsap } from 'gsap';
import { ScrollTrigger } from 'gsap/ScrollTrigger';
import { initializeLenis } from './lenis'; 

gsap.registerPlugin(ScrollTrigger);

import Prism from 'prismjs';


// Import a theme (optional) - choose one from available themes
import 'prismjs/themes/prism-tomorrow.css';


function initializeScripts() {
  initializeLenis(); 
}

document.addEventListener('DOMContentLoaded', function () {
  // Find all code blocks
  const codeBlocks = document.querySelectorAll('.wp-block-preformatted');
  
  codeBlocks.forEach(block => {
    // Create copy button
    const copyButton = document.createElement('button');
    copyButton.className = 'copy-button';
    copyButton.innerHTML = `
      <span class="copy-icon">📋</span>
      <span class="copy-text">Copy</span>
    `;
    
    // Add button to code block
    block.style.position = 'relative';
    block.appendChild(copyButton);
    
    // Add click handler
    copyButton.addEventListener('click', async () => {
      const code = block.querySelector('code').textContent;
      
      try {
        await navigator.clipboard.writeText(code);
        copyButton.classList.add('copied');
        copyButton.querySelector('.copy-text').textContent = 'Copied!';
        
        // Reset button after 2 seconds
        setTimeout(() => {
          copyButton.classList.remove('copied');
          copyButton.querySelector('.copy-text').textContent = 'Copy';
        }, 2000);
      } catch (err) {
        console.error('Failed to copy:', err);
        copyButton.querySelector('.copy-text').textContent = 'Error!';
      }
    });
  });
  initializeScripts();
  Prism.highlightAll();
});

export { initializeLenis };
