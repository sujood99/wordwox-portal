<?php

namespace App\View\Components;

use App\Services\CmsFooterSectionService;
use Illuminate\View\Component;
use Illuminate\Support\Facades\Auth;

/**
 * Footer Component
 * 
 * Displays dynamic footer content from CMS footer sections
 * 
 * Usage:
 * <x-footer />
 * <x-footer :orgId="$orgId" />
 */
class Footer extends Component
{
    public $orgId;
    public $portalId;
    public $socialLinks;
    public $footerSections;

    /**
     * Create a new component instance.
     */
    public function __construct($orgId = null, $portalId = 1)
    {
        // Get org ID from auth if not provided
        $this->orgId = $orgId ?? optional(Auth::user()?->orgUser)->org_id ?? config('cms.default_org_id', 8);
        $this->portalId = $portalId;
        
        // Load footer data
        $this->loadFooterData();
    }

    private function loadFooterData()
    {
        // Get social links (legacy support)
        $this->socialLinks = CmsFooterSectionService::getSocialLinks(
            $this->orgId,
            $this->portalId
        );
        
        // Get all footer sections (dynamic blocks)
        $this->footerSections = CmsFooterSectionService::getFooterSections(
            $this->orgId,
            $this->portalId
        );
    }

    /**
     * Render the component.
     */
    public function render()
    {
        return view('components.footer', [
            'socialLinks' => $this->socialLinks,
            'footerSections' => $this->footerSections,
        ]);
    }
}
