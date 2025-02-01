import gsap from 'gsap';
import './main.js';
import { initWordStack, animateWordsUp, animateWordsDown, playTimeline, pauseTimeline } from './animations/wordstack';

document.addEventListener('DOMContentLoaded', function () {
    var menuOverlay = document.getElementById('menu-overlay');
    var menuContent = document.querySelector('.menu-content');
    var primaryMenuItems = document.querySelectorAll('.primary-menu li');
    var contactItems = document.querySelectorAll('.contact-item');
    var submenuItems = document.querySelectorAll('.submenu-item');
    const menuContainer = document.querySelector('.menu-container');
    const clipBox = document.querySelector('.clip-box');
    const navburgerCircle = document.querySelector('.navburger-circle');
    const lines = document.querySelectorAll('.TheNavigationBurger-line');
    const menuText = document.querySelector('.menu-text');
    const wideContainer = document.getElementById('wide-container');
    gsap.set(lines, { opacity: 0 }); // Set initial state for submenu items

    let wordStackInitialized = false;

    function openMenu() {
        const wordsArray = ['ERIC CLAY MINER'];

        if (!wordStackInitialized) {
            // Initialize the WordStack
            initWordStack(wordsArray);
            wordStackInitialized = true;
        } else {
            // Animate words back up and resume timeline
            animateWordsUp();
            playTimeline();
        }

        menuOverlay.style.visibility = 'visible'; // Make overlay visible
        gsap.set(menuOverlay, { transformOrigin: 'bottom center' });
        gsap.set(menuContent, { opacity: 1 }); // Ensure menu content is visible
        gsap.set(primaryMenuItems, { opacity: 0, y: 50 }); // Set initial state for primary menu items
        gsap.set(contactItems, { opacity: 0, y: 50 }); // Set initial state for contact items
        gsap.set(submenuItems, { opacity: 0, y: 50 }); // Set initial state for submenu items

        gsap.fromTo(menuOverlay, 
            { clipPath: 'inset(100% 0 0 0)' }, 
            { duration: 1, clipPath: 'inset(0% 0 0 0)', ease: 'power4.inOut' });

        // Animate primary menu items
        gsap.fromTo(primaryMenuItems, 
            { opacity: 0, y: 50 }, 
            { duration: 1, opacity: 1, y: 0, stagger: 0.1, ease: 'power4.inOut' });

        // Animate contact items with a slight delay
        gsap.fromTo(contactItems, 
            { opacity: 0, y: 50 }, 
            { duration: 1, opacity: 1, y: 0, stagger: 0.1, ease: 'power4.inOut' });

        // Animate submenu items with a slight delay
        gsap.fromTo(submenuItems, 
            { opacity: 0, y: 50 }, 
            { duration: 1, opacity: 1, y: 0, stagger: 0.1, ease: 'power4.inOut' });
    }

    function closeMenu() {
        gsap.set(menuOverlay, { transformOrigin: 'top center' });

        // Create a timeline for the closing animation
        const closeTimeline = gsap.timeline({
            onComplete: function() {
               // pauseTimeline();
                menuOverlay.style.visibility = 'hidden'; // Hide overlay after animation
                gsap.set(primaryMenuItems, { opacity: 0, y: 50 }); // Hide primary menu items when menu is closed
                gsap.set(contactItems, { opacity: 0, y: 50 }); // Hide contact items when menu is closed
                gsap.set(submenuItems, { opacity: 0, y: 50 }); // Hide submenu items when menu is closed
               // destroyWordStack();
            }
        });

        // Animate words down and mask slide up concurrently
        closeTimeline
            .to('.word-svg', {
                y: '100%',
                opacity: 0,
                duration: 0.8,
                stagger: 0.1,
                ease: 'power3.in'
            }, 0)
            .fromTo(menuOverlay, 
                { clipPath: 'inset(0% 0 0 0)' }, 
                { duration: 0.8, clipPath: 'inset(0% 0% 100% 0)', ease: 'power2.inOut' }, 0);
    }

    function destroyWordStack() {
        // Remove all child elements from the wideContainer
        while (wideContainer.firstChild) {
            wideContainer.removeChild(wideContainer.firstChild);
        }
        // Optional: Remove any GSAP animations
        gsap.killTweensOf('*');
    }

    menuContainer.addEventListener('mouseenter', () => {
        console.log('Mouse entered menu container');
        if (!menuContainer.classList.contains('open')) {
            console.log('Menu container is not open, applying transformations');

            gsap.to(navburgerCircle, {
                clipPath: 'circle(36%)',
                duration: 0.3,
                ease: 'power1.inOut'
            });
            gsap.to(lines, {
                opacity: 1,
                duration: 0.3,
                ease: 'power1.inOut'
            });
            gsap.to(menuText, {
                x: -6, /* Slide text to the left */
                duration: 0.3,
                ease: 'power1.inOut',
                onUpdate: () => {
                    const transformValue = gsap.getProperty(menuText, 'x');
                    console.log('Updating transform', transformValue);
                },
                onComplete: () => console.log('Transform complete')
            });
        }
    });

    menuContainer.addEventListener('mouseleave', () => {
        console.log('Mouse left menu container');
        if (!menuContainer.classList.contains('open')) {
            console.log('Menu container is not open, reverting transformations');

            gsap.to(navburgerCircle, {
                clipPath: 'circle(10%)',
                duration: 0.3,
                ease: 'power1.inOut'
            });
            gsap.to(lines, {
                opacity: 0,
                duration: 0.3,
                ease: 'power1.inOut'
            });
            gsap.to(menuText, {
                x: '0rem',
                duration: 0.3,
                ease: 'power1.inOut',
                onUpdate: () => {
                    const transformValue = gsap.getProperty(menuText, 'x');
                    console.log('Updating transform', transformValue);
                },
                onComplete: () => console.log('Transform complete')
            });
        }
    });

    menuContainer.addEventListener('click', () => {
        menuContainer.classList.toggle('open');
        if (menuContainer.classList.contains('open')) {
            openMenu();
            gsap.to(clipBox, {
                clipPath: 'inset(0 0 0 calc(100% - 50px) round 25px)',
                duration: 0.3,
                ease: 'power1.inOut'
            });
            gsap.to(navburgerCircle, {
                clipPath: 'circle(75%)',
                duration: 0.3,
                ease: 'power1.inOut'
            });
            gsap.to(navburgerCircle, {
                scale: 1, /* Scale the circle 10% larger on click */
                duration: 0.3,
                ease: 'power1.inOut'
            });
            gsap.to(lines[0], {
                opacity: 1,
                rotate: 45,
                y: 0,
                z: 0,
                duration: 0.3,
                ease: 'power1.inOut'
            });
            gsap.to(lines[1], {
                opacity: 1,
                rotate: -45,
                y: 0,
                z: 0,
                duration: 0.3,
                ease: 'power1.inOut'
            });
        } else {
            closeMenu();
            gsap.to(clipBox, {
                clipPath: 'inset(0 0 0 0 round 25px)',
                duration: 0.5,
                ease: 'power1.inOut'
            });
            gsap.to(navburgerCircle, {
                clipPath: 'circle(10%)',
                duration: 0.5,
                ease: 'power1.inOut'
            });
            gsap.to(navburgerCircle, {
                scale: 1,
                duration: 0.5,
                ease: 'power1.inOut'
            });
            gsap.to(lines, {
                opacity: 0,
                duration: 0.5,
                ease: 'power1.inOut',
                onComplete: () => {
                    gsap.set(lines, { opacity: 0 }); // Ensure opacity is reset
                }
            });
            gsap.to(lines[0], {
                rotate: 0,
                y: -4,
                z: 0,
                duration: 0.5,
                ease: 'power1.inOut'
            });
            gsap.to(lines[1], {
                rotate: 0,
                y: 4,
                z: 0,
                duration: 0.5,
                ease: 'power1.inOut'
            });
        }
    });
});
