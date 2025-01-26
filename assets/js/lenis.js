import Lenis from 'lenis';



export function initializeLenis() {
  const lenis = new Lenis({
    duration: 1.2,
    easing: (t) => 1 - Math.pow(1 - t, 3),
    smooth: true,
  });

  function raf(time) {
    lenis.raf(time);
    
    requestAnimationFrame(raf);
  }

  requestAnimationFrame(raf);
}
