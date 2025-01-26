import { gsap } from 'gsap';
import { ScrollTrigger } from 'gsap/ScrollTrigger';
import { initializeLenis } from './lenis'; 

gsap.registerPlugin(ScrollTrigger);

function initializeScripts() {
  initializeLenis(); 
}

document.addEventListener('DOMContentLoaded', function () {
  initializeScripts();
});

export { initializeLenis };
