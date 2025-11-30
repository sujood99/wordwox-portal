<?php

namespace App\Livewire;

use App\Models\CmsPage;
use App\Models\TemplateThemeColor;
use Livewire\Component;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Flux\Flux;

class TemplatePreview extends Component
{
    public $selectedTemplate = 'default';
    public $previewPage = null;
    public $availableTemplates = [];
    public $successMessage = '';
    public $errorMessage = '';
    
    // Theme color customization (for fitness template)
    public $showColorCustomization = false;
    public $themeColors = [];

    public function mount()
    {
        // Get a sample page for preview (preferably homepage)
        $this->previewPage = CmsPage::where('org_id', env('CMS_DEFAULT_ORG_ID', 8))
            ->where('status', 'published')
            ->where('is_homepage', true)
            ->with(['sections' => function($query) {
                $query->where('is_active', true)
                      ->where('is_visible', true)
                      ->orderBy('sort_order', 'asc');
            }])
            ->first();

        // If no homepage, get any published page
        if (!$this->previewPage) {
            $this->previewPage = CmsPage::where('org_id', env('CMS_DEFAULT_ORG_ID', 8))
                ->where('status', 'published')
                ->with(['sections' => function($query) {
                    $query->where('is_active', true)
                          ->where('is_visible', true)
                          ->orderBy('sort_order', 'asc');
                }])
                ->first();
        }

        $this->loadAvailableTemplates();
        
        // Set selected template - use page template if it exists in available templates, otherwise use first available
        if ($this->previewPage && $this->previewPage->template && isset($this->availableTemplates[$this->previewPage->template])) {
            $this->selectedTemplate = $this->previewPage->template;
        } elseif (!empty($this->availableTemplates)) {
            $this->selectedTemplate = array_key_first($this->availableTemplates);
        } else {
            $this->selectedTemplate = '';
        }
        
        // Load theme colors if fitness template is selected
        $this->loadThemeColors();
    }
    
    public function loadThemeColors()
    {
        if ($this->selectedTemplate === 'fitness') {
            $orgId = Auth::user()->orgUser->org_id ?? env('CMS_DEFAULT_ORG_ID', 8);
            $themeColor = TemplateThemeColor::getOrCreateForOrg($orgId, 'fitness');
            
            $this->themeColors = [
                'primary_color' => $themeColor->primary_color,
                'secondary_color' => $themeColor->secondary_color,
                'text_dark' => $themeColor->text_dark,
                'text_gray' => $themeColor->text_gray,
                'text_base' => $themeColor->text_base,
                'text_light' => $themeColor->text_light,
                'bg_white' => $themeColor->bg_white,
                'bg_light' => $themeColor->bg_light,
                'bg_lighter' => $themeColor->bg_lighter,
                'bg_packages' => $themeColor->bg_packages,
                'bg_footer' => $themeColor->bg_footer,
                'primary_hover' => $themeColor->primary_hover,
                'secondary_hover' => $themeColor->secondary_hover,
            ];
        } else {
            $this->themeColors = [];
        }
    }

    public function loadAvailableTemplates()
    {
        $this->availableTemplates = [
            'modern' => [
                'name' => 'Modern Template',
                'description' => 'Futuristic Glass Design',
                'icon' => '🚀',
                'preview_color' => 'bg-gradient-to-r from-purple-100 to-pink-100',
                'features' => ['Glass morphism', 'Neon glows', 'Floating animations']
            ],
            'meditative' => [
                'name' => 'Meditative Template',
                'description' => 'Zen Wellness Design',
                'icon' => '🧘‍♀️',
                'preview_color' => 'bg-gradient-to-r from-purple-100 via-pink-100 to-indigo-100',
                'features' => ['Zen aesthetics', 'Peaceful colors', 'Mindful animations']
            ],
            'fitness' => [
                'name' => 'Fitness Template',
                'description' => 'Modern Gym & Fitness Design',
                'icon' => '💪',
                'preview_color' => 'bg-gradient-to-r from-red-100 to-orange-100',
                'features' => ['Gym layout', 'Bootstrap 5', 'Responsive design', 'Multiple page types']
            ],
        ];
    }

    public function selectTemplate($template)
    {
        $this->selectedTemplate = $template;
        $this->loadThemeColors();
        $this->showColorCustomization = ($template === 'fitness');
    }
    
    public function toggleColorCustomization()
    {
        $this->showColorCustomization = !$this->showColorCustomization;
    }
    
    public function saveThemeColors()
    {
        try {
            if ($this->selectedTemplate !== 'fitness') {
                $this->errorMessage = 'Theme colors can only be customized for the Fitness template.';
                return;
            }
            
            $orgId = Auth::user()->orgUser->org_id ?? env('CMS_DEFAULT_ORG_ID', 8);
            $themeColor = TemplateThemeColor::getOrCreateForOrg($orgId, 'fitness');
            
            $themeColor->update([
                'primary_color' => $this->themeColors['primary_color'] ?? '#ff6b6b',
                'secondary_color' => $this->themeColors['secondary_color'] ?? '#4ecdc4',
                'text_dark' => $this->themeColors['text_dark'] ?? '#2c3e50',
                'text_gray' => $this->themeColors['text_gray'] ?? '#6c757d',
                'text_base' => $this->themeColors['text_base'] ?? '#333',
                'text_light' => $this->themeColors['text_light'] ?? '#ffffff',
                'bg_white' => $this->themeColors['bg_white'] ?? '#ffffff',
                'bg_light' => $this->themeColors['bg_light'] ?? '#f8f9fa',
                'bg_lighter' => $this->themeColors['bg_lighter'] ?? '#e9ecef',
                'bg_packages' => $this->themeColors['bg_packages'] ?? '#f2f4f6',
                'bg_footer' => $this->themeColors['bg_footer'] ?? '#2c3e50',
                'primary_hover' => $this->themeColors['primary_hover'] ?? '#ff5252',
                'secondary_hover' => $this->themeColors['secondary_hover'] ?? '#3db8a8',
            ]);
            
            $this->successMessage = 'Theme colors saved successfully!';
            Flux::toast(
                variant: 'success',
                heading: 'Colors Saved',
                text: 'Your theme colors have been updated successfully.'
            );
            
        } catch (\Exception $e) {
            Log::error('Error saving theme colors: ' . $e->getMessage());
            $this->errorMessage = 'Error saving theme colors: ' . $e->getMessage();
            Flux::toast(
                variant: 'danger',
                heading: 'Error',
                text: $this->errorMessage
            );
        }
    }
    
    public function resetThemeColors()
    {
        $defaults = TemplateThemeColor::getDefaults();
        $this->themeColors = $defaults;
    }

    public function applyTemplate()
    {
        try {
            if (!$this->selectedTemplate || !isset($this->availableTemplates[$this->selectedTemplate])) {
                $this->errorMessage = 'Please select a valid template first.';
                return;
            }

            $orgId = env('CMS_DEFAULT_ORG_ID', 8);
            $portalId = env('CMS_DEFAULT_PORTAL_ID', 1);

            // Log the attempt
            Log::info('Attempting to apply template to all organization pages', [
                'org_id' => $orgId,
                'portal_id' => $portalId,
                'selected_template' => $this->selectedTemplate,
            ]);

            // Update template for all pages in the organization
            $updatedCount = CmsPage::where('org_id', $orgId)
                ->update(['template' => $this->selectedTemplate]);
            
            if ($updatedCount > 0) {
                Log::info('Template applied successfully to all pages', [
                    'pages_updated' => $updatedCount,
                    'template' => $this->selectedTemplate
                ]);
                
                $this->successMessage = 'Template "' . $this->availableTemplates[$this->selectedTemplate]['name'] . 
                    '" applied successfully to all ' . $updatedCount . ' page(s) in your organization!';
                
                // Show success toast notification
                Flux::toast(
                    variant: 'success',
                    heading: 'Template Applied',
                    text: $this->successMessage
                );
            } else {
                Log::warning('No pages found to update template for');
                $this->errorMessage = 'No pages found to apply template to. Please create some pages first.';
                Flux::toast(
                    variant: 'danger',
                    heading: 'No Pages Found',
                    text: $this->errorMessage
                );
            }
            
        } catch (\Exception $e) {
            Log::error('Error applying template to all pages: ' . $e->getMessage());
            $this->errorMessage = 'Error applying template: ' . $e->getMessage();
            Flux::toast(
                variant: 'danger',
                heading: 'Error',
                text: $this->errorMessage
            );
        }
    }

    public function previewTemplate($template)
    {
        // Open preview in new tab
        $url = $this->previewPage ? 
            ('/' . ($this->previewPage->slug === 'home' ? '' : $this->previewPage->slug) . '?preview_template=' . $template) :
            '/?preview_template=' . $template;
            
        // Dispatch event with URL as first parameter for Livewire 3 compatibility
        $this->dispatch('open-preview', $url);
    }

    public function render()
    {
        return view('livewire.template-preview');
    }
}
