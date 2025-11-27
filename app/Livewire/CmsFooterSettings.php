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
     * Simplified to 5 essential block types
     */
    public function getBlockTypesProperty()
    {
        return [
            // Rich Content Block
            'paragraph' => ['name' => 'Paragraph', 'icon' => 'document-text', 'description' => 'Rich text with formatting', 'category' => 'content'],
            
            // Navigation Block
            'links' => ['name' => 'Links', 'icon' => 'link', 'description' => 'List of links', 'category' => 'content'],
            
            // Contact Information Block  
            'contact' => ['name' => 'Contact', 'icon' => 'envelope', 'description' => 'Contact details', 'category' => 'content'],
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
            'paragraph' => '<p>Enter your paragraph content here...</p>',
            'heading' => 'Section Heading',
            'spacer' => '30',
            'links' => json_encode([
                ['label' => 'Home', 'url' => '/'],
                ['label' => 'About', 'url' => '/about'],
            ]),
            'contact' => 'Contact Us',
            default => '',
        };
    }
    
    /**
     * Get default name for a block type
     */
    private function getDefaultName($type)
    {
        return match($type) {
            'paragraph' => 'Content Block',
            'heading' => 'Section Heading',
            'spacer' => 'Spacer',
            'links' => 'Quick Links',
            'contact' => 'Contact Info',
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
     * Update a specific link within a links block
     */
    public function updateLinkInBlock($blockId, $linkIndex, $field, $value)
    {
        try {
            $block = CmsSection::findOrFail($blockId);
            
            // Get current links
            $links = [];
            if (is_string($block->content)) {
                $links = json_decode($block->content, true) ?? [];
            } elseif (is_array($block->content)) {
                $links = $block->content;
            }
            
            // Update the specific link field
            if (!isset($links[$linkIndex])) {
                $links[$linkIndex] = ['label' => '', 'url' => ''];
            }
            $links[$linkIndex][$field] = $value;
            
            // Save back to database
            $block->content = json_encode($links);
            $block->save();
            
            // Reload blocks
            $this->loadFooterBlocks();
            
        } catch (\Exception $e) {
            Flux::toast(
                text: 'Error updating link: ' . $e->getMessage(),
                variant: 'error',
                duration: 3000
            );
        }
    }
    
    /**
     * Add a new link to a links block
     */
    public function addLinkToBlock($blockId)
    {
        try {
            $block = CmsSection::findOrFail($blockId);
            
            // Get current links
            $links = [];
            if (is_string($block->content)) {
                $links = json_decode($block->content, true) ?? [];
            } elseif (is_array($block->content)) {
                $links = $block->content;
            }
            
            // Add new empty link
            $links[] = ['label' => '', 'url' => ''];
            
            // Save back to database
            $block->content = json_encode($links);
            $block->save();
            
            // Reload blocks
            $this->loadFooterBlocks();
            
            Flux::toast(
                text: 'New link added successfully',
                variant: 'success',
                duration: 2000
            );
            
        } catch (\Exception $e) {
            Flux::toast(
                text: 'Error adding link: ' . $e->getMessage(),
                variant: 'error',
                duration: 3000
            );
        }
    }
    
    /**
     * Remove a link from a links block
     */
    public function removeLinkFromBlock($blockId, $linkIndex)
    {
        try {
            $block = CmsSection::findOrFail($blockId);
            
            // Get current links
            $links = [];
            if (is_string($block->content)) {
                $links = json_decode($block->content, true) ?? [];
            } elseif (is_array($block->content)) {
                $links = $block->content;
            }
            
            // Remove the link at specified index
            if (isset($links[$linkIndex])) {
                array_splice($links, $linkIndex, 1);
            }
            
            // Save back to database
            $block->content = json_encode($links);
            $block->save();
            
            // Reload blocks
            $this->loadFooterBlocks();
            
            Flux::toast(
                text: 'Link removed successfully',
                variant: 'success',
                duration: 2000
            );
            
        } catch (\Exception $e) {
            Flux::toast(
                text: 'Error removing link: ' . $e->getMessage(),
                variant: 'error',
                duration: 3000
            );
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
