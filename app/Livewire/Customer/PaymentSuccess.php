<?php

namespace App\Livewire\Customer;

use App\Models\OrgPlan;
use App\Models\OrgUserPlan;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Livewire\Component;

class PaymentSuccess extends Component
{
    public $plan = null;
    public $ref = null;
    public $navigationPages;

    public function mount($ref = null)
    {
        $this->ref = $ref ?? request()->query('ref');
        
        // Load navigation pages
        $this->loadNavigationPages();
        
        // Parse reference to get plan UUID
        if ($this->ref) {
            $refParts = explode('_', $this->ref);
            if (count($refParts) >= 2 && $refParts[0] === 'plan') {
                $planUuid = $refParts[1];
                
                // Load plan details
                try {
                    $this->plan = OrgPlan::where('uuid', $planUuid)->first();
                    
                    if ($this->plan) {
                        Log::info('PaymentSuccess: Plan loaded', [
                            'plan_uuid' => $planUuid,
                            'plan_id' => $this->plan->id,
                            'plan_name' => $this->plan->name,
                        ]);
                    } else {
                        Log::warning('PaymentSuccess: Plan not found', [
                            'plan_uuid' => $planUuid,
                        ]);
                    }
                } catch (\Exception $e) {
                    Log::error('PaymentSuccess: Error loading plan', [
                        'plan_uuid' => $planUuid,
                        'error' => $e->getMessage(),
                    ]);
                }
            }
        }
    }

    /**
     * Load navigation pages for navbar
     */
    protected function loadNavigationPages()
    {
        $orgId = env('CMS_DEFAULT_ORG_ID', 8);
        // Cache navigation pages for 1 hour to improve performance
        $this->navigationPages = \Illuminate\Support\Facades\Cache::remember(
            "navigation_pages_{$orgId}",
            3600,
            function () use ($orgId) {
                return \App\Models\CmsPage::where('org_id', $orgId)
                    ->where('status', 'published')
                    ->where('show_in_navigation', true)
                    ->orderBy('sort_order')
                    ->get();
            }
        );
    }

    public function render()
    {
        return view('livewire.customer.payment-success', [
            'navigationPages' => $this->navigationPages ?? collect(),
        ])->layout('components.layouts.templates.fitness', [
            'navigationPages' => $this->navigationPages ?? collect(),
        ]);
    }
}

