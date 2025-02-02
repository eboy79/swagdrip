import './page-load-info.js';
import { VFX } from "@vfx-js/core";
import { gsap } from "gsap";
import { CustomEase } from 'gsap/CustomEase';
import { ScrollTrigger } from "gsap/ScrollTrigger";
import { initializeNavigation } from './navigation';
import { initializeLenis } from "./lenis"; 
import { initInfiniteScroll } from './infinite-scroll';
import Prism from "prismjs";
import "prismjs/themes/prism-tomorrow.css";
import { ForwardShaderAnimation } from './animations/ShaderAnimation';
let currentShaderAnim;
gsap.registerPlugin(CustomEase, ScrollTrigger);

CustomEase.create("subtleExpoEase", "M0,0 C0.25,1 0.5,1 1,1");
CustomEase.create("mediumExpoEase", "M0,0 C0.42,1 0.58,1 1,1");
CustomEase.create("strongExpoEase", "M0,0 C0.68,1 0.86,1 1,1");
CustomEase.create("veryStrongExpoEase", "M0,0 C0.8,1 0.9,1 1,1");
CustomEase.create("ultraStrongExpoEase", "M0,0 C0.9,1 0.95,1 1,1");

gsap.defaults({ ease: "none", duration: 1 });

function initializeScripts() {
    initializeLenis(); 
    initInfiniteScroll();
    // Navigation will receive the shader instance later.
    initializeNavigation();
}

document.addEventListener('DOMContentLoaded', function () {
    initializeScripts();

    // (Code for copy buttons, etc.)
    const codeBlocks = document.querySelectorAll(".wp-block-preformatted");
    codeBlocks.forEach(block => {
        const copyButton = document.createElement("button");
        copyButton.className = "copy-button";
        copyButton.innerHTML = `<span class="copy-icon">📋</span><span class="copy-text">Copy</span>`;
        block.style.position = "relative";
        block.appendChild(copyButton);
        copyButton.addEventListener("click", async () => {
            const code = block.querySelector("code").textContent;
            try {
                await navigator.clipboard.writeText(code);
                copyButton.classList.add("copied");
                copyButton.querySelector(".copy-text").textContent = "Copied!";
                setTimeout(() => {
                    copyButton.classList.remove("copied");
                    copyButton.querySelector(".copy-text").textContent = "Copy";
                }, 2000);
            } catch (err) {
                console.error("Failed to copy:", err);
                copyButton.querySelector(".copy-text").textContent = "Error!";
            }
        });
    });

// Create the shader animation for your logo element.
const logoElement = document.getElementById('img');

  // Create an instance using the forward class.
  currentShaderAnim = new ForwardShaderAnimation(logoElement, {
    uniforms: {      
      mode: 1,      
      width: 0.2,   
      layers: 3,    
      speed: 0.75,  
      delay: 0,
      enterTime: 0,  // This controls the shader state (0 = logo visible, 1 = logo hidden)
      leaveTime: 0,
    }
  });
    // Create an instance using the forward class.
    const shaderAnim = new ForwardShaderAnimation(logoElement);
    shaderAnim.play();

    Prism.highlightAll();
});

export { initInfiniteScroll, initializeLenis, initializeNavigation };
