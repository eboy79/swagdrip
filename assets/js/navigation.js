import { gsap, ScrollTrigger } from 'gsap/all';
gsap.registerPlugin(ScrollTrigger);

export function initializeNavigation() {
    console.log('Initializing Navigation');
    const navToggle = document.getElementById('nav-toggle');
    const navMenu = document.getElementById('nav-menu');
    let menuOpen = false;

    if (navToggle && navMenu) {
        console.log('Navigation elements found');
        navToggle.addEventListener('click', () => {
            console.log('Toggle Menu Clicked');
            if (!menuOpen) {
                gsap.to(navMenu, { duration: 0.5, width: '100%' });
                gsap.to(navMenu.querySelector('ul'), { duration: 0.5, opacity: 1, delay: 0.5 });
                const navLinks = navMenu.querySelectorAll('ul li');
                navLinks.forEach((link, index) => {
                    gsap.fromTo(link, { opacity: 0, y: -20 }, { opacity: 1, y: 0, delay: 0.6 + index * 0.1, duration: 0.5 });
                });
                menuOpen = true;
            } else {
                gsap.to(navMenu.querySelector('ul'), { duration: 0.3, opacity: 0 });
                gsap.to(navMenu, { duration: 0.5, width: '0', delay: 0.3 });
                menuOpen = false;
            }
        });
    }
}
