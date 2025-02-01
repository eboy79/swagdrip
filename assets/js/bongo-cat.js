function initBongoCat() {
    const leftPaw = document.querySelector('#bongo-cat .paw.left');
    const rightPaw = document.querySelector('#bongo-cat .paw.right');
    
    console.log('Bongo Cat Init'); // Debug

    if (!leftPaw || !rightPaw) {
        console.error('Bongo Cat paws not found');
        return;
    }

    function animatePaw(paw) {
        // Reset animation
        paw.style.animation = 'none';
        paw.offsetHeight; // Force reflow
        // Start new animation
        paw.style.animation = '';
        paw.classList.add('typing');
        
        // Remove class after animation completes
        setTimeout(() => {
            paw.classList.remove('typing');
        }, 100);
    }

    function isLeftSideKey(code) {
        return ['KeyQ','KeyW','KeyE','KeyR','KeyT',
                'KeyA','KeyS','KeyD','KeyF','KeyG',
                'KeyZ','KeyX','KeyC','KeyV','KeyB'].includes(code);
    }

    function isRightSideKey(code) {
        return ['KeyY','KeyU','KeyI','KeyO','KeyP',
                'KeyH','KeyJ','KeyK','KeyL',
                'KeyN','KeyM'].includes(code);
    }

    window.addEventListener('keydown', (e) => {
        console.log('Key pressed:', e.code); // Debug
        
        // Prevent repeating animations on key hold
        if (e.repeat) return;

        if (isLeftSideKey(e.code)) {
            animatePaw(leftPaw);
        } else if (isRightSideKey(e.code)) {
            animatePaw(rightPaw);
        }
    }, false);
}

export { initBongoCat };  // Use this style of export
