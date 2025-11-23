/**
 * CKEditor 5 CDN with Dark Mode & Image Upload
 * Complete implementation using CDN with all features and dark theme support
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
 * Enhanced CKEditor 5 initialization with all features and dark mode
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
        darkMode: false,
        height: '400px',
        toolbar: 'full', // 'full', 'standard', 'minimal'
        imageUpload: true,
        mediaEmbed: true,
        codeBlocks: true,
        tables: true,
        specialCharacters: true,
        findReplace: true,
        wordCount: true,
        sourceEditing: true
    };

    options = { ...defaultOptions, ...options };

    // Auto-detect dark mode if not explicitly set
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

    function getPlugins() {
        const basePlugins = [
            Essentials,
            Paragraph,
            Heading,
            Bold,
            Italic,
            Link,
            List,
            BlockQuote,
            Undo,
            Alignment
        ];

        // Add table support if enabled
        if (options.tables) {
            basePlugins.push(Table, TableToolbar);
        }

        // Add image upload if enabled
        if (options.imageUpload) {
            basePlugins.push(SimpleUploadAdapter);
        }

        return basePlugins;
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
                'bold', 'italic', '|',
                'link', '|',
                'alignment', '|',
                'bulletedList', 'numberedList', '|',
                'blockQuote', '|',
                options.tables ? 'insertTable' : null,
                '|',
                'undo', 'redo'
            ].filter(item => item !== null),
            
            full: [
                'heading', '|',
                'bold', 'italic', '|',
                'link', '|',
                'alignment', '|',
                'bulletedList', 'numberedList', '|',
                'blockQuote', '|',
                options.tables ? 'insertTable' : null,
                '|',
                'undo', 'redo'
            ].filter(item => item !== null)
        };

        return {
            items: toolbars[options.toolbar] || toolbars.standard,
            shouldNotGroupWhenFull: true
        };
    }

    function initEditor() {
        const config = {
            language: options.language,
            plugins: getPlugins(),
            toolbar: getToolbarConfig(),
            
            // Heading configuration
            heading: {
                options: [
                    { model: 'paragraph', title: 'Paragraph', class: 'ck-heading_paragraph' },
                    { model: 'heading1', view: 'h1', title: 'Heading 1', class: 'ck-heading_heading1' },
                    { model: 'heading2', view: 'h2', title: 'Heading 2', class: 'ck-heading_heading2' },
                    { model: 'heading3', view: 'h3', title: 'Heading 3', class: 'ck-heading_heading3' },
                    { model: 'heading4', view: 'h4', title: 'Heading 4', class: 'ck-heading_heading4' },
                    { model: 'heading5', view: 'h5', title: 'Heading 5', class: 'ck-heading_heading5' },
                    { model: 'heading6', view: 'h6', title: 'Heading 6', class: 'ck-heading_heading6' }
                ]
            },



            // Alignment
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
                    },
                    downloadable: {
                        mode: 'manual',
                        label: 'Downloadable',
                        attributes: {
                            download: 'file'
                        }
                    }
                },
                addTargetToExternalLinks: true,
                defaultProtocol: 'https://'
            },

            // Image upload configuration
            ...(options.imageUpload && {
                simpleUpload: {
                    uploadUrl: '/cms-admin/upload-image',
                    withCredentials: true,
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
                    }
                }
            }),

            // Table configuration
            ...(options.tables && {
                table: {
                    contentToolbar: [
                        'tableColumn', 'tableRow', 'mergeTableCells'
                    ]
                }
            })
        };

        return ClassicEditor.create(element, config);
    }

    return initEditor()
        .then(editor => {
            // Apply dark mode styling if enabled
            if (options.darkMode) {
                applyDarkMode(editor.ui.view.element);
            }

            // Set initial content
            if (initialContent) {
                editor.setData(initialContent);
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
        })
        .catch(error => {
            console.error('Advanced CKEditor initialization error:', error);
            throw error;
        });
};

/**
 * Apply dark mode styles to CKEditor
 */
function applyDarkMode(editorElement) {
    if (!editorElement) return;

    // Add dark mode CSS custom properties
    const darkModeStyles = `
        .ck-editor__main {
            --ck-color-base-background: #1f2937 !important;
            --ck-color-base-foreground: #f9fafb !important;
            --ck-color-focus-border: #3b82f6 !important;
            --ck-color-text: #f9fafb !important;
            --ck-color-shadow-drop: rgba(0, 0, 0, 0.3) !important;
            --ck-color-shadow-inner: rgba(0, 0, 0, 0.2) !important;
            --ck-color-button-default-background: transparent !important;
            --ck-color-button-default-hover-background: #374151 !important;
            --ck-color-button-on-background: #3b82f6 !important;
            --ck-color-button-on-hover-background: #2563eb !important;
            --ck-color-dropdown-panel-background: #1f2937 !important;
            --ck-color-input-background: #374151 !important;
            --ck-color-input-border: #4b5563 !important;
            --ck-color-list-background: #1f2937 !important;
            --ck-color-list-button-hover-background: #374151 !important;
            --ck-color-list-button-on-background: #3b82f6 !important;
            --ck-color-panel-background: #1f2937 !important;
            --ck-color-panel-border: #4b5563 !important;
            --ck-color-toolbar-background: #111827 !important;
            --ck-color-toolbar-border: #374151 !important;
            --ck-color-tooltip-background: #111827 !important;
            --ck-color-tooltip-text: #f9fafb !important;
            --ck-color-engine-placeholder-text: #9ca3af !important;
            --ck-color-upload-bar-background: #3b82f6 !important;
            --ck-color-link-default: #60a5fa !important;
            --ck-color-link-selected-background: rgba(59, 130, 246, 0.3) !important;
        }
        
        .ck-editor__main .ck-content {
            background-color: #1f2937 !important;
            color: #f9fafb !important;
        }
        
        .ck-editor__main .ck-placeholder::before {
            color: #9ca3af !important;
        }
        
        .ck-editor__main .ck-table-cell {
            border-color: #4b5563 !important;
        }
        
        .ck-editor__main .ck-widget {
            outline-color: #3b82f6 !important;
        }
    `;

    // Create and inject dark mode styles
    const styleId = `ckeditor-dark-mode-${Date.now()}`;
    let styleElement = document.getElementById(styleId);
    
    if (!styleElement) {
        styleElement = document.createElement('style');
        styleElement.id = styleId;
        styleElement.textContent = darkModeStyles;
        document.head.appendChild(styleElement);
    }

    // Add dark mode class to editor
    editorElement.classList.add('ck-editor-dark');
}

/**
 * Simplified initialization function for backward compatibility
 */
window.initCKEditor = function(elementId, wireModel, initialContent = '', language = 'en') {
    return window.initAdvancedCKEditor(elementId, wireModel, initialContent, {
        language: language,
        darkMode: 'auto',
        toolbar: 'standard'
    });
};

/**
 * Initialize CKEditor with dark mode
 */
window.initDarkCKEditor = function(elementId, wireModel, initialContent = '', language = 'en') {
    return window.initAdvancedCKEditor(elementId, wireModel, initialContent, {
        language: language,
        darkMode: true,
        toolbar: 'full',
        height: '500px'
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
        } else {
            editor.ui.view.element.classList.remove('ck-editor-dark');
        }
    }
};