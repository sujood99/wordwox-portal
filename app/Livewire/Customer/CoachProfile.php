<?php

namespace App\Livewire\Customer;

use App\Models\CmsPage;
use App\Models\OrgUser;
use Illuminate\Support\Facades\Log;
use Livewire\Component;

/**
 * Coach Profile Component
 * 
 * Displays detailed information about a coach including:
 * - Profile photo
 * - Full name
 * - Bio
 * - Favorite quote
 * - Certificates
 * 
 * Similar to Yii's coach/view page
 */
class CoachProfile extends Component
{
    public ?OrgUser $coach = null;
    public ?string $coachId = null;
    public $navigationPages;

    public function mount($id = null)
    {
        // Get id from route parameter or query parameter
        $this->coachId = $id ?? request()->query('id');
        
        Log::info('CoachProfile mount called', [
            'id_parameter' => $id,
            'query_id' => request()->query('id'),
            'coachId' => $this->coachId,
            'request_url' => request()->fullUrl(),
            'query_params' => request()->query()
        ]);
        
        if ($this->coachId) {
            // Find coach by UUID (same as Yii project)
            // More flexible query - checks for null or false on isDeleted
            $this->coach = OrgUser::where('uuid', $this->coachId)
                ->where('isOnRoster', true)
                ->where(function($query) {
                    $query->where('isDeleted', false)
                          ->orWhereNull('isDeleted');
                })
                ->first();
            
            if (!$this->coach) {
                Log::warning('Coach not found', [
                    'uuid' => $this->coachId,
                    'url' => request()->fullUrl()
                ]);
                session()->flash('error', 'Coach not found.');
                $this->redirect(route('home'), navigate: false);
                return;
            }
            
            Log::info('Coach profile loaded successfully', [
                'coach_id' => $this->coach->id,
                'coach_name' => $this->coach->fullName,
                'uuid' => $this->coachId
            ]);
            
            // Load navigation pages for the navbar
            $this->loadNavigationPages();
        } else {
            Log::warning('No coach ID provided', [
                'url' => request()->fullUrl(),
                'query_params' => request()->query()
            ]);
            session()->flash('error', 'No coach specified.');
            $this->redirect(route('home'), navigate: false);
        }
    }
    
    /**
     * Load navigation pages for the navbar
     * Similar to CmsPageViewer component
     */
    protected function loadNavigationPages()
    {
        $orgId = env('CMS_DEFAULT_ORG_ID', 8);
        $this->navigationPages = CmsPage::where('org_id', $orgId)
            ->where('status', 'published')
            ->where('show_in_navigation', true)
            ->where('is_homepage', false)
            ->where('slug', '!=', 'home')
            ->orderBy('sort_order', 'asc')
            ->get();
    }

    public function render()
    {
        return view('livewire.customer.coach-profile')
            ->layout('components.layouts.templates.fitness', [
                'navigationPages' => $this->navigationPages ?? collect(),
            ]);
    }
}
