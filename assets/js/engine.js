(function() {
  if (!window.THREE || !window.gsap || !window.ScrollTrigger) return;

  var canvas = document.createElement('canvas');
  canvas.id = 'sued-global-canvas';
  canvas.style.position = 'fixed';
  canvas.style.top = '0';
  canvas.style.left = '0';
  canvas.style.width = '100vw';
  canvas.style.height = '100vh';
  canvas.style.zIndex = '-1';
  canvas.style.pointerEvents = 'none';
  document.body.prepend(canvas);

  // Add class to remove CSS backgrounds
  document.body.classList.add('has-global-webgl');

  var scene = new THREE.Scene();
  var camera = new THREE.PerspectiveCamera(45, window.innerWidth / window.innerHeight, 0.1, 1000);
  camera.position.z = 20;

  var renderer = new THREE.WebGLRenderer({ canvas: canvas, alpha: true, antialias: true });
  renderer.setSize(window.innerWidth, window.innerHeight);
  renderer.setPixelRatio(Math.min(window.devicePixelRatio, 2));

  // Colors
  var cDark = new THREE.Color(0x0F1923);
  var cProcess = new THREE.Color(0x0c151e);
  var cMid = new THREE.Color(0x2A3A47);
  var cDiff = new THREE.Color(0x0a1118);

  // Background Plane
  var bgGeo = new THREE.PlaneGeometry(100, 100, 1, 1);
  var bgMat = new THREE.MeshBasicMaterial({ color: cDark, depthWrite: false });
  var bgMesh = new THREE.Mesh(bgGeo, bgMat);
  bgMesh.position.z = -50;
  scene.add(bgMesh);

  // Global State for Scroll
  var state = {
    bgProgress: 0,
    heroToPos: 0,
    posToProcess: 0,
    processToResults: 0
  };

  // Particles
  var COUNT = 2000;
  var geo = new THREE.BufferGeometry();
  
  var posHero = new Float32Array(COUNT * 3);
  var posPositioning = new Float32Array(COUNT * 3);
  var posProcess = new Float32Array(COUNT * 3);
  var currentPos = new Float32Array(COUNT * 3);
  var sizes = new Float32Array(COUNT);
  var colors = new Float32Array(COUNT * 3);

  // Canvas Texture for circular particles
  var texCanvas = document.createElement('canvas');
  texCanvas.width = 64; texCanvas.height = 64;
  var ctx = texCanvas.getContext('2d');
  ctx.beginPath();
  ctx.arc(32, 32, 28, 0, Math.PI * 2);
  ctx.fillStyle = '#FFF';
  ctx.fill();
  var particleTex = new THREE.CanvasTexture(texCanvas);

  // Colors
  var cBlue = new THREE.Color(0x26AFFF);
  var cGold = new THREE.Color(0xC8A96E);
  var cWhite = new THREE.Color(0xF5F2EC);

  for (var i = 0; i < COUNT; i++) {
    // Hero: Random nebula
    var hX = (Math.random() - 0.5) * 40;
    var hY = (Math.random() - 0.5) * 30;
    var hZ = (Math.random() - 0.5) * 20 - 5;
    posHero[i*3] = hX; posHero[i*3+1] = hY; posHero[i*3+2] = hZ;

    // Positioning: Icosahedron / Vortex
    var pX, pY, pZ;
    if (i < 42) {
      // Golden spiral points on sphere
      var phi = Math.acos(1 - 2 * (i + 0.5) / 42);
      var theta = Math.PI * (1 + Math.pow(5, 0.5)) * i;
      pX = Math.cos(theta) * Math.sin(phi) * 6;
      pY = Math.sin(theta) * Math.sin(phi) * 6;
      pZ = Math.cos(phi) * 6;
    } else {
      // Vortex
      var angle = Math.random() * Math.PI * 2;
      var radius = 6 + Math.random() * 15;
      pX = Math.cos(angle) * radius;
      pY = (Math.random() - 0.5) * 20;
      pZ = Math.sin(angle) * radius - 10;
    }
    posPositioning[i*3] = pX; posPositioning[i*3+1] = pY; posPositioning[i*3+2] = pZ;

    // Process: Flow lines
    var prX = (Math.random() - 0.5) * 30;
    var prY = Math.sin(prX * 0.5) * 5 + (Math.random() - 0.5) * 10;
    var prZ = (Math.random() - 0.5) * 15 - 5;
    posProcess[i*3] = prX; posProcess[i*3+1] = prY; posProcess[i*3+2] = prZ;

    currentPos[i*3] = hX; currentPos[i*3+1] = hY; currentPos[i*3+2] = hZ;
    
    sizes[i] = 0.5 + Math.random() * 1.5;

    // Colors
    var r = Math.random();
    var c;
    if (r < 0.1) c = cGold;
    else if (r < 0.4) c = cBlue;
    else if (r < 0.7) c = cWhite;
    else c = cDark;

    colors[i*3] = c.r; colors[i*3+1] = c.g; colors[i*3+2] = c.b;
  }

  geo.setAttribute('position', new THREE.BufferAttribute(currentPos, 3));
  geo.setAttribute('color', new THREE.BufferAttribute(colors, 3));
  geo.setAttribute('size', new THREE.BufferAttribute(sizes, 1));

  var mat = new THREE.ShaderMaterial({
    uniforms: {
      time: { value: 0 },
      pointTexture: { value: particleTex }
    },
    vertexShader: `
      attribute float size;
      attribute vec3 color;
      varying vec3 vColor;
      uniform float time;
      void main() {
        vColor = color;
        vec4 mvPosition = modelViewMatrix * vec4(position, 1.0);
        gl_PointSize = size * (30.0 / -mvPosition.z);
        gl_Position = projectionMatrix * mvPosition;
      }
    `,
    fragmentShader: `
      uniform sampler2D pointTexture;
      varying vec3 vColor;
      void main() {
        gl_FragColor = vec4(vColor, 1.0) * texture2D(pointTexture, gl_PointCoord);
      }
    `,
    blending: THREE.AdditiveBlending,
    depthTest: false,
    transparent: true
  });

  var points = new THREE.Points(geo, mat);
  scene.add(points);

  // Mouse interaction
  var mouse = new THREE.Vector2(0, 0);
  var smoothMouse = new THREE.Vector2(0, 0);
  window.addEventListener('mousemove', function(e) {
    mouse.x = (e.clientX / window.innerWidth) * 2 - 1;
    mouse.y = -(e.clientY / window.innerHeight) * 2 + 1;
  });

  // ScrollTriggers for morphing
  gsap.to(state, {
    heroToPos: 1,
    ease: "none",
    scrollTrigger: {
      trigger: ".sued-positioning",
      start: "top bottom",
      end: "center center",
      scrub: true
    }
  });

  gsap.to(state, {
    posToProcess: 1,
    ease: "none",
    scrollTrigger: {
      trigger: ".sued-process",
      start: "top bottom",
      end: "center center",
      scrub: true
    }
  });

  gsap.to(state, {
    processToResults: 1,
    ease: "none",
    scrollTrigger: {
      trigger: ".sued-results",
      start: "top bottom",
      end: "center center",
      scrub: true
    }
  });

  // BG Color transitions
  var sections = gsap.utils.toArray('.sued-section');
  if (sections.length > 0) {
    ScrollTrigger.create({
      trigger: ".sued-process",
      start: "top bottom",
      end: "top top",
      scrub: true,
      onUpdate: self => { bgMat.color.lerpColors(cDark, cProcess, self.progress); }
    });
    ScrollTrigger.create({
      trigger: ".sued-results",
      start: "top bottom",
      end: "top top",
      scrub: true,
      onUpdate: self => { bgMat.color.lerpColors(cProcess, cMid, self.progress); }
    });
    ScrollTrigger.create({
      trigger: ".sued-differential",
      start: "top bottom",
      end: "top top",
      scrub: true,
      onUpdate: self => { bgMat.color.lerpColors(cMid, cDiff, self.progress); }
    });
    ScrollTrigger.create({
      trigger: ".sued-cta",
      start: "top bottom",
      end: "top top",
      scrub: true,
      onUpdate: self => { bgMat.color.lerpColors(cDiff, cDark, self.progress); }
    });
  }

  var clock = new THREE.Clock();

  function animate() {
    requestAnimationFrame(animate);
    var t = clock.getElapsedTime();
    mat.uniforms.time.value = t;

    smoothMouse.lerp(mouse, 0.05);

    // Parallax camera
    camera.position.x = smoothMouse.x * 2;
    camera.position.y = smoothMouse.y * 2;
    camera.lookAt(scene.position);

    // Morph positions
    var posAttr = geo.attributes.position;
    for (var i = 0; i < COUNT; i++) {
      var ix = i * 3, iy = i * 3 + 1, iz = i * 3 + 2;
      
      var tx = posHero[ix];
      var ty = posHero[iy];
      var tz = posHero[iz];

      // Morph 1: Hero -> Positioning
      if (state.heroToPos > 0) {
        tx = THREE.MathUtils.lerp(tx, posPositioning[ix], state.heroToPos);
        ty = THREE.MathUtils.lerp(ty, posPositioning[iy], state.heroToPos);
        tz = THREE.MathUtils.lerp(tz, posPositioning[iz], state.heroToPos);
      }
      
      // Morph 2: Positioning -> Process
      if (state.posToProcess > 0) {
        tx = THREE.MathUtils.lerp(tx, posProcess[ix], state.posToProcess);
        ty = THREE.MathUtils.lerp(ty, posProcess[iy], state.posToProcess);
        tz = THREE.MathUtils.lerp(tz, posProcess[iz], state.posToProcess);
      }

      // Morph 3: Process -> Results (disperse gently)
      if (state.processToResults > 0) {
        tx = THREE.MathUtils.lerp(tx, posHero[ix] * 1.5, state.processToResults);
        ty = THREE.MathUtils.lerp(ty, posHero[iy] * 1.5 - 10, state.processToResults);
        tz = THREE.MathUtils.lerp(tz, posHero[iz] * 1.5, state.processToResults);
      }

      // Add gentle floating motion
      tx += Math.sin(t * 0.5 + i) * 0.05;
      ty += Math.cos(t * 0.3 + i) * 0.05;

      posAttr.array[ix] = tx;
      posAttr.array[iy] = ty;
      posAttr.array[iz] = tz;
    }
    posAttr.needsUpdate = true;

    // Rotate points group slightly based on scroll
    points.rotation.y = (window.scrollY * 0.001);
    points.rotation.x = Math.sin(t * 0.1) * 0.1;

    renderer.render(scene, camera);
  }

  animate();

  window.addEventListener('resize', function() {
    camera.aspect = window.innerWidth / window.innerHeight;
    camera.updateProjectionMatrix();
    renderer.setSize(window.innerWidth, window.innerHeight);
  });

})();
