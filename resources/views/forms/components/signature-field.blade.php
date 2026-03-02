@php
    $penColor = $field->getPenColor();
    $bgColor = $field->getBackgroundColor();
    $canvasHeight = $field->getCanvasHeight();
    $lineWidth = $field->getLineWidth();
    $statePath = $field->getStatePath();
@endphp

<x-dynamic-component :component="$getFieldWrapperView()" :field="$field">
    <div
        wire:ignore
        x-data="signaturePad({
            state: $wire.{{ $applyStateBindingModifiers("\$entangle('{$statePath}')") }},
            exportPenColor: '{{ $penColor }}',
            bgColor: '{{ $bgColor }}',
            lineWidth: {{ $lineWidth }},
            canvasHeight: {{ $canvasHeight }},
        })"
        x-init="init()"
        class="signature-pad-container"
        style="width: 100%; max-width: {{ round($canvasHeight * 4 / 3) }}px;"
    >
        <div
            x-ref="canvasWrap"
            class="rounded-lg border border-gray-300 dark:border-gray-600 overflow-hidden bg-white dark:bg-gray-800"
            style="position: relative; width: 100%;"
        >
            <canvas
                x-ref="canvas"
                style="width: 100%; display: block; touch-action: none; cursor: crosshair;"
                :style="'height: ' + {{ $canvasHeight }} + 'px'"
            ></canvas>

            {{-- Placeholder text --}}
            <div
                x-show="isEmpty"
                class="pointer-events-none absolute inset-0 flex items-center justify-center text-gray-400 dark:text-gray-500"
                style="font-size: 14px;"
            >
                Tanda tangan di sini
            </div>
        </div>

        {{-- Action buttons --}}
        <div class="flex items-center gap-2 mt-2">
            <button
                type="button"
                x-on:click="undo()"
                :disabled="strokes.length === 0"
                class="inline-flex items-center gap-1.5 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 px-3 py-1.5 text-xs font-medium text-gray-700 dark:text-gray-300 shadow-sm hover:bg-gray-50 dark:hover:bg-gray-700 disabled:opacity-40 disabled:cursor-not-allowed transition"
            >
                <svg xmlns="http://www.w3.org/2000/svg" class="shrink-0" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M3 7v6h6"/>
                    <path d="M21 17a9 9 0 0 0-9-9 9 9 0 0 0-6 2.3L3 13"/>
                </svg>
                Undo
            </button>

            <button
                type="button"
                x-on:click="clear()"
                :disabled="strokes.length === 0"
                class="inline-flex items-center gap-1.5 rounded-lg border border-red-300 dark:border-red-700 bg-white dark:bg-gray-800 px-3 py-1.5 text-xs font-medium text-red-600 dark:text-red-400 shadow-sm hover:bg-red-50 dark:hover:bg-red-900/30 disabled:opacity-40 disabled:cursor-not-allowed transition"
            >
                <svg xmlns="http://www.w3.org/2000/svg" class="shrink-0" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M3 6h18"/>
                    <path d="M19 6v14c0 1-1 2-2 2H7c-1 0-2-1-2-2V6"/>
                    <path d="M8 6V4c0-1 1-2 2-2h4c1 0 2 1 2 2v2"/>
                    <line x1="10" y1="11" x2="10" y2="17"/>
                    <line x1="14" y1="11" x2="14" y2="17"/>
                </svg>
                Hapus
            </button>
        </div>
    </div>
</x-dynamic-component>

@once
<script>
    document.addEventListener('alpine:init', () => {
        Alpine.data('signaturePad', (config) => ({
            state: config.state,
            exportPenColor: config.exportPenColor,
            bgColor: config.bgColor,
            lineWidth: config.lineWidth,
            canvasHeight: config.canvasHeight,

            canvas: null,
            ctx: null,
            isDrawing: false,
            isEmpty: true,
            strokes: [],
            currentStroke: [],
            dpr: 1,
            isDark: false,
            initialized: false,

            get displayColor() {
                return this.isDark ? '#ffffff' : '#000000';
            },

            init() {
                // Prevent double init on the same element
                if (this.initialized) return;
                this.initialized = true;

                this.canvas = this.$refs.canvas;
                if (!this.canvas) return;
                this.ctx = this.canvas.getContext('2d');
                this.dpr = window.devicePixelRatio || 1;

                this.isDark = document.documentElement.classList.contains('dark');

                // Watch dark mode changes
                const darkObserver = new MutationObserver(() => {
                    const wasDark = this.isDark;
                    this.isDark = document.documentElement.classList.contains('dark');
                    if (wasDark !== this.isDark) {
                        this.redraw();
                    }
                });
                darkObserver.observe(document.documentElement, { attributes: true, attributeFilter: ['class'] });

                // Size canvas after DOM is ready
                this.$nextTick(() => {
                    this.sizeCanvas();
                    // Load existing signature if editing
                    if (this.state) {
                        this.loadFromDataUrl(this.state);
                    }
                });

                // Bind mouse events
                this.canvas.addEventListener('mousedown', (e) => this.startStroke(e));
                this.canvas.addEventListener('mousemove', (e) => this.continueStroke(e));
                this.canvas.addEventListener('mouseup', (e) => this.endStroke(e));
                this.canvas.addEventListener('mouseleave', (e) => this.endStroke(e));

                // Bind touch events
                this.canvas.addEventListener('touchstart', (e) => {
                    e.preventDefault();
                    this.startStroke(e.touches[0]);
                }, { passive: false });

                this.canvas.addEventListener('touchmove', (e) => {
                    e.preventDefault();
                    this.continueStroke(e.touches[0]);
                }, { passive: false });

                this.canvas.addEventListener('touchend', (e) => {
                    e.preventDefault();
                    this.endStroke(e);
                }, { passive: false });

                this.canvas.addEventListener('touchcancel', (e) => {
                    this.endStroke(e);
                });
            },

            sizeCanvas() {
                const wrap = this.$refs.canvasWrap;
                if (!wrap) return;
                const width = wrap.getBoundingClientRect().width;
                if (width < 10) return;

                const height = this.canvasHeight;

                this.canvas.width = width * this.dpr;
                this.canvas.height = height * this.dpr;
                this.canvas.style.width = width + 'px';
                this.canvas.style.height = height + 'px';

                this.ctx.setTransform(1, 0, 0, 1, 0, 0);
                this.ctx.scale(this.dpr, this.dpr);
            },

            getPoint(e) {
                const rect = this.canvas.getBoundingClientRect();
                return {
                    x: e.clientX - rect.left,
                    y: e.clientY - rect.top,
                };
            },

            startStroke(e) {
                this.isDrawing = true;
                const point = this.getPoint(e);
                this.currentStroke = [point];

                this.ctx.fillStyle = this.displayColor;
                this.ctx.beginPath();
                this.ctx.arc(point.x, point.y, this.lineWidth / 2, 0, Math.PI * 2);
                this.ctx.fill();
            },

            continueStroke(e) {
                if (!this.isDrawing) return;
                const point = this.getPoint(e);
                this.currentStroke.push(point);

                const prev = this.currentStroke[this.currentStroke.length - 2];
                this.ctx.beginPath();
                this.ctx.moveTo(prev.x, prev.y);
                this.ctx.lineTo(point.x, point.y);
                this.ctx.strokeStyle = this.displayColor;
                this.ctx.lineWidth = this.lineWidth;
                this.ctx.lineCap = 'round';
                this.ctx.lineJoin = 'round';
                this.ctx.stroke();
            },

            endStroke(e) {
                if (!this.isDrawing) return;
                this.isDrawing = false;

                if (this.currentStroke.length > 0) {
                    this.strokes.push([...this.currentStroke]);
                    this.currentStroke = [];
                    this.isEmpty = false;
                    this.saveState();
                }
            },

            redraw() {
                if (!this.ctx) return;
                const w = this.canvas.width / this.dpr;
                const h = this.canvas.height / this.dpr;
                this.ctx.clearRect(0, 0, w, h);

                for (const stroke of this.strokes) {
                    if (stroke.length === 0) continue;

                    if (stroke.length === 1) {
                        this.ctx.fillStyle = this.displayColor;
                        this.ctx.beginPath();
                        this.ctx.arc(stroke[0].x, stroke[0].y, this.lineWidth / 2, 0, Math.PI * 2);
                        this.ctx.fill();
                        continue;
                    }

                    this.ctx.beginPath();
                    this.ctx.moveTo(stroke[0].x, stroke[0].y);
                    for (let i = 1; i < stroke.length; i++) {
                        this.ctx.lineTo(stroke[i].x, stroke[i].y);
                    }
                    this.ctx.strokeStyle = this.displayColor;
                    this.ctx.lineWidth = this.lineWidth;
                    this.ctx.lineCap = 'round';
                    this.ctx.lineJoin = 'round';
                    this.ctx.stroke();
                }
            },

            saveState() {
                const w = this.canvas.width;
                const h = this.canvas.height;

                const offscreen = document.createElement('canvas');
                offscreen.width = w;
                offscreen.height = h;
                const offCtx = offscreen.getContext('2d');
                offCtx.scale(this.dpr, this.dpr);

                for (const stroke of this.strokes) {
                    if (stroke.length === 0) continue;

                    if (stroke.length === 1) {
                        offCtx.fillStyle = this.exportPenColor;
                        offCtx.beginPath();
                        offCtx.arc(stroke[0].x, stroke[0].y, this.lineWidth / 2, 0, Math.PI * 2);
                        offCtx.fill();
                        continue;
                    }

                    offCtx.beginPath();
                    offCtx.moveTo(stroke[0].x, stroke[0].y);
                    for (let i = 1; i < stroke.length; i++) {
                        offCtx.lineTo(stroke[i].x, stroke[i].y);
                    }
                    offCtx.strokeStyle = this.exportPenColor;
                    offCtx.lineWidth = this.lineWidth;
                    offCtx.lineCap = 'round';
                    offCtx.lineJoin = 'round';
                    offCtx.stroke();
                }

                this.state = offscreen.toDataURL('image/png');
            },

            undo() {
                if (this.strokes.length === 0) return;
                this.strokes.pop();
                this.isEmpty = this.strokes.length === 0;
                this.redraw();

                if (this.isEmpty) {
                    this.state = null;
                } else {
                    this.saveState();
                }
            },

            clear() {
                this.strokes = [];
                this.currentStroke = [];
                this.isEmpty = true;
                this.ctx.clearRect(0, 0, this.canvas.width / this.dpr, this.canvas.height / this.dpr);
                this.state = null;
            },

            loadFromDataUrl(url) {
                if (!url) return;
                const img = new Image();
                img.onload = () => {
                    this.sizeCanvas();
                    this.ctx.drawImage(img, 0, 0, this.canvas.width / this.dpr, this.canvas.height / this.dpr);
                    this.isEmpty = false;
                    this.strokes.push([{x: 0, y: 0}]);
                };
                img.src = url;
            },
        }));
    });
</script>
@endonce
