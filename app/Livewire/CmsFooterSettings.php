<?php

namespace App\Livewire;

use App\Models\CmsPage;
use App\Services\CmsFooterService;
use Livewire\Component;
use Illuminate\Support\Facades\Auth;
use Flux\Flux;

class CmsFooterSettings extends Component
{
    public $quoteText = '';
    public $quoteAuthor = '';
    public $quoteIsActive = true;
    
    public $aboutTitle = '';
    public $aboutDescription = '';
    public $aboutIsActive = true;
    public $socialLinks = [];
    
    public $classesTitle = '';
    public $classesItems = [];
    public $classesIsActive = true;
    
    public $contactTitle = '';
    public $contactAddress = '';
    public $contactPhone = '';
    public $contactEmail = '';
    public $contactIsActive = true;
    
    public $hoursTitle = '';
    public $weekdaysDays = '';
    public $weekdaysTime = '';
    public $weekdaysNote = '';
    public $weekendDays = '';
    public $weekendTime = '';
    public $weekendNote = '';
    public $hoursIsActive = true;
    
    public $copyrightText = '';
    public $copyrightIsActive = true;
    
    public function mount()
    {
        $orgId = auth()->user()?->orgUser?->org_id ?? env('CMS_DEFAULT_ORG_ID', env('DEFAULT_ORG_ID', 8));
        $portalId = env('CMS_DEFAULT_PORTAL_ID', 1);
        
        // Try to load existing footer data
        $footerPage = CmsPage::where('org_id', $orgId)
            ->where('orgPortal_id', $portalId)
            ->where('slug', 'footer')
            ->first();
        
        if ($footerPage && $footerPage->meta_data) {
            $metaData = is_array($footerPage->meta_data) ? $footerPage->meta_data : json_decode($footerPage->meta_data, true);
            $footerData = $metaData['footer_data'] ?? [];
            
            if (!empty($footerData)) {
                $this->loadFooterData($footerData);
            } else {
                $this->loadDefaultData();
            }
        } else {
            $this->loadDefaultData();
        }
    }
    
    private function loadFooterData($data)
    {
        // Quote
        $this->quoteText = $data['quote']['text'] ?? '';
        $this->quoteAuthor = $data['quote']['author'] ?? '';
        $this->quoteIsActive = $data['quote']['is_active'] ?? true;
        
        // About
        $this->aboutTitle = $data['about']['title'] ?? '';
        $this->aboutDescription = $data['about']['description'] ?? '';
        $this->aboutIsActive = $data['about']['is_active'] ?? true;
        $this->socialLinks = $data['about']['social_links'] ?? [['icon' => '', 'url' => '']];
        
        // Classes
        $this->classesTitle = $data['classes']['title'] ?? '';
        $this->classesItems = $data['classes']['items'] ?? [''];
        $this->classesIsActive = $data['classes']['is_active'] ?? true;
        
        // Contact
        $this->contactTitle = $data['contact']['title'] ?? '';
        $this->contactAddress = $data['contact']['address'] ?? '';
        $this->contactPhone = $data['contact']['phone'] ?? '';
        $this->contactEmail = $data['contact']['email'] ?? '';
        $this->contactIsActive = $data['contact']['is_active'] ?? true;
        
        // Hours
        $this->hoursTitle = $data['hours']['title'] ?? '';
        $this->weekdaysDays = $data['hours']['weekdays']['days'] ?? '';
        $this->weekdaysTime = $data['hours']['weekdays']['time'] ?? '';
        $this->weekdaysNote = $data['hours']['weekdays']['note'] ?? '';
        $this->weekendDays = $data['hours']['weekend']['days'] ?? '';
        $this->weekendTime = $data['hours']['weekend']['time'] ?? '';
        $this->weekendNote = $data['hours']['weekend']['note'] ?? '';
        $this->hoursIsActive = $data['hours']['is_active'] ?? true;
        
        // Copyright
        $this->copyrightText = $data['copyright']['text'] ?? '';
        $this->copyrightIsActive = $data['copyright']['is_active'] ?? true;
    }
    
    private function loadDefaultData()
    {
        $default = CmsFooterService::getDefaultFooterData();
        $this->loadFooterData($default);
    }
    
    public function addSocialLink()
    {
        $this->socialLinks[] = ['icon' => '', 'url' => ''];
    }
    
    public function removeSocialLink($index)
    {
        unset($this->socialLinks[$index]);
        $this->socialLinks = array_values($this->socialLinks);
    }
    
    public function addClassItem()
    {
        $this->classesItems[] = '';
    }
    
    public function removeClassItem($index)
    {
        unset($this->classesItems[$index]);
        $this->classesItems = array_values($this->classesItems);
    }
    
    public function save()
    {
        $orgId = auth()->user()?->orgUser?->org_id ?? env('CMS_DEFAULT_ORG_ID', env('DEFAULT_ORG_ID', 8));
        $portalId = env('CMS_DEFAULT_PORTAL_ID', 1);
        
        // Prepare footer data
        $footerData = [
            'quote' => [
                'text' => $this->quoteText,
                'author' => $this->quoteAuthor,
                'is_active' => $this->quoteIsActive
            ],
            'about' => [
                'title' => $this->aboutTitle,
                'description' => $this->aboutDescription,
                'is_active' => $this->aboutIsActive,
                'social_links' => array_filter($this->socialLinks, function($link) {
                    return !empty($link['icon']) || !empty($link['url']);
                })
            ],
            'classes' => [
                'title' => $this->classesTitle,
                'is_active' => $this->classesIsActive,
                'items' => array_filter($this->classesItems, function($item) {
                    return !empty($item);
                })
            ],
            'contact' => [
                'title' => $this->contactTitle,
                'address' => $this->contactAddress,
                'phone' => $this->contactPhone,
                'email' => $this->contactEmail,
                'is_active' => $this->contactIsActive
            ],
            'hours' => [
                'title' => $this->hoursTitle,
                'is_active' => $this->hoursIsActive,
                'weekdays' => [
                    'days' => $this->weekdaysDays,
                    'time' => $this->weekdaysTime,
                    'note' => $this->weekdaysNote
                ],
                'weekend' => [
                    'days' => $this->weekendDays,
                    'time' => $this->weekendTime,
                    'note' => $this->weekendNote
                ]
            ],
            'copyright' => [
                'text' => $this->copyrightText,
                'year' => date('Y'),
                'is_active' => $this->copyrightIsActive
            ]
        ];
        
        // Find or create footer page
        $footerPage = CmsPage::where('org_id', $orgId)
            ->where('orgPortal_id', $portalId)
            ->where('slug', 'footer')
            ->first();
        
        if (!$footerPage) {
            $footerPage = CmsPage::create([
                'org_id' => $orgId,
                'orgPortal_id' => $portalId,
                'title' => 'Footer Settings',
                'slug' => 'footer',
                'status' => 'published',
                'type' => 'custom',
                'is_homepage' => false,
                'show_in_navigation' => false,
                'meta_data' => ['footer_data' => $footerData],
                'created_by' => Auth::id(),
                'updated_by' => Auth::id(),
            ]);
        } else {
            $metaData = is_array($footerPage->meta_data) ? $footerPage->meta_data : json_decode($footerPage->meta_data, true) ?? [];
            $metaData['footer_data'] = $footerData;
            
            $footerPage->update([
                'meta_data' => $metaData,
                'updated_by' => Auth::id(),
            ]);
        }
        
        // Clear cache
        CmsFooterService::clearCache($orgId, $portalId);
        
        Flux::toast(
            variant: 'success',
            heading: 'Footer Settings Saved',
            text: 'Your footer content has been updated successfully!'
        );
    }
    
    public function render()
    {
        return view('livewire.cms-footer-settings');
    }
}

