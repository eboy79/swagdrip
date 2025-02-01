

import { VFX } from '@vfx-js/core';



  //const img = document.querySelector('#img');
  const el = document.querySelector('#div');

  const vfx = new VFX();
  //vfx.add(img, { shader: "glitch", overflow: 100 });
  //vfx.add(img, { shader: "glitch", overflow: 100 });

  const shader = `
  precision highp float;
  uniform vec2 resolution;
  uniform vec2 offset;
  uniform float time;
  uniform sampler2D src;
  uniform float scroll;
  out vec4 outColor;
  
  void main (void) {
      vec2 uv = (gl_FragCoord.xy - offset) / resolution;
  
      // Scroll X
      uv.x = fract(uv.x + scroll + time * 0.08);
  
      outColor = texture2D(src, uv);
  }
  `;
  
  vfx.add(el, {
      shader,
      uniforms: {
          // Uniform functions are evaluated every frame
          scroll: () => window.scrollY / window.innerHeight,
      }
  });
  //vfx.add(img, { shader: "rgbShift" });
  vfx.add(img, { shader: "rainbow" });
            
