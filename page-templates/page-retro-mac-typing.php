<?php
/**
 * Template Name: Retro Mac Typing
 * Description: A custom page template that displays a “retro Mac” image and types text on its screen.
 */

// Load the header
get_header();
?>

<style>
/* Basic reset & center */
html, body {
  margin: 0;
  padding: 0;
  background: #333;
}

/* A container to center everything vertically */
.retro-mac-page-wrap {
  min-height: 100vh;
  display: flex;
  justify-content: center;
  align-items: center;
}

/* Container for the Mac image */
#mac-container {
  width: 600px;  /* match your intended display size */
  height: 800px;
  
  /* Use your image URL here */
  background: url('https://mnrdsgn2025.local/wp-content/uploads/2025/01/jason-leung-VeUSCLJrLf4-unsplash.jpg') 
              no-repeat center center;
  background-size: cover;
  
  position: relative;
}

/* The overlay (typing region) on the “screen” */
#screen-text {
  position: absolute;
  
  /* Adjust these so the overlay lines up with the Mac’s black screen */
  top: 140px;    /* Move text up/down */
  left: 120px;   /* Move text left/right */
  width: 360px;  /* Adjust to fit the width of the screen area */
  height: 260px; /* Adjust for correct height */

  /* Retro terminal styling */
  color: #0f0;
  background-color: transparent;
  font-family: "Courier New", Courier, monospace;
  font-size: 18px;
  
  /* Important to preserve spacing/newlines */
  white-space: pre;
  
  /* Overflow hidden for that old CRT look */
  overflow: hidden;
  padding: 10px;
}
</style>

<div class="retro-mac-page-wrap">
  <div id="mac-container">
    <div id="screen-text">
      <span id="typed-content"></span>
    </div>
  </div>
</div>

<script>
  // The text to “type out”
  const textToType = `Welcome to Macintosh
Starting up...
> ls
Desktop   Documents   System
>
`;

  // Reference to the typed-content element
  const typedContent = document.getElementById('typed-content');

  // Track our current position in textToType
  let idx = 0;

  // Control the typing speed (ms delay between characters)
  const typingSpeed = 50;

  function typeCharacter() {
    if (idx < textToType.length) {
      typedContent.textContent += textToType.charAt(idx);
      idx++;
      setTimeout(typeCharacter, typingSpeed);
    }
  }

  // Initiate typing when the page loads
  window.addEventListener('load', typeCharacter);
</script>

<?php
// Load the footer
get_footer();
