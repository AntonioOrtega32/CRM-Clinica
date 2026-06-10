<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
  <title>Revista Digital</title>

  <style>
    :root{
      /* Paleta clínica */
      --teal:#1C6C73;
      --sky:#4298A7;
      --sand:#CDAF95;
      --sand-2:#DED5CE;
      --taupe:#C8BAAF;

      /* UI */
      --bg:#071018;           /* más profundo */
      --panel: rgba(255,255,255,.04);
      --panel2: rgba(255,255,255,.06);
      --muted:#b8c4d1;
      --text:#ffffff;

      --accent: var(--sky);
      --accent2: var(--teal);
      --border: rgba(255,255,255,.10);
      --shadow: 0 25px 60px rgba(0,0,0,.45);
    }

    *{ box-sizing:border-box; }
    html, body{
      height:100%;
      margin:0;
      font-family: system-ui, -apple-system, Segoe UI, Roboto, Arial;
      background:
        radial-gradient(900px 500px at 50% -10%, rgba(66,152,167,.22), transparent 55%),
        radial-gradient(800px 420px at 15% 15%, rgba(28,108,115,.20), transparent 55%),
        radial-gradient(600px 360px at 85% 25%, rgba(205,175,149,.10), transparent 60%),
        var(--bg);
      color: var(--text);
    }
    body{ overscroll-behavior:none; }

    .wrap{ min-height:100%; display:flex; flex-direction:column; }

    /* TOPBAR */
    .topbar{
      position: sticky; top:0; z-index:50;
      padding-top: env(safe-area-inset-top);
      background:
        linear-gradient(180deg, rgba(7,16,24,.92), rgba(7,16,24,.72));
      backdrop-filter: blur(12px);
      border-bottom: 1px solid rgba(66,152,167,.25);
      box-shadow: 0 12px 30px rgba(0,0,0,.30);
    }
    .topbar-inner{
      max-width: 1100px;
      margin:0 auto;
      padding: 10px 12px;
      display:flex;
      align-items:center;
      gap:10px;
      flex-wrap:wrap;
    }
    .brand{
      display:flex;
      align-items:center;
      gap:10px;
      font-weight:800;
      letter-spacing:.2px;
    }
    .brand-badge{
      width:12px; height:12px; border-radius:999px;
      background: linear-gradient(180deg, var(--sky), var(--teal));
      box-shadow: 0 0 0 6px rgba(66,152,167,.12);
    }
    .pill{
      color: rgba(255,255,255,.75);
      font-size: 13px;
      padding: 6px 10px;
      border-radius: 999px;
      background: rgba(255,255,255,.05);
      border: 1px solid rgba(255,255,255,.08);
      font-variant-numeric: tabular-nums;
    }

    .controls{
      margin-left:auto;
      display:flex;
      gap:8px;
      align-items:center;
      flex-wrap:wrap;
    }

    /* Buttons */
    button.ctrl{
      border: 1px solid rgba(66,152,167,.25);
      background: rgba(255,255,255,.06);
      color:#fff;
      padding: 9px 11px;
      border-radius: 12px;
      cursor:pointer;
      transition: .15s ease;
      display:flex;
      align-items:center;
      gap:8px;
      user-select:none;
      white-space: nowrap;
      box-shadow: 0 10px 20px rgba(0,0,0,.18);
    }
    button.ctrl:hover{
      background: rgba(255,255,255,.10);
      border-color: rgba(66,152,167,.40);
      transform: translateY(-1px);
    }
    button.ctrl:active{
      transform: translateY(0);
    }
    button.ctrl:disabled{
      opacity:.45;
      cursor:not-allowed;
      transform:none;
      box-shadow:none;
    }

    /* Variantes */
    .ctrl.primary{
      background: linear-gradient(180deg, rgba(28,108,115,.92), rgba(28,108,115,.70));
      border-color: rgba(66,152,167,.55);
    }
    .ctrl.primary:hover{
      background: linear-gradient(180deg, rgba(28,108,115,.98), rgba(28,108,115,.78));
    }

    .ctrl.soft{
      border-color: rgba(205,175,149,.35);
      background: rgba(205,175,149,.08);
    }

    .ctrl .k{
      font-variant-numeric: tabular-nums;
      color: rgba(255,255,255,.75);
      font-size:13px;
    }
    .zoom{
      min-width: 64px;
      text-align:center;
      font-variant-numeric: tabular-nums;
      color: rgba(255,255,255,.78);
      padding: 8px 10px;
      border-radius: 12px;
      background: rgba(255,255,255,.05);
      border: 1px solid rgba(255,255,255,.08);
    }

    .main{
      flex:1;
      max-width: 1100px;
      width: 100%;
      margin: 16px auto 18px;
      padding: 0 12px;
    }

    .viewer{
      background:
        radial-gradient(1100px 520px at 50% 0%, rgba(66,152,167,.20), transparent 60%),
        radial-gradient(900px 420px at 10% 10%, rgba(28,108,115,.16), transparent 60%),
        var(--panel);
      border: 1px solid rgba(66,152,167,.18);
      border-radius: 20px;
      padding: 14px;
      box-shadow: var(--shadow);
    }

    .loading{
      display:flex;
      align-items:center;
      gap:10px;
      color: rgba(255,255,255,.75);
      padding: 8px 4px;
    }
    .dot{
      width:10px; height:10px; border-radius:50%;
      background: var(--accent);
      box-shadow: 0 0 0 6px rgba(66,152,167,.16);
      animation:pulse 1.2s infinite;
    }
    @keyframes pulse {
      0%{ transform:scale(.9); }
      50%{ transform:scale(1.15); }
      100%{ transform:scale(.9); }
    }

    /* Canvas */
    .canvas-wrap{
      position: relative;
      display:flex;
      justify-content:center;
      align-items:center;
      overflow: hidden;
      padding: 6px 0;
      touch-action: pan-y;
    }

    canvas{
      border-radius: 16px;
      background:#fff;
      box-shadow: 0 24px 55px rgba(0,0,0,.45);
      max-width: 100%;
      height: auto;
      outline: 1px solid rgba(205,175,149,.18);
    }

    .hint{
      margin-top:10px;
      color: rgba(255,255,255,.65);
      font-size: 13px;
      text-align:center;
    }

    /* Watermark */
    .watermark{
      position:absolute;
      inset:0;
      pointer-events:none;
      display:grid;
      place-items:center;
      opacity: .10;
      font-weight: 900;
      font-size: 22px;
      color: rgba(28,108,115,.95); /* teal */
      text-transform: uppercase;
      transform: rotate(-30deg);
      user-select:none;
      letter-spacing: .6px;
      text-shadow: 0 2px 0 rgba(255,255,255,.35);
    }
    .watermark span{ display:block; margin: 13px 0; }

    /* Mobile tuning */
    @media (max-width: 768px){
      .main{ margin:0; padding:0; max-width:100%; }
      .viewer{
        border-radius: 0;
        border: 0;
        padding: 10px;
        min-height: calc(100vh - 64px);
        box-shadow:none;
        background:
          radial-gradient(900px 420px at 50% 0%, rgba(66,152,167,.20), transparent 65%),
          rgba(255,255,255,.03);
      }
      .hint{ display:none; }

      .topbar-inner{ padding: 10px 10px; gap:8px; }
      .controls{ gap:6px; }

      /* chips compactos */
      button.ctrl{
        padding: 9px 10px;
        border-radius: 999px;
      }
      button.ctrl span:not(.k){ display:none; } /* oculta texto en mobile */
      .zoom{
        min-width: 56px;
        border-radius: 999px;
      }
      .pill{
        padding: 6px 10px;
        border-radius: 999px;
      }
    }
  </style>
</head>

<body>
<div class="wrap">
  <div class="topbar">
    <div class="topbar-inner">
      <div class="brand">
        <span class="brand-badge"></span>
        <span>Revista Digital</span>
      </div>

      <div class="pill" id="statusText">Cargando…</div>

      <div class="controls">
        <button class="ctrl soft" id="prevBtn" title="Anterior">◀ <span>Anterior</span></button>
        <button class="ctrl soft" id="nextBtn" title="Siguiente"><span>Siguiente</span> ▶</button>

        <button class="ctrl" id="zoomOutBtn" title="Zoom -">−</button>
        <div class="zoom" id="zoomLabel">100%</div>
        <button class="ctrl" id="zoomInBtn" title="Zoom +">+</button>

        <button class="ctrl primary" id="fitBtn" title="Ajustar">↔ <span class="k">Ajustar</span></button>
        <button class="ctrl primary" id="fsBtn" title="Pantalla completa">⛶ <span class="k">Full</span></button>
      </div>
    </div>
  </div>

  <div class="main">
    <div class="viewer">
      <div class="loading" id="loadingRow">
        <div class="dot"></div>
        <div>Cargando PDF…</div>
      </div>

      <div class="canvas-wrap" id="canvasWrap" style="display:none;">
        <canvas id="pdfCanvas"></canvas>

        <div class="watermark">
          <span>{{ $watermark }}</span>
          <span>{{ $watermark2 }}</span>
        </div>
      </div>

      <div class="hint">Tip: usa la rueda del mouse para scroll dentro del visor.</div>
    </div>
  </div>
</div>

<script type="module">
  import * as pdfjsLib from "https://cdn.jsdelivr.net/npm/pdfjs-dist@4.10.38/build/pdf.min.mjs";
  pdfjsLib.GlobalWorkerOptions.workerSrc =
    "https://cdn.jsdelivr.net/npm/pdfjs-dist@4.10.38/build/pdf.worker.min.mjs";

  const url = @json($pdfUrl);

  const canvas = document.getElementById("pdfCanvas");
  const ctx = canvas.getContext("2d", { alpha: false });

  const statusText = document.getElementById("statusText");
  const loadingRow = document.getElementById("loadingRow");
  const canvasWrap = document.getElementById("canvasWrap");

  const prevBtn = document.getElementById("prevBtn");
  const nextBtn = document.getElementById("nextBtn");
  const zoomInBtn = document.getElementById("zoomInBtn");
  const zoomOutBtn = document.getElementById("zoomOutBtn");
  const zoomLabel = document.getElementById("zoomLabel");
  const fitBtn = document.getElementById("fitBtn");
  const fsBtn = document.getElementById("fsBtn");

  let pdfDoc = null;
  let pageNum = 1;
  let scale = 1.0;
  let rendering = false;
  let pendingPage = null;

  function setStatus(){
    if (!pdfDoc) return;
    statusText.textContent = `Página ${pageNum} / ${pdfDoc.numPages}`;
    prevBtn.disabled = pageNum <= 1;
    nextBtn.disabled = pageNum >= pdfDoc.numPages;
    zoomLabel.textContent = `${Math.round(scale * 100)}%`;
  }

  function isMobile(){
    return window.matchMedia("(max-width: 768px)").matches;
  }

  function getFitScale(page){
    const viewport1 = page.getViewport({ scale: 1 });

    const wrapWidth = canvasWrap.clientWidth || window.innerWidth;
    const targetWidth = Math.max(320, wrapWidth - 16);
    const scaleByWidth = targetWidth / viewport1.width;

    if (!isMobile()) return Math.min(Math.max(scaleByWidth, 0.6), 2.2);

    const topbarH = document.querySelector(".topbar")?.offsetHeight ?? 64;
    const targetHeight = Math.max(320, window.innerHeight - topbarH - 40);
    const scaleByHeight = targetHeight / viewport1.height;

    return Math.min(Math.max(scaleByHeight, scaleByWidth), 2.2);
  }

  async function renderPage(num){
    rendering = true;

    const page = await pdfDoc.getPage(num);
    const viewport = page.getViewport({ scale });

    canvas.width = Math.floor(viewport.width);
    canvas.height = Math.floor(viewport.height);

    const task = page.render({ canvasContext: ctx, viewport });
    await task.promise;

    rendering = false;

    if (pendingPage !== null){
      const p = pendingPage;
      pendingPage = null;
      renderPage(p);
    }
  }

  function queueRender(num){
    if (rendering) pendingPage = num;
    else renderPage(num);
  }

  function goPrev(){
    if (pageNum <= 1) return;
    pageNum--;
    setStatus();
    queueRender(pageNum);
  }
  function goNext(){
    if (!pdfDoc || pageNum >= pdfDoc.numPages) return;
    pageNum++;
    setStatus();
    queueRender(pageNum);
  }

  prevBtn.addEventListener("click", goPrev);
  nextBtn.addEventListener("click", goNext);

  zoomInBtn.addEventListener("click", () => {
    scale = Math.min(scale + 0.15, 3);
    setStatus();
    queueRender(pageNum);
  });

  zoomOutBtn.addEventListener("click", () => {
    scale = Math.max(scale - 0.15, 0.5);
    setStatus();
    queueRender(pageNum);
  });

  fitBtn.addEventListener("click", async () => {
    const page = await pdfDoc.getPage(pageNum);
    scale = getFitScale(page);
    setStatus();
    queueRender(pageNum);
  });

  let resizeTimer = null;
  window.addEventListener("resize", () => {
    clearTimeout(resizeTimer);
    resizeTimer = setTimeout(async () => {
      if (!pdfDoc) return;
      const page = await pdfDoc.getPage(pageNum);
      scale = getFitScale(page);
      setStatus();
      queueRender(pageNum);
    }, 180);
  });

  // “Anti” print/save (no infalible)
  document.addEventListener("contextmenu", e => e.preventDefault());
  document.addEventListener("keydown", (e) => {
    const k = e.key.toLowerCase();
    if ((e.ctrlKey || e.metaKey) && (k === "p" || k === "s")) e.preventDefault();
  });

  // Swipe móvil
  let sx=0, sy=0, ex=0, ey=0;
  const SWIPE_MIN_X = 55;
  const SWIPE_MAX_Y = 60;

  canvasWrap.addEventListener("touchstart", (e) => {
    const t = e.touches[0];
    sx = ex = t.clientX;
    sy = ey = t.clientY;
  }, { passive:true });

  canvasWrap.addEventListener("touchmove", (e) => {
    const t = e.touches[0];
    ex = t.clientX;
    ey = t.clientY;
  }, { passive:true });

  canvasWrap.addEventListener("touchend", () => {
    const dx = ex - sx;
    const dy = ey - sy;
    if (Math.abs(dy) > SWIPE_MAX_Y) return;
    if (dx <= -SWIPE_MIN_X) goNext();
    else if (dx >= SWIPE_MIN_X) goPrev();
  });

  // Fullscreen
  function isFullscreen(){
    return document.fullscreenElement || document.webkitFullscreenElement;
  }
  async function toggleFullscreen(){
    const el = document.documentElement;
    try{
      if (!isFullscreen()){
        if (el.requestFullscreen) await el.requestFullscreen();
        else if (el.webkitRequestFullscreen) await el.webkitRequestFullscreen();
      } else {
        if (document.exitFullscreen) await document.exitFullscreen();
        else if (document.webkitExitFullscreen) await document.webkitExitFullscreen();
      }
    }catch(e){
      console.warn("No se pudo activar fullscreen:", e);
    }
  }
  fsBtn?.addEventListener("click", toggleFullscreen);

  // Init
  try{
    pdfDoc = await pdfjsLib.getDocument({ url }).promise;

    const firstPage = await pdfDoc.getPage(1);
    scale = getFitScale(firstPage);

    loadingRow.style.display = "none";
    canvasWrap.style.display = "flex";

    setStatus();
    await renderPage(pageNum);
  }catch(err){
    console.error(err);
    statusText.textContent = "Error cargando el PDF.";
    loadingRow.innerHTML = `<div style="color:#fca5a5">No se pudo cargar el PDF.</div>`;
  }
</script>
</body>
</html>
