class AccessibilityWidget {
    constructor() {
        this.options = {
            fontSize: 100, // %
            contrast: false,
            dyslexia: false,
            gray: false,
            saturation: 'normal', // normal, low, high
            bigCursor: false,
            readingGuide: false,
            highlightLinks: false,
            spacing: false,
            alignLeft: false,
            daltonism: 'none',
            focusMode: false
        };

        this.loadState();
        this.init();
    }

    init() {
        this.createStyles();
        this.createWidgetUI();
        this.addSVGFilters();
        this.applyState();
        this.setupEventListeners();
        console.log("Super Accessibility Widget v2 Initialized");
    }

    createStyles() {
        if (!document.getElementById('acc-css')) {
            const link = document.createElement('link');
            link.id = 'acc-css';
            link.rel = 'stylesheet';
            link.href = '/lab2/assets/css/accessibility-widget.css';
            document.head.appendChild(link);
        }
    }

    createWidgetUI() {
        if (document.getElementById('accessibility-panel')) return; // Evitar duplicados

        // 1. Botón Trigger
        const trigger = document.createElement('button');
        trigger.id = 'accessibility-trigger';
        trigger.innerHTML = `<i class="bi bi-universal-access"></i>`;
        trigger.title = "Herramientas de Accesibilidad";
        trigger.setAttribute('aria-label', 'Abrir menú de accesibilidad');
        document.body.appendChild(trigger);

        // 2. Panel
        const panel = document.createElement('div');
        panel.id = 'accessibility-panel';
        panel.innerHTML = `
            <h3><i class="bi bi-gear-fill"></i> Accesibilidad</h3>

            <!-- SECCIÓN VISUAL -->
            <div class="access-section">
                <div class="access-section-title">Visual</div>
                <div class="access-grid">
                    <button class="access-btn" id="acc-font-inc" title="Aumentar el tamaño del texto">
                        <i class="bi bi-zoom-in"></i>
                        <span>Aumentar Texto</span>
                        <small>Hacer letra más grande</small>
                    </button>
                    <button class="access-btn" id="acc-font-dec" title="Disminuir el tamaño del texto">
                        <i class="bi bi-zoom-out"></i>
                        <span>Disminuir Texto</span>
                        <small>Hacer letra más chica</small>
                    </button>
                    <button class="access-btn" id="acc-contrast" title="Invertir colores para alto contraste">
                        <i class="bi bi-circle-half"></i>
                        <span>Alto Contraste</span>
                        <small>Invertir colores</small>
                    </button>
                    <button class="access-btn" id="acc-saturation" title="Ajustar intensidad de colores">
                        <i class="bi bi-palette"></i>
                        <span id="sat-text">Saturación</span>
                        <small>Normal / Baja / Alta</small>
                    </button>
                     <button class="access-btn" id="acc-daltonism" title="Filtros para daltonismo">
                        <i class="bi bi-eye"></i>
                        <span id="daltonism-text">Daltonismo</span>
                        <small>Ajuste de colores</small>
                    </button>
                </div>
            </div>

            <!-- SECCIÓN COGNITIVA / LECTURA -->
            <div class="access-section">
                <div class="access-section-title">Lectura y Cognición</div>
                <div class="access-grid">
                    <button class="access-btn" id="acc-dyslexia" title="Usar fuente optimizada para dislexia">
                        <i class="bi bi-book"></i>
                        <span>Dislexia</span>
                        <small>Fuente legible</small>
                    </button>
                   
                    <button class="access-btn" id="acc-cursor" title="Aumentar tamaño del cursor">
                        <i class="bi bi-cursor-fill"></i>
                        <span>Cursor Grande</span>
                        <small>Más fácil de ver</small>
                    </button>
                    <button class="access-btn" id="acc-links" title="Resaltar todos los enlaces">
                        <i class="bi bi-link-45deg"></i>
                        <span>Resaltar Links</span>
                        <small>Subrayar enlaces</small>
                    </button>
                     <button class="access-btn" id="acc-spacing" title="Aumentar espaciado entre texto">
                        <i class="bi bi-text-paragraph"></i>
                        <span>Espaciado</span>
                        <small>Separar texto</small>
                    </button>
                     <button class="access-btn" id="acc-align" title="Alinear texto a la izquierda">
                        <i class="bi bi-text-left"></i>
                        <span>Alinear</span>
                        <small>Izquierda fija</small>
                    </button>
                    <button class="access-btn" id="acc-focus" title="Activar Modo Foco (Linterna)">
                        <i class="bi bi-lightbulb"></i>
                        <span>Modo Foco</span>
                        <small>Lectura concentrada</small>
                    </button>
                </div>
            </div>

            <button class="reset-btn" id="acc-reset">
                <i class="bi bi-arrow-counterclockwise"></i> Restablecer Ajustes
            </button>
        `;
        document.body.appendChild(panel);
    }

    addSVGFilters() {
        const div = document.createElement('div');
        div.className = 'svg-container';
        div.innerHTML = `
            <svg>
                <defs>
                    <filter id="p-filter"><feColorMatrix type="matrix" values="0.567,0.433,0,0,0 0.558,0.442,0,0,0 0,0.242,0.758,0,0 0,0,0,1,0" /></filter>
                    <filter id="d-filter"><feColorMatrix type="matrix" values="0.625,0.375,0,0,0 0.7,0.3,0,0,0 0,0.3,0.7,0,0 0,0,0,1,0" /></filter>
                    <filter id="t-filter"><feColorMatrix type="matrix" values="0.95,0.05,0,0,0 0,0.433,0.567,0,0 0,0.475,0.525,0,0 0,0,0,1,0" /></filter>
                </defs>
            </svg>
        `;
        document.body.appendChild(div);

        // Ensure Overlay Exists IMMEDIATELY (Global Check)
        if (!document.getElementById('global-focus-overlay')) {
            const overlay = document.createElement('div');
            overlay.id = 'global-focus-overlay';
            overlay.className = 'focus-overlay-global';
            document.body.appendChild(overlay);
        }
    }

    setupEventListeners() {
        const trigger = document.getElementById('accessibility-trigger');
        const panel = document.getElementById('accessibility-panel');

        trigger.addEventListener('click', (e) => {
            e.stopPropagation();
            panel.classList.toggle('active');
        });

        document.addEventListener('click', (e) => {
            if (panel.classList.contains('active') && !panel.contains(e.target) && !trigger.contains(e.target)) {
                panel.classList.remove('active');
            }
        });

        // Eventos Botones
        const bind = (id, fn) => document.getElementById(id)?.addEventListener('click', fn);

        bind('acc-font-inc', () => this.font(10));
        bind('acc-font-dec', () => this.font(-10));
        bind('acc-contrast', () => this.toggle('contrast', 'contrast-mode'));
        bind('acc-dyslexia', () => this.toggle('dyslexia', 'dyslexia-mode'));
        bind('acc-cursor', () => this.toggle('bigCursor', 'big-cursor-mode'));
        bind('acc-links', () => this.toggle('highlightLinks', 'links-mode'));
        bind('acc-spacing', () => this.toggle('spacing', 'spacing-mode'));
        bind('acc-align', () => this.toggle('alignLeft', 'align-left-mode'));
        bind('acc-focus', () => this.toggleFocusMode());

        bind('acc-saturation', () => this.cycleSaturation());
        bind('acc-daltonism', () => this.cycleDaltonism());
        bind('acc-reset', () => this.reset());

        setTimeout(() => this.initTour(), 1000);
    }

    initTour() {
        // Disable auto-tour. We will handle tours individually in each page (index.php, pagina-principal.php)
        // to ensure correct selectors and flow.
        return;

        if (typeof window.driver === 'undefined') return;

        const path = window.location.pathname;
        if (!path.includes('index.php')) return;

        // Check if already viewed
        if (localStorage.getItem('labAccessTourShown_Index_v1')) return;

        const driverObj = window.driver.js.driver({
            showProgress: true,
            steps: [
                {
                    element: '#accessibility-trigger',
                    popover: {
                        title: '🦾 Accesibilidad Universal',
                        description: 'Haz clic en este botón para personalizar tu experiencia: tamaño de letra, contraste, dislexia y más.',
                        side: "left",
                        align: 'start'
                    }
                }
            ],
            onDestroyStarted: () => {
                localStorage.setItem('labAccessTourShown_Index_v1', 'true');
            }
        });

        setTimeout(() => {
            driverObj.drive();
        }, 1500);
    }

    toggle(key, cls) {
        this.options[key] = !this.options[key];
        document.documentElement.classList.toggle(cls, this.options[key]);
        document.getElementById('acc-' + key.replace(/([A-Z])/g, '-$1').toLowerCase())?.classList.toggle('active', this.options[key]);
        this.save();
    }

    font(delta) {
        this.options.fontSize = Math.max(70, Math.min(200, this.options.fontSize + delta));
        document.documentElement.style.fontSize = this.options.fontSize + '%';
        this.save();
    }

    cycleSaturation() {
        const modes = ['normal', 'low', 'high'];
        let idx = modes.indexOf(this.options.saturation);
        this.options.saturation = modes[(idx + 1) % modes.length];

        document.documentElement.classList.remove('low-sat-mode', 'high-sat-mode');
        if (this.options.saturation === 'low') document.documentElement.classList.add('low-sat-mode');
        if (this.options.saturation === 'high') document.documentElement.classList.add('high-sat-mode');

        const labels = { 'normal': 'Saturación', 'low': 'Baja Sat. (Grises)', 'high': 'Alta Sat.' };
        // document.getElementById('sat-text').textContent = labels[this.options.saturation]; // Opcional cambiar texto
        document.getElementById('acc-saturation').classList.toggle('active', this.options.saturation !== 'normal');
        this.save();
    }

    cycleDaltonism() {
        const modes = ['none', 'protanopia', 'deuteranopia', 'tritanopia'];
        let idx = modes.indexOf(this.options.daltonism);
        const next = modes[(idx + 1) % modes.length];

        if (this.options.daltonism !== 'none') document.documentElement.classList.remove(this.options.daltonism + '-mode');
        this.options.daltonism = next;
        if (next !== 'none') document.documentElement.classList.add(next + '-mode');

        const labels = { 'none': 'Daltonismo', 'protanopia': 'Protanopia', 'deuteranopia': 'Deuteranopia', 'tritanopia': 'Tritanopia' };
        document.getElementById('daltonism-text').textContent = labels[next];
        document.getElementById('acc-daltonism').classList.toggle('active', next !== 'none');
        this.save();
    }

    save() {
        localStorage.setItem('labExploraAccessV2', JSON.stringify(this.options));
    }

    loadState() {
        const s = localStorage.getItem('labExploraAccessV2');
        if (s) this.options = { ...this.options, ...JSON.parse(s) };
    }

    applyState() {
        const o = this.options;
        const html = document.documentElement;

        html.style.fontSize = o.fontSize + '%';

        if (o.contrast) html.classList.add('contrast-mode');
        if (o.dyslexia) html.classList.add('dyslexia-mode');
        if (o.saturation === 'low') html.classList.add('low-sat-mode');
        if (o.saturation === 'high') html.classList.add('high-sat-mode');
        if (o.bigCursor) html.classList.add('big-cursor-mode');
        if (o.highlightLinks) html.classList.add('links-mode');
        if (o.spacing) html.classList.add('spacing-mode');
        if (o.alignLeft) html.classList.add('align-left-mode');
        if (o.daltonism !== 'none') html.classList.add(o.daltonism + '-mode');

        // UI Active States (Delayed to ensure DOM exists)
        setTimeout(() => {
            const set = (k, id) => document.getElementById(id)?.classList.toggle('active', o[k]);
            set('contrast', 'acc-contrast');
            set('dyslexia', 'acc-dyslexia');
            set('bigCursor', 'acc-cursor');
            set('highlightLinks', 'acc-links');
            set('spacing', 'acc-spacing');
            set('alignLeft', 'acc-align');

            if (o.saturation !== 'normal') document.getElementById('acc-saturation')?.classList.add('active');
            if (o.daltonism !== 'none') {
                document.getElementById('acc-daltonism')?.classList.add('active');
                const labels = { 'none': 'Daltonismo', 'protanopia': 'Protanopia', 'deuteranopia': 'Deuteranopia', 'tritanopia': 'Tritanopia' };
                const el = document.getElementById('daltonism-text');
                if (el) el.textContent = labels[o.daltonism];
            }

            // Focus Mode State
            if (o.focusMode) {
                this.enableFocusMode();
                document.getElementById('acc-focus')?.classList.add('active');
            }
        }, 100);
    }

    toggleFocusMode() {
        this.options.focusMode = !this.options.focusMode;
        if (this.options.focusMode) {
            this.enableFocusMode();
            document.getElementById('acc-focus')?.classList.add('active');
        } else {
            this.disableFocusMode();
            document.getElementById('acc-focus')?.classList.remove('active');
        }
        this.save();
    }

    enableFocusMode() {
        const overlay = document.getElementById('global-focus-overlay');
        if (!overlay) {
            console.error("Critical: Focus Overlay not found.");
            return;
        }

        console.log("Activating Focus Mode");
        overlay.classList.add('active');
        document.documentElement.classList.add('focus-mode-active');

        // Use Global Root Variables for maximum reach
        const setCoords = (x, y) => {
            document.documentElement.style.setProperty('--focus-x', x + 'px');
            document.documentElement.style.setProperty('--focus-y', y + 'px');
        };

        // Initialize listeners if not existing
        if (!this._focusListener) {
            this._focusListener = (e) => setCoords(e.clientX, e.clientY);
            this._focusTouchListener = (e) => {
                const touch = e.touches[0];
                setCoords(touch.clientX, touch.clientY);
            };

            window.addEventListener('mousemove', this._focusListener);
            window.addEventListener('touchmove', this._focusTouchListener, { passive: true });

            // Init Center
            setCoords(window.innerWidth / 2, window.innerHeight / 2);
        }
    }

    disableFocusMode() {
        const overlay = document.getElementById('global-focus-overlay');
        if (overlay) overlay.classList.remove('active');
        document.documentElement.classList.remove('focus-mode-active');

        // Opcional: Remover listeners si se desea ahorrar recursos, 
        // pero mantenerlos es seguro y evita re-crearlos.
        // Por ahora los dejamos activos pero el overlay está oculto.
    }

    reset() {
        localStorage.removeItem('labExploraAccessV2');
        location.reload();
    }
}
// Init
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', () => {
        new AccessibilityWidget();
    });
} else {
    new AccessibilityWidget();
}
