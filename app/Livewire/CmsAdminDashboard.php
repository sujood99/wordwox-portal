<?php

namespace App\Livewire;

use App\Models\CmsPage;
use App\Models\CmsSection;
use Livewire\Component;

class CmsAdminDashboard extends Component
{
    public $stats = [];
    public $recentPages = [];
    public $orgId;
    public $portalId;

    public function mount()
    {
        // Get current user's org and portal
        $user = auth()->user();
        $this->orgId = $user && $user->orgUser ? $user->orgUser->org_id : 8;
        $this->portalId = 1; // Default portal for now
        
        $this->loadStats();
        $this->loadRecentPages();
    }

    public function loadStats()
    {
        $this->stats = [
            'total_pages' => CmsPage::where('org_id', $this->orgId)
                ->count(),
            'published_pages' => CmsPage::where('org_id', $this->orgId)
                ->where('status', 'published')
                ->count(),
            'draft_pages' => CmsPage::where('org_id', $this->orgId)
                ->where('status', 'draft')
                ->count(),
            'total_sections' => CmsSection::forOrg($this->orgId)->count(),
        ];
    }

    public function loadRecentPages()
    {
        $this->recentPages = CmsPage::where('org_id', $this->orgId)
            ->orderBy('updated_at', 'desc')
            ->limit(5)
            ->get();
    }

    public function render()
    {
        return view('livewire.cms-admin-dashboard');
    }
}
