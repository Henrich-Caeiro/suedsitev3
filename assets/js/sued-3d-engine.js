/**
 * SUED Studio — Global 3D Engine
 *
 * Implements a continuous, morphing particle system that transitions
 * seamlessly across sections as the user scrolls.
 */
(function () {
  'use strict';

  var MAX_RETRIES = 20;
  var RETRY_MS = 250;

  function initEngine(attempt) {
    attempt = attempt || 0;
    if (typeof THREE === 'undefined' || typeof gsap === 'undefined' || typeof ScrollTrigger === 'undefined') {
      if (attempt < MAX_RETRIES) {
        setTimeout(function () { initEngine(attempt + 1); }, RETRY_MS);
      }
      return;
    }

    gsap.registerPlugin(ScrollTrigger);

    /* ===================== Canvas & DOM ===================== */
    var canvas = document.createElement('canvas');
    canvas.className = 'sued-global-canvas';
    canvas.setAttribute('aria-hidden', 'true');
    document.body.prepend(canvas);

    var w = window.innerWidth;
    var h = window.innerHeight;

    /* ===================== Renderer ===================== */
    var renderer = new THREE.WebGLRenderer({
      canvas: canvas,
      alpha: true,
      antialias: true,
      powerPreference: 'high-performance'
    });
    renderer.setPixelRatio(Math.min(window.devicePixelRatio, 1.5));
    renderer.setSize(w, h);

    var scene = new THREE.Scene();
    
    // Add subtle ambient fog to hide distant particles
    scene.fog = new THREE.FogExp2(0x0a1118, 0.025);

    var camera = new THREE.PerspectiveCamera(45, w / h, 0.1, 100);
    camera.position.z = 15;

    /* ===================== Data Structures ===================== */
    var COUNT = 2500;
    
    var pos0 = new Float32Array(COUNT * 3); // Hero (Chaos)
    var pos1 = new Float32Array(COUNT * 3); // Positioning (Icosahedron)
    var pos2 = new Float32Array(COUNT * 3); // Process (DNA Helix)
    var pos3 = new Float32Array(COUNT * 3); // Results/Rest (Grid)
    
    var colors = new Float32Array(COUNT * 3);
    var sizes = new Float32Array(COUNT);

    /* ===================== Color Palette ===================== */
    var accentR = 38 / 255,  accentG = 175 / 255, accentB = 255 / 255;
    var lightR  = 245 / 255, lightG  = 242 / 255, lightB  = 236 / 255;
    var darkR   = 15 / 255,  darkG   = 25 / 255,  darkB   = 35 / 255;
    var goldR   = 200 / 255, goldG   = 169 / 255, goldB   = 110 / 255;

    /* ===================== Geometries Generators ===================== */
    // Helper: Icosahedron points
    var icoGeo = new THREE.IcosahedronGeometry(3.5, 1);
    var icoPos = icoGeo.attributes.position.array;
    var icoVerts = icoPos.length / 3;

    for (var i = 0; i < COUNT; i++) {
      var i3 = i * 3;
      var t = Math.random();

      // --- COLORS & SIZES ---
      if (t < 0.1) {
        colors[i3] = goldR; colors[i3+1] = goldG; colors[i3+2] = goldB;
        sizes[i] = 1.5;
      } else if (t < 0.3) {
        colors[i3] = darkR; colors[i3+1] = darkG; colors[i3+2] = darkB;
        sizes[i] = 2.5; // Dark particles slightly larger for depth
      } else if (t < 0.6) {
        colors[i3] = accentR; colors[i3+1] = accentG; colors[i3+2] = accentB;
        sizes[i] = 1.0;
      } else {
        var m = Math.random();
        colors[i3] = lightR + m * (accentR - lightR);
        colors[i3+1] = lightG + m * (accentG - lightG);
        colors[i3+2] = lightB + m * (accentB - lightB);
        sizes[i] = 0.8;
      }

      // --- STATE 0: HERO (Chaos Sphere) ---
      var phi = Math.random() * Math.PI * 2;
      var theta = Math.acos(2 * Math.random() - 1);
      var r0 = 2 + Math.random() * 25; // Wide spread
      pos0[i3]   = r0 * Math.sin(theta) * Math.cos(phi);
      pos0[i3+1] = r0 * Math.sin(theta) * Math.sin(phi);
      pos0[i3+2] = r0 * Math.cos(theta);

      // --- STATE 1: POSITIONING (Icosahedron Core + Rings) ---
      if (i < icoVerts * 8) { // Overlap multiple particles per vertex for glow
        var vIdx = (i % icoVerts) * 3;
        var jitter = 0.2;
        pos1[i3]   = icoPos[vIdx]   + (Math.random()-0.5)*jitter;
        pos1[i3+1] = icoPos[vIdx+1] + (Math.random()-0.5)*jitter;
        pos1[i3+2] = icoPos[vIdx+2] + (Math.random()-0.5)*jitter;
      } else {
        // Form rings
        var ringRadius = 5 + Math.random() * 12;
        var ringAngle = Math.random() * Math.PI * 2;
        pos1[i3]   = Math.cos(ringAngle) * ringRadius;
        pos1[i3+1] = (Math.random() - 0.5) * 1.5;
        pos1[i3+2] = Math.sin(ringAngle) * ringRadius;
      }

      // --- STATE 2: PROCESS (DNA Helix / Energy Flow) ---
      var hProgress = i / COUNT;
      var hAngle = hProgress * Math.PI * 16; // 8 full turns
      var hRadius = 3 + Math.sin(hProgress * Math.PI) * 1.5;
      var hY = (hProgress - 0.5) * 25;
      if (i % 2 === 0) {
        pos2[i3]   = Math.cos(hAngle) * hRadius;
        pos2[i3+1] = hY;
        pos2[i3+2] = Math.sin(hAngle) * hRadius;
      } else {
        pos2[i3]   = Math.cos(hAngle + Math.PI) * hRadius;
        pos2[i3+1] = hY;
        pos2[i3+2] = Math.sin(hAngle + Math.PI) * hRadius;
      }

      // --- STATE 3: RESULTS/DIFFERENTIAL (Structured Data Grid) ---
      var side = Math.ceil(Math.pow(COUNT, 1/3)); // approx 14
      var spacing = 1.2;
      var gx = i % side;
      var gy = Math.floor(i / side) % side;
      var gz = Math.floor(i / (side * side));
      pos3[i3]   = (gx - side/2) * spacing;
      pos3[i3+1] = (gy - side/2) * spacing;
      pos3[i3+2] = (gz - side/2) * spacing;
    }

    // Working array (starts at State 0)
    var currentPos = new Float32Array(pos0);

    /* ===================== Geometry & Material ===================== */
    var geo = new THREE.BufferGeometry();
    geo.setAttribute('position', new THREE.BufferAttribute(currentPos, 3));
    geo.setAttribute('color', new THREE.BufferAttribute(colors, 3));
    
    // Size attribute for shader
    var sizeAttr = new THREE.BufferAttribute(sizes, 1);
    geo.setAttribute('aSize', sizeAttr);

    // Circular particle texture
    var circleCanvas = document.createElement('canvas');
    circleCanvas.width = 64;
    circleCanvas.height = 64;
    var ctx = circleCanvas.getContext('2d');
    ctx.beginPath();
    ctx.arc(32, 32, 30, 0, Math.PI * 2);
    ctx.fillStyle = '#ffffff';
    ctx.fill();
    var circleTexture = new THREE.CanvasTexture(circleCanvas);

    // Custom ShaderMaterial to support individual particle sizes and vertex colors
    var shaderMat = new THREE.ShaderMaterial({
      uniforms: {
        pointTexture: { value: circleTexture },
        time: { value: 0 },
        globalScale: { value: h }
      },
      vertexShader: `
        attribute float aSize;
        varying vec3 vColor;
        uniform float time;
        uniform float globalScale;
        void main() {
          vColor = color;
          // Add slight organic breathing
          vec3 p = position;
          p.y += sin(time * 2.0 + p.x) * 0.1;
          
          vec4 mvPosition = modelViewMatrix * vec4(p, 1.0);
          // Scale by aSize and global scale for responsiveness
          gl_PointSize = aSize * (globalScale / 20.0) * (1.0 / -mvPosition.z);
          gl_Position = projectionMatrix * mvPosition;
        }
      `,
      fragmentShader: `
        uniform sampler2D pointTexture;
        varying vec3 vColor;
        void main() {
          vec4 texColor = texture2D(pointTexture, gl_PointCoord);
          if (texColor.a < 0.1) discard;
          gl_FragColor = vec4(vColor, texColor.a * 0.8);
        }
      `,
      transparent: true,
      depthWrite: false,
      vertexColors: true
    });

    var particles = new THREE.Points(geo, shaderMat);
    scene.add(particles);

    /* ===================== Global Progress State ===================== */
    var engineState = { progress: 0 };

    // Map DOM sections to global progress
    var sectionPositions = document.querySelector('.sued-positioning');
    var sectionProcess   = document.querySelector('.sued-process');
    var sectionResults   = document.querySelector('.sued-results');

    if (sectionPositions) {
      gsap.to(engineState, {
        progress: 1,
        scrollTrigger: {
          trigger: sectionPositions,
          start: 'top 80%',
          end: 'top 20%',
          scrub: 1
        }
      });
    }
    if (sectionProcess) {
      gsap.to(engineState, {
        progress: 2,
        scrollTrigger: {
          trigger: sectionProcess,
          start: 'top 80%',
          end: 'top 20%',
          scrub: 1
        }
      });
    }
    if (sectionResults) {
      gsap.to(engineState, {
        progress: 3,
        scrollTrigger: {
          trigger: sectionResults,
          start: 'top 80%',
          end: 'top 20%',
          scrub: 1
        }
      });
    }

    /* ===================== Mouse Interaction ===================== */
    var mouse = { x: 0, y: 0 };
    var smoothMouse = { x: 0, y: 0 };
    window.addEventListener('mousemove', function(e) {
      mouse.x = (e.clientX / window.innerWidth - 0.5) * 2;
      mouse.y = (e.clientY / window.innerHeight - 0.5) * 2;
    }, { passive: true });

    /* ===================== Resize ===================== */
    window.addEventListener('resize', function () {
      w = window.innerWidth;
      h = window.innerHeight;
      renderer.setSize(w, h);
      camera.aspect = w / h;
      camera.updateProjectionMatrix();
      shaderMat.uniforms.globalScale.value = h;
    }, { passive: true });

    /* ===================== Render Loop ===================== */
    var clock = new THREE.Clock();

    function getTargetArrays(prog) {
      if (prog < 1) return [pos0, pos1, prog];
      if (prog < 2) return [pos1, pos2, prog - 1];
      if (prog < 3) return [pos2, pos3, prog - 2];
      return [pos3, pos3, 0];
    }

    function animate() {
      requestAnimationFrame(animate);
      var elapsedTime = clock.getElapsedTime();
      shaderMat.uniforms.time.value = elapsedTime;

      // Smooth mouse
      smoothMouse.x += (mouse.x - smoothMouse.x) * 0.05;
      smoothMouse.y += (mouse.y - smoothMouse.y) * 0.05;

      var pData = getTargetArrays(engineState.progress);
      var arrA = pData[0];
      var arrB = pData[1];
      var t = pData[2]; // 0 to 1 interpolation factor

      // Custom easing function for morphing (Smoothstep)
      var easeT = t * t * (3 - 2 * t);

      var posAttr = geo.attributes.position;
      
      for (var i = 0; i < COUNT * 3; i+=3) {
        // Base interpolation
        var bx = arrA[i]   + (arrB[i]   - arrA[i])   * easeT;
        var by = arrA[i+1] + (arrB[i+1] - arrA[i+1]) * easeT;
        var bz = arrA[i+2] + (arrB[i+2] - arrA[i+2]) * easeT;

        // Apply global scene rotation manually to coordinates (simpler than rotating groups if we want organic flow)
        posAttr.array[i]   = bx;
        posAttr.array[i+1] = by;
        posAttr.array[i+2] = bz;
      }
      posAttr.needsUpdate = true;

      // Group rotation based on time and scroll
      // As user scrolls, the entire scene spins dynamically
      particles.rotation.y = elapsedTime * 0.05 + engineState.progress * Math.PI * 0.5 + smoothMouse.x * 0.5;
      particles.rotation.x = smoothMouse.y * 0.2;
      particles.rotation.z = engineState.progress * 0.1;

      // Camera drift
      camera.position.x = smoothMouse.x * 2;
      camera.position.y = -smoothMouse.y * 2;
      camera.lookAt(scene.position);

      renderer.render(scene, camera);
    }
    
    animate();
    console.log('[SUED Engine] Unified 3D Canvas Initialized.');
  }

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', function () { initEngine(0); });
  } else {
    initEngine(0);
  }
}());
