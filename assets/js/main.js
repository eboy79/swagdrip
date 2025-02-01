import { VFX } from "@vfx-js/core";
import { gsap } from "gsap";
import { ScrollTrigger } from "gsap/ScrollTrigger";
import { initializeLenis } from "./lenis"; 
import Prism from "prismjs";
import "prismjs/themes/prism-tomorrow.css";

export function initializeScripts() {
    initializeLenis();
    // ...existing code...
}



export { initializeLenis };