import Lenis from 'lenis';

export function initializeLenis() {
  // Create Lenis instance with optimized settings
  const lenis = new Lenis({
    duration: 0.8,           // Reduced from 1.2 for snappier response
    easing: (t) => Math.min(1, 1.001 - Math.pow(2, -10 * t)), // Improved easing
    smooth: true,
    smoothTouch: false,      // Disable on touch devices for better performance
    touchMultiplier: 2,      // Better touch response
    infinite: false,         // Disable infinite scroll unless needed
    wheelMultiplier: 1,      // Normalized wheel speed
    syncTouch: true,        // Sync touch and wheel events
  });

  // Optimize the animation frame
  let rafId;
  
  function raf(time) {
    lenis.raf(time);
    rafId = requestAnimationFrame(raf);
  }
  
  // Start the animation loop
  requestAnimationFrame(raf);

  // Clean up function
  function destroy() {
    cancelAnimationFrame(rafId);
    lenis.destroy();
  }

  // Handle page visibility changes to prevent background processing
  document.addEventListener('visibilitychange', () => {
    if (document.hidden) {
      lenis.stop();
    } else {
      lenis.start();
    }
  });

  // Optional: Stop scrolling during window resize for better performance
  let resizeTimeout;
  window.addEventListener('resize', () => {
    if (!resizeTimeout) {
      lenis.stop();
    }
    
    clearTimeout(resizeTimeout);
    resizeTimeout = setTimeout(() => {
      lenis.start();
      resizeTimeout = null;
    }, 100);
  });

  return {
    lenis,
    destroy
  };
}