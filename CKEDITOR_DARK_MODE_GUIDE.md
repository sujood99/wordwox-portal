# CKEditor 5 with Dark Mode - Complete Implementation Guide

## 🚀 Installation Complete!

Your Laravel project now has **CKEditor 5 with full dark mode support** and advanced features implemented!

## 📦 What Was Installed

### 1. **CKEditor 5 Packages Added**
```json
{
  "@ckeditor/ckeditor5-alignment": "^47.2.0",
  "@ckeditor/ckeditor5-font": "^47.2.0", 
  "@ckeditor/ckeditor5-image": "^47.2.0",
  "@ckeditor/ckeditor5-upload": "^47.2.0",
  "@ckeditor/ckeditor5-media-embed": "^47.2.0",
  "@ckeditor/ckeditor5-code-block": "^47.2.0",
  "@ckeditor/ckeditor5-special-characters": "^47.2.0",
  "@ckeditor/ckeditor5-find-and-replace": "^47.2.0",
  "@ckeditor/ckeditor5-word-count": "^47.2.0",
  "@ckeditor/ckeditor5-source-editing": "^47.2.0",
  "@ckeditor/ckeditor5-html-support": "^47.2.0",
  "ckeditor5": "^47.2.0"
}
```

### 2. **Files Created/Updated**

#### ✨ **New Files:**
- `resources/js/ckeditor-advanced.js` - Full-featured CKEditor with dark mode
- `resources/css/ckeditor-dark.css` - Complete dark mode styling
- `resources/views/examples/ckeditor-usage.blade.php` - Usage examples

#### 🔄 **Updated Files:**
- `package.json` - Added CKEditor packages
- `resources/js/app.js` - Included advanced CKEditor
- `resources/css/app.css` - Added CKEditor dark mode CSS

## 🎯 Available Functions

### **Main Initialization Functions**

```javascript
// 1. Advanced CKEditor with all options
window.initAdvancedCKEditor(elementId, wireModel, initialContent, options)

// 2. Simple CKEditor (backward compatible)
window.initCKEditor(elementId, wireModel, initialContent, language)

// 3. CKEditor with dark mode enabled
window.initDarkCKEditor(elementId, wireModel, initialContent, language)

// 4. Utility functions
window.destroyCKEditor(elementId)
window.updateCKEditor(elementId, content)
window.toggleCKEditorDarkMode(elementId, enabled)
```

## 🌙 **Dark Mode Features**

### **Automatic Dark Mode Detection**
```javascript
window.initAdvancedCKEditor('editor-1', 'content', '', {
    darkMode: 'auto' // Automatically detects system/page dark mode
});
```

### **Force Dark Mode**
```javascript
window.initAdvancedCKEditor('editor-2', 'content', '', {
    darkMode: true // Always use dark mode
});
```

### **Manual Toggle**
```javascript
// Toggle existing editor
window.toggleCKEditorDarkMode('editor-3', true); // Enable dark mode
window.toggleCKEditorDarkMode('editor-3', false); // Disable dark mode
```

## 🛠️ **Configuration Options**

```javascript
const options = {
    // Basic Options
    language: 'en',           // Language ('en', 'ar', etc.)
    darkMode: 'auto',         // Dark mode: true, false, 'auto'
    height: '400px',          // Editor height
    toolbar: 'full',          // Toolbar: 'minimal', 'standard', 'full'
    
    // Feature Options
    imageUpload: true,        // Enable image upload
    mediaEmbed: true,         // Enable video/media embed
    codeBlocks: true,         // Enable code blocks
    tables: true,             // Enable tables
    specialCharacters: true,  // Enable special characters
    findReplace: true,        // Enable find & replace
    wordCount: true,          // Enable word count
    sourceEditing: true       // Enable HTML source editing
};
```

## 📝 **Usage Examples**

### **1. Basic Usage in Blade Templates**

```blade
<div wire:ignore>
    <textarea 
        id="ckeditor-{{ $index }}"
        wire:model.defer="blocks.{{ $index }}.content"
        class="min-h-[400px]"
    >{!! $block['content'] ?? '' !!}</textarea>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    window.initAdvancedCKEditor('ckeditor-{{ $index }}', 'blocks.{{ $index }}.content', @js($block['content'] ?? ''), {
        language: '{{ app()->getLocale() }}',
        darkMode: 'auto',
        toolbar: 'full',
        height: '500px'
    });
});
</script>
```

### **2. With Word Count Display**

```blade
<div wire:ignore>
    <textarea id="ckeditor-wordcount-{{ $index }}">{!! $content !!}</textarea>
</div>

<!-- Word count will appear here -->
<div id="ckeditor-wordcount-{{ $index }}-word-count" class="text-sm text-gray-500 mt-2"></div>

<script>
window.initAdvancedCKEditor('ckeditor-wordcount-{{ $index }}', 'content', @js($content), {
    wordCount: true,
    darkMode: 'auto'
});
</script>
```

### **3. Minimal Editor**

```blade
<div wire:ignore>
    <textarea id="ckeditor-minimal-{{ $index }}">{!! $content !!}</textarea>
</div>

<script>
window.initAdvancedCKEditor('ckeditor-minimal-{{ $index }}', 'content', @js($content), {
    toolbar: 'minimal',
    height: '200px',
    imageUpload: false,
    tables: false,
    darkMode: 'auto'
});
</script>
```

### **4. Alpine.js Integration**

```blade
<div x-data="{ 
    content: @entangle('content'), 
    darkMode: false,
    initEditor() {
        window.initAdvancedCKEditor('alpine-editor', 'content', this.content, {
            darkMode: this.darkMode,
            toolbar: 'full'
        });
    },
    toggleDark() {
        this.darkMode = !this.darkMode;
        window.toggleCKEditorDarkMode('alpine-editor', this.darkMode);
    }
}" x-init="initEditor()">
    
    <button @click="toggleDark()" x-text="darkMode ? 'Light Mode' : 'Dark Mode'"></button>
    
    <div wire:ignore>
        <textarea id="alpine-editor">{!! $content !!}</textarea>
    </div>
</div>
```

### **5. Arabic/RTL Support**

```blade
<div wire:ignore>
    <textarea 
        id="ckeditor-arabic-{{ $index }}"
        dir="rtl"
    >{!! $content !!}</textarea>
</div>

<script>
window.initAdvancedCKEditor('ckeditor-arabic-{{ $index }}', 'content', @js($content), {
    language: 'ar',
    darkMode: 'auto',
    toolbar: 'full'
});
</script>
```

## 🎨 **Dark Mode CSS Classes**

The dark mode styles are automatically applied, but you can also use these CSS classes:

```css
/* Force dark mode on specific editor */
.ck-editor-dark { /* Dark mode styles applied */ }

/* Custom dark mode container */
.dark .ckeditor-container { /* Your custom dark styles */ }
```

## 🖼️ **Image Upload Setup**

Image upload is **already configured** and working! It uploads to:
- **Route**: `/cms-admin/upload-image`
- **Storage**: `storage/app/public/cms/images/`
- **Max Size**: 10MB
- **Formats**: JPG, PNG, GIF

## 📱 **Responsive & Accessibility Features**

✅ **Responsive toolbar** - Adapts to mobile screens  
✅ **High contrast support** - Works with high contrast mode  
✅ **Reduced motion support** - Respects user motion preferences  
✅ **Keyboard navigation** - Full keyboard accessibility  
✅ **Screen reader support** - Proper ARIA labels  

## 🔧 **Integration with Your CMS**

### **Update Your Existing CKEditor Calls**

**Before:**
```javascript
window.initCKEditor('editor-id', 'wire.model', content, 'en');
```

**After (Enhanced):**
```javascript
window.initAdvancedCKEditor('editor-id', 'wire.model', content, {
    language: 'en',
    darkMode: 'auto',
    toolbar: 'full',
    height: '400px'
});
```

### **Or Keep Using Simple Version:**
Your existing `initCKEditor` calls will still work! They now automatically support dark mode detection.

## 🚨 **Troubleshooting**

### **Dark Mode Not Working?**
1. Ensure your HTML has `class="dark"` for dark mode
2. Check if `window.matchMedia('(prefers-color-scheme: dark)')` works
3. Use `darkMode: true` to force dark mode

### **Images Not Uploading?**
1. Check `/cms-admin/upload-image` route exists
2. Verify CSRF token is present: `<meta name="csrf-token" content="{{ csrf_token() }}">`
3. Check storage permissions: `php artisan storage:link`

### **Editor Not Loading?**
1. Run `npm run build` to rebuild assets
2. Clear browser cache
3. Check console for JavaScript errors

## 📚 **Advanced Features**

### **Custom Toolbar Configuration**
```javascript
window.initAdvancedCKEditor('custom-editor', 'content', '', {
    toolbar: {
        items: [
            'heading', '|',
            'bold', 'italic', '|', 
            'link', '|',
            'bulletedList', 'numberedList', '|',
            'insertTable', '|',
            'undo', 'redo'
        ]
    }
});
```

### **Event Handling**
```javascript
window.initAdvancedCKEditor('editor', 'content', '').then(editor => {
    editor.model.document.on('change:data', () => {
        console.log('Content changed:', editor.getData());
    });
    
    editor.editing.view.document.on('focus', () => {
        console.log('Editor focused');
    });
});
```

## 🎉 **You're All Set!**

Your CKEditor 5 with dark mode is now ready to use! The implementation includes:

- ✅ **Full dark mode support** with automatic detection
- ✅ **Advanced toolbar** with all formatting options  
- ✅ **Image uploads** working out of the box
- ✅ **Table support** with advanced formatting
- ✅ **Multi-language support** including Arabic/RTL
- ✅ **Mobile responsive** design
- ✅ **Accessibility compliant**
- ✅ **Livewire integration** for seamless Laravel workflow

Check the `/resources/views/examples/ckeditor-usage.blade.php` file for more detailed examples!

## 🆘 **Need Help?**

If you encounter any issues:
1. Check browser console for errors
2. Verify all CSS/JS assets loaded correctly
3. Test with `darkMode: true` to force dark mode
4. Refer to the examples in the usage file

Happy editing with your new dark mode CKEditor! 🌙✨