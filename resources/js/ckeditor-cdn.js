/**
 * CKEditor 5 CDN with Dark Mode & Image Upload
 * Complete working implementation for Laravel
 */

// Global storage for editor instances
window.ckeditorInstances = window.ckeditorInstances || {};

// Load CKEditor 5 CDN if not already loaded
function loadCKEditorCDN() {
    return new Promise((resolve, reject) => {
        if (typeof ClassicEditor !== 'undefined') {
            resolve();
            return;
        }

        const script = document.createElement('script');
        script.src = 'https://cdn.ckeditor.com/ckeditor5/41.2.1/classic/ckeditor.js';
        script.onload = function() {
            console.log('CKEditor 5 CDN loaded successfully');
            resolve();
        };
        script.onerror = function() {
            console.error('Failed to load CKEditor 5 CDN');
            reject(new Error('Failed to load CKEditor 5 CDN'));
        };
        document.head.appendChild(script);
    });
}

// Load CKEditor CDN immediately
loadCKEditorCDN();

/**
 * Initialize CKEditor 5 with CDN, Dark Mode, and Image Upload
 */
window.initAdvancedCKEditor = function(elementId, wireModel, initialContent = '', options = {}) {
    const element = document.querySelector(`#${elementId}`);
    if (!element) {
        console.error(`Element #${elementId} not found`);
        return Promise.resolve();
    }

    // Default options
    const defaultOptions = {
        language: 'en',
        darkMode: 'auto',
        height: '400px',
        toolbar: 'full',
        imageUpload: true
    };

    options = { ...defaultOptions, ...options };

    // Auto-detect dark mode
    if (options.darkMode === 'auto') {
        options.darkMode = document.documentElement.classList.contains('dark') || 
                          document.body.classList.contains('dark-mode') ||
                          window.matchMedia('(prefers-color-scheme: dark)').matches;
    }

    // Destroy existing instance
    if (window.ckeditorInstances[elementId]) {
        return window.ckeditorInstances[elementId].destroy()
            .then(() => {
                delete window.ckeditorInstances[elementId];
            })
            .then(() => initEditor());
    }

    function getToolbarConfig() {
        const toolbars = {
            minimal: [
                'heading', '|',
                'bold', 'italic', '|',
                'link', '|',
                'bulletedList', 'numberedList', '|',
                'undo', 'redo'
            ],
            
            standard: [
                'heading', '|',
                'bold', 'italic', 'underline', '|',
                'fontSize', '|',
                'alignment:left', 'alignment:center', 'alignment:right', 'alignment:justify', '|',
                'bulletedList', 'numberedList', '|',
                'link', 'insertTable', 'blockQuote', '|',
                options.imageUpload ? 'imageUpload' : null,
                '|',
                'undo', 'redo'
            ].filter(item => item !== null),
            
            full: [
                'heading', '|',
                'fontFamily', 'fontSize', '|',
                'bold', 'italic', 'underline', 'strikethrough', '|',
                'alignment:left', 'alignment:center', 'alignment:right', 'alignment:justify', '|',
                'bulletedList', 'numberedList', 'todoList', '|',
                'insertTable', 'tableColumn', 'tableRow', 'mergeTableCells', '|',
                options.imageUpload ? 'imageUpload' : null,
                'link', 'mediaEmbed', '|',
                'blockQuote', 'codeBlock', '|',
                'undo', 'redo'
            ].filter(item => item !== null)
        };

        return {
            items: toolbars[options.toolbar] || toolbars.standard,
            shouldNotGroupWhenFull: true
        };
    }

    async function initEditor() {
        try {
            // Wait for ClassicEditor to be available
            await loadCKEditorCDN();

            const config = {
                language: options.language,
                toolbar: getToolbarConfig(),
                
                // Image upload configuration (CDN method)
                ckfinder: {
                    uploadUrl: '/cms-admin/upload-image'
                },

                // Alignment configuration
                alignment: {
                    options: ['left', 'center', 'right', 'justify']
                },

                // Link configuration
                link: {
                    decorators: {
                        openInNewTab: {
                            mode: 'manual',
                            label: 'Open in a new tab',
                            attributes: {
                                target: '_blank',
                                rel: 'noopener noreferrer'
                            }
                        }
                    }
                },

                // Table configuration
                table: {
                    contentToolbar: [
                        'tableColumn', 'tableRow', 'mergeTableCells'
                    ]
                }
            };

            const editor = await ClassicEditor.create(element, config);

            // Set initial content
            if (initialContent) {
                editor.setData(initialContent);
            }

            // Apply dark mode styling
            if (options.darkMode) {
                applyDarkMode(editor.ui.view.element);
            }

            // Set editor height
            if (options.height) {
                const editingView = editor.ui.view.editable.element;
                editingView.style.minHeight = options.height;
            }

            // Livewire integration
            const livewireComponent = element.closest('[wire\\:id]');
            if (livewireComponent) {
                const componentId = livewireComponent.getAttribute('wire:id');
                const livewire = window.Livewire?.find(componentId);

                if (livewire && wireModel) {
                    let timeout;
                    editor.model.document.on('change:data', () => {
                        clearTimeout(timeout);
                        timeout = setTimeout(() => {
                            const data = editor.getData();
                            livewire.set(wireModel, data, false);
                        }, 300);
                    });
                }
            }

            // Store editor instance
            window.ckeditorInstances[elementId] = editor;

            return editor;
            
        } catch (error) {
            console.error('CKEditor initialization error:', error);
            throw error;
        }
    }

    return initEditor();
};

/**
 * Apply dark mode styles to CKEditor
 */
function applyDarkMode(editorElement) {
    if (!editorElement) return;

    // Inject dark mode styles if not already present
    if (!document.getElementById('ckeditor-dark-styles')) {
        const style = document.createElement('style');
        style.id = 'ckeditor-dark-styles';
        style.textContent = `
            html.dark .ck.ck-editor__editable,
            html.dark .ck.ck-content {
                background: #1e1e1e !important;
                color: white !important;
                border-color: #444 !important;
            }

            html.dark .ck.ck-toolbar {
                background: #2b2b2b !important;
                border-color: #444 !important;
            }

            html.dark .ck.ck-button {
                color: white !important;
            }

            html.dark .ck.ck-button:hover {
                background: #3a3a3a !important;
            }

            html.dark .ck.ck-button.ck-on {
                background: #555 !important;
                color: white !important;
            }

            html.dark .ck.ck-dropdown__panel {
                background: #2b2b2b !important;
                border-color: #444 !important;
            }

            html.dark .ck.ck-list__item {
                color: white !important;
            }

            html.dark .ck.ck-list__item:hover {
                background: #3a3a3a !important;
            }

            html.dark .ck.ck-list__item.ck-on {
                background: #555 !important;
            }

            html.dark .ck.ck-input {
                background: #1e1e1e !important;
                color: white !important;
                border-color: #444 !important;
            }

            html.dark .ck.ck-tooltip {
                background: #2b2b2b !important;
                color: white !important;
                border-color: #444 !important;
            }

            html.dark .ck.ck-balloon-panel {
                background: #2b2b2b !important;
                border-color: #444 !important;
            }

            html.dark .ck.ck-widget.ck-widget_selected,
            html.dark .ck.ck-widget.ck-widget_selected:hover {
                outline: 2px solid #3b82f6 !important;
            }

            html.dark .ck.ck-placeholder::before {
                color: #9ca3af !important;
            }

            /* Table styling in dark mode */
            html.dark .ck.ck-content .table td,
            html.dark .ck.ck-content .table th {
                border-color: #444 !important;
            }

            /* Media and link styling in dark mode */
            html.dark .ck.ck-content blockquote {
                background: #2b2b2b !important;
                border-left: 5px solid #3b82f6 !important;
            }

            html.dark .ck.ck-content pre {
                background: #1a1a1a !important;
                color: white !important;
            }
        `;
        document.head.appendChild(style);
    }

    // Add dark class to editor
    editorElement.classList.add('ck-editor-dark');
}

/**
 * Initialize CKEditor with dark mode (shortcut function)
 */
window.initDarkCKEditor = function(elementId, wireModel, initialContent = '', language = 'en') {
    return window.initAdvancedCKEditor(elementId, wireModel, initialContent, {
        language: language,
        darkMode: true,
        toolbar: 'full',
        height: '500px',
        imageUpload: true
    });
};

/**
 * Simple CKEditor initialization (backward compatibility)
 */
window.initCKEditor = function(elementId, wireModel, initialContent = '', language = 'en') {
    return window.initAdvancedCKEditor(elementId, wireModel, initialContent, {
        language: language,
        darkMode: 'auto',
        toolbar: 'standard',
        imageUpload: true
    });
};

/**
 * Destroy CKEditor instance
 */
window.destroyCKEditor = function(elementId) {
    if (window.ckeditorInstances && window.ckeditorInstances[elementId]) {
        return window.ckeditorInstances[elementId].destroy()
            .then(() => {
                delete window.ckeditorInstances[elementId];
            })
            .catch(error => {
                console.error('CKEditor destruction error:', error);
            });
    }
    return Promise.resolve();
};

/**
 * Update CKEditor content
 */
window.updateCKEditor = function(elementId, content) {
    if (window.ckeditorInstances && window.ckeditorInstances[elementId]) {
        window.ckeditorInstances[elementId].setData(content || '');
    }
};

/**
 * Toggle dark mode for existing editor
 */
window.toggleCKEditorDarkMode = function(elementId, enable = true) {
    if (window.ckeditorInstances && window.ckeditorInstances[elementId]) {
        const editor = window.ckeditorInstances[elementId];
        if (enable) {
            applyDarkMode(editor.ui.view.element);
            document.documentElement.classList.add('dark');
        } else {
            editor.ui.view.element.classList.remove('ck-editor-dark');
            document.documentElement.classList.remove('dark');
        }
    }
};