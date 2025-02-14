// index.js
import gsap from 'gsap';
import './main.js';
import { initWordStack, animateWordsUp, animateWordsDown, playTimeline, pauseTimeline } from './animations/wordstack';
import { ForwardShaderAnimation } from './animations/ShaderAnimation';

document.addEventListener('DOMContentLoaded', function () {

    // Initialize Lenis on page load

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
    const img2Element = document.getElementById('img2');  // Ensure img2 exists in your HTML
    const img2Anim = new ForwardShaderAnimation(img2Element);  // Initialize the animation
    const logoElement = document.getElementById('img');

    const shaderAnim = new ForwardShaderAnimation(logoElement);

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

        // Disable background scrolling via JS (optional if you’re using CSS)
        // Pause Lenis scrolling


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
                // Hide overlay after animation
                menuOverlay.style.visibility = 'hidden';
                gsap.set(primaryMenuItems, { opacity: 0, y: 50 }); // Hide primary menu items when menu is closed
                gsap.set(contactItems, { opacity: 0, y: 50 }); // Hide contact items when menu is closed
                gsap.set(submenuItems, { opacity: 0, y: 50 }); // Hide submenu items when menu is closed
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
        if (!menuContainer.classList.contains('open')) {
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
                ease: 'power1.inOut'
            });
        }
    });

    menuContainer.addEventListener('mouseleave', () => {
        if (!menuContainer.classList.contains('open')) {
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
                ease: 'power1.inOut'
            });
        }
    });

    menuContainer.addEventListener('click', () => {
        menuContainer.classList.toggle('open');
        if (menuContainer.classList.contains('open')) {
            // Add the class to disable scroll via CSS
            document.body.classList.add('overlay-active');
            // Call openMenu() to animate and disable scrolling via JS
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
                scale: 1,
                duration: 0.3,
                ease: 'power1.inOut'
            });
            gsap.to(lines[0], {
                opacity: 1,
                rotate: 45,
                y: 0,
                duration: 0.3,
                ease: 'power1.inOut'
            });
            gsap.to(lines[1], {
                opacity: 1,
                rotate: -45,
                y: 0,
                duration: 0.3,
                ease: 'power1.inOut'
            });
            img2Anim.play(); 
            gsap.to(logoElement, {
                opacity: 0,
                duration: 0.1,
                delay: 0.6,
                ease: 'power1.inOut'
            });
            gsap.to(img2Element, {
                opacity: 1,
                duration: 0.1,
                delay: 0.6,
                ease: 'power1.inOut'
            });
            gsap.set(img2Element, { className: '+=display-blk' });
        } else {
            // Remove the class to re-enable scrolling via CSS
            document.body.classList.remove('overlay-active');
            // Call closeMenu() to animate and re-enable scrolling via JS
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
                    gsap.set(lines, { opacity: 0 });
                }
            });
            gsap.to(lines[0], {
                rotate: 0,
                y: -4,
                duration: 0.5,
                ease: 'power1.inOut'
            });
            gsap.to(lines[1], {
                rotate: 0,
                y: 4,
                duration: 0.5,
                ease: 'power1.inOut'
            });
            gsap.to(logoElement, {
                autoAlpha: 1,
                duration: 0.1,
                delay: 0.6,
                ease: 'power1.inOut'
            });
            gsap.to(img2Element, {
                autoAlpha: 0,
                duration: 0.1,
                delay: 0.6,
                ease: 'power1.inOut',
                onComplete: () => {
                    img2Element.classList.add('display-none');
                }
            });
        }
    });
});

