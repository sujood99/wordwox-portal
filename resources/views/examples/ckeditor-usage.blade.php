{{-- 
CKEditor 5 Dark Mode Usage Examples
Place these examples in your Blade templates where you want to use CKEditor
--}}

{{-- Example 1: Basic CKEditor with Auto Dark Mode Detection --}}
<div wire:ignore>
    <textarea 
        id="ckeditor-basic-{{ $index }}"
        wire:model.defer="blocks.{{ $index }}.content"
        class="min-h-[300px]"
    >{!! $block['content'] ?? '' !!}</textarea>
</div>

{{-- Word count display --}}
<div id="ckeditor-basic-{{ $index }}-word-count" class="text-sm text-gray-500 mt-2"></div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Basic CKEditor with auto dark mode
    window.initAdvancedCKEditor('ckeditor-basic-{{ $index }}', 'blocks.{{ $index }}.content', @js($block['content'] ?? ''), {
        language: '{{ app()->getLocale() }}',
        darkMode: 'auto', // Auto-detect dark mode
        toolbar: 'standard',
        height: '400px',
        wordCount: true
    });
});
</script>

{{-- Example 2: Full-Featured CKEditor with Dark Mode --}}
<div wire:ignore>
    <textarea 
        id="ckeditor-full-{{ $index }}"
        wire:model.defer="blocks.{{ $index }}.content"
        class="min-h-[500px]"
    >{!! $block['content'] ?? '' !!}</textarea>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Full CKEditor with all features and dark mode
    window.initAdvancedCKEditor('ckeditor-full-{{ $index }}', 'blocks.{{ $index }}.content', @js($block['content'] ?? ''), {
        language: '{{ app()->getLocale() }}',
        darkMode: true, // Force dark mode
        toolbar: 'full',
        height: '500px',
        imageUpload: true,
        mediaEmbed: true,
        codeBlocks: true,
        tables: true,
        specialCharacters: true,
        findReplace: true,
        wordCount: true,
        sourceEditing: true
    });
});
</script>

{{-- Example 3: Minimal CKEditor --}}
<div wire:ignore>
    <textarea 
        id="ckeditor-minimal-{{ $index }}"
        wire:model.defer="blocks.{{ $index }}.content"
        class="min-h-[200px]"
    >{!! $block['content'] ?? '' !!}</textarea>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Minimal CKEditor
    window.initAdvancedCKEditor('ckeditor-minimal-{{ $index }}', 'blocks.{{ $index }}.content', @js($block['content'] ?? ''), {
        language: '{{ app()->getLocale() }}',
        darkMode: 'auto',
        toolbar: 'minimal',
        height: '200px',
        imageUpload: false,
        mediaEmbed: false,
        codeBlocks: false,
        tables: false,
        specialCharacters: false,
        findReplace: false,
        wordCount: false,
        sourceEditing: false
    });
});
</script>

{{-- Example 4: CKEditor with Dynamic Dark Mode Toggle --}}
<div class="space-y-4">
    {{-- Dark mode toggle --}}
    <div class="flex items-center space-x-2">
        <label class="flex items-center">
            <input 
                type="checkbox" 
                id="dark-mode-toggle-{{ $index }}"
                class="form-checkbox" 
                onchange="toggleEditorDarkMode({{ $index }}, this.checked)"
            >
            <span class="ml-2">Dark Mode</span>
        </label>
    </div>
    
    {{-- Editor --}}
    <div wire:ignore>
        <textarea 
            id="ckeditor-toggle-{{ $index }}"
            wire:model.defer="blocks.{{ $index }}.content"
            class="min-h-[400px]"
        >{!! $block['content'] ?? '' !!}</textarea>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    window.initAdvancedCKEditor('ckeditor-toggle-{{ $index }}', 'blocks.{{ $index }}.content', @js($block['content'] ?? ''), {
        language: '{{ app()->getLocale() }}',
        darkMode: false, // Start with light mode
        toolbar: 'full',
        height: '400px'
    });
    
    // Set initial toggle state
    const toggle = document.getElementById('dark-mode-toggle-{{ $index }}');
    toggle.checked = document.documentElement.classList.contains('dark');
});

function toggleEditorDarkMode(index, enabled) {
    window.toggleCKEditorDarkMode('ckeditor-toggle-' + index, enabled);
}
</script>

{{-- Example 5: Alpine.js Integration with CKEditor --}}
<div 
    x-data="{
        editorReady: false,
        content: @entangle('blocks.{{ $index }}.content').defer,
        darkMode: window.matchMedia('(prefers-color-scheme: dark)').matches,
        
        initEditor() {
            window.initAdvancedCKEditor('ckeditor-alpine-{{ $index }}', 'blocks.{{ $index }}.content', this.content, {
                language: '{{ app()->getLocale() }}',
                darkMode: this.darkMode,
                toolbar: 'full',
                height: '400px'
            }).then(() => {
                this.editorReady = true;
            });
        },
        
        toggleDarkMode() {
            this.darkMode = !this.darkMode;
            window.toggleCKEditorDarkMode('ckeditor-alpine-{{ $index }}', this.darkMode);
        },
        
        updateContent(newContent) {
            window.updateCKEditor('ckeditor-alpine-{{ $index }}', newContent);
        }
    }"
    x-init="$nextTick(() => initEditor())"
    class="space-y-4"
>
    {{-- Controls --}}
    <div class="flex items-center justify-between">
        <button 
            @click="toggleDarkMode()" 
            class="px-4 py-2 bg-gray-200 dark:bg-gray-700 rounded-lg"
            x-text="darkMode ? 'Light Mode' : 'Dark Mode'"
        ></button>
        
        <span 
            x-show="editorReady" 
            class="text-sm text-green-600"
        >
            ✓ Editor Ready
        </span>
    </div>
    
    {{-- Editor --}}
    <div wire:ignore>
        <textarea 
            id="ckeditor-alpine-{{ $index }}"
            wire:model.defer="blocks.{{ $index }}.content"
            class="min-h-[400px]"
        >{!! $block['content'] ?? '' !!}</textarea>
    </div>
</div>

{{-- Example 6: Arabic/RTL Language Support --}}
<div wire:ignore>
    <textarea 
        id="ckeditor-arabic-{{ $index }}"
        wire:model.defer="blocks.{{ $index }}.content"
        class="min-h-[400px]"
        dir="rtl"
    >{!! $block['content'] ?? '' !!}</textarea>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    window.initAdvancedCKEditor('ckeditor-arabic-{{ $index }}', 'blocks.{{ $index }}.content', @js($block['content'] ?? ''), {
        language: 'ar', // Arabic language
        darkMode: 'auto',
        toolbar: 'full',
        height: '400px'
    });
});
</script>