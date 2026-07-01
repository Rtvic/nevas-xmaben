---
name: elite-restaurante
description: "Creación de sitios web para restaurantes, taquerías, fondas, cocina económica, pizzerías, cafeterías y negocios de comida. Usar cuando el cliente sea un negocio de alimentos."
---

# Elite Web Studio — Restaurantes & Comida

## Identidad visual

- **Color principal**: Naranja `#ea580c` (#ea580c)
- **Color secundario**: Ámbar `#f97316`
- **Degradado hero**: `#ea580c` → `#f97316`
- **Acento glow**: `#fb923c`
- **Partículas canvas**: `rgba(234,88,12,ALPHA)`
- **Three.js 3D**: `0xea580c` (wireframe), estrellas `0xfb923c`
- **Cursor custom**: naranja con glow `rgba(234,88,12,.12)`

## Paleta completa

```css
--bg:#07070d; --card:rgba(255,255,255,.02);
--accent:#ea580c; --accent2:#f97316; --accent3:#fb923c;
--text:#e0e0e0; --text2:#888; --border:rgba(255,255,255,.05);
--glass:rgba(7,7,13,.8); --spring:cubic-bezier(.34,1.56,.64,1);
```

## Estructura específica para restaurantes

1. **Nav** con nombre del local + logo emoji
2. **Hero** con foto del local/platillo + "Los mejores [platillo] de [ubicación]"
3. **Menú interactivo** con platillos, descripciones y precios
4. **Galería** de platillos (fotos reales del local)
5. **Ubicación + Horarios** con Google Maps embed
6. **Agendador** para reservaciones (vs citas)
7. **Redes sociales** (Facebook, Instagram)
8. **WhatsApp para pedidos** "Pide por WhatsApp"

## Menú interactivo

```html
<div class="menu-g">
  <div class="mc">
    <div class="mc-h">🔥 Tacos</div>
    <div class="mitem"><div class="mi-i"><h4>Taco al Pastor</h4><p>Trompo, piña, cebolla, cilantro</p></div><div class="mi-p">$25</div></div>
    <div class="mitem"><div class="mi-i"><h4>Taco de Suadero</h4><p>Suadero dorado, salsa verde</p></div><div class="mi-p">$30</div></div>
    <div class="mitem"><div class="mi-i"><h4>Taco de Carnitas</h4><p>Maciza, cuerito, salsa roja</p></div><div class="mi-p">$25</div></div>
  </div>
  <div class="mc">
    <div class="mc-h">🌮 Especialidades</div>
    <div class="mitem"><div class="mi-i"><h4>Taco Campechano</h4><p>Pastor, suadero, longaniza</p></div><div class="mi-p">$35</div></div>
    <div class="mitem"><div class="mi-i"><h4>Gringa</h4><p>Tortilla de harina, pastor, queso gratinado</p></div><div class="mi-p">$45</div></div>
    <div class="mitem"><div class="mi-i"><h4>Volcán</h4><p>Tostada, pastor, queso fundido, aguacate</p></div><div class="mi-p">$40</div></div>
  </div>
</div>
```
```css
.menu-g{display:grid;grid-template-columns:1fr 1fr;gap:12px}
@media(max-width:600px){.menu-g{grid-template-columns:1fr}}
.mc-h{color:var(--accent);font-size:1.1em;font-weight:700;padding:8px 12px;margin-bottom:4px}
.mitem{display:flex;justify-content:space-between;align-items:flex-start;
  padding:10px 14px;background:var(--card);border-radius:10px;border:1px solid var(--border);
  margin-bottom:6px;transition:all .3s var(--spring)}
.mitem:hover{background:rgba(234,88,12,.08);transform:translateX(4px)}
.mi-i h4{margin:0;font-size:.95em;color:var(--text)}
.mi-i p{margin:2px 0 0;font-size:.8em;color:var(--text2)}
.mi-p{color:var(--accent);font-weight:700;font-size:1.1em;white-space:nowrap;margin-left:12px}
```

## Texto WhatsApp para pedidos

```
Hola, quiero hacer un pedido de [PLATILLO] para [FECHA] a las [HORA]. Mi nombre es [NOMBRE]
```

## Argumentos de venta para dueños de restaurantes/taquerías

- "Tu menú en digital — la gente ve los precios antes de ir"
- "WhatsApp directo para pedidos — no pierdas clientes"
- "Agenda de reservaciones para fines de semana"
- "Google Maps para que te encuentren fácil"
- "Hosting + dominio incluido, tú solo pagas y vendes"
- Gancho: **"Imagina que llega un cliente y en vez de preguntar precios, ya los vio en tu página y solo pide"**

## Ejemplo real

`http://localhost/demo_taqueria.html` — Taquería con Three.js 3D, menú, agendador, WhatsApp flotante.
