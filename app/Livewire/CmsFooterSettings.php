<?php

namespace App\Livewire;

use App\Models\CmsSection;
use Livewire\Component;
use Illuminate\Support\Facades\Auth;
use Flux\Flux;

/**
 * Footer Settings Component
 * 
 * Manages footer content using dynamic blocks, similar to page editor.
 * All static sections have been removed - footer is now built entirely with blocks.
 */
class CmsFooterSettings extends Component
{
    // Footer Blocks Management
    public $footerBlocks = [];
    public $showBlockSelector = false;
    
    /**
     * Get available block types for footer
     * Matching edit page block types for consistency
     */
    public function getBlockTypesProperty()
    {
        return [
            // Text and Content Blocks
            'text' => ['name' => 'Text', 'icon' => 'document-text', 'description' => 'Simple text content', 'category' => 'content'],
            'paragraph' => ['name' => 'Paragraph', 'icon' => 'document-text', 'description' => 'Rich text with formatting', 'category' => 'richtext'],
            'html' => ['name' => 'HTML', 'icon' => 'code-bracket', 'description' => 'Custom HTML code', 'category' => 'content'],
            
            // Link Blocks
            'links' => ['name' => 'Links', 'icon' => 'link', 'description' => 'List of links', 'category' => 'content'],
            
            // Visual Blocks
            'heading' => ['name' => 'Heading', 'icon' => 'h1', 'description' => 'Section heading', 'category' => 'structured'],
            'image' => ['name' => 'Image', 'icon' => 'photo', 'description' => 'Image block', 'category' => 'structured'],
            'spacer' => ['name' => 'Spacer', 'icon' => 'minus', 'description' => 'Vertical spacing', 'category' => 'structured'],
            
            // Information Blocks
            'contact' => ['name' => 'Contact', 'icon' => 'envelope', 'description' => 'Contact details', 'category' => 'structured'],
        ];
    }
    
    public function mount()
    {
        // Load dynamic footer blocks
        $this->loadFooterBlocks();
    }

    /**
     * Watch for changes to footerBlocks array and persist to database
     */
    #[\Livewire\Attributes\On('updated')]
    public function updatedFooterBlocks()
    {
        // Save changes to database when footerBlocks array is updated
        foreach ($this->footerBlocks as $block) {
            $section = CmsSection::find($block['id']);
            if ($section) {
                $section->update([
                    'content' => $block['content'] ?? '',
                    'name' => $block['name'] ?? '',
                    'is_active' => $block['is_active'] ?? true,
                ]);
            }
        }
    }

    /**
     * Updated hook for when block content changes
     * This is called by Livewire when any property starting with footerBlocks changes
     */
    public function updated($property)
    {
        if (str_starts_with($property, 'footerBlocks.')) {
            // Extract block index from property path (e.g., "footerBlocks.0.content")
            preg_match('/footerBlocks\.(\d+)/', $property, $matches);
            if (!empty($matches[1])) {
                $blockIndex = (int)$matches[1];
                if (isset($this->footerBlocks[$blockIndex])) {
                    $block = $this->footerBlocks[$blockIndex];
                    $section = CmsSection::find($block['id']);
                    if ($section) {
                        $section->update([
                            'content' => $block['content'] ?? '',
                            'name' => $block['name'] ?? '',
                            'is_active' => $block['is_active'] ?? true,
                        ]);
                    }
                }
            }
        }
    }
    
    /**
     * Load footer blocks from database
     */
    public function loadFooterBlocks()
    {
        $this->footerBlocks = CmsSection::where('container', 'footer')
            ->where('cms_page_id', null)
            ->orderBy('sort_order')
            ->get()
            ->map(function ($block) {
                return [
                    'id' => $block->id,
                    'type' => $block->type,
                    'name' => $block->name,
                    'content' => $block->content ?? '',
                    'data' => $block->data ?? [],
                    'is_active' => $block->is_active ?? true,
                    'sort_order' => $block->sort_order ?? 0,
                ];
            })
            ->toArray();
    }
    
    /**
     * Add a new block to the footer
     */
    public function addBlock($type)
    {
        // Get max sort order for footer blocks
        $maxOrder = CmsSection::where('container', 'footer')
            ->where('cms_page_id', null)
            ->max('sort_order') ?? 0;
        
        // Get default content based on block type
        $defaultContent = $this->getDefaultContent($type);
        $defaultName = $this->getDefaultName($type);
        
        // Create the block
        $block = CmsSection::create([
            'cms_page_id' => null,
            'container' => 'footer',
            'type' => $type,
            'name' => $defaultName,
            'content' => $defaultContent,
            'data' => $this->getDefaultData($type),
            'settings' => $this->getDefaultSettings($type),
            'is_active' => true,
            'sort_order' => $maxOrder + 1,
        ]);
        
        // Reload blocks and close selector
        $this->loadFooterBlocks();
        $this->showBlockSelector = false;
        
        Flux::toast(
            variant: 'success',
            heading: 'Block Added',
            text: 'New ' . ucfirst($type) . ' block created successfully.'
        );
    }
    
    /**
     * Get default content for a block type
     */
    private function getDefaultContent($type)
    {
        return match($type) {
            'text' => 'Enter your text content here...',
            'html' => '<div>Enter your HTML content here...</div>',
            'links' => json_encode([
                ['label' => 'Home', 'url' => '/'],
                ['label' => 'About', 'url' => '/about'],
            ]),
            'paragraph' => '<p>Enter your paragraph content here...</p>',
            'heading' => 'Section Heading',
            'image' => '',
            'contact' => 'Contact Us',
            'spacer' => '',
            default => '',
        };
    }
    
    /**
     * Get default name for a block type
     */
    private function getDefaultName($type)
    {
        return match($type) {
            'text' => 'Text Block',
            'html' => 'HTML Block',
            'links' => 'Links Block',
            'paragraph' => 'Paragraph Block',
            'heading' => 'Heading Block',
            'image' => 'Image Block',
            'contact' => 'Contact Block',
            'spacer' => 'Spacer Block',
            default => 'New Block',
        };
    }
    
    /**
     * Get default data for a block type
     */
    private function getDefaultData($type)
    {
        return match($type) {
            'links' => [
                ['label' => 'Home', 'url' => '/'],
                ['label' => 'About', 'url' => '/about'],
            ],
            'contact' => [
                'email' => '',
                'phone' => '',
                'address' => '',
            ],
            'image' => [
                'url' => '',
                'alt' => '',
                'caption' => '',
            ],
            default => [],
        };
    }
    
    /**
     * Get default settings for a block type
     */
    private function getDefaultSettings($type)
    {
        return match($type) {
            'heading' => [
                'level' => 'h3',
                'alignment' => 'left',
            ],
            'image' => [
                'width' => 'full',
                'alignment' => 'center',
            ],
            'spacer' => [
                'height' => 'md',
            ],
            default => [],
        };
    }
    
    /**
     * Update a single field on a block (for inline editing)
     */
    public function updateBlockField($blockId, $field, $value)
    {
        $block = CmsSection::find($blockId);
        if ($block) {
            $block->update([$field => $value]);
            $this->loadFooterBlocks();
        }
    }
    
    /**
     * Update a field in the block's data JSON
     */
    public function updateBlockDataField($blockId, $field, $value)
    {
        $block = CmsSection::find($blockId);
        if ($block) {
            $data = is_array($block->data) ? $block->data : (json_decode($block->data ?? '{}', true) ?? []);
            $data[$field] = $value;
            $block->update(['data' => $data]);
            $this->loadFooterBlocks();
        }
    }
    
    /**
     * Remove a block from the footer
     */
    public function removeBlock($blockId)
    {
        $block = CmsSection::find($blockId);
        if ($block) {
            $block->delete();
            $this->loadFooterBlocks();
            
            Flux::toast(
                variant: 'success',
                heading: 'Block Deleted',
                text: 'Block removed from footer.'
            );
        }
    }
    
    /**
     * Move block up in order
     */
    public function moveBlockUp($blockId)
    {
        $block = CmsSection::find($blockId);
        if (!$block) {
            return;
        }
        
        // Find the block above this one
        $previousBlock = CmsSection::where('container', 'footer')
            ->where('cms_page_id', null)
            ->where('sort_order', '<', $block->sort_order)
            ->orderBy('sort_order', 'desc')
            ->first();
        
        if ($previousBlock) {
            // Swap sort orders
            $tempOrder = $block->sort_order;
            $block->update(['sort_order' => $previousBlock->sort_order]);
            $previousBlock->update(['sort_order' => $tempOrder]);
            
            $this->loadFooterBlocks();
        }
    }
    
    /**
     * Move block down in order
     */
    public function moveBlockDown($blockId)
    {
        $block = CmsSection::find($blockId);
        if (!$block) {
            return;
        }
        
        // Find the block below this one
        $nextBlock = CmsSection::where('container', 'footer')
            ->where('cms_page_id', null)
            ->where('sort_order', '>', $block->sort_order)
            ->orderBy('sort_order', 'asc')
            ->first();
        
        if ($nextBlock) {
            // Swap sort orders
            $tempOrder = $block->sort_order;
            $block->update(['sort_order' => $nextBlock->sort_order]);
            $nextBlock->update(['sort_order' => $tempOrder]);
            
            $this->loadFooterBlocks();
        }
    }
    
    /**
     * Toggle block active/inactive status
     */
    public function toggleBlockActive($blockId)
    {
        $block = CmsSection::find($blockId);
        if ($block) {
            $block->update(['is_active' => !($block->is_active ?? true)]);
            $this->loadFooterBlocks();
        }
    }

    /**
     * Save all block changes
     * Persists all block modifications and displays success message
     */
    public function saveBlocks()
    {
        try {
            // All changes are already saved via wire:change on individual fields
            // This method serves as a confirmation/final save trigger
            $blocksCount = count($this->footerBlocks);
            
            Flux::toast(
                text: "Footer settings saved successfully! ($blocksCount blocks updated)",
                variant: 'success',
                duration: 3000
            );
            
            $this->loadFooterBlocks();
        } catch (\Exception $e) {
            Flux::toast(
                text: 'Error saving footer settings: ' . $e->getMessage(),
                variant: 'error',
                duration: 5000
            );
        }
    }
    
    public function render()
    {
        return view('livewire.cms-footer-settings');
    }
}
