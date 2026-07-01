<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1.0,viewport-fit=cover">
<title>Nexo — Tablero Victor</title>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&family=JetBrains+Mono:wght@400;600;700&display=swap" rel="stylesheet">
<style>
:root{--bg:#08080f;--s:rgba(18,18,32,.85);--s2:rgba(24,24,44,.75);--b:rgba(48,48,80,.25);--t:#eef0f6;--t2:#6b6b9e;--accent:#6366f1;--a2:#818cf8;--a3:#a5b4fc;--gold:#fbbf24;--green:#10b981;--red:#ef4444;--cyan:#22d3ee;--pink:#f472b6;--r:16px;--r2:12px;--spring:cubic-bezier(.34,1.56,.64,1)}
*,::after,::before{margin:0;padding:0;box-sizing:border-box;-webkit-tap-highlight-color:transparent}
html,body{height:100%;overflow:hidden;font-family:Inter,sans-serif;background:var(--bg);color:var(--t)}
::-webkit-scrollbar{width:2px}
::-webkit-scrollbar-thumb{background:var(--b);border-radius:2px}

/* AMBIENT BG */
#amb{position:fixed;inset:0;z-index:0;pointer-events:none;overflow:hidden}
#amb .gr{position:absolute;border-radius:50%;filter:blur(80px);animation:ambDrift 20s ease-in-out infinite alternate}
#amb .gr:nth-child(1){width:500px;height:500px;background:rgba(99,102,241,.06);top:-15%;left:-10%}
#amb .gr:nth-child(2){width:400px;height:400px;background:rgba(34,211,238,.04);bottom:-20%;right:-10%;animation-delay:-7s}
#amb .gr:nth-child(3){width:300px;height:300px;background:rgba(251,191,36,.03);top:50%;left:60%;animation-delay:-14s}
@keyframes ambDrift{0%{transform:translate(0,0)scale(1)}100%{transform:translate(40px,-40px)scale(1.15)}}
#grid{position:fixed;inset:0;z-index:0;opacity:.03;background-image:linear-gradient(rgba(99,102,241,.1)1px,transparent 1px),linear-gradient(90deg,rgba(99,102,241,.1)1px,transparent 1px);background-size:60px 60px;pointer-events:none}
#noise{position:fixed;inset:0;z-index:0;opacity:.015;pointer-events:none;background-image:url("data:image/svg+xml,%3Csvg viewBox='0 0 256 256' xmlns='http://www.w3.org/2000/svg'%3E%3Cfilter id='n'%3E%3CfeTurbulence type='fractalNoise' baseFrequency='.9' numOctaves='4' stitchTiles='stitch'/%3E%3C/filter%3E%3Crect width='100%25' height='100%25' filter='url(%23n)'/%3E%3C/svg%3E");background-size:256px}
#part{position:fixed;inset:0;z-index:0;pointer-events:none}
#part canvas{width:100%;height:100%}

.app{position:fixed;inset:0;display:flex;flex-direction:column;z-index:1}

/* HEADER */
.hdr{padding:14px 16px 0;z-index:10;flex-shrink:0}
.hdr-t{display:flex;justify-content:space-between;align-items:center}
.hdr-l{display:flex;align-items:center;gap:10px}
.av{width:36px;height:36px;border-radius:10px;background:linear-gradient(135deg,var(--accent),#4f46e5);display:flex;align-items:center;justify-content:center;font-size:13px;font-weight:800;color:#fff;position:relative;overflow:hidden}
.av::after{content:'';position:absolute;inset:0;background:linear-gradient(135deg,transparent 40%,rgba(255,255,255,.12)50%,transparent 60%);animation:avShine 3s ease-in-out infinite}
@keyframes avShine{0%,100%{transform:translateX(-100%)}50%{transform:translateX(100%)}}
.hdr-n{font-size:13px;font-weight:700}
.hdr-n span{display:block;font-size:7.5px;font-weight:400;color:var(--t2);margin-top:-1px}
.hdr-r{display:flex;align-items:center;gap:10px}

/* LIVE DOT */
.live-dot{display:flex;align-items:center;gap:5px;padding:4px 10px 4px 8px;border-radius:20px;background:rgba(16,185,129,.06);border:1px solid rgba(16,185,129,.12);font-size:7.5px;font-weight:600;color:var(--green);font-family:'JetBrains Mono',monospace}
.live-dot .dot{width:6px;height:6px;border-radius:50%;background:var(--green);animation:livePulse 1.8s ease-in-out infinite}
.live-dot .dot.off{background:var(--red);animation:none}
@keyframes livePulse{0%,100%{box-shadow:0 0 0 0 rgba(16,185,129,.6)}50%{box-shadow:0 0 0 6px rgba(16,185,129,0)}}

/* BALANCE */
.bal{padding:4px 16px 0;z-index:10;flex-shrink:0}
.bal-l{display:flex;align-items:center;justify-content:space-between}
.bal-l .bl{font-size:8px;font-weight:600;color:var(--t2);text-transform:uppercase;letter-spacing:1.5px}
.bal-l .bl span{color:var(--accent)}
.bal-r{display:flex;align-items:baseline;gap:6px;overflow:hidden;margin-top:-2px}
.bal-v{font-size:36px;font-weight:900;font-family:'JetBrains Mono',monospace;letter-spacing:-2px;display:flex;align-items:center;gap:0}
.bal-v .sign{font-size:20px;margin-right:1px}
.bal-v.neg{color:var(--red);text-shadow:0 0 40px rgba(239,68,68,.1)}
.bal-v.pos{color:var(--green);text-shadow:0 0 40px rgba(16,185,129,.1)}
.bal-s{font-size:9px;color:var(--t2);display:flex;align-items:center;gap:6px;margin-top:1px}

/* QUICK ACTIONS */
.qa{padding:8px 16px 0;z-index:10;flex-shrink:0;display:flex;gap:6px}
.qa-b{flex:1;padding:9px 10px;border-radius:var(--r2);background:var(--s);border:1px solid var(--b);color:var(--t);font-size:9px;font-weight:600;font-family:Inter,sans-serif;cursor:pointer;transition:all .3s var(--spring);display:flex;align-items:center;justify-content:center;gap:6px}
.qa-b:active{transform:scale(.95)}
.qa-b.pri{background:linear-gradient(135deg,var(--accent),#4f46e5);border-color:transparent;color:#fff;box-shadow:0 4px 20px rgba(99,102,241,.15)}
.qa-b.pri:active{box-shadow:0 2px 10px rgba(99,102,241,.1)}

/* PROGRESS */
.prog{padding:8px 16px 0;z-index:10;flex-shrink:0;display:flex;align-items:center;gap:12px}
.prog-r{position:relative;width:42px;height:42px;flex-shrink:0}
.prog-r svg{transform:rotate(-90deg)}
.prog-r .bg{fill:none;stroke:rgba(48,48,80,.35);stroke-width:3.5}
.prog-r .fg{fill:none;stroke:url(#progGrad);stroke-width:3.5;stroke-linecap:round;transition:stroke-dashoffset 1.5s var(--spring);filter:drop-shadow(0 0 8px rgba(99,102,241,.3))}
.prog-r .ct{position:absolute;inset:0;display:flex;align-items:center;justify-content:center;font-size:10px;font-weight:800;font-family:'JetBrains Mono',monospace;color:var(--t)}
.prog-i{flex:1}
.prog-i .b{font-size:17px;font-weight:900;background:linear-gradient(90deg,var(--accent),var(--cyan),var(--gold));background-size:200% 100%;-webkit-background-clip:text;-webkit-text-fill-color:transparent;animation:progShine 4s linear infinite}
@keyframes progShine{0%{background-position:0% 50%}100%{background-position:200% 50%}}
.prog-i .s{font-size:8px;color:var(--t2)}
.prog-i .s strong{color:var(--t)}

/* SEGMENTED */
.seg-c{padding:8px 16px 6px;z-index:10;flex-shrink:0}
.seg{display:flex;background:var(--s);border-radius:10px;border:1px solid var(--b);padding:3px;position:relative}
.seg-in{position:absolute;top:3px;bottom:3px;left:3px;width:calc(50% - 3px);background:linear-gradient(135deg,var(--accent),#4f46e5);border-radius:8px;transition:transform .4s var(--spring);z-index:0}
.seg-in.r{transform:translateX(100%)}
.seg-b{flex:1;padding:6px;border:none;background:transparent;color:var(--t2);font-size:9px;font-weight:600;cursor:pointer;position:relative;z-index:1;transition:color .3s;font-family:Inter,sans-serif}
.seg-b.active{color:#fff}

/* SCROLL */
.scr{flex:1;overflow-y:auto;overflow-x:hidden;-webkit-overflow-scrolling:touch;padding:0 14px 90px;z-index:10}
.scr-in{max-width:600px;margin:0 auto}

/* QUICK STATS BENTO */
.qs{display:grid;grid-template-columns:1fr 1fr;gap:6px;margin-bottom:10px}
.qs-i{display:flex;align-items:center;gap:10px;padding:10px 12px;background:var(--s);border-radius:var(--r2);border:1px solid var(--b);transition:all .3s;position:relative;overflow:hidden}
.qs-i:active{transform:scale(.97)}
.qs-ic{font-size:16px;flex-shrink:0;width:28px;height:28px;border-radius:8px;display:flex;align-items:center;justify-content:center;background:rgba(99,102,241,.08)}
.qs-ic.g{background:rgba(16,185,129,.08)}
.qs-ic.r{background:rgba(239,68,68,.08)}
.qs-ic.y{background:rgba(251,191,36,.08)}
.qs-inf{flex:1}
.qs-v{font-size:15px;font-weight:800;font-family:'JetBrains Mono',monospace}
.qs-l{font-size:7px;color:var(--t2);text-transform:uppercase;letter-spacing:.8px}

/* SECTION TITLE */
.st{font-size:12px;font-weight:700;margin-bottom:6px;display:flex;align-items:center;gap:8px;padding-top:10px}
.st .c{font-size:8px;font-weight:600;color:var(--t2);background:var(--s);padding:1px 6px;border-radius:5px;font-family:'JetBrains Mono',monospace}

/* DAY TIMELINE */
.tl{display:flex;flex-direction:column;gap:4px;margin-bottom:4px}
.tl-d{display:flex;align-items:center;gap:8px;padding:9px 10px;border-radius:var(--r2);background:var(--s);border:1px solid var(--b);cursor:pointer;position:relative;overflow:hidden;transition:all .4s var(--spring)}
.tl-d:active{transform:scale(.97)}
.tl-d::after{content:'';position:absolute;inset:0;background:linear-gradient(135deg,rgba(255,255,255,.012),transparent);pointer-events:none}
.tl-d.done{--bb:var(--green)}.tl-d.fail{--bb:var(--red);opacity:.6}.tl-d.cur{--bb:var(--gold);box-shadow:0 0 20px rgba(251,191,36,.03)}.tl-d.proj{opacity:.5}
.tl-d::before{content:'';position:absolute;left:0;top:4px;bottom:4px;width:2.5px;border-radius:2px;background:var(--bb,transparent);transition:all .4s}
.tl-n{width:24px;text-align:center;font-size:9px;font-weight:700;font-family:'JetBrains Mono',monospace;color:var(--t2);flex-shrink:0}
.tl-d.done .tl-n{color:var(--green)}.tl-d.fail .tl-n{color:var(--red)}.tl-d.cur .tl-n{color:var(--gold)}
.tl-i{flex:1;min-width:0}
.tl-dt{font-size:7.5px;color:var(--t2);font-family:'JetBrains Mono',monospace}
.tl-dt strong{color:var(--t);font-size:10px;font-family:Inter,sans-serif}
.tl-tk{font-size:6.5px;color:var(--t2);white-space:nowrap;overflow:hidden;text-overflow:ellipsis;margin-top:1px;opacity:.6}
.tl-rt{text-align:right;flex-shrink:0;font-size:8px;font-weight:600;font-family:'JetBrains Mono',monospace}
.tl-rt .e{color:var(--green)}.tl-rt .s{color:var(--red)}.tl-rt .l{color:var(--t2);font-size:12px}

/* BOLITAS SCROLL */
.bc-scr{display:flex;gap:8px;overflow-x:auto;padding:2px 0 8px;-webkit-overflow-scrolling:touch;scroll-snap-type:x mandatory}
.bc-scr::-webkit-scrollbar{display:none}
.bc-c{min-width:145px;padding:10px 12px;border-radius:var(--r2);background:var(--s2);border:1px solid var(--b);scroll-snap-align:start;transition:all .4s var(--spring);flex-shrink:0;position:relative;overflow:hidden}
.bc-c:active{transform:scale(.96)}
.bc-c.act{border-color:rgba(251,191,36,.15);background:rgba(251,191,36,.015)}
.bc-c.done{border-color:rgba(16,185,129,.15)}
.bc-c.lock{opacity:.35;filter:grayscale(.5)}
.bc-ct{display:flex;justify-content:space-between;align-items:center;margin-bottom:3px}
.bc-n{font-size:9px;font-weight:700}
.bc-p{font-size:11px;font-weight:800;font-family:'JetBrains Mono',monospace}
.bc-l{font-size:6.5px;color:var(--t2);margin-bottom:5px}
.bc-tr{height:3px;background:rgba(48,48,80,.4);border-radius:2px;overflow:hidden}
.bc-f{height:100%;border-radius:2px;transition:width 1s var(--spring);position:relative}
.bc-f::after{content:'';position:absolute;inset:0;background:linear-gradient(90deg,transparent,rgba(255,255,255,.1),transparent);animation:bcShine 1.5s infinite}
@keyframes bcShine{0%{transform:translateX(-100%)}100%{transform:translateX(100%)}}

/* DEBT */
.dl{display:flex;flex-direction:column;gap:4px;margin-bottom:10px}
.di{display:flex;align-items:center;justify-content:space-between;padding:7px 10px;background:var(--s);border-radius:8px;border:1px solid var(--b);font-size:9px;transition:all .3s}
.di:active{transform:scale(.98)}
.dn{font-weight:500}.da{font-weight:700;font-family:'JetBrains Mono',monospace;color:var(--red)}
.ds{font-size:6.5px;color:var(--t2);margin-left:5px;font-family:'JetBrains Mono',monospace}
.dt{display:flex;justify-content:space-between;padding:8px 10px;margin-top:4px;background:var(--s);border-radius:8px;border:2px solid var(--b);font-weight:800;font-size:11px}
.dt .ta{font-family:'JetBrains Mono',monospace;color:var(--red)}

/* TAB BAR */
.tb{position:fixed;bottom:0;left:0;right:0;z-index:100;background:rgba(8,8,15,.94);backdrop-filter:blur(30px);-webkit-backdrop-filter:blur(30px);border-top:1px solid var(--b);display:flex;padding:5px 0 calc(5px + env(safe-area-inset-bottom,0px));justify-content:space-around}
.tbb{display:flex;flex-direction:column;align-items:center;gap:2px;padding:4px 12px;border:none;background:transparent;color:var(--t2);font-size:6.5px;font-weight:600;cursor:pointer;transition:all .4s var(--spring);font-family:Inter,sans-serif;position:relative}
.tbb.act{color:var(--accent)}
.tbb.act .tbi{transform:scale(1.1)}.tbb:active .tbi{transform:scale(.8)}
.tbi{font-size:17px;line-height:1;transition:transform .4s var(--spring);display:block}
.tbl{font-size:6.5px;text-transform:uppercase;letter-spacing:1px}

/* SHEET */
.sh-o{position:fixed;inset:0;z-index:200;background:rgba(0,0,0,.45);display:none;backdrop-filter:blur(6px)}
.sh-o.s{display:block;animation:shFade .3s ease}
@keyframes shFade{from{opacity:0}to{opacity:1}}
.sh{position:fixed;bottom:0;left:0;right:0;z-index:201;background:rgba(12,12,22,.98);backdrop-filter:blur(40px);-webkit-backdrop-filter:blur(40px);border-radius:var(--r) var(--r) 0 0;border:1px solid var(--b);border-bottom:none;padding:10px 18px 30px;max-height:80vh;overflow-y:auto;transform:translateY(100%);transition:transform .5s var(--spring)}
.sh.s{transform:translateY(0)}
.sh-h{width:32px;height:3.5px;border-radius:2px;background:var(--b);margin:0 auto 12px}
.sh-t{font-size:16px;font-weight:800;margin-bottom:1px}
.sh-s{font-size:8px;color:var(--t2);font-family:'JetBrains Mono',monospace;margin-bottom:8px}
.sh-m{display:grid;grid-template-columns:1fr 1fr 1fr 1fr;gap:5px;margin-bottom:8px}
.sh-m>div{padding:6px 4px;background:var(--s);border-radius:8px;text-align:center;border:1px solid var(--b)}
.sh-m .ml{font-size:5px;color:var(--t2);text-transform:uppercase;letter-spacing:1px}
.sh-m .mv{font-size:11px;font-weight:800;font-family:'JetBrains Mono',monospace;margin-top:2px}
.sh-ht{margin-bottom:6px;padding:6px 10px;background:rgba(251,191,36,.025);border-radius:8px;border:1px solid rgba(251,191,36,.06);text-align:center}
.sh-ht .hl{font-size:6.5px;color:var(--gold);text-transform:uppercase;letter-spacing:1px}
.sh-ht .hv{font-size:9px;font-weight:700;margin-top:1px}
.sh-ia{padding:4px 8px;background:rgba(99,102,241,.025);border-radius:7px;border:1px solid rgba(99,102,241,.05);display:flex;align-items:center;gap:6px;font-size:7px;color:var(--t2);margin-bottom:6px}
.sh-tt{font-size:7px;color:var(--t2);text-transform:uppercase;letter-spacing:1px;margin-bottom:4px;font-family:'JetBrains Mono',monospace}
.sh-tk{display:flex;align-items:flex-start;gap:7px;padding:4px 0;border-bottom:1px solid rgba(255,255,255,.012);font-size:8.5px;transition:all .3s;cursor:pointer}
.sh-tk:active{opacity:.5}
.tc{width:16px;height:16px;border-radius:50%;border:2px solid var(--b);flex-shrink:0;margin-top:1px;display:flex;align-items:center;justify-content:center;font-size:7px;transition:all .4s var(--spring)}
.tc.d{background:var(--green);border-color:var(--green);color:#08080f;box-shadow:0 0 10px rgba(16,185,129,.2)}
.sh-nt{margin-top:6px;padding:6px 10px;background:rgba(251,191,36,.015);border-radius:7px;font-size:8px;color:var(--t2);border-left:2.5px solid var(--gold)}
.sh-ac{margin-top:10px;display:flex;gap:6px}
.sh-ac button{flex:1;padding:8px;border-radius:8px;border:none;font-size:8px;font-weight:600;cursor:pointer;font-family:Inter,sans-serif;transition:all .3s var(--spring)}
.sh-ac button:active{transform:scale(.95)}
.sh-ac .cmp{background:var(--green);color:#08080f}
.sh-ac .fal{background:var(--red);color:#fff}
.sh-ac .cur{background:var(--gold);color:#08080f}

/* LOG SHEET */
.log-form{display:flex;flex-direction:column;gap:8px;margin-top:8px}
.log-form input{padding:10px 12px;border-radius:8px;border:1px solid var(--b);background:rgba(255,255,255,.03);color:var(--t);font-size:14px;font-family:'JetBrains Mono',monospace;font-weight:700;outline:none;transition:border-color .3s}
.log-form input:focus{border-color:var(--accent)}
.log-form .lf-r{display:flex;gap:6px}
.log-form .lf-r button{flex:1;padding:9px;border-radius:8px;border:none;font-size:8px;font-weight:700;cursor:pointer;font-family:Inter,sans-serif;transition:all .3s var(--spring)}
.log-form .lf-r button:active{transform:scale(.95)}
.log-form .lf-r .lg{background:var(--green);color:#08080f}
.log-form .lf-r .lg2{background:var(--red);color:#fff}

/* TOAST */
.ts-c{position:fixed;top:60px;right:10px;z-index:300;display:flex;flex-direction:column;gap:4px;pointer-events:none}
.ts{pointer-events:auto;padding:7px 10px;background:rgba(12,12,22,.96);backdrop-filter:blur(20px);border-radius:12px;border:1px solid var(--b);box-shadow:0 10px 40px rgba(0,0,0,.5);max-width:260px;display:flex;align-items:center;gap:6px;animation:tsIn .5s var(--spring)}
.ts.out{animation:tsOut .35s ease-in forwards}
@keyframes tsIn{from{transform:translateX(120%)scale(.7)rotate(10deg);opacity:0}to{transform:translateX(0)scale(1)rotate(0);opacity:1}}
@keyframes tsOut{from{opacity:1}to{opacity:0;transform:translateX(80%)}}
.ts-ic{font-size:15px}.ts-t h4{font-size:9px;font-weight:700}.ts-t p{font-size:7px;color:var(--t2);margin-top:1px}

/* SKELETON */
.sk{background:linear-gradient(90deg,var(--s)25%,var(--s2)50%,var(--s)75%);background-size:200% 100%;animation:sk 1.5s infinite;border-radius:8px}
@keyframes sk{0%{background-position:200% 0}100%{background-position:-200% 0}}
.es{text-align:center;padding:20px;color:var(--t2);font-size:9px}
.es .b{font-size:30px;margin-bottom:6px;display:block}

@media(min-width:768px){
.hdr{padding:18px 28px 0}.bal{padding:6px 28px 0}.bal-v{font-size:44px}
.qa{padding:10px 28px 0;max-width:500px}.prog{padding:10px 28px 0;max-width:500px}
.seg-c{padding:10px 28px 8px;max-width:350px}.scr{padding:0 28px 100px}.tbb{padding:4px 18px}
.scr-in{max-width:700px}
}
@media(max-width:400px){
.hdr{padding:12px 12px 0}.hdr-n{font-size:11px}.bal{padding:2px 12px 0}.bal-v{font-size:28px}.bal-v .sign{font-size:16px}
.qa{padding:6px 12px 0;gap:4px}.qa-b{font-size:8px;padding:7px 8px}
.prog{padding:6px 12px 0;gap:8px}.prog-r{width:36px;height:36px}.prog-r svg{width:36px;height:36px}.prog-r .ct{font-size:9px}.prog-i .b{font-size:14px}
.seg-c{padding:6px 12px 4px}
.scr{padding:0 10px 84px}.qs{gap:4px}.qs-i{padding:8px 10px;gap:8px}.qs-ic{width:24px;height:24px;font-size:14px}.qs-v{font-size:13px}
.st{font-size:11px}.tl-d{padding:7px 8px;gap:6px}.tl-n{width:20px;font-size:8px}.tl-dt strong{font-size:9px}.tl-rt{font-size:7px}
.bc-c{min-width:125px;padding:8px 10px}.bc-p{font-size:10px}
.di{padding:6px 8px;font-size:8px}
.sh{padding:8px 12px 24px}.sh-t{font-size:14px}.sh-m{gap:4px}.sh-m>div{padding:5px 2px}
}
</style>
</head>
<body>

<div id="amb"><div class="gr"></div><div class="gr"></div><div class="gr"></div></div>
<div id="grid"></div>
<div id="noise"></div>
<div id="part"><canvas id="pc"></canvas></div>

<div class="app">

<div class="hdr">
  <div class="hdr-t">
    <div class="hdr-l">
      <div class="av">V</div>
      <div class="hdr-n">Victor <span>X-Mabel &middot; Hopelch&eacute;n</span></div>
    </div>
    <div class="hdr-r">
      <div class="live-dot" id="liveDot"><span class="dot" id="liveDotI"></span><span id="liveDotT">EN VIVO</span></div>
    </div>
  </div>
</div>

<div class="bal">
  <div class="bal-l">
    <div class="bl">Capital <span>total</span></div>
    <div class="bl" style="font-weight:400;font-size:7px" id="balUpdated">&mdash;</div>
  </div>
  <div class="bal-r">
    <div class="bal-v neg" id="balVal"><span class="sign">$</span><span class="num" id="balDigits">0</span></div>
  </div>
  <div class="bal-s"><span id="balSub"></span></div>
</div>

<div class="qa">
  <button class="qa-b pri" id="qaToday">✅ Marcar hoy</button>
  <button class="qa-b" id="qaLog">💰 Registrar</button>
  <button class="qa-b" id="qaRefresh">🔄 Sincronizar</button>
</div>

<div class="prog">
  <div class="prog-r">
    <svg viewBox="0 0 48 48" width="42" height="42">
      <circle class="bg" cx="24" cy="24" r="19.5"/>
      <circle class="fg" id="progRing" cx="24" cy="24" r="19.5" stroke-dasharray="122.5" stroke-dashoffset="122.5"/>
    </svg>
    <div class="ct" id="progPct">0%</div>
  </div>
  <div class="prog-i">
    <div class="b" id="progBig">0%</div>
    <div class="s"><strong id="doneDays">0</strong> de <strong id="totalDays">365</strong> d&iacute;as &middot; <span id="rankBadge"></span></div>
  </div>
</div>

<div class="seg-c">
  <div class="seg">
    <div class="seg-in" id="segIn"></div>
    <button class="seg-b active" id="segAll" onclick="setF('all')">Todos</button>
    <button class="seg-b" id="segReal" onclick="setF('real')">Reales</button>
  </div>
</div>

<div class="scr" id="scr">
  <div class="scr-in">

    <div class="qs">
      <div class="qs-i"><div class="qs-ic">🔥</div><div class="qs-inf"><div class="qs-v" id="qsRacha">0</div><div class="qs-l">Racha actual</div></div></div>
      <div class="qs-i"><div class="qs-ic g">✅</div><div class="qs-inf"><div class="qs-v" id="qsDone">0</div><div class="qs-l">D&iacute;as cumplidos</div></div></div>
      <div class="qs-i"><div class="qs-ic r">💳</div><div class="qs-inf"><div class="qs-v" id="qsDeuda">$0</div><div class="qs-l">Deuda total</div></div></div>
      <div class="qs-i"><div class="qs-ic y">💰</div><div class="qs-inf"><div class="qs-v" id="qsEarned">$0</div><div class="qs-l">Generado</div></div></div>
    </div>

    <div class="st">Plan de d&iacute;as <span class="c" id="daysC">0</span></div>
    <div class="tl" id="tlC">
      <div class="sk" style="height:48px"></div>
      <div class="sk" style="height:48px"></div>
      <div class="sk" style="height:48px"></div>
    </div>

    <div class="st" style="margin-top:12px">Bolitas <span class="c" id="bolC">0</span></div>
    <div class="bc-scr" id="bolScr">
      <div class="sk" style="min-width:145px;height:82px;flex-shrink:0"></div>
      <div class="sk" style="min-width:145px;height:82px;flex-shrink:0"></div>
    </div>

    <div class="st" style="margin-top:12px">Deudas <span class="c" id="debtC">0</span></div>
    <div class="dl" id="debtCtr">
      <div class="sk" style="height:35px"></div>
      <div class="sk" style="height:35px"></div>
    </div>
    <div style="height:10px"></div>
  </div>
</div>
</div>

<div class="tb">
  <button class="tbb act" onclick="tabS(0,this)"><span class="tbi">📊</span><span class="tbl">Panel</span></button>
  <button class="tbb" onclick="tabS(1,this)"><span class="tbi">📅</span><span class="tbl">D&iacute;as</span></button>
  <button class="tbb" onclick="tabS(2,this)"><span class="tbi">🎯</span><span class="tbl">Metas</span></button>
  <button class="tbb" onclick="tabS(3,this)"><span class="tbi">👤</span><span class="tbl">Perfil</span></button>
</div>

<!-- DAY SHEET -->
<div class="sh-o" id="shO"></div>
<div class="sh" id="sh">
  <div class="sh-h"></div>
  <div class="sh-t" id="shT">D&iacute;a</div>
  <div class="sh-s" id="shS">&mdash;</div>
  <div id="shB"></div>
  <div class="sh-ac" id="shAc"></div>
  <button onclick="closeSh()" style="width:100%;margin-top:8px;padding:8px;border-radius:8px;background:var(--s);border:1px solid var(--b);color:var(--t2);font-size:8px;font-weight:600;cursor:pointer;font-family:Inter,sans-serif">Cerrar</button>
</div>

<!-- LOG SHEET -->
<div class="sh-o" id="logO"></div>
<div class="sh" id="logSh">
  <div class="sh-h"></div>
  <div class="sh-t">💰 Registrar movimiento</div>
  <div class="sh-s" id="logDate">&mdash;</div>
  <div class="log-form">
    <input type="number" id="logAmount" placeholder="$ Monto" min="1">
    <div class="lf-r">
      <button class="lg" onclick="logEarnings()">+ Ingreso</button>
      <button class="lg2" onclick="logSpending()">- Gasto</button>
    </div>
  </div>
  <button onclick="closeLog()" style="width:100%;margin-top:8px;padding:8px;border-radius:8px;background:var(--s);border:1px solid var(--b);color:var(--t2);font-size:8px;font-weight:600;cursor:pointer;font-family:Inter,sans-serif">Cancelar</button>
</div>

<div class="ts-c" id="tsC"></div>

<svg width="0" height="0"><defs><linearGradient id="progGrad" x1="0%" y1="0%" x2="100%" y2="0%"><stop offset="0%" stop-color="#6366f1"/><stop offset="50%" stop-color="#818cf8"/><stop offset="100%" stop-color="#fbbf24"/></linearGradient></defs></svg>

<script>
const RANKS=[
  {n:'Bronce',i:'🏆',s:'El viaje arranca',x:0},
  {n:'Plata',i:'🥈',s:'Tomando vuelo',x:50},
  {n:'Oro',i:'🥇',s:'Imparable',x:150},
  {n:'Platino',i:'💎',s:'Maestro',x:300},
  {n:'Diamante',i:'👑',s:'Leyenda',x:500},
  {n:'Nexo',i:'⭐',s:'Dueño',x:800}
]
let data=null,fc={},fl='all',pv=0,ls=false,shDate=''

function gR(x){for(let i=RANKS.length-1;i>=0;i--)if(x>=RANKS[i].x)return{...RANKS[i],i,nx:RANKS[i+1],px:RANKS[i].x,nxp:RANKS[i+1]?RANKS[i+1].x:RANKS[i].x+200};return{...RANKS[0],i:0,nx:RANKS[1],px:0,nxp:RANKS[1].x}}

async function fd(){try{
  const r=await fetch('snowball_data.json?t='+Date.now())
  if(!r.ok)return false
  data=await r.json()
  return true
}catch(e){return false}}

function fm(n){if(typeof n!=='number')return'$0';return n<0?'-$'+Math.abs(n).toLocaleString('es-MX'):'$'+n.toLocaleString('es-MX')}
function fs(ds){return new Date(ds+'T12:00:00').toLocaleDateString('es-MX',{day:'numeric',month:'short'})}
function ff(ds){return new Date(ds+'T12:00:00').toLocaleDateString('es-MX',{day:'numeric',month:'long',year:'numeric'})}
function dn(ds){return['dom','lun','mar','mié','jue','vie','sáb'][new Date(ds+'T12:00:00').getDay()]}
function se(s){return{completado:'✅',fallido:'❌',en_curso:'▶️',pendiente:'⏳'}[s]||'⏳'}

function gP(){
  if(!data)return{}
  const c=JSON.parse(JSON.stringify(data.calendario||{}))
  const e=data.persona.diasEstimados||365
  const st=new Date(data.persona.fechaInicio+'T12:00:00')
  const ex=Object.keys(c).sort()
  const hi=data.plan?.hitos||[]
  const er=['Ofrecer servicio técnico a 3 personas','Publicar en WhatsApp servicios','Separar $50 para gasolina','Registrar ingresos y gastos','Llamar a cliente potencial','Crear video de servicio','Enviar 3 mensajes']
  const mi=['Realizar servicio programado','Actualizar herramientas','Ahorrar $100 en bolita','Revisar meta semanal','Dar mantenimiento a equipo','Pedir referidos','Publicar 2 historias']
  const la=['Capacitarse 30 min','Diseñar volante digital','Cotizar refacciones','Evaluar progreso','Crear tutorial','Contactar 2 negocios','Revisar precios']
  const fi=['Revisar meta mensual','Actualizar dashboard','Planificar siguiente ciclo','Celebrar hitos','Compartir experiencia','Analizar ingresos','Nueva meta']
  function tf(d){const t=[];let p;if(d<=30)p=er;else if(d<=90)p=mi;else if(d<=200)p=la;else p=fi;const cnt=2+((d*7)%4);for(let i=0;i<Math.min(cnt,6);i++)t.push({text:p[(d+i*3)%p.length],done:false});hi.forEach(h=>{if(h.dia===d)t.push({text:'🏁 '+h.meta,done:false})});return t}
  function nf(d){for(const h of hi)if(h.dia===d)return'🏁 Hito: '+h.meta;if(d<=30)return'Fase activación';if(d<=60)return'Fase consolidación';if(d<=90)return'Fase crecimiento';if(d<=180)return'Fase expansión';if(d<=270)return'Fase aceleración';return'Fase final'}
  for(let n=ex.length+1;n<=e;n++){const d=new Date(st);d.setDate(d.getDate()+n-1);const ds=d.toISOString().split('T')[0];const hi2=hi.find(h=>h.dia===n);c[ds]={diaNumero:n,estado:'pendiente',locked:true,tasks:tf(n),earned:0,spent:0,notas:nf(n),estimado:true,hito:!!hi2,hitoMeta:hi2?hi2.meta:null,generadoEstimado:Math.round(n*(data.plan?.ingresoDiarioNecesario||301))}}
  return c
}

function animDigits(el,target){
  if(!el)return
  const abs=Math.abs(target),str=abs.toString()
  let html=''
  for(let i=0;i<str.length;i++){html+='<span class="digit" style="display:inline-block;transition:transform .5s var(--spring),opacity .5s">'+str[i]+'</span>'}
  el.innerHTML=html
}

function r(){
  if(!data)return
  const ex=Object.keys(data.calendario||{}).sort()
  fc=gP()
  const al=Object.keys(fc).sort()
  const pr=al.filter(d=>!ex.includes(d))
  const est=data.persona.diasEstimados||365
  const tot=ex.length
  const pct=Math.min(100,(tot/est)*100)
  const com=data.stats.diasCompletados||0
  const gen=data.stats.totalGenerado||0
  const deu=data.banco.deudas||[]
  const deuT=deu.reduce((s,x)=>s+(x.monto||0),0)

  function $(id,v){const e=document.getElementById(id);if(e)e.textContent=v}
  function $h(id,v){const e=document.getElementById(id);if(e)e.innerHTML=v}

  const cap=data.banco.capital
  const bv=document.getElementById('balVal')
  if(bv){
    bv.className='bal-v '+(cap<0?'neg':'pos')
    const nd=document.getElementById('balDigits')
    if(nd)animDigits(nd,cap)
  }

  const rg=document.getElementById('progRing')
  if(rg){const circ=2*Math.PI*19.5;rg.style.strokeDasharray=circ;rg.style.strokeDashoffset=circ-(pct/100)*circ}
  $('progPct',Math.round(pct)+'%')
  $('progBig',Math.round(pct)+'%')
  $('doneDays',tot)
  $('totalDays',est)

  const rank=gR(com)
  document.getElementById('rankBadge').innerHTML=rank.i+' '+rank.n

  const sub=document.getElementById('balSub')
  if(sub)sub.innerHTML='Día <strong>'+tot+'</strong> · Progreso <strong>'+Math.round(pct)+'%</strong>'

  const upd=data.ultimaActualizacion
  if(upd){
    const d=new Date(upd)
    document.getElementById('balUpdated').textContent='Actualizado '+d.toLocaleTimeString('es-MX',{hour:'2-digit',minute:'2-digit'})
  }

  $('qsRacha',data.stats.rachaActual||0)
  $('qsDone',com)
  $h('qsDeuda',fm(deuT))
  $h('qsEarned',fm(gen))

  const dd=fl==='all'?[...ex,...pr]:[...ex]
  $('daysC',dd.length)
  const tl=document.getElementById('tlC')
  let h=''
  dd.forEach((ds,i)=>{
    const d=fc[ds],st=d.estado||'pendiente',ip=d.estimado&&!ex.includes(ds)
    const cl=st+(ip?' proj':'')
    const dl=Math.min(i*0.01,0.2)
    const td=(d.tasks||[]).filter(t=>t.done).length
    const tt=(d.tasks||[]).length
    const tp=(d.tasks||[]).map(t=>t.text).join(', ').substring(0,40)
    const em=ip?'🔒':se(st)
    let rt=''
    if(d.earned)rt+='<span class="e">+$'+d.earned+'</span>';if(d.spent)rt+=' <span class="s">-$'+d.spent+'</span>'
    if(!rt)rt=ip?'<span class="l">🔒</span>':'<span class="l" style="font-size:10px">'+em+'</span>'
    const sc={completado:'done',fallido:'fail',en_curso:'cur',pendiente:''}
    h+='<div class="tl-d '+sc[st]+(ip?' proj':'')+'" style="animation-delay:'+dl+'s" onclick="openD(\''+ds+'\')"><div class="tl-n">'+d.diaNumero+'</div><div class="tl-i"><div class="tl-dt"><strong>'+fs(ds)+'</strong> '+dn(ds)+'</div><div class="tl-tk">'+td+'/'+tt+' · '+(tp||'Sin tareas')+'</div></div><div class="tl-rt">'+rt+'</div></div>'
  })
  tl.innerHTML=h||'<div class="es"><span class="b">🚀</span>No hay días</div>'
  setTimeout(()=>{const c=tl.querySelector('.cur');if(c)c.scrollIntoView({block:'center',behavior:'smooth'})},200)
  rB()
  rD()
}

function setF(f){
  fl=f
  const s=document.getElementById('segIn')
  if(f==='all'){s.classList.remove('r');document.getElementById('segAll').classList.add('active');document.getElementById('segReal').classList.remove('active')}
  else{s.classList.add('r');document.getElementById('segReal').classList.add('active');document.getElementById('segAll').classList.remove('active')}
  r()
}

async function saveDayState(date,state){
  try{
    const r=await fetch('save_data.php',{
      method:'POST',
      headers:{'Content-Type':'application/json'},
      body:JSON.stringify({action:'set_day_state',date,state})
    })
    const j=await r.json()
    if(j.ok){
      toast(state==='completado'?'Día completado 🎉':state==='fallido'?'Día fallido 😤':'Día en curso ⏳','Guardado en vivo')
      await refresh()
    }
  }catch(e){toast('Error','No se pudo guardar','⚠️')}
}

async function toggleTask(date,taskIndex){
  try{
    const r=await fetch('save_data.php',{
      method:'POST',
      headers:{'Content-Type':'application/json'},
      body:JSON.stringify({action:'toggle_task',date,taskIndex})
    })
    const j=await r.json()
    if(j.ok){
      if(shDate===date)openD(date)
      await refresh()
    }
  }catch(e){}
}

async function logEarnings(){await logMoney('earned')}
async function logSpending(){await logMoney('spent')}
async function logMoney(type){
  const inp=document.getElementById('logAmount')
  const amt=parseInt(inp.value)
  if(!amt||amt<=0){toast('Monto inválido','Ingresa una cantidad','⚠️');return}
  try{
    const r=await fetch('save_data.php',{
      method:'POST',
      headers:{'Content-Type':'application/json'},
      body:JSON.stringify({action:'log_earnings',date:shDate,amount:amt,type})
    })
    const j=await r.json()
    if(j.ok){
      toast(type==='earned'?'+ $'+amt+' registrado':'-$'+amt+' registrado','Sincronizado al instante',type==='earned'?'💰':'💸')
      inp.value=''
      closeLog()
      await refresh()
    }
  }catch(e){toast('Error','No se pudo registrar','⚠️')}
}

function openLog(){
  const today=new Date().toISOString().split('T')[0]
  shDate=fc[today]?today:Object.keys(data.calendario||{}).sort().pop()||today
  document.getElementById('logDate').textContent=shDate?ff(shDate):'Hoy'
  document.getElementById('logAmount').value=''
  document.getElementById('logO').classList.add('s')
  document.getElementById('logSh').classList.add('s')
}

function closeLog(){
  document.getElementById('logO').classList.remove('s')
  document.getElementById('logSh').classList.remove('s')
}

function openD(ds){
  const d=fc[ds]
  if(!d)return
  shDate=ds
  $h('shT',(d.locked?'🔒 ':'')+'Día '+d.diaNumero+' '+se(d.estado))
  let sub=ff(ds)+' '+dn(ds)
  if(d.estimado)sub+=' · Proyectado IA'
  $('shS',sub)
  const sm={completado:'Completado',fallido:'Fallido',en_curso:'En curso',pendiente:'Pendiente'}
  const sc={completado:'var(--green)',fallido:'var(--red)',en_curso:'var(--gold)',pendiente:'var(--t2)'}
  const ed=d.estimado?(d.generadoEstimado?'~$'+d.generadoEstimado.toLocaleString():'$0'):(d.earned?'+$'+d.earned:'$0')
  const ec=d.estimado?'var(--t2)':'var(--green)'
  let h='<div class="sh-m"><div><div class="ml">Estado</div><div class="mv" style="color:'+(sc[d.estado]||'var(--t2)')+'">'+(sm[d.estado]||d.estado)+'</div></div><div><div class="ml">'+(d.estimado?'Est.':'Ganado')+'</div><div class="mv" style="color:'+ec+'">'+ed+'</div></div><div><div class="ml">Gastado</div><div class="mv" style="color:var(--red)">'+(d.spent?'-$'+d.spent:'$0')+'</div></div><div><div class="ml">Tareas</div><div class="mv" style="color:var(--blue)">'+(d.tasks||[]).filter(t=>t.done).length+'/'+(d.tasks||[]).length+'</div></div></div>'
  if(d.hito)h+='<div class="sh-ht"><div class="hl">🏁 Hito</div><div class="hv">'+d.hitoMeta+'</div></div>'
  if(d.estimado&&d.locked)h+='<div class="sh-ia">🤖 Día proyectado · Las tareas se ajustan a tu progreso</div>'
  h+='<div class="sh-tt">Tareas</div>'
  ;(d.tasks||[]).forEach((t,i)=>{
    const idx=i
    h+='<div class="sh-tk" onclick="toggleTask(\''+ds+'\','+idx+')"><div class="tc '+(t.done?'d':'')+'">'+(t.done?'✓':'')+'</div><div style="flex:1;'+(t.done?'text-decoration:line-through;opacity:.4':'')+'">'+t.text+'</div></div>'
  })
  if(d.notas)h+='<div class="sh-nt">📝 '+d.notas+'</div>'
  document.getElementById('shB').innerHTML=h

  const isLocked=d.estimado&&d.locked
  if(!isLocked){
    let ac=''
    if(d.estado!=='completado')ac+='<button class="cmp" onclick="saveDayState(\''+ds+'\',\'completado\');closeSh()">✅ Completar</button>'
    if(d.estado!=='fallido')ac+='<button class="fal" onclick="saveDayState(\''+ds+'\',\'fallido\');closeSh()">❌ Fallar</button>'
    if(d.estado!=='en_curso')ac+='<button class="cur" onclick="saveDayState(\''+ds+'\',\'en_curso\');closeSh()">▶️ En curso</button>'
    document.getElementById('shAc').innerHTML=ac
  }else{
    document.getElementById('shAc').innerHTML=''
  }
  document.getElementById('shO').classList.add('s')
  document.getElementById('sh').classList.add('s')
}

function closeSh(){
  document.getElementById('shO').classList.remove('s')
  document.getElementById('sh').classList.remove('s')
}

function rB(){
  if(!data)return
  const b=data.bolitas||[]
  $('bolC',b.length)
  const s=document.getElementById('bolScr')
  if(!s)return
  if(!b.length){s.innerHTML='<div class="es" style="min-width:100%">Sin bolitas</div>';return}
  let h=''
  b.forEach((x,i)=>{
    let c='bc-c'
    if(x.estatus==='active')c+=' act'
    else if(x.estatus==='completed')c+=' done'
    else c+=' lock'
    h+='<div class="'+c+'"><div class="bc-ct"><span class="bc-n">'+x.nombre+'</span><span class="bc-p" style="color:'+(x.color||'#888')+'">'+Math.round(x.progreso||0)+'%</span></div><div class="bc-l">'+(x.label||'')+'</div><div class="bc-tr"><div class="bc-f" style="width:'+(x.progreso||0)+'%;background:linear-gradient(90deg,'+(x.color||'#888')+','+(x.color||'#888')+'cc)"></div></div></div>'
  })
  s.innerHTML=h
}

function rD(){
  if(!data)return
  const d=data.banco.deudas||[]
  $('debtC',d.length)
  const c=document.getElementById('debtCtr')
  if(!c)return
  if(!d.length){c.innerHTML='<div style="text-align:center;padding:12px;color:var(--green);font-size:9px">🎉 Sin deudas</div>';return}
  let h=''
  d.forEach(x=>{h+='<div class="di"><span class="dn">'+x.nombre+'</span><div><span class="da">'+fm(x.monto)+'</span> <span class="ds">'+(x.estatus||'')+'</span></div></div>'})
  const t=d.reduce((s,x)=>s+(x.monto||0),0)
  h+='<div class="dt"><span>TOTAL</span><span class="ta">'+fm(t)+'</span></div>'
  c.innerHTML=h
}

function tabS(i,b){
  document.querySelectorAll('.tbb').forEach(x=>x.classList.remove('act'))
  b.classList.add('act')
}

function toast(t,m,i){
  if(!i)i='⚡'
  const c=document.getElementById('tsC')
  const n=document.createElement('div')
  n.className='ts'
  n.innerHTML='<div class="ts-ic">'+i+'</div><div class="ts-t"><h4>'+t+'</h4><p>'+m+'</p></div>'
  c.appendChild(n)
  setTimeout(()=>{n.classList.add('out');setTimeout(()=>n.remove(),400)},3000)
}

// LIVE DOT
function setLive(on){
  const dot=document.getElementById('liveDotI')
  const txt=document.getElementById('liveDotT')
  if(on){
    dot.className='dot'
    txt.textContent='EN VIVO'
    document.querySelector('.live-dot').style.borderColor='rgba(16,185,129,.12)'
  }else{
    dot.className='dot off'
    txt.textContent='SIN CONEXIÓN'
    document.querySelector('.live-dot').style.borderColor='rgba(239,68,68,.12)'
  }
}

// PARTICLES
;(function(){const c=document.getElementById('pc'),ctx=c.getContext('2d')
let W,H
function resize(){W=c.width=window.innerWidth;H=c.height=window.innerHeight;c.style.width=W+'px';c.style.height=H+'px'}
resize();window.addEventListener('resize',resize)
const pts=[]
for(let i=0;i<30;i++){pts.push({x:Math.random()*W,y:Math.random()*H,r:Math.random()*1.2+.5,vx:(Math.random()-.5)*.15,vy:(Math.random()-.5)*.15,o:Math.random()*.08+.02})}
function draw(){ctx.clearRect(0,0,W,H)
  for(let p of pts){p.x+=p.vx;p.y+=p.vy
    if(p.x<0||p.x>W)p.vx*=-1;if(p.y<0||p.y>H)p.vy*=-1
    ctx.beginPath();ctx.arc(p.x,p.y,p.r,0,Math.PI*2)
    ctx.fillStyle='rgba(99,102,241,'+p.o+')';ctx.fill()}
  for(let i=0;i<pts.length;i++){for(let j=i+1;j<pts.length;j++){let dx=pts[i].x-pts[j].x,dy=pts[i].y-pts[j].y,d=Math.sqrt(dx*dx+dy*dy)
    if(d<120){ctx.beginPath();ctx.moveTo(pts[i].x,pts[i].y);ctx.lineTo(pts[j].x,pts[j].y)
      ctx.strokeStyle='rgba(99,102,241,'+(.015*(1-d/120))+')';ctx.stroke()}}}
  requestAnimationFrame(draw)}
draw()})()

// REFRESH / SYNC
let lastUpdate=null

async function refresh(){
  const ok=await fd()
  if(!ok){setLive(false);return}
  setLive(true)

  if(pv!==0&&data.banco.capital!==pv){
    const d=data.banco.capital-pv
    toast('Capital actualizado',d<0?'Deuda '+fm(Math.abs(d)):'+'+fm(d),d<0?'💸':'💰')
  }
  pv=data.banco.capital

  if(lastUpdate&&data.ultimaActualizacion&&data.ultimaActualizacion!==lastUpdate){
    if(shDate)openD(shDate)
  }
  lastUpdate=data.ultimaActualizacion

  r()
}

// INIT
async function init(){
  document.getElementById('qaToday').addEventListener('click',function(){
    const today=new Date().toISOString().split('T')[0]
    if(fc[today]&&!fc[today].locked){
      if(fc[today].estado==='en_curso')saveDayState(today,'completado')
      else saveDayState(today,'en_curso')
    }else{
      toast('Hoy no disponible','El día de hoy no está disponible','📅')
    }
  })
  document.getElementById('qaLog').addEventListener('click',openLog)
  document.getElementById('qaRefresh').addEventListener('click',function(){
    toast('Sincronizando','Actualizando datos...','🔄')
    refresh()
  })
  document.getElementById('logO').addEventListener('click',closeLog)
  document.getElementById('shO').addEventListener('click',closeSh)
  await refresh()
  setInterval(refresh,5000)
}

document.addEventListener('DOMContentLoaded',init)
</script>
</body>
</html>
