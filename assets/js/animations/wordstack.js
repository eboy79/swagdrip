import { gsap } from 'gsap';
import { svgChars, kerningPairs } from './svgChars';

const svgColor = "#fff";
const letterSpacingMultiplier = 4.5; // Increase this value to add more space between letters

function applyKerning(groups, kerningPairs, baseSpacing) {
    let currentX = 0;
    groups.forEach((g, index) => {
        const letter = g.getAttribute("data-letter");
        const width = parseInt(g.getAttribute("data-width"));

        g.setAttribute("transform", `translate(${currentX}, 800) scale(1, -1)`);
        currentX += width;

        if (index < groups.length - 1) {
            const nextLetter = groups[index + 1].getAttribute("data-letter");
            if (letter !== ' ' && nextLetter !== ' ') {  // Skip kerning adjustments for spaces
                const pair = letter + nextLetter;
                let adjustment = baseSpacing * letterSpacingMultiplier;
                if (kerningPairs[pair]) {
                    adjustment += kerningPairs[pair];
                }
                currentX += adjustment;
            }
        }
    });
}


function generateCombinedSVG(letters, combinedViewBox, wordClass, maxHeight) {
    const svgNS = "http://www.w3.org/2000/svg";
    const svg = document.createElementNS(svgNS, "svg");
    svg.setAttribute("viewBox", combinedViewBox);
    svg.setAttribute("xmlns", svgNS);
    svg.setAttribute("class", wordClass);
    svg.setAttribute("height", "100%");
    svg.setAttribute("shape-rendering", "geometricPrecision");
    svg.style.opacity = "1";

    let totalWidth = 0;
    const groups = [];
    const heightPercentage = 0.1;
    const baseSpacing = 200 * heightPercentage;
    const spaceWidth = baseSpacing * letterSpacingMultiplier * 2; // Adjust this to control space size

    letters.forEach(letter => {
        if (letter === ' ') {
            const g = document.createElementNS(svgNS, "g");  // Dummy group for space
            g.setAttribute("data-letter", " ");
            g.setAttribute("data-width", spaceWidth);
            svg.appendChild(g);
            groups.push(g);
            totalWidth += spaceWidth;  // Increment totalWidth for space
        } else {
            const letterData = svgChars[letter.toUpperCase()];
            if (letterData) {
                const g = document.createElementNS(svgNS, "g");
                g.setAttribute("class", `letter-${letter.toLowerCase()}`);
                g.setAttribute("data-letter", letter.toUpperCase());
                g.setAttribute("data-width", letterData.viewBox.split(' ')[2]);
                const path = document.createElementNS(svgNS, "path");
                path.setAttribute("d", letterData.path);
                path.setAttribute("class", `letter-${letter.toLowerCase()}-path`);
                path.setAttribute("fill", svgColor);
                g.appendChild(path);
                svg.appendChild(g);
                groups.push(g);
                totalWidth += parseInt(letterData.viewBox.split(' ')[2]) + baseSpacing * letterSpacingMultiplier;
            } else {
                console.error(`No SVG data found for letter: ${letter}`);
            }
        }
    });

    applyKerning(groups, kerningPairs, baseSpacing);

    const aspectRatio = totalWidth / 800;
    const scaledWidth = 200 * aspectRatio;
    const maxHeightScale = maxHeight / 200;
    svg.setAttribute("width", `${scaledWidth * maxHeightScale}px`);
    svg.setAttribute("height", `${200 * maxHeightScale}px`);
    return svg;
}


function displayWords(words, maxHeight) {
    const wideContainer = document.getElementById("wide-container");
    wideContainer.innerHTML = "";

    words.forEach(word => {
        const totalWidth = word.split('').reduce((acc, letter) => {
            if (letter === ' ') {
                return acc + (200 * 0.1 * letterSpacingMultiplier * 2); // Extra spacing for spaces
            } else {
                return acc + parseInt(svgChars[letter.toUpperCase()].viewBox.split(' ')[2]) + (200 * 0.1 * letterSpacingMultiplier);
            }
        }, 0);

        const endPadding = 0; // Adjust this to control space at the end
        const combinedViewBox = `0 0 ${totalWidth + endPadding} 800`;

        const svgElement = generateCombinedSVG(word.split(''), combinedViewBox, `word-svg word-${word.replace(/\s/g, '-').toLowerCase()}`, maxHeight);
        wideContainer.appendChild(svgElement);
    });

    animateIntroAndSlide();
}


let slideTimeline;

function animateIntroAndSlide() {
    const words = document.querySelectorAll('.word-svg');
    const svgContainer = document.getElementById("svg-container");
    const wideContainer = document.getElementById("wide-container");
    const totalWidth = wideContainer.offsetWidth;
    const padding = 0;

    const clonedContainer = wideContainer.cloneNode(true);
    clonedContainer.style.left = `${totalWidth + padding}px`;
    svgContainer.appendChild(clonedContainer);

    if (slideTimeline) {
        slideTimeline.kill();
    }

    slideTimeline = gsap.timeline({ repeat: -1, ease: "none" })
        .to([wideContainer, clonedContainer], { x: -totalWidth, duration: 20, ease: "none" })
        .set(wideContainer, { x: 0 })
        .set(clonedContainer, { x: totalWidth + padding });

    words.forEach((word, index) => {
        gsap.from(word, {
            y: 840,
            duration: 1.8,
            ease: "power3.out",
            delay: index * 1.6
        });
    });

    slideTimeline.play();
}

export function initWordStack(words) {
    displayWords(words, 180);
}

export function animateWordsUp() {
    const words = document.querySelectorAll('.word-svg');
    gsap.to(words, {
        y: 0,
        opacity: 1,
        duration: 1.8,
        ease: "power3.out",
        stagger: 0.1
    });
}

export function animateWordsDown() {
    const words = document.querySelectorAll('.word-svg');
    gsap.to(words, {
        y: 840,
        opacity: 0,
        duration: 1.8,
        ease: "power3.in",
        stagger: 0.1,
        onComplete: pauseTimeline // Pause the timeline after the words slide down
    });
}

export function playTimeline() {
    if (slideTimeline) {
        slideTimeline.play();
    }
}

export function pauseTimeline() {
    if (slideTimeline) {
        slideTimeline.pause();
    }
}
