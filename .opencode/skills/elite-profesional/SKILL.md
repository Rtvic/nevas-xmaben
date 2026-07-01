---
name: elite-profesional
description: "Creación de sitios web para abogados, contadores, arquitectos, dentistas, doctores, consultores, agentes de seguros, agentes inmobiliarios y profesionales de servicios. Usar cuando el cliente sea un profesionista independiente."
---

# Elite Web Studio — Profesionistas & Consultores

## Identidad visual

- **Color principal**: Violeta `#8b5cf6` (seriedad, creatividad)
- **Color secundario**: Rosa `#c084fc`
- **Degradado hero**: `#6d28d9` → `#8b5cf6`
- **Acento glow**: `#a78bfa`
- **Partículas canvas**: `rgba(139,92,246,ALPHA)`
- **Three.js 3D**: `0x8b5cf6` (wireframe), estrellas `0xa78bfa`
- **Cursor custom**: violeta con glow `rgba(139,92,246,.12)`

## Paleta completa

```css
--bg:#07070d; --card:rgba(255,255,255,.02);
--accent:#8b5cf6; --accent2:#c084fc; --accent3:#a78bfa;
--text:#e0e0e0; --text2:#888; --border:rgba(255,255,255,.05);
--glass:rgba(7,7,13,.8); --spring:cubic-bezier(.34,1.56,.64,1);
```

## Estructura específica para profesionistas

1. **Nav** con nombre del profesionista + logo
2. **Hero** profesional con foto y eslogan ("[Nombre] — Tu abogado de confianza en [Ubicación]")
3. **Servicios** detallados con áreas de práctica
4. **Sobre mí** con credenciales, experiencia, foto
5. **Testimonios** de clientes (slider automático)
6. **Blog/Artículos** (opcional, genera autoridad)
7. **Agendador de consultas** con costo opcional
8. **Contacto directo** WhatsApp + teléfono + email
9. **WhatsApp flotante** pulse

## Hero profesional

```html
<section class="hero">
  <div class="hero-bg">
    <canvas id="bg"></canvas>
    <div class="hero-glow"></div>
  </div>
  <div class="hero-c">
    <div class="hf">⚖️</div>
    <h1>Lic. [Nombre] <span>— Abogado</span></h1>
    <p>Más de [X] años defendiendo los derechos de las familias en [Ubicación]</p>
    <div class="hb">
      <a href="#agenda" class="mg-btn pri">Agendar consulta</a>
      <a href="https://wa.me/521TU_NUMERO" target="_blank" class="mg-btn sec">WhatsApp</a>
    </div>
  </div>
</section>
```

## Servicios profesionales

```html
<div class="sv-grid">
  <div class="card"><div class="ci">⚖️</div>
    <h3>Derecho Familiar</h3>
    <p>Divorcios, pensión alimenticia, custodia, adopciones</p>
  </div>
  <div class="card"><div class="ci">📝</div>
    <h3>Derecho Civil</h3>
    <p>Contratos, testamentos, sucesiones, propiedades</p>
  </div>
  <div class="card"><div class="ci">🏢</div>
    <h3>Derecho Corporativo</h3>
    <p>Constitución de empresas, asesoría legal comercial</p>
  </div>
  <div class="card"><div class="ci">🚔</div>
    <h3>Derecho Penal</h3>
    <p>Defensa penal, asesoría en procesos judiciales</p>
  </div>
</div>
```

## Testimonios slider automático

```html
<div class="ts-wrap" id="tsw">
  <div class="ts active">"Excelente abogado, resolvió mi caso rápido."<div class="ta">— Cliente</div></div>
  <div class="ts">"Muy profesional, me explicó todo paso a paso."<div class="ta">— Cliente</div></div>
  <div class="ts">"Lo recomiendo ampliamente, honesto y eficiente."<div class="ta">— Cliente</div></div>
</div>
```
```javascript
var tsi=0;
setInterval(function(){
  document.querySelectorAll('.tsw .ts').forEach(function(t){t.classList.remove('active')});
  tsi=(tsi+1)%document.querySelectorAll('.tsw .ts').length;
  document.querySelectorAll('.tsw .ts')[tsi].classList.add('active')
},4000);
```

## Textos WhatsApp para profesionistas

- Consulta legal: `Hola, quiero agendar una consulta sobre [TEMA LEGAL]. ¿Cuándo tiene disponibilidad?`
- Contabilidad: `Hola, necesito asesoría para declaración de impuestos. ¿Puedo agendar una cita?`
- Arquitectura: `Hola, quiero una cotización para [TIPO DE PROYECTO]. ¿Podemos agendar una visita?`

## Argumentos de venta para profesionistas

- "Tus clientes buscan abogados/contadores en Google — que te encuentren a ti"
- "Genera confianza antes de la primera llamada con tu sitio profesional"
- "Agendador de consultas online — ahorras tiempo en llamadas"
- "Testimonios visibles = clientes nuevos sin preguntar"
- Gancho: **"Tu página trabaja 24/7 mientras tú descansas. ¿Cuántos clientes pierdes porque no te encuentran?"**

## Precios sugeridos para profesionistas

- Starter (1 página, info básica): $1,500
- Profesional (agendador + testimonios + blog): $3,500
- Ultra Premium (3D + animaciones): $5,000
- Hosting + Dominio 1 año: $600

## Demo base

Usar `http://localhost/demo_sitio.html` como base.
