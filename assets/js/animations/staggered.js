import { gsap } from 'gsap/all';

const DOM = {
    content: {
        about: {
            section: document.querySelector('.content__item--about'),
            get chars() {
                // Now selecting the whole paragraphs
                return this.section.querySelectorAll('.content__paragraph');
            },
            get picture() {
                return this.section.querySelector('.content__figure');
            },
            isVisible: false
        }
    },
    links: {
        about: document.querySelector('a.frame__about')
    }
};

const timelineSettings = {
    staggerValue: 0.3,  // Adjusted for less granularity since we're now dealing with fewer, larger elements
    charsDuration: 0.5
};

const timeline = gsap.timeline({ paused: true })
    .set(DOM.content.about.chars, {
        y: '100%',  // Initial position off-screen
        opacity: 0
    })
    .to(DOM.content.about.chars, {
        duration: timelineSettings.charsDuration,
        ease: 'Power3.easeOut',
        y: '0%',
        opacity: 1,
        stagger: timelineSettings.staggerValue
    })
    .to(DOM.content.about.picture, {
        duration: 0.8,
        ease: 'Power3.easeOut',
        y: '0%',
        opacity: 1,
        rotation: 0
    }, '-=0.4');  // Overlap with the end of the text animation

// Event listeners for interaction
DOM.links.about.addEventListener('click', () => {
    if (!DOM.content.about.isVisible) {
        timeline.play();
        DOM.content.about.isVisible = true;
    } else {
        timeline.reverse();
        DOM.content.about.isVisible = false;
    }
});

export { timeline };
