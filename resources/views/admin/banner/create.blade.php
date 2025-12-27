@extends('admin.layouts.app')

@section('content')
<style>
    #editor {
        border: 2px solid #ddd;
        border-radius: 4px;
        overflow: hidden;
    }
    .gjs-cv-canvas {
        background-color: #fff;
    }
    #codeModal .modal-dialog {
        max-width: 90%;
    }
    .code-editor-container {
        display: flex;
        gap: 20px;
    }
    .code-editor-section {
        flex: 1;
    }
    .code-editor-section textarea {
        width: 100%;
        height: 500px;
        font-family: 'Courier New', monospace;
        font-size: 14px;
        padding: 15px;
        border: 1px solid #ddd;
        border-radius: 4px;
        background: #1e1e1e;
        color: #d4d4d4;
        resize: none;
    }
    .code-editor-section h6 {
        margin-bottom: 10px;
        font-weight: 600;
    }
    .animation-helper {
        background: #f8f9fa;
        padding: 15px;
        border-radius: 4px;
        margin-bottom: 15px;
    }
    .animation-presets {
        display: flex;
        flex-wrap: wrap;
        gap: 10px;
        margin-top: 10px;
    }
    .animation-preset-btn {
        padding: 8px 15px;
        background: #667eea;
        color: white;
        border: none;
        border-radius: 4px;
        cursor: pointer;
        font-size: 12px;
    }
    .animation-preset-btn:hover {
        background: #5568d3;
    }
</style>

<div class="row">
    <!-- TOP: FORM (FULL WIDTH) -->
    <div class="col-12 mb-4">
        <div class="card">
            <div class="card-body">
                <h4 class="card-title">Create Banner</h4>

                <form id="bannerForm" method="POST" action="{{ route('admin.banners.store') }}">
                    @csrf

                    <div class="form-group mb-3">
                        <label>Banner Title</label>
                        <input name="title" class="form-control" placeholder="Banner title" required>
                    </div>

                    <input type="hidden" name="html" id="html">
                    <input type="hidden" name="css" id="css">

                    <div class="d-flex flex-row gap-2">
                        <button type="submit" class="btn btn-primary btn-sm">
                            <i class="fa fa-save"></i> Save Banner
                        </button>
                        <button type="button" id="codeBtn" class="btn btn-info btn-sm">
                            <i class="fa fa-code"></i> Edit Code
                        </button>
                        <button type="button" id="previewBtn" class="btn btn-secondary btn-sm">
                            <i class="fa fa-eye"></i> Preview
                        </button>
                        <button type="button" id="resetBtn" class="btn btn-danger btn-sm">
                            <i class="fa fa-refresh"></i> Reset
                        </button>
                    </div>
                </form>

            </div>
        </div>
    </div>
</div>

<div class="row">
    <!-- BOTTOM: EDITOR (FULL WIDTH) -->
    <div class="col-12">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <strong>Banner Editor</strong>
                <small class="text-muted">Drag & Drop · Responsive · HTML/CSS Editable · Animation Support</small>
            </div>

            <div class="card-body">
                <div id="editor"></div>
            </div>
        </div>
    </div>

</div>

<!-- Code Edit Modal -->
<div class="modal fade" id="codeModal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="fa fa-code"></i> Edit HTML & CSS Code
                </h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <!-- Animation Helper -->
                <div class="animation-helper">
                    <h6><i class="fa fa-magic"></i> Quick Animation Presets</h6>
                    <p class="text-muted small mb-2">Select an element in the editor, then click a preset to add animation:</p>
                    <div class="animation-presets">
                        <button class="animation-preset-btn" data-animation="fadeIn">Fade In</button>
                        <button class="animation-preset-btn" data-animation="slideInLeft">Slide In Left</button>
                        <button class="animation-preset-btn" data-animation="slideInRight">Slide In Right</button>
                        <button class="animation-preset-btn" data-animation="slideInUp">Slide In Up</button>
                        <button class="animation-preset-btn" data-animation="slideInDown">Slide In Down</button>
                        <button class="animation-preset-btn" data-animation="zoomIn">Zoom In</button>
                        <button class="animation-preset-btn" data-animation="bounceIn">Bounce In</button>
                        <button class="animation-preset-btn" data-animation="rotateIn">Rotate In</button>
                        <button class="animation-preset-btn" data-animation="pulse">Pulse (Loop)</button>
                        <button class="animation-preset-btn" data-animation="shake">Shake (Loop)</button>
                    </div>
                </div>

                <div class="code-editor-container">
                    <div class="code-editor-section">
                        <h6><i class="fa fa-html5"></i> HTML Code</h6>
                        <textarea id="htmlEditor" spellcheck="false"></textarea>
                    </div>
                    <div class="code-editor-section">
                        <h6><i class="fa fa-css3"></i> CSS Code (Animation support included)</h6>
                        <textarea id="cssEditor" spellcheck="false"></textarea>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">
                    <i class="fa fa-times"></i> Close
                </button>
                <button type="button" id="applyCodeBtn" class="btn btn-primary">
                    <i class="fa fa-check"></i> Apply Changes
                </button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
(function() {
    'use strict';

    let editor = null;

    // Animation CSS Templates
    const animationCSS = `
/* ========== ANIMATION KEYFRAMES ========== */

/* Fade Animations */
@keyframes fadeIn {
    from { opacity: 0; }
    to { opacity: 1; }
}

/* Slide Animations */
@keyframes slideInLeft {
    from {
        opacity: 0;
        transform: translateX(-100px);
    }
    to {
        opacity: 1;
        transform: translateX(0);
    }
}

@keyframes slideInRight {
    from {
        opacity: 0;
        transform: translateX(100px);
    }
    to {
        opacity: 1;
        transform: translateX(0);
    }
}

@keyframes slideInUp {
    from {
        opacity: 0;
        transform: translateY(100px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

@keyframes slideInDown {
    from {
        opacity: 0;
        transform: translateY(-100px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

/* Zoom Animations */
@keyframes zoomIn {
    from {
        opacity: 0;
        transform: scale(0.3);
    }
    to {
        opacity: 1;
        transform: scale(1);
    }
}

/* Bounce Animation */
@keyframes bounceIn {
    0% {
        opacity: 0;
        transform: scale(0.3);
    }
    50% {
        opacity: 1;
        transform: scale(1.05);
    }
    70% {
        transform: scale(0.9);
    }
    100% {
        transform: scale(1);
    }
}

/* Rotate Animation */
@keyframes rotateIn {
    from {
        opacity: 0;
        transform: rotate(-200deg);
    }
    to {
        opacity: 1;
        transform: rotate(0);
    }
}

/* Loop Animations */
@keyframes pulse {
    0%, 100% {
        transform: scale(1);
    }
    50% {
        transform: scale(1.05);
    }
}

@keyframes shake {
    0%, 100% {
        transform: translateX(0);
    }
    10%, 30%, 50%, 70%, 90% {
        transform: translateX(-10px);
    }
    20%, 40%, 60%, 80% {
        transform: translateX(10px);
    }
}

/* Animation Classes */
.animate-fadeIn {
    animation: fadeIn 1s ease-in-out;
}

.animate-slideInLeft {
    animation: slideInLeft 0.8s ease-out;
}

.animate-slideInRight {
    animation: slideInRight 0.8s ease-out;
}

.animate-slideInUp {
    animation: slideInUp 0.8s ease-out;
}

.animate-slideInDown {
    animation: slideInDown 0.8s ease-out;
}

.animate-zoomIn {
    animation: zoomIn 0.6s ease-out;
}

.animate-bounceIn {
    animation: bounceIn 1s ease-out;
}

.animate-rotateIn {
    animation: rotateIn 0.8s ease-out;
}

.animate-pulse {
    animation: pulse 2s ease-in-out infinite;
}

.animate-shake {
    animation: shake 0.5s ease-in-out infinite;
}

/* Delay Classes */
.delay-1 { animation-delay: 0.2s; }
.delay-2 { animation-delay: 0.4s; }
.delay-3 { animation-delay: 0.6s; }
.delay-4 { animation-delay: 0.8s; }
.delay-5 { animation-delay: 1s; }
`;

    window.addEventListener('load', function() {
        setTimeout(function() {

            const container = document.getElementById('editor');
            if (!container) {
                console.error('Editor container not found!');
                return;
            }

            console.log('Initializing GrapeJS...');

            try {
                editor = grapesjs.init({
                    container: '#editor',
                    fromElement: false,
                    height: '600px',
                    width: 'auto',

                    storageManager: false,

                    styleManager: {
                        sectors: [{
                            name: 'General',
                            open: true,
                            properties: [
                                'display',
                                'width',
                                'height',
                                'max-width',
                                'min-height',
                                'margin',
                                'padding'
                            ]
                        }, {
                            name: 'Typography',
                            open: false,
                            properties: [
                                'font-family',
                                'font-size',
                                'font-weight',
                                'color',
                                'text-align',
                                'line-height',
                                'letter-spacing'
                            ]
                        }, {
                            name: 'Background',
                            open: false,
                            properties: [
                                'background-color',
                                'background-image',
                                'background-size',
                                'background-position',
                                'background-repeat'
                            ]
                        }, {
                            name: 'Border',
                            open: false,
                            properties: [
                                'border',
                                'border-radius',
                                'box-shadow'
                            ]
                        }, {
                            name: 'Animation',
                            open: false,
                            properties: [
                                {
                                    name: 'Animation Name',
                                    property: 'animation-name',
                                    type: 'select',
                                    options: [
                                        { value: '', name: 'None' },
                                        { value: 'fadeIn', name: 'Fade In' },
                                        { value: 'slideInLeft', name: 'Slide In Left' },
                                        { value: 'slideInRight', name: 'Slide In Right' },
                                        { value: 'slideInUp', name: 'Slide In Up' },
                                        { value: 'slideInDown', name: 'Slide In Down' },
                                        { value: 'zoomIn', name: 'Zoom In' },
                                        { value: 'bounceIn', name: 'Bounce In' },
                                        { value: 'rotateIn', name: 'Rotate In' },
                                        { value: 'pulse', name: 'Pulse (Loop)' },
                                        { value: 'shake', name: 'Shake (Loop)' }
                                    ]
                                },
                                'animation-duration',
                                'animation-delay',
                                {
                                    name: 'Animation Timing',
                                    property: 'animation-timing-function',
                                    type: 'select',
                                    options: [
                                        { value: 'ease', name: 'Ease' },
                                        { value: 'ease-in', name: 'Ease In' },
                                        { value: 'ease-out', name: 'Ease Out' },
                                        { value: 'ease-in-out', name: 'Ease In Out' },
                                        { value: 'linear', name: 'Linear' }
                                    ]
                                },
                                {
                                    name: 'Animation Count',
                                    property: 'animation-iteration-count',
                                    type: 'select',
                                    options: [
                                        { value: '1', name: 'Once' },
                                        { value: '2', name: 'Twice' },
                                        { value: '3', name: '3 times' },
                                        { value: 'infinite', name: 'Infinite' }
                                    ]
                                }
                            ]
                        }]
                    },

                    canvas: {
                        styles: [
                            'https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css'
                        ]
                    }
                });

                console.log('GrapeJS initialized successfully!');

                // Add blocks
                const bm = editor.BlockManager;

                bm.add('hero-section', {
                    label: 'Hero Banner',
                    content: `
                        <section style="min-height: 520px; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); display: flex; align-items: center; justify-content: center; padding: 50px 20px;">
                            <div style="text-align: center; color: white; max-width: 900px;">
                                <h1 class="animate-fadeIn" style="font-size: 3.5rem; font-weight: 800; margin-bottom: 1rem; text-shadow: 2px 4px 8px rgba(0,0,0,0.3);">Fresh Fruits Daily</h1>
                                <p class="animate-slideInUp delay-1" style="font-size: 1.5rem; margin-bottom: 2rem; opacity: 0.95;">Handpicked & Delivered to Your Door 🌿</p>
                                <a href="#shop" class="animate-bounceIn delay-2" style="display: inline-block; padding: 15px 50px; background: white; color: #667eea; text-decoration: none; border-radius: 50px; font-weight: bold;">Shop Now</a>
                            </div>
                        </section>
                    `,
                    category: 'Sections'
                });

                bm.add('text', {
                    label: 'Text',
                    content: '<div style="padding: 20px;"><p>Your text here...</p></div>',
                    category: 'Basic'
                });

                bm.add('heading', {
                    label: 'Heading',
                    content: '<h2 style="padding: 20px;">Heading Text</h2>',
                    category: 'Basic'
                });

                bm.add('image', {
                    label: 'Image',
                    content: '<img src="https://via.placeholder.com/400x300" style="max-width: 100%; height: auto;" alt="placeholder"/>',
                    category: 'Basic'
                });

                bm.add('button', {
                    label: 'Button',
                    content: '<a href="#" style="display: inline-block; padding: 10px 30px; background: #667eea; color: white; text-decoration: none; border-radius: 5px; font-weight: bold;">Button</a>',
                    category: 'Basic'
                });

                bm.add('2-columns', {
                    label: '2 Columns',
                    content: `
                        <div style="display: flex; gap: 20px; padding: 20px;">
                            <div class="animate-slideInLeft" style="flex: 1; padding: 20px; background: #f5f5f5; min-height: 200px;">Column 1</div>
                            <div class="animate-slideInRight" style="flex: 1; padding: 20px; background: #f5f5f5; min-height: 200px;">Column 2</div>
                        </div>
                    `,
                    category: 'Layout'
                });

                bm.add('3-columns', {
                    label: '3 Columns',
                    content: `
                        <div style="display: flex; gap: 20px; padding: 20px;">
                            <div class="animate-fadeIn" style="flex: 1; padding: 20px; background: #f5f5f5; min-height: 200px;">Column 1</div>
                            <div class="animate-fadeIn delay-1" style="flex: 1; padding: 20px; background: #f5f5f5; min-height: 200px;">Column 2</div>
                            <div class="animate-fadeIn delay-2" style="flex: 1; padding: 20px; background: #f5f5f5; min-height: 200px;">Column 3</div>
                        </div>
                    `,
                    category: 'Layout'
                });

                // Set default content with animations
                editor.setComponents(`
                    <section style="min-height: 520px; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); display: flex; align-items: center; justify-content: center; padding: 50px 20px;">
                        <div style="text-align: center; color: white; max-width: 900px;">
                            <h1 class="animate-fadeIn" style="font-size: 3.5rem; font-weight: 800; margin-bottom: 1rem; text-shadow: 2px 4px 8px rgba(0,0,0,0.3);">Fresh Fruits Daily</h1>
                            <p class="animate-slideInUp delay-1" style="font-size: 1.5rem; margin-bottom: 2rem; opacity: 0.95;">Handpicked & Delivered to Your Door 🌿</p>
                            <a href="#shop" class="animate-bounceIn delay-2" style="display: inline-block; padding: 15px 50px; background: white; color: #667eea; text-decoration: none; border-radius: 50px; font-weight: bold; box-shadow: 0 10px 30px rgba(0,0,0,0.2);">Shop Now →</a>
                        </div>
                    </section>
                `);

                // Set default styles with animations
                editor.setStyle(`
                    * {
                        box-sizing: border-box;
                    }
                    body {
                        margin: 0;
                        padding: 0;
                        font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
                    }
                    ${animationCSS}
                `);

                // Setup event handlers
                setupEventHandlers();

            } catch (error) {
                console.error('Error initializing GrapeJS:', error);
                alert('Error loading editor: ' + error.message);
            }

        }, 500);
    });

    function setupEventHandlers() {
        // Save form
        document.getElementById('bannerForm').addEventListener('submit', function(e) {
            if (editor) {
                document.getElementById('html').value = editor.getHtml();
                document.getElementById('css').value = editor.getCss();
            }
        });

        // Code edit button
        document.getElementById('codeBtn').addEventListener('click', function() {
            if (!editor) {
                alert('Editor is not initialized yet!');
                return;
            }

            const html = editor.getHtml();
            const css = editor.getCss();

            document.getElementById('htmlEditor').value = html;
            document.getElementById('cssEditor').value = css;

            $('#codeModal').modal('show');
        });

        // Animation preset buttons
        document.querySelectorAll('.animation-preset-btn').forEach(btn => {
            btn.addEventListener('click', function() {
                const animationName = this.getAttribute('data-animation');
                const cssEditor = document.getElementById('cssEditor');

                // Add helpful comment in CSS
                const comment = `\n\n/* Add this class to any element: class="animate-${animationName}" */\n`;
                const exampleUsage = `/* Example: <div class="animate-${animationName}">Your content</div> */\n`;

                cssEditor.value += comment + exampleUsage;
                cssEditor.scrollTop = cssEditor.scrollHeight;

                // Show notification
                showNotification(`Added ${animationName} animation! Add class="animate-${animationName}" to your HTML elements.`, 'info');
            });
        });

        // Apply code changes
        document.getElementById('applyCodeBtn').addEventListener('click', function() {
            if (!editor) {
                alert('Editor is not initialized!');
                return;
            }

            const html = document.getElementById('htmlEditor').value;
            const css = document.getElementById('cssEditor').value;

            try {
                editor.setComponents(html);
                editor.setStyle(css);
                $('#codeModal').modal('hide');

                showNotification('Code changes applied successfully!', 'success');

            } catch (error) {
                alert('Error applying changes: ' + error.message);
                console.error('Error:', error);
            }
        });

        // Preview button
        document.getElementById('previewBtn').addEventListener('click', function() {
            if (!editor) {
                alert('Editor is not initialized!');
                return;
            }

            const html = editor.getHtml();
            const css = editor.getCss();

            const previewWindow = window.open('', '_blank', 'width=1200,height=800');
            if (previewWindow) {
                previewWindow.document.write(`
                    <!DOCTYPE html>
                    <html lang="en">
                    <head>
                        <meta charset="UTF-8">
                        <meta name="viewport" content="width=device-width, initial-scale=1.0">
                        <title>Banner Preview</title>
                        <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
                        <style>${css}</style>
                    </head>
                    <body>
                        ${html}
                    </body>
                    </html>
                `);
                previewWindow.document.close();
            }
        });

        // Reset button
        document.getElementById('resetBtn').addEventListener('click', function() {
            if (!editor) {
                alert('Editor is not initialized!');
                return;
            }

            if (confirm('Are you sure you want to reset? This will clear all content.')) {
                editor.setComponents('');
                editor.setStyle('');
            }
        });
    }

    // Helper function for notifications
    function showNotification(message, type = 'success') {
        const alertDiv = document.createElement('div');
        alertDiv.className = `alert alert-${type} alert-dismissible fade show`;
        alertDiv.style.position = 'fixed';
        alertDiv.style.top = '20px';
        alertDiv.style.right = '20px';
        alertDiv.style.zIndex = '9999';
        alertDiv.style.minWidth = '300px';
        alertDiv.innerHTML = `
            <strong>${type === 'success' ? 'Success!' : 'Info:'}</strong> ${message}
            <button type="button" class="close" data-dismiss="alert">&times;</button>
        `;
        document.body.appendChild(alertDiv);

        setTimeout(() => alertDiv.remove(), 4000);
    }

})();
</script>
@endpush
