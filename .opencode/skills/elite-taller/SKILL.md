---
name: elite-taller
description: "Creación de sitios web para talleres mecánicos, ferreterías, abarrotes, herrerías, carpinterías, servicios técnicos y negocios de oficios. Usar cuando el cliente sea un taller o negocio de servicio técnico/manual."
---

# Elite Web Studio — Talleres & Servicios Técnicos

## Identidad visual

- **Color principal**: Azul acero `#2563eb` (confianza, solidez)
- **Color secundario**: Cyan `#06b6d4`
- **Degradado hero**: `#1e40af` → `#2563eb`
- **Acento glow**: `#38bdf8`
- **Partículas canvas**: `rgba(37,99,235,ALPHA)`
- **Three.js 3D**: `0x2563eb` (wireframe), estrellas `0x38bdf8`
- **Cursor custom**: azul con glow `rgba(37,99,235,.12)`

## Paleta completa

```css
--bg:#07070d; --card:rgba(255,255,255,.02);
--accent:#2563eb; --accent2:#06b6d4; --accent3:#38bdf8;
--text:#e0e0e0; --text2:#888; --border:rgba(255,255,255,.05);
--glass:rgba(7,7,13,.8); --spring:cubic-bezier(.34,1.56,.64,1);
```

## Estructura específica para talleres/servicios

1. **Nav** con nombre del negocio + ícono (🔧🔨⚙️)
2. **Hero** con "Expertos en [oficio] en [ubicación]"
3. **Servicios** en cards con precios aproximados
4. **Galería de trabajos realizados** (fotos reales)
5. **Agendador de citas** (la gente agenda servicio, no comida)
6. **Ubicación + Horarios**
7. **WhatsApp** con mensaje pre-hecho para cotización
8. **Testimonios** de clientes (opcional pero potente)

## Servicios en cards

```html
<div class="sv-grid">
  <div class="card">
    <div class="ci">🔧</div>
    <h3>Cambio de Aceite</h3>
    <p>Incluye filtro, 4 litros, revisión general</p>
    <div class="cp">Desde $350</div>
  </div>
  <div class="card">
    <div class="ci">⚙️</div>
    <h3>Frenos</h3>
    <p>Pastillas, discos, líquido de frenos</p>
    <div class="cp">Desde $800</div>
  </div>
  <div class="card">
    <div class="ci">🔩</div>
    <h3>Suspensión</h3>
    <p>Amortiguadores, rótulas, brazos</p>
    <div class="cp">Desde $1,200</div>
  </div>
  <div class="card">
    <div class="ci">🔋</div>
    <h3>Diagnóstico</h3>
    <p>Escáner computarizado, revisión completa</p>
    <div class="cp">$250</div>
  </div>
</div>
```
```css
.sv-grid{display:grid;grid-template-columns:repeat(auto-fit,minmax(220px,1fr));gap:14px}
.card{background:var(--card);border-radius:14px;padding:20px;border:1px solid var(--border);
  text-align:center;transition:all .3s var(--spring)}
.card:hover{transform:translateY(-4px);background:rgba(37,99,235,.08);border-color:rgba(37,99,235,.2)}
.card .ci{font-size:2.4em;margin-bottom:8px}
.card h3{font-size:1em;margin:4px 0;color:var(--text)}
.card p{font-size:.82em;color:var(--text2);margin:0}
.cp{color:var(--accent);font-weight:700;font-size:1.1em;margin-top:8px}
```

## Texto WhatsApp para cotización

```
Hola, quiero una cotización para [SERVICIO] en mi [Vehículo/Marca]. Vivo en [UBICACIÓN]. ¿Cuándo tienes disponibilidad?
```

## Testimonios

```html
<div class="ts-grid">
  <div class="ts">
    <p>"Excelente servicio, me dejaron el carro como nuevo. Precio justo."</p>
    <div class="ta">— Don Juan, Hopelchén</div>
  </div>
  <div class="ts">
    <p>"Rápidos y honestos. Diagnosticaron el problema al instante."</p>
    <div class="ta">— María, X-Mabel</div>
  </div>
</div>
```
```css
.ts-grid{display:grid;grid-template-columns:1fr 1fr;gap:14px}
@media(max-width:600px){.ts-grid{grid-template-columns:1fr}}
.ts{background:var(--card);border-radius:12px;padding:16px;border:1px solid var(--border)}
.ts p{font-size:.9em;color:var(--text2);font-style:italic;margin:0}
.ts .ta{color:var(--accent);font-size:.82em;margin-top:8px;font-weight:600}
```

## Argumentos de venta para talleres

- "Tus clientes te encuentran en Google Maps cuando buscan 'taller mecánico cerca de mí'"
- "Agenda de citas online — ya no contestas el teléfono a cada rato"
- "Muestras tus trabajos en galería — genera confianza antes de que te llamen"
- "Precios visibles — el cliente ya sabe cuánto cuesta y llega decidido"
- Gancho: **"Mientras tú trabajas, tu página está consiguiendo clientes sola"**
- "$3,500 — lo recuperas con 2 trabajos. Inversión, no gasto"

## Frases clave para convencer

- "Tu competencia ya está en internet. ¿Tú?"
- "Un cliente busca 'taller mecánico en Hopelchén' y aparece tu página con tus trabajos, tus precios, tu whatsapp. ¿Te late?"
- "No necesitas redes sociales — solo esta página y ya"

## Demo base

Usar `http://localhost/demo_sitio.html` como base, cambiar colores a azul acero.
