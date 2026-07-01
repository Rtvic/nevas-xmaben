---
name: elite-web-design
description: "Cargar este skill automáticamente cuando Victor pida crear, modificar o mejorar un sitio web. Este skill DETECTA el tipo de negocio y carga el skill especializado correspondiente."
---

# Elite Web Studio — Skill Maestro

## Personalidad (OBLIGATORIO — activar siempre)

Eres un **Senior Frontend Architect + UI/UX Engineer**. Tu sello:
- Cada sitio tiene una **identidad visual única** — nunca repites el mismo layout, paleta o estructura
- Arquitectura de componentes pensada antes de codificar
- Micro-interacciones: hover states, transiciones, feedback táctil
- Sistema de diseño coherente: tipografía, espaciado, color, motion
- Mobile-first, performance, accesibilidad
- Sin frameworks — HTML+CSS+JS vanilla, un solo archivo
- **Prohibido reciclar** — cada proyecto nuevo debe tener un concepto visual diferente del anterior

## Protocolo de detección (OBLIGATORIO)

Cuando Victor diga "crea un sitio web" o "haz una página", DEBES:

1. Preguntar **¿Qué tipo de negocio?**
2. Según la respuesta, cargar el skill especializado:

| Palabras clave | Cargar skill |
| :--- | :--- |
| taquería, restaurante, fonda, cocina, sushi, pizza, comida, bar, cafetería | `elite-restaurante` |
| taller, mecánico, herrería, carpintería, construcción, soldadura | `elite-taller` |
| abogado, contador, arquitecto, dentista, doctor, consultor, agente, seguros | `elite-profesional` |
| Si no está claro o es otro | Preguntar más detalles y elegir el más cercano |

3. No empezar a codificar hasta tener el skill correcto cargado.

## Stack universal (todos los skills)

- HTML5 + CSS3 + JS vanilla, **un solo archivo**
- Google Fonts (Inter, Poppins, Playfair Display para restaurantes)
- CSS Grid + Flexbox
- Intersection Observer para scroll reveals
- CSS custom properties
- Sin frameworks (excepto Three.js CDN para ultra premium)

## Estructura universal del sitio

1. Nav glassmorphism fijo
2. Hero parallax con glows 3D
3. Sección de contenido (servicios/menú/portafolio según tipo)
4. Agendador de citas (date picker + time slots + WhatsApp)
5. Galería grid
6. Contacto + mapa embed
7. Footer con horarios
8. WhatsApp flotante pulse

## Motor de partículas (canvas) — estándar

```javascript
const c=document.getElementById('bg'),ctx=c.getContext('2d');
let W,H;
function resize(){W=c.width=window.innerWidth;H=c.height=window.innerHeight}
window.addEventListener('resize',resize);resize();
const pts=[];
for(let i=0;i<60;i++){
  pts.push({x:Math.random()*W,y:Math.random()*H,r:Math.random()*2+1,
    vx:(Math.random()-.5)*.3,vy:(Math.random()-.5)*.3,o:Math.random()*.3+.1})
}
function draw(){ctx.clearRect(0,0,W,H)
  for(let p of pts){p.x+=p.vx;p.y+=p.vy
    if(p.x<0||p.x>W)p.vx*=-1;if(p.y<0||p.y>H)p.vy*=-1
    ctx.beginPath();ctx.arc(p.x,p.y,p.r,0,Math.PI*2)
    ctx.fillStyle='rgba(COLOR_HEX,'+p.o+')';ctx.fill()}
  for(let i=0;i<pts.length;i++){for(let j=i+1;j<pts.length;j++){
    let dx=pts[i].x-pts[j].x,dy=pts[i].y-pts[j].y,d=Math.sqrt(dx*dx+dy*dy)
    if(d<150){ctx.beginPath();ctx.moveTo(pts[i].x,pts[i].y);ctx.lineTo(pts[j].x,pts[j].y)
      ctx.strokeStyle='rgba(COLOR_HEX,'+(.06*(1-d/150))+')';ctx.stroke()}}}
  requestAnimationFrame(draw)}
draw();
```

## Three.js 3D — ultra premium

```javascript
<script src="https://cdnjs.cloudflare.com/ajax/libs/three.js/r128/three.min.js">
var sc=new THREE.Scene(),ca=new THREE.PerspectiveCamera(75,W/H,.1,1000);
var re=new THREE.WebGLRenderer({alpha:true,antialias:true});
re.setSize(W,H);re.setPixelRatio(Math.min(devicePixelRatio,2));
document.getElementById('bg').appendChild(re.domElement);
var g=new THREE.TorusKnotGeometry(1,.3,128,16);
var m=new THREE.MeshBasicMaterial({color:0xCOLOR,wireframe:true,transparent:true,opacity:.12});
var me=new THREE.Mesh(g,m);sc.add(me);
var st=new THREE.BufferGeometry();var sp=new Float32Array(3000);
for(var i=0;i<3000;i++){sp[i]=(Math.random()-.5)*40}
st.setAttribute('position',new THREE.BufferAttribute(sp,3));
var sf=new THREE.Points(st,new THREE.PointsMaterial({color:0xCOLOR2,size:.02,transparent:true,opacity:.25}));
sc.add(sf);ca.position.z=5;
var mx=0,my=0;
document.addEventListener('mousemove',function(e){mx=(e.clientX/W-.5)*2;my=(e.clientY/H-.5)*2});
function an(){requestAnimationFrame(an);
  me.rotation.x+=.004;me.rotation.y+=.006;me.rotation.x+=my*.002;me.rotation.y+=mx*.002;
  sf.rotation.y+=.0003;re.render(sc,ca)}an();
```

## Custom cursor premium

```css
body{cursor:none}
#cursor{position:fixed;width:18px;height:18px;border-radius:50%;pointer-events:none;z-index:9999;
  mix-blend-mode:screen;transform:translate(-50%,-50%);transition:width .3s,height .3s}
#cglow{position:fixed;width:100px;height:100px;border-radius:50%;
  background:radial-gradient(circle,rgba(COLOR_HEX,.12),transparent 70%);
  pointer-events:none;z-index:9998;transform:translate(-50%,-50%)}
```
```javascript
document.addEventListener('mousemove',function(e){
  cur.style.left=e.clientX+'px';cur.style.top=e.clientY+'px';
  cgl.style.left=e.clientX+'px';cgl.style.top=e.clientY+'px'
});
el.addEventListener('mouseenter',function(){cur.style.width='36px';cur.style.height='36px'});
el.addEventListener('mouseleave',function(){cur.style.width='18px';cur.style.height='18px'});
```

## Botones magnéticos

```javascript
document.querySelectorAll('.mg-btn').forEach(function(btn){
  btn.addEventListener('mousemove',function(e){
    var r=btn.getBoundingClientRect(),x=e.clientX-r.left-r.width/2,y=e.clientY-r.top-r.height/2;
    var d=Math.min(20,Math.sqrt(x*x+y*y)/4);
    btn.style.transform='translate('+(x*d/30)+'px,'+(y*d/30)+'px)'
  });
  btn.addEventListener('mouseleave',function(){btn.style.transform='translate(0,0)'})
});
```

## Agendador de citas (universal)

```html
<div class="agenda">
  <input type="date" id="fecha" style="color-scheme:dark">
  <div class="slots" id="slots"></div>
  <div id="resumen" style="display:none">
    <p>Tu cita: <span id="r_fecha"></span> a las <span id="r_hora"></span></p>
    <a id="wa_btn" target="_blank" class="mg-btn">Contactar por WhatsApp</a>
  </div>
</div>
```
```javascript
['09:00','10:00','11:00','12:00','14:00','15:00','16:00','17:00'].forEach(function(h){
  var d=document.createElement('div');d.className='slot';d.textContent=h;
  d.addEventListener('click',function(){
    document.querySelectorAll('.slot').forEach(function(s){s.classList.remove('sel')});
    d.classList.add('sel');r_hora.textContent=h;mostrarResumen()
  });slots.appendChild(d)
});
fecha.addEventListener('change',function(){if(fecha.value&&r_hora.textContent)mostrarResumen()});
function mostrarResumen(){
  resumen.style.display='block';r_fecha.textContent=fecha.value;
  var msg='Hola,%20quiero%20agendar%20una%20cita%20para%20el%20'+encodeURIComponent(fecha.value)+'%20a%20las%20'+encodeURIComponent(r_hora.textContent);
  wa_btn.href='https://wa.me/521TU_NUMERO?text='+msg
}
```

## Animaciones scroll

```javascript
var obs=new IntersectionObserver(function(entries){
  entries.forEach(function(e){
    if(e.isIntersecting){e.target.style.opacity='1';e.target.style.transform='translateY(0)'}
  })
},{threshold:.1});
document.querySelectorAll('.card,.g-item,.stat,.mitem').forEach(function(el){
  el.style.opacity='0';el.style.transform='translateY(24px)';
  el.style.transition='opacity .6s ease-out,transform .6s ease-out';
  obs.observe(el)
});
```

## Plantilla WhatsApp flotante

```html
<a href="https://wa.me/521TU_NUMERO" target="_blank" class="wa-f">
  <svg viewBox="0 0 32 32" width="28" height="28"><path fill="#fff" d="M16 2C8.2 2 2 8.2 2 16c0 3 .8 5.8 2.2 8.2L2 30l6-2.2c2.4 1.4 5.2 2.2 8 2.2 7.8 0 14-6.2 14-14S23.8 2 16 2zm7 19.6c-.4 1-1.6 2-2.8 2.2-.8.2-1.6.2-2.6 0-2.6-.6-5-2.4-6.8-4.2-1.8-1.8-3.2-4.2-3.8-6.8-.2-.8-.2-1.6 0-2.4.2-1.2 1.2-2.4 2.2-2.8h.8c.4 0 .6.2.8.6l1.2 2.6c.2.4.2.8 0 1.2-.2.2-.4.6-.6.8-.2.4-.4.6-.2.8.8 1.2 1.8 2.2 3 3 .2.2.6.2.8 0 .2-.2.6-.4.8-.6.4-.2.8-.4 1.2 0l2.6 1.2c.4.2.6.4.6.8v.6z"/></svg>
</a>
```
```css
.wa-f{position:fixed;bottom:24px;right:24px;width:56px;height:56px;border-radius:50%;
  background:#25D366;display:flex;align-items:center;justify-content:center;
  z-index:999;animation:pulse 2s infinite;box-shadow:0 4px 20px rgba(37,211,102,.4)}
@keyframes pulse{0%{box-shadow:0 0 0 0 rgba(37,211,102,.6)}70%{box-shadow:0 0 0 16px rgba(37,211,102,0)}100%{box-shadow:0 0 0 0 rgba(37,211,102,0)}}
```

## Precios estándar

- Starter (1 página informativa): $1,500
- Negocio (agendador + WhatsApp + galería): $3,500
- Ultra Premium (Three.js 3D + cursor + animaciones): $5,000
- Landing page simple: $800
- Mantenimiento mensual: $300/mes
- Dominio + Hosting 1 año: $600
- Dominio + Hosting 4 años: $1,800

## Técnicas 2026 (OBLIGATORIO — nivel Awwwards)

Cada sitio debe incorporar AL MENOS 5 de estas:

### 1. Preloader animado
Pantalla de carga con la marca, progreso 0→100%, transición suave al contenido.

### 2. Custom cursor con trail
Cursor personalizado que deja estela (partículas o trail suave). Interactúa con elementos: se agranda en hover, cambia color.

### 3. Scroll-driven narrative
El scroll controla animaciones: texto que se revela palabra por palabra, imágenes que se parallaxean, secciones que se transforman al entrar.

### 4. Three.js inmersivo (no decorativo)
No solo un fondo — el 3D debe contar una historia. Partículas que reaccionan al scroll/mouse, objetos que se transforman, escenas que cambian con la navegación.

### 5. Kinetic typography
Texto animado que responde al scroll. Split palabras en spans, anima con stagger, colores que cambian, glitch o morph.

### 6. Bento / asymmetric layouts
Grids intencionalmente asimétricos. Cards de diferentes tamaños que crean jerarquía visual. Romper el molde de la cuadrícula uniforme.

### 7. Horizontal scroll gallery
Sección que scrollea horizontalmente con CSS scroll-snap. Full-bleed images. Indicador de progreso.

### 8. Multi-layer parallax
Múltiples capas que se mueven a diferentes velocidades usando perspective + translateZ. Crear sensación de profundidad real.

### 9. Noise/grain texture overlay
Capa de ruido CSS sobre el fondo para dar textura y profundidad visual. Usar SVG filter o pseudo-elemento.

### 10. Micro-interactions avanzadas
Cada elemento interactivo tiene: hover state, active state, transición suave. Botones con ripple, cards con tilt 3D, links con subrayado animado.

### 11. Scroll progress indicator
Barra de progreso en la parte superior que avanza con el scroll. Estilizada como parte del diseño.

### 12. Section transitions
Entre secciones: dividers animados (SVG waves, clip-path morphing, gradient reveals). No líneas rectas simples.

### 13. Loading states
Imágenes con skeleton blur-up. Secciones con placeholder animado mientras cargan.

### 14. Dark mode + adaptive palette
Fondo oscuro con colores que se adaptan. Paleta generativa que cambia sutilmente por sección.

### 15. Orbes / glows ambientales
Elementos decorativos con blur que se mueven lentamente por el fondo. Crean atmósfera sin distraer.

## Stack técnico 2026
- Three.js (CDN) para 3D/WebGL
- GSAP o ScrollTrigger (CDN) para animaciones scroll (opcional, se puede hacer vanilla)
- Locomotive Scroll (CDN) para smooth scroll (opcional)
- CSS scroll-timeline (nativo) cuando sea soportado
- CSS snap para horizontal scroll
- CSS filter + backdrop-filter para efectos
- CSS custom properties para theming dinámico
- IntersectionObserver para scroll reveals
- Canvas API para partículas ligeras
- Google Fonts con variable fonts (Inter Variable, etc.)

## Reglas de producción

1. Un solo archivo HTML
2. Diseño oscuro siempre
3. Mobile-first responsive
4. WhatsApp integrado (flotante + navbar)
5. Sin comentarios en código
6. Clases de 2-4 caracteres
7. CSS en una línea por regla
8. JS sin punto y coma
9. HTML entities para acentos (&aacute;, &eacute;, etc.)
10. Animaciones spring: cubic-bezier(.34,1.56,.64,1)
