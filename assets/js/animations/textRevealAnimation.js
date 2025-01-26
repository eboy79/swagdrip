// src/js/animations/textRevealAnimation.js
import "splitting/dist/splitting.css";
import "splitting/dist/splitting-cells.css";
import Splitting from "splitting";
import gsap from 'gsap';

export function applyTextRevealAnimation(selector) {
    console.log(`Running applyTextRevealAnimation for selector: ${selector}`);
    
    const section = document.querySelector(selector);

    if (!section) {
        console.error(`No element found for selector: ${selector}`);
        return;
    }

    console.log(`Element found for selector: ${selector}`, section);

    Splitting({ target: section });

    const chars = section.querySelectorAll('.char');

    if (chars.length === 0) {
        console.error('No characters found for animation.');
        return;
    }

    console.log(`Characters found: ${chars.length}`, chars);

    const charsArray = Array.from(chars);

    const timeline = gsap.timeline({ paused: true });

    gsap.set(charsArray, { y: '100%', opacity: 0 });

    timeline.to(charsArray, {
        y: '0%',
        opacity: 1,
        ease: 'power3.out',
        stagger: 0.05,
        duration: 0.5
    });

    timeline.play();
}
