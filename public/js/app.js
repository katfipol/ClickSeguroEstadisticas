(() => {
  'use strict';

  // --- Validación de formato (HU-02) ---
  const esUrl = (v) => {
    v = v.trim();
    if (!v || /\s/.test(v)) return false;
    try {
      const u = new URL(/^[a-z][a-z0-9+.-]*:\/\//i.test(v) ? v : 'http://' + v);
      return ['http:', 'https:'].includes(u.protocol) && u.hostname.includes('.');
    } catch { return false; }
  };

  document.querySelectorAll('form[data-url-form]').forEach((form) => {
    form.addEventListener('submit', (e) => {
      const input = form.querySelector('input[name=url]');
      const error = form.querySelector('.js-error');
      let msg = '';
      if (!input.value.trim()) msg = 'Ingresa una URL.';
      else if (!esUrl(input.value)) msg = 'El formato de la URL no es válido. Ejemplo: https://ejemplo.com';
      if (msg) { e.preventDefault(); error.textContent = msg; input.focus(); return; }
      error.textContent = '';
      const boton = form.querySelector('button[type=submit]');
      boton.disabled = true; boton.textContent = 'Procesando…';
    });
  });

  // --- Botón pegar ---
  document.querySelectorAll('[data-paste]').forEach((btn) => {
    btn.addEventListener('click', async () => {
      const form = btn.closest('form'), input = form.querySelector('input[name=url]'), error = form.querySelector('.js-error');
      try { input.value = (await navigator.clipboard.readText()).trim(); error.textContent = ''; input.focus(); }
      catch { error.textContent = 'No se pudo leer el portapapeles. Pega con Ctrl+V.'; }
    });
  });

  // --- Confirmación antes de eliminar (HU-06) ---
  document.querySelectorAll('form[data-confirm]').forEach((form) => {
    form.addEventListener('submit', (e) => { if (!confirm(form.dataset.confirm)) e.preventDefault(); });
  });

  // --- Lectura de QR en el navegador (HU-08). La URL nunca se abre automáticamente. ---
  const archivo = document.getElementById('qr-file');
  if (!archivo) return;

  const cargarJsQR = () => window.jsQR ? Promise.resolve() : new Promise((ok, ko) => {
    const s = document.createElement('script');
    s.src = '/js/vendor/jsQR.js'; s.onload = ok; s.onerror = () => ko(new Error('jsQR no disponible'));
    document.head.appendChild(s);
  });

  const leerConJsQR = async (bmp) => {
    await cargarJsQR();
    const c = document.createElement('canvas'); c.width = bmp.width; c.height = bmp.height;
    const ctx = c.getContext('2d'); ctx.drawImage(bmp, 0, 0);
    const d = ctx.getImageData(0, 0, c.width, c.height);
    return window.jsQR(d.data, d.width, d.height)?.data ?? null;
  };

  archivo.addEventListener('change', async () => {
    const f = archivo.files[0], msg = document.getElementById('qr-msg');
    const input = document.querySelector('form[data-url-form] input[name=url]');
    if (!f) return;
    if (!['image/png', 'image/jpeg', 'image/webp'].includes(f.type) || f.size > 2 * 1024 * 1024) {
      msg.textContent = 'Usa una imagen PNG, JPG o WEBP de hasta 2 MB.'; return;
    }
    msg.textContent = 'Leyendo el código QR…';
    try {
      const bmp = await createImageBitmap(f);
      let texto = null;
      if ('BarcodeDetector' in window) {
        const r = await new BarcodeDetector({ formats: ['qr_code'] }).detect(bmp);
        texto = r[0]?.rawValue ?? null;
      }
      if (texto === null) texto = await leerConJsQR(bmp);
      if (texto === null) { msg.textContent = 'No se encontró un código QR legible en la imagen.'; return; }
      if (!esUrl(texto)) { msg.textContent = 'El QR no contiene una URL válida.'; return; }
      input.value = texto.trim();
      msg.textContent = 'URL encontrada. Revísala y pulsa Analizar; no se abre automáticamente.';
    } catch {
      msg.textContent = 'No se pudo procesar la imagen. Prueba con otra o pega la URL a mano.';
    }
  });
})();
