import "splitting/dist/splitting.css";
import "splitting/dist/splitting-cells.css";
import gsap from 'gsap';
import Splitting from 'splitting';

export function applyTextHideAnimation(selector) {

        // Initialize Splitting to split text for animation
        Splitting();

        // Define the DOM elements
        const section = document.querySelector(selector);
        const chars = section.querySelectorAll('.char');

        // GSAP Timeline
        const timeline = gsap.timeline({ paused: true });

        // Initial setup for characters, on-screen (visible)
        gsap.set(chars, { y: '0%', opacity: 1 });

        // Animation timeline for hiding characters
        timeline.to(chars, {
            y: '-100%', // Move upwards
            opacity: 0, // Fade out
            ease: 'power3.in',
            stagger: 0.05, // Adjust stagger value as needed
            duration: 0.5
        });
        timeline.play();

}
