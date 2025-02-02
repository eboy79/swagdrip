import { VFX } from "@vfx-js/core";
import { gsap } from "gsap";

const defaultShader = `
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
    vec4 K = vec4(1.0, 2.0/3.0, 1.0/3.0, 3.0);
    vec3 p = abs(fract(c.xxx + K.xyz)*6.0 - K.www);
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
  
  vec4 draw(vec2 uv, vec2 p, float t, float scale) {
    vec4 c = readTex(uv);
    vec2 pi = floor(p * scale);
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

    if (enterTime <= 0.0 && leaveTime <= 0.0) {
      gl_FragColor = vec4(0.0, 0.0, 0.0, 0.0);  // Fully transparent
    } else {
      gl_FragColor = draw(uv, p, t, 10.);
    }
  }
`;

class ForwardShaderAnimation {
  constructor(element, options = {}) {
    this.options = Object.assign({
      shader: defaultShader,
      overflow: 30,
      uniforms: {
        mode: 1,
        width: 0.2,
        layers: 3,
        speed: 0.75,
        delay: 0,
        enterTime: 0,  // Start fully hidden
        leaveTime: 0,
      }
    }, options);

    this.element = element;
    this.vfx = new VFX();
    this.shaderAdded = false;
  }

  play() {
    if (!this.shaderAdded) {
      this.vfx.add(this.element, {
        shader: this.options.shader,
        overflow: this.options.overflow,
        uniforms: this.options.uniforms
      });
      this.shaderAdded = true;
    }

    console.log("Revealing image: enterTime -> 1");
    gsap.to(this.options.uniforms, {
      duration: 1,
      enterTime: 1,  // Reveal image
      ease: "power3.out",
      onUpdate: () => {
        if (this.vfx.update) {
          this.vfx.update();
        }
      }
    });
  }

  reversePlay() {
    console.log("Hiding image: enterTime -> 0");
    gsap.to(this.options.uniforms, {
      duration: 1,
      enterTime: 0,  // Hide image
      ease: "power3.inOut",
      onUpdate: () => {
        if (this.vfx.update) {
          this.vfx.update();
        }
      }
    });
  }
}

export { ForwardShaderAnimation };