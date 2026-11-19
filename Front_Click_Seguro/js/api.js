// 1. CONFIGURACIÓN DE URL:
// Si usas "php artisan serve" en Laravel, usa el puerto 8000: 'http://127.0.0.1:8000/api/v1'
// Si usas MAMP/Docker en el puerto 8888, déjalo en 'http://localhost:8888/api/v1'
const API_BASE_URL = 'http://127.0.0.1:8000/api/v1';

// Helper para obtener cabeceras con el Token Bearer de Sanctum
function getHeaders(isJson = true) {
    const headers = {
        'Accept': 'application/json',
        'X-Requested-With': 'XMLHttpRequest'
    };
    if (isJson) {
        headers['Content-Type'] = 'application/json';
    }
    const token = localStorage.getItem('token');
    if (token) {
        headers['Authorization'] = `Bearer ${token}`;
    }
    return headers;
}

// Petición genérica a la API
async function apiRequest(endpoint, method = 'GET', data = null) {
    const config = {
        method: method,
        headers: getHeaders(true)
    };

    if (data) {
        config.body = JSON.stringify(data);
    }

    const response = await fetch(`${API_BASE_URL}${endpoint}`, config);

    if (response.status === 401) {
        localStorage.removeItem('token');
        localStorage.removeItem('user');
        window.location.href = 'index.html';
        throw new Error('Sesión expirada o no autenticado.');
    }

    const json = await response.json();

    if (!response.ok) {
        let mensajeDetallado = json?.message || 'Error en la petición.';
        if (json?.errors && typeof json.errors === 'object') {
            const listaErrores = Object.values(json.errors).flat();
            if (listaErrores.length > 0) {
                mensajeDetallado = listaErrores.join(' | ');
            }
        }
        throw new Error(mensajeDetallado);
    }

    return json;
}

// ---------------- Auth API ----------------
export const Auth = {
    async login(email, password) {
        const response = await fetch(`${API_BASE_URL}/auth/login`, {
            method: 'POST',
            headers: getHeaders(true),
            body: JSON.stringify({ email, password })
        });
        const json = await response.json();

        // Guardamos el token automáticamente si el login fue exitoso
        if (response.ok && json.data?.token) {
            localStorage.setItem('token', json.data.token);
        }

        // Adaptamos la salida para que index.html lea data.usuario sin cambios
        return {
            ok: response.ok,
            status: response.status,
            json: async () => ({
                message: json.message,
                usuario: json.data?.usuario
            })
        };
    },

    async register(userData) {
        const response = await fetch(`${API_BASE_URL}/auth/register`, {
            method: 'POST',
            headers: getHeaders(true),
            body: JSON.stringify(userData)
        });
        const json = await response.json();

        if (response.ok && json.data?.token) {
            localStorage.setItem('token', json.data.token);
        }

        const primerError = json.errors ? Object.values(json.errors)[0][0] : null;

        return {
            ok: response.ok,
            status: response.status,
            json: async () => ({
                message: primerError || json.message,
                usuario: json.data?.usuario
            })
        };
    },

    async logout() {
        try {
            await apiRequest('/auth/logout', 'POST');
        } finally {
            localStorage.removeItem('token');
            localStorage.removeItem('user');
        }
    },

    async me() {
        const json = await apiRequest('/auth/me', 'GET');
        return json.data;
    }
};

// ---------------- Análisis API ----------------
export const Analyses = {
    async create(url) {
        const json = await apiRequest('/analyses', 'POST', { url });
        return json.data;
    },
    async getAll() {
        const json = await apiRequest('/analyses', 'GET');
        return json.data; // Devuelve el array de análisis
    },
    async getById(id) {
        const json = await apiRequest(`/analyses/${id}`, 'GET');
        return json.data;
    },
    async getStatus(id) {
        const json = await apiRequest(`/analyses/${id}/status`, 'GET');
        return json.data;
    },
    async reanalyze(id) {
        const json = await apiRequest(`/analyses/${id}/reanalyze`, 'POST');
        return json.data;
    },
    async delete(id) {
        return await apiRequest(`/analyses/${id}`, 'DELETE');
    },
    async downloadReport(id) {
     const response = await fetch(`${API_BASE_URL}/analyses/${id}/report`, {
         method: 'GET',
         headers: getHeaders(false)
     });
     if (!response.ok) throw new Error('No se pudo generar el informe PDF.');
     const blob = await response.blob();
     const url = window.URL.createObjectURL(blob);
     const a = document.createElement('a');
     a.href = url;
     a.download = `informe-analisis-${id}.pdf`;
     document.body.appendChild(a);
     a.click();
     a.remove();
     window.URL.revokeObjectURL(url);
 }

};

// ---------------- Archivos API ----------------
export const Files = {
    async verify(sha256, nombre, tamano) {
        const json = await apiRequest('/files', 'POST', { sha256, nombre, tamano });
        // archivos.html espera result.archivo.id_archivo
        return { archivo: json.data };
    },
    async getAll() {
        const json = await apiRequest('/files', 'GET');
        return json.data;
    },
    async getById(id) {
        const json = await apiRequest(`/files/${id}`, 'GET');
        return json.data;
    },
    async delete(id) {
        return await apiRequest(`/files/${id}`, 'DELETE');
    },
    async downloadReport(id) {
     const response = await fetch(`${API_BASE_URL}/files/${id}/report`, {
         method: 'GET',
         headers: getHeaders(false)
     });
     if (!response.ok) throw new Error('No se pudo generar el informe PDF.');
     const blob = await response.blob();
     const url = window.URL.createObjectURL(blob);
     const a = document.createElement('a');
     a.href = url;
     a.download = `informe-archivo-${id}.pdf`;
     document.body.appendChild(a);
     a.click();
     a.remove();
     window.URL.revokeObjectURL(url);
    }
};

// ---------------- Documentos API ----------------
export const Documents = {
    async upload(formData) {
        const response = await fetch(`${API_BASE_URL}/documents`, {
            method: 'POST',
            headers: getHeaders(false), // false para que el navegador ponga el boundary de FormData
            body: formData
        });
        const json = await response.json();
        return {
            ok: response.ok,
            json: async () => ({
                documento: json.data,
                error: json.message
            })
        };
    },
    async getAll() {
        const json = await apiRequest('/documents', 'GET');
        return json.data.data ?? json.data;
    },
    async getById(id) {
        const json = await apiRequest(`/documents/${id}`, 'GET');
        return json.data;
    },
    async getStatus(id) {
        const json = await apiRequest(`/documents/${id}/status`, 'GET');
        return json.data;
    }
};

// ---------------- Dominios API ----------------
export const Domains = {
    async lookup(nombre) {
        const json = await apiRequest(`/domains/lookup?nombre=${encodeURIComponent(nombre)}`, 'GET');
        // Adaptado a lo que espera dominios.html (data.nombre, data.ficha.tipo, data.ficha.dominio, data.ficha.datos)
        return {
            nombre: json.data.consulta,
            ficha: {
                tipo: json.data.tipo,
                dominio: json.data.dominio,
                datos: json.data.ip_info
            },
            previos: json.data.analisis_previos
        };
    }
};

// ---------------- Estadísticas API ----------------
export const Statistics = {
    async personal() {
        const json = await apiRequest('/statistics/personal', 'GET');
        return json.data;
    },
    async global(dias = 30) {
        const json = await apiRequest(`/statistics/global?dias=${dias}`, 'GET');
        return json.data;
    }
};

// ---------------- Reportes API ----------------
export const Reports = {
    async store(url, motivo = '') {
        const json = await apiRequest('/reports', 'POST', { url, motivo });
        return json.data;
    },
    async create(url, motivo = '') {
        const json = await apiRequest('/reports', 'POST', { url, motivo });
        return json.data;
    }
};
window.Reports = Reports;

// ---------------- Utilidades de Interfaz (Modales y Bloqueo de Botones) ----------------
// Inyectar estilos del Modal y Bloqueo para evitar problemas de caché en el navegador
if (!document.getElementById('cyber-ui-styles')) {
    const style = document.createElement('style');
    style.id = 'cyber-ui-styles';
    style.textContent = `
        .cyber-modal-overlay {
            position: fixed !important;
            top: 0 !important;
            left: 0 !important;
            width: 100vw !important;
            height: 100vh !important;
            background: rgba(0, 0, 0, 0.85) !important;
            backdrop-filter: blur(4px);
            display: flex !important;
            align-items: center !important;
            justify-content: center !important;
            z-index: 99999 !important;
        }
        .cyber-modal {
            background: #0a0e0a;
            border: 1px solid #00ff41;
            border-radius: 8px;
            padding: 1.75rem;
            width: 90%;
            max-width: 420px;
            box-shadow: 0 0 30px rgba(0, 255, 65, 0.3);
            text-align: center;
            font-family: var(--font-display, monospace);
        }
        .cyber-modal h3 {
            color: #00ff41;
            margin-bottom: 0.75rem;
            font-size: 1.15rem;
            letter-spacing: 1px;
        }
        .cyber-modal p {
            color: #d1f7dc;
            font-family: var(--font-body, sans-serif);
            font-size: 0.95rem;
            margin-bottom: 1.25rem;
        }
        .cyber-modal-actions {
            display: flex;
            justify-content: center;
            gap: 1rem;
        }
        .btn-locked {
            opacity: 0.5 !important;
            cursor: not-allowed !important;
            box-shadow: none !important;
            transform: none !important;
        }
        .acciones-celda {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
        }
        .input-error {
            border-color: var(--cyber-red, #ff0040) !important;
            box-shadow: 0 0 8px rgba(255, 0, 64, 0.25) !important;
        }
    `;
    document.head.appendChild(style);
}

export const UI = {
    // Bloquea el botón visualmente pero permite detectar si el usuario insiste haciendo clic
    lockButton(btn, loadingText = 'Procesando...') {
        if (!btn) return;
        if (btn.dataset.locked !== 'true') {
            btn.dataset.originalText = btn.textContent;
        }
        btn.dataset.locked = 'true';
        btn.classList.add('btn-locked');
        btn.textContent = loadingText;
    },

    unlockButton(btn) {
        if (!btn) return;
        btn.dataset.locked = 'false';
        btn.classList.remove('btn-locked');
        if (btn.dataset.originalText) {
            btn.textContent = btn.dataset.originalText;
        }
    },

    // Modal temporal que se cierra solo después de unos segundos
    notice(titulo, mensaje, duracionMs = 2200) {
        if (document.getElementById('cyber-notice-modal')) return;
        const overlay = document.createElement('div');
        overlay.id = 'cyber-notice-modal';
        overlay.className = 'cyber-modal-overlay';
        overlay.innerHTML = `
            <div class="cyber-modal" style="border-color: var(--cyber-cyan, #00e5ff);">
                <h3 style="color: var(--cyber-cyan, #00e5ff);">${titulo}</h3>
                <p style="margin-bottom: 0;">${mensaje}</p>
            </div>
        `;
        document.body.appendChild(overlay);
        setTimeout(() => overlay.remove(), duracionMs);
    },

    // Modal emergente con botón Aceptar
    alert(titulo = 'Aviso', mensaje = '') {
        return new Promise((resolve) => {
            const overlay = document.createElement('div');
            overlay.className = 'cyber-modal-overlay';
            overlay.innerHTML = `
                <div class="cyber-modal">
                    <h3>${titulo}</h3>
                    <p>${mensaje}</p>
                    <div class="cyber-modal-actions">
                        <button type="button" class="btn" id="cyber-modal-ok">Aceptar</button>
                    </div>
                </div>
            `;
            document.body.appendChild(overlay);
            overlay.querySelector('#cyber-modal-ok').addEventListener('click', () => {
                overlay.remove();
                resolve();
            });
        });
    },

    // Modal emergente de confirmación (Cancelar / Confirmar)
    confirm(titulo, mensaje) {
        return new Promise((resolve) => {
            const overlay = document.createElement('div');
            overlay.className = 'cyber-modal-overlay';
            overlay.innerHTML = `
                <div class="cyber-modal">
                    <h3>${titulo}</h3>
                    <p>${mensaje}</p>
                    <div class="cyber-modal-actions">
                        <button type="button" class="btn sec" id="cyber-modal-cancel">Cancelar</button>
                        <button type="button" class="btn" id="cyber-modal-confirm">Confirmar</button>
                    </div>
                </div>
            `;
            document.body.appendChild(overlay);
            overlay.querySelector('#cyber-modal-cancel').addEventListener('click', () => {
                overlay.remove();
                resolve(false);
            });
            overlay.querySelector('#cyber-modal-confirm').addEventListener('click', () => {
                overlay.remove();
                resolve(true);
            });
        });
    },

    // Modal emergente con barra de progreso
    progressModal(titulo = 'Reanalizando URL...') {
        const overlay = document.createElement('div');
        overlay.className = 'cyber-modal-overlay';
        overlay.innerHTML = `
            <div class="cyber-modal">
                <h3>${titulo}</h3>
                <div style="background:#030804; height:12px; border-radius:6px; overflow:hidden; margin:1rem 0; border:1px solid var(--cyber-cyan);">
                    <div id="modal-progress-bar" style="background:var(--cyber-green); width:5%; height:100%; transition:width 0.5s;"></div>
                </div>
                <p class="muted" style="margin-bottom:0;">Tiempo transcurrido: <span id="modal-timer" style="color:var(--cyber-cyan); font-weight:bold;">0</span>s</p>
            </div>
        `;
        document.body.appendChild(overlay);

        let seconds = 0;
        const bar = overlay.querySelector('#modal-progress-bar');
        const timerEl = overlay.querySelector('#modal-timer');
        const interval = setInterval(() => {
            seconds++;
            timerEl.textContent = seconds;
            bar.style.width = Math.min((seconds / 20) * 90, 90) + '%';
        }, 1000);

        return {
            completeAndRedirect(urlDestino) {
                clearInterval(interval);
                bar.style.width = '100%';
                setTimeout(() => {
                    overlay.remove();
                    window.location.href = urlDestino;
                }, 400);
            },
            close() {
                clearInterval(interval);
                overlay.remove();
            }
        };
    },
    // Devuelve la clase CSS de color segun el nivel de riesgo
    obtenerClaseTextoRiesgo(nivel = '') {
        const valor = (nivel || '').toLowerCase();
        if (valor.includes('peligroso') || valor.includes('fallido')) return 'texto-riesgo--peligroso';
        if (valor.includes('sospechoso') || valor.includes('medio')) return 'texto-riesgo--sospechoso';
        if (valor.includes('seguro')) return 'texto-riesgo--seguro';
        return 'texto-riesgo--desconocido';
    },

    // Clasifica el resultado individual de un motor o categoria para colorearlo y ordenarlo
    clasificarResultadoMotor(resultado = '', categoria = '') {
        const texto = `${resultado} ${categoria}`.toLowerCase();
        if (
            texto.includes('malicious') || texto.includes('malicioso') ||
            texto.includes('peligroso') || texto.includes('phishing') ||
            texto.includes('suplantaci') || texto.includes('malware') ||
            texto.includes('estafa') || texto.includes('fraude') ||
            texto.includes('ransomware') || texto.includes('troyano') ||
            texto.includes('spyware') || texto.includes('botnet')
        ) {
            return { nivel: 'peligroso', peso: 3, claseFila: 'fila-riesgo--peligroso', claseBadge: 'riesgo-peligroso' };
        }
        if (
            texto.includes('suspicious') || texto.includes('sospechoso') ||
            texto.includes('riesgo') || texto.includes('spam') ||
            texto.includes('correo no deseado') || texto.includes('adware') ||
            texto.includes('miner')
        ) {
            return { nivel: 'sospechoso', peso: 2, claseFila: 'fila-riesgo--sospechoso', claseBadge: 'riesgo-sospechoso' };
        }
        if (texto.includes('clean') || texto.includes('harmless') || texto.includes('limpio') || texto.includes('seguro') || texto.includes('inofensivo')) {
            return { nivel: 'seguro', peso: 1, claseFila: '', claseBadge: 'riesgo-seguro' };
        }
        return { nivel: 'desconocido', peso: 0, claseFila: '', claseBadge: 'riesgo-desconocido' };
    },

    // Ordena la lista de motores poniendo primero los peligrosos y sospechosos
    ordenarDetallesPorRiesgo(detalles = []) {
        return [...detalles].sort((a, b) => {
            const infoA = this.clasificarResultadoMotor(a.resultado, a.categoria || a.descripcion);
            const infoB = this.clasificarResultadoMotor(b.resultado, b.categoria || b.descripcion);
            return infoB.peso - infoA.peso;
        });
    },
    // Traduce al espanol cualquier categoria o resultado en ingles de VirusTotal
    traducirTerminoSeguridad(texto = '') {
        if (!texto || texto === '—') return '—';
        const clave = String(texto).trim().toLowerCase();
        const mapa = {
            'malicious': 'Malicioso',
            'suspicious': 'Sospechoso',
            'phishing': 'Phishing / Suplantación',
            'malware': 'Malware / Código dañino',
            'harmless': 'Inofensivo / Seguro',
            'clean': 'Limpio',
            'undetected': 'Sin detecciones',
            'unrated': 'Sin calificar',
            'spam': 'Spam / Correo no deseado',
            'scam': 'Estafa / Fraude',
            'ransomware': 'Ransomware',
            'trojan': 'Troyano'
        };
        return mapa[clave] || texto;
    }
};

// Interceptor global: si el usuario hace clic en cualquier botón bloqueado, muestra el aviso automático
document.addEventListener('click', function(e) {
    const lockedBtn = e.target.closest('[data-locked="true"]');
    if (lockedBtn) {
        e.preventDefault();
        e.stopPropagation();
        UI.notice('Operación en curso', 'Tu solicitud ya se está procesando. Por favor, espera unos segundos.');
    }
}, true);

// Interceptor global para evitar doble envío con la tecla Enter en formularios bloqueados
document.addEventListener('submit', function(e) {
    const lockedBtn = e.target.querySelector('[data-locked="true"]');
    if (lockedBtn) {
        e.preventDefault();
        e.stopPropagation();
        UI.notice('Operación en curso', 'Tu solicitud ya se está procesando. Por favor, espera unos segundos.');
    }
}, true);

// Inicialización global de UX: Menú Hamburguesa, Drag & Drop real y limpieza de inputs
document.addEventListener('DOMContentLoaded', () => {
    // 1. Inyectar botón de Menú Hamburguesa en todas las barras de navegación
    const headerNav = document.querySelector('.nav');
    const menuLinks = document.querySelector('.nav nav');
    if (headerNav && menuLinks && !document.querySelector('.menu-toggle')) {
        const toggleBtn = document.createElement('button');
        toggleBtn.type = 'button';
        toggleBtn.className = 'menu-toggle';
        toggleBtn.setAttribute('aria-label', 'Abrir menú');
        toggleBtn.textContent = '☰';
        toggleBtn.addEventListener('click', () => {
            const abierto = menuLinks.classList.toggle('nav-open');
            toggleBtn.textContent = abierto ? '✕' : '☰';
        });
        headerNav.insertBefore(toggleBtn, menuLinks);
    }

    // 2. Activar Drag & Drop real en las zonas de carga de Archivos y Documentos
    document.querySelectorAll('.file-upload-area').forEach(zone => {
        const fileInput = zone.querySelector('input[type="file"]');
        if (!fileInput) return;

        ['dragenter', 'dragover'].forEach(evt => {
            zone.addEventListener(evt, (e) => {
                e.preventDefault();
                e.stopPropagation();
                zone.classList.add('file-drag-over');
            });
        });

        ['dragleave', 'drop'].forEach(evt => {
            zone.addEventListener(evt, (e) => {
                e.preventDefault();
                e.stopPropagation();
                zone.classList.remove('file-drag-over');
            });
        });

        zone.addEventListener('drop', (e) => {
            const files = e.dataTransfer?.files;
            if (files && files.length > 0) {
                fileInput.files = files;
                fileInput.dispatchEvent(new Event('change', { bubbles: true }));
            }
        });
    });

    // 3. Limpieza reactiva: al escribir o adjuntar archivo, ocultar mensajes rojos automáticamente
    document.querySelectorAll('input, textarea, select').forEach(el => {
        ['input', 'change'].forEach(evento => {
            el.addEventListener(evento, () => {
                el.classList.remove('input-error');
                const zone = el.closest('.file-upload-area');
                if (zone) zone.classList.remove('input-error');

                // Ocultar alertas rojas superiores (.alert-error) si están visibles
                const container = el.closest('form, .panel, main') || document;
                container.querySelectorAll('.alert-error').forEach(alerta => {
                    alerta.style.display = 'none';
                    alerta.textContent = '';
                });

                // Limpiar textos de error (.error) cercanos al campo
                container.querySelectorAll('.error').forEach(err => {
                    err.textContent = '';
                });
            });
        });
    });

    // 4. Colocar automáticamente el escudo (logo.svg) como ícono de la pestaña del navegador
    if (!document.querySelector('link[rel="icon"]')) {
        const favicon = document.createElement('link');
        favicon.rel = 'icon';
        favicon.type = 'image/svg+xml';
        favicon.href = 'images/logo.svg';
        document.head.appendChild(favicon);
    }

    // 5. Si el usuario ya tiene sesión activa y entra a Login o Registro, enviarlo al Dashboard
    const paginaActual = window.location.pathname.split('/').pop() || 'index.html';
    if ((paginaActual === 'index.html' || paginaActual === 'register.html') && localStorage.getItem('token') && localStorage.getItem('user')) {
        window.location.href = 'dashboard.html';
    }
});