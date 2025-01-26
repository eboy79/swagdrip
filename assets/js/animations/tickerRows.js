import { gsap } from 'gsap';
import { svgChars, kerningPairs } from './svgChars';
gsap.ticker.fps(240)
const pastelColors = [
    '#6FBBD1', '#4AA2B9', '#FF6F71', '#FF8891',
    '#FFAD59', '#FFC77D', '#FFFF5C', '#FFFF73',
    '#78DA79', '#88F089', '#6ABF73', '#8CBF7E',
    '#D17FC6', '#FFA07A', '#E39D95', '#F4A5A9'
];

function getDifferentColor(excludedColors) {
    let newColor;
    do {
        newColor = pastelColors[Math.floor(Math.random() * pastelColors.length)];
    } while (excludedColors.includes(newColor));
    return newColor;
}

function colorizeLetters(svgText, color) {
    const parser = new DOMParser();
    const doc = parser.parseFromString(svgText, "image/svg+xml");
    const paths = doc.querySelectorAll('path');
    paths.forEach(path => {
        path.setAttribute('fill', color);
    });
    return new XMLSerializer().serializeToString(doc);
}

function applyKerning(container, kerningPairs, baseSpacing, dimensions, addSpacingToEnd) {
    const svgs = container.querySelectorAll('.svg-letter');
    let totalWidth = 0;

    svgs.forEach((svg, index) => {
        const width = dimensions[index].width;
        totalWidth += width;

        if (index < svgs.length - 1) {
            const currentPair = svg.dataset.letter.toUpperCase() + svgs[index + 1].dataset.letter.toUpperCase();
            let adjustment = baseSpacing;

            if (kerningPairs[currentPair]) {
                adjustment += kerningPairs[currentPair];
            }

            svg.style.marginRight = `${adjustment}px`;
            totalWidth += adjustment;
        }
    });

    if (addSpacingToEnd) {
        totalWidth += baseSpacing;
        const paddingDiv = document.createElement('div');
        paddingDiv.style.width = `${baseSpacing}px`;
        container.appendChild(paddingDiv);
    }

    return totalWidth;
}

function adjustDimensionsAndAnimate(word, row, container, dimensions, index, addSpacingToEnd) {
    let maxHeight = Math.max(...dimensions.map(dim => dim.height));
    let baseSpacing = maxHeight * 0.1;

    const totalWidth = applyKerning(container, kerningPairs, baseSpacing, dimensions, addSpacingToEnd);

    container.style.width = `${totalWidth}px`;
    container.style.height = `${maxHeight}px`;

    const rowWidth = row.getBoundingClientRect().width;
    const scaleFactor = rowWidth / totalWidth;

    row.style.height = `${maxHeight * scaleFactor}px`;

    gsap.set(container, {
        scale: scaleFactor,
        transformOrigin: 'left top'
    });

    row.style.visibility = 'hidden';

    let clone = container.cloneNode(true);
    row.appendChild(clone);

    const direction = (index % 2 === 0) ? scaleFactor * totalWidth : -scaleFactor * totalWidth;
    gsap.set(clone, { x: direction });

    const baseDuration = 30;
    const animationDuration = baseDuration / (1 + 0.00 * index);
    const animationDirection = index % 2 === 0 ? -scaleFactor * totalWidth : scaleFactor * totalWidth;

    row.classList.remove('hidden');
    row.classList.add('loaded');

    container.style.visibility = 'visible';
    clone.style.visibility = 'visible';
    animateLetters(container, clone);

    gsap.to([container, clone], {
        x: `+=${animationDirection}`,
        duration: animationDuration,
        ease: "none",
        force3D: true,

        autoAlpha:1,
        repeat: -1,
        modifiers: {
            x: gsap.utils.unitize(x => parseFloat(x) % (scaleFactor * totalWidth))
        },
        onStart: () => {
            row.style.visibility = 'visible';
        }
    });
}

function ensureDifferentColors(container) {
    const svgs = container.children;
    const usedColors = new Set();

    for (let i = 0; i < svgs.length; i++) {
        const svg = svgs[i];
        let newColor = getDifferentColor(Array.from(usedColors));
        if (i > 0 && newColor === svgs[i - 1].dataset.currentColor) {
            newColor = getDifferentColor([svgs[i - 1].dataset.currentColor]);
        }

        svg.dataset.currentColor = newColor;
        usedColors.add(newColor);

        const coloredSVG = colorizeLetters(svg.getAttribute('data-svg'), newColor);
        svg.innerHTML = coloredSVG;
    }

    const firstSVG = svgs[0];
    const lastSVG = svgs[svgs.length - 1];
    if (firstSVG.dataset.currentColor === lastSVG.dataset.currentColor) {
        let newColor = getDifferentColor([firstSVG.dataset.currentColor]);
        lastSVG.dataset.currentColor = newColor;
        const lastSVGSvg = colorizeLetters(lastSVG.getAttribute('data-svg'), newColor);
        lastSVG.innerHTML = lastSVGSvg;
    }
}

function animateLetters(container, clone) {
    const svgs = [...container.children, ...clone.children];
    gsap.fromTo(svgs, {
        y: '105%'
    }, {
        y: '0%',
        opacity: 1,
        force3D: true,
        duration: .8,
        ease: 'power3.inOut',
        stagger: 0.03
    });
}

function generateSVGElement(letter, color) {
    const letterData = svgChars[letter.toUpperCase()];
    if (!letterData) {
        console.error(`No SVG data found for letter: ${letter}`);
        return null;
    }

    const adjustedPath = `<path d="${letterData.path}" transform="scale(1, -1) translate(0, -${letterData.viewBox.split(' ')[3]})" fill="${color}"/>`;
    const svgText = `<svg xmlns="http://www.w3.org/2000/svg" viewBox="${letterData.viewBox}" preserveAspectRatio="xMidYMid meet">
                        ${adjustedPath}
                     </svg>`;
    return svgText;
}

export function initTickerRows(wordsConfig) {
    const rowsContainer = document.querySelector('.content-wrapper');
    rowsContainer.innerHTML = '';

    wordsConfig.forEach(([word, addSpacingToEnd], index) => {
        const row = document.createElement('div');
        row.className = 'ticker-row';
        rowsContainer.appendChild(row);

        let container = document.createElement('div');
        container.className = 'ticker-container pb-4';
        container.style.visibility = 'hidden';

        let dimensions = word.split('').map(letter => {
            const letterData = svgChars[letter.toUpperCase()];
            if (!letterData) {
                console.error(`No SVG data found for letter: ${letter}`);
                return null;
            }
            return {
                width: parseFloat(letterData.viewBox.split(' ')[2]),
                height: parseFloat(letterData.viewBox.split(' ')[3]),
                svgText: generateSVGElement(letter, getDifferentColor([]))
            };
        }).filter(Boolean);

        dimensions.forEach((dim, i) => {
            let svgElement = document.createElement('div');
            svgElement.innerHTML = dim.svgText;
            svgElement.className = 'svg-letter';
            svgElement.dataset.letter = word[i];
            svgElement.dataset.svg = dim.svgText;
            container.appendChild(svgElement);
        });

        row.appendChild(container);

        ensureDifferentColors(container);
        setTimeout(() => {
            console.log('All SVG images loaded.');
            adjustDimensionsAndAnimate(word, row, container, dimensions, index, addSpacingToEnd);
            row.classList.add('loaded');
        }, 100);
    });

    rowsContainer.classList.remove('hidden');
}

