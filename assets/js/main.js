import './page-load-info.js';
import { VFX } from "@vfx-js/core";
import { gsap } from "gsap";
import { CustomEase } from 'gsap/CustomEase';
import { ScrollTrigger } from "gsap/ScrollTrigger";
import { initializeNavigation } from './navigation';
import { initializeLenis } from "./lenis"; 
import { initInfiniteScroll } from './infinite-scroll';
import Prism from "prismjs";
import "prismjs/themes/prism-tomorrow.css";

gsap.registerPlugin(CustomEase, ScrollTrigger);

CustomEase.create("subtleExpoEase", "M0,0 C0.25,1 0.5,1 1,1");
CustomEase.create("mediumExpoEase", "M0,0 C0.42,1 0.58,1 1,1");
CustomEase.create("strongExpoEase", "M0,0 C0.68,1 0.86,1 1,1");
CustomEase.create("veryStrongExpoEase", "M0,0 C0.8,1 0.9,1 1,1");
CustomEase.create("ultraStrongExpoEase", "M0,0 C0.9,1 0.95,1 1,1");

gsap.defaults({ ease: "none", duration: 1 });
function initializeScripts() {
    // Array of word configurations
const wordsConfig = [
    ['Hello', true],
    ['World', false],
    ['Example', true]
  ];

  initializeLenis(); 
  initInfiniteScroll();
  initializeNavigation();
}
document.addEventListener('DOMContentLoaded', function () {
    initializeScripts();

  // Find all code blocks and add copy button functionality
  
  const codeBlocks = document.querySelectorAll(".wp-block-preformatted");

  codeBlocks.forEach(block => {
    const copyButton = document.createElement("button");
    copyButton.className = "copy-button";
    copyButton.innerHTML = `<span class="copy-icon">📋</span><span class="copy-text">Copy</span>`;
    
    block.style.position = "relative";
    block.appendChild(copyButton);

    copyButton.addEventListener("click", async () => {
      const code = block.querySelector("code").textContent;
      
      try {
        await navigator.clipboard.writeText(code);
        copyButton.classList.add("copied");
        copyButton.querySelector(".copy-text").textContent = "Copied!";
        setTimeout(() => {
          copyButton.classList.remove("copied");
          copyButton.querySelector(".copy-text").textContent = "Copy";
        }, 2000);
      } catch (err) {
        console.error("Failed to copy:", err);
        copyButton.querySelector(".copy-text").textContent = "Error!";
      }
    });
  });

  // ✅ Fixed GLSL Shader String
  const shader = `
precision highp float;
uniform sampler2D src;
uniform vec2 resolution;
uniform vec2 offset;
uniform float time;
uniform float enterTime;
uniform float leaveTime;

uniform int mode;
uniform float layers;
uniform float speed;
uniform float delay;
uniform float width;

#define W width
#define LAYERS layers

vec4 readTex(vec2 uv) {
  if (uv.x < 0. || uv.x > 1. || uv.y < 0. || uv.y > 1.) {
    return vec4(0);
  }
  return texture(src, uv);
}

float hash(vec2 p) {
  return fract(sin(dot(p, vec2(4859., 3985.))) * 3984.);
}

vec3 hsv2rgb(vec3 c) {
  vec4 K = vec4(1.0, 2.0 / 3.0, 1.0 / 3.0, 3.0);
  vec3 p = abs(fract(c.xxx + K.xyz) * 6.0 - K.www);
  return c.z * mix(K.xxx, clamp(p - K.xxx, 0.0, 1.0), c.y);
}

float sdBox(vec2 p, float r) {
  vec2 q = abs(p) - r;
  return min(length(q), max(q.y, q.x));
}

float dir = 1.;

float toRangeT(vec2 p, float scale) {
  float d;
  if (mode == 0) {
    d = p.x / (scale * 2.) + .5; 
  } else if (mode == 1) {
    d = 1. - (p.y / (scale * 2.) + .5); 
  } else if (mode == 2) {
    d = length(p) / scale; 
  }
  d = dir > 0. ? d : (1. - d);
  return d;
}

vec4 cell(vec2 p, vec2 pi, float scale, float t, float edge) {
  vec2 pc = pi + .5;
  vec2 uvc = pc / scale;
  uvc.y /= resolution.y / resolution.x;
  uvc = uvc * 0.5 + 0.5;
  if (uvc.x < 0. || uvc.x > 1. || uvc.y < 0. || uvc.y > 1.) {
    return vec4(0);
  }
  float alpha = smoothstep(.0, .1, texture2D(src, uvc, 3.).a);
  vec4 color = vec4(hsv2rgb(vec3((pc.x * 13. / pc.y * 17.) * 0.3, 1, 1)), 1);
  float x = toRangeT(pi, scale);
  float n = hash(pi);
  float anim = smoothstep(W * 2., .0, abs(x + n * W - t));
  color *= anim;    

  color *= mix(
    1., 
    clamp(.3 / abs(sdBox(p - pc, .5)), 0., 10.),
    edge * pow(anim, 10.)
  ); 
  return color * alpha;
}

vec4 draw(vec2 uv, vec2 p, float t, float scale) {
  vec4 c = readTex(uv);
  vec2 pi = floor(p * scale);
  vec2 pf = fract(p * scale);
  float n = hash(pi);
  t = t * (1. + W * 4.) - W * 2.; 
  float x = toRangeT(pi, scale);
  float a1 = smoothstep(t, t - W, x + n * W);    
  c *= a1;
  return c;
}

void main() {
  vec2 uv = (gl_FragCoord.xy - offset) / resolution;
  vec2 p = uv * 2. - 1.;
  p.y *= resolution.y / resolution.x;
  float t;
  if (leaveTime > 0.) {
    dir = -1.;
    t = clamp(leaveTime * speed, 0., 1.);
  } else {
    t = clamp((enterTime - delay) * speed, 0., 1.);  
  }      
  t = (fract(t * .99999) - 0.5) * dir + 0.5;      
  gl_FragColor = draw(uv, p, t, 10.);
}
`;

  // ✅ Properly Initialize VFX-JS and Apply the Shader Effect
  const logo = document.getElementById("img"); // Ensure logo exists
  if (logo) {
    const vfx = new VFX();
    vfx.add(logo, { 
      shader, 
      overflow: 30, 
      intersection: { threshold: 0.99, once: true }, // 👈 Stops retriggering on scroll
      uniforms: {      
        mode: 1,      
        width: 0.2,   
        layers: 3,    
        speed: 0.75,  
        delay: 0,     
      }
    });

  }

  Prism.highlightAll();


  
});

export { initInfiniteScroll, initializeLenis, initializeNavigation };
