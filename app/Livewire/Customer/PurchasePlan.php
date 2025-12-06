<?php

namespace App\Livewire\Customer;

use App\Models\CmsPage;
use App\Models\OrgPlan;
use App\Models\OrgUser;
use App\Models\OrgSettingsPaymentGateway;
use App\Services\MyFatoorahPaymentApiService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Livewire\Component;

/**
 * Customer Purchase Plan Component
 * 
 * Handles package purchase flow:
 * 1. Load plan details
 * 2. Call wodworx-pay API to create payment (NO database records)
 * 3. Get payment URL and redirect user
 * 4. Database records created in callback after successful payment
 */
class PurchasePlan extends Component
{
    public $planUuid;
    public $plan;
    public $paymentUrl;
    public $loading = false;
    public $error = null;
    public $navigationPages;
    public $showFreeConfirmation = false;
    
    public function mount($plan = null)
    {
        // Show loading state initially
        $this->loading = true;
        
        // Load navigation pages (can be deferred but keeping for now)
        $this->loadNavigationPages();
        
        // Get plan UUID from query parameter
        $this->planUuid = $plan ?? request()->query('plan');
        
        if (!$this->planUuid) {
            $this->error = 'No plan specified.';
            $this->loading = false;
            return;
        }
        
        // Check if user is authenticated
        if (!Auth::check()) {
            // Redirect to login with redirect parameter
            return redirect()->route('login', ['redirect' => request()->fullUrl()]);
        }
        
        // Load plan details (fast - just database query)
        $this->loadPlan();
        
        if (!$this->plan) {
            $this->error = 'Plan not found.';
            $this->loading = false;
            return;
        }
        
        // Check if plan is free (zero price) - must check BEFORE trying to create payment
        if ($this->plan->price == 0 || $this->plan->price == 0.00 || $this->plan->price < 0.1) {
            // For free plans, show checkout page with "Complete order" button (no payment gateway)
            $this->showFreeConfirmation = true;
            $this->loading = false;
            return;
        }
        
        // Check if user already has an active plan (only for paid plans)
        $this->checkExistingMembership();
        if ($this->error) {
            $this->loading = false;
            return; // Error message already set by checkExistingMembership
        }
        
        // Check if MyFatoorah is configured
        if (!$this->isMyFatoorahAvailable()) {
            $this->error = 'Online payment is not available at this time.';
            $this->loading = false;
            return;
        }
        
        // For paid plans, start payment creation immediately (non-blocking)
        // Page will render immediately, payment URL will update when ready
        $this->createPayment();
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
                return CmsPage::where('org_id', $orgId)
                    ->where('status', 'published')
                    ->where('show_in_navigation', true)
                    ->orderBy('sort_order')
                    ->get();
            }
        );
    }
    
    /**
     * Load plan details
     */
    protected function loadPlan()
    {
        try {
            // Try to find by UUID first
            $this->plan = OrgPlan::where('uuid', $this->planUuid)
                ->orWhere('id', $this->planUuid)
                ->first();
                
            if (!$this->plan) {
                Log::warning('PurchasePlan: Plan not found', [
                    'plan_uuid' => $this->planUuid,
                ]);
            }
        } catch (\Exception $e) {
            Log::error('PurchasePlan: Error loading plan', [
                'plan_uuid' => $this->planUuid,
                'error' => $e->getMessage(),
            ]);
        }
    }
    
    /**
     * Check if MyFatoorah is available for this org
     */
    protected function isMyFatoorahAvailable(): bool
    {
        $orgId = Auth::user()->orgUser->org_id ?? env('CMS_DEFAULT_ORG_ID', 8);
        $settings = OrgSettingsPaymentGateway::getMyFatoorahForOrg($orgId);
        return !is_null($settings);
    }
    
    /**
     * Check for existing active memberships
     * Prevents duplicate purchases of the same plan
     */
    protected function checkExistingMembership()
    {
        $user = Auth::user();
        if (!$user->orgUser_id) {
            Log::info('PurchasePlan: No orgUser_id found for user', [
                'user_id' => $user->id,
            ]);
            return;
        }
        
        $orgUser = OrgUser::find($user->orgUser_id);
        if (!$orgUser) {
            Log::warning('PurchasePlan: OrgUser not found', [
                'orgUser_id' => $user->orgUser_id,
            ]);
            return;
        }
        
        // Check for active, upcoming, or pending membership for this specific plan
        $existingMembership = \App\Models\OrgUserPlan::where('orgUser_id', $orgUser->id)
            ->where('orgPlan_id', $this->plan->id)
            ->whereIn('status', [
                \App\Models\OrgUserPlan::STATUS_ACTIVE,
                \App\Models\OrgUserPlan::STATUS_UPCOMING,
                \App\Models\OrgUserPlan::STATUS_PENDING,
            ])
            ->where('isCanceled', false)
            ->where('isDeleted', false)
            ->first();
        
        Log::info('PurchasePlan: Checking existing membership', [
            'orgUser_id' => $orgUser->id,
            'plan_id' => $this->plan->id,
            'found_membership' => $existingMembership ? true : false,
            'membership_status' => $existingMembership->status ?? null,
        ]);
            
        if ($existingMembership) {
            $status = $existingMembership->status;
            
            if ($status == \App\Models\OrgUserPlan::STATUS_ACTIVE) {
                $this->error = 'You already have an active membership for this plan.';
                Log::warning('PurchasePlan: Active membership exists', [
                    'membership_id' => $existingMembership->id,
                    'plan_id' => $this->plan->id,
                ]);
            } elseif ($status == \App\Models\OrgUserPlan::STATUS_UPCOMING) {
                $this->error = 'You already have an upcoming membership for this plan. Please wait for it to start or contact support.';
                Log::warning('PurchasePlan: Upcoming membership exists', [
                    'membership_id' => $existingMembership->id,
                    'plan_id' => $this->plan->id,
                ]);
            } else {
                // PENDING status (6) or other status
                $this->error = 'You already have a pending membership for this plan. Please complete the payment or wait for it to be processed.';
                Log::warning('PurchasePlan: Pending membership exists', [
                    'membership_id' => $existingMembership->id,
                    'plan_id' => $this->plan->id,
                    'status' => $status,
                ]);
            }
        }
    }
    
    /**
     * Create payment via API and get payment URL
     */
    protected function createPayment()
    {
        // Don't set loading state - page should show immediately
        try {
            $user = Auth::user();
            $orgUser = $user->orgUser;
            
            if (!$orgUser) {
                $this->error = 'User information not found.';
                $this->loading = false;
                return;
            }
            
            $orgId = $orgUser->org_id;
            $orgSettings = OrgSettingsPaymentGateway::getMyFatoorahForOrg($orgId);
            
            if (!$orgSettings) {
                $this->error = 'Payment gateway not configured for this organization.';
                $this->loading = false;
                return;
            }
            
            // Prepare membership data structure (same as FOH) for callback
            $membershipData = [
                'org_id' => $orgId,
                'orgUser_id' => $orgUser->id,
                'orgPlan_id' => $this->plan->id,
                'invoiceStatus' => \App\Models\OrgUserPlan::INVOICE_STATUS_PAID,
                'invoiceMethod' => 'online',
                'status' => \App\Models\OrgUserPlan::STATUS_ACTIVE,
                'created_by' => $orgUser->id,
                'sold_by' => $orgUser->id,
                'note' => 'Purchased online via customer portal',
                'startDateLoc' => now()->format('Y-m-d'), // Start from today
            ];
            
            // Prepare payment data for API
            // Use same data source as FOH: orgUser.fullName and orgUser phone fields
            $customerName = $orgUser->fullName ?? '';
            $customerEmail = $orgUser->email ?? $user->email ?? '';
            
            // Format phone number same as FOH: CONCAT('+(', phoneCountry, ') ', phoneNumber)
            $customerPhone = null;
            if ($orgUser->phoneCountry && $orgUser->phoneNumber) {
                $customerPhone = '+(' . $orgUser->phoneCountry . ') ' . $orgUser->phoneNumber;
            }
            
            $paymentData = [
                'org_id' => $orgId,
                'payment_method_id' => 2, // Default to Visa/Mastercard
                'invoice_value' => (float) $this->plan->price,
                'customer_name' => $customerName,
                'currency_iso' => $orgSettings->currency ?? 'KWD',
                'language' => 'en',
                'membership_data' => $membershipData, // Include membership data for callback
            ];
            
            // Add optional fields (same as FOH)
            if ($customerEmail) {
                $paymentData['customer_email'] = $customerEmail;
            }
            
            // Phone number is not included in payment link creation
            // MyFatoorah will collect phone number during checkout if needed
            // But we have it available if needed: $customerPhone
            
            // Add plan information for callback
            $paymentData['org_plan_id'] = $this->plan->id;
            $paymentData['org_user_id'] = $orgUser->id;
            $paymentData['plan_name'] = $this->plan->name;
            
            Log::info('PurchasePlan: Creating payment via API', [
                'org_id' => $orgId,
                'plan_id' => $this->plan->id,
                'invoice_value' => $paymentData['invoice_value'],
            ]);
            
            // Call API to create payment
            $paymentService = app(MyFatoorahPaymentApiService::class);
            $result = $paymentService->createPayment($paymentData);
            
            if ($result['success'] && isset($result['data']['payment_url'])) {
                $this->paymentUrl = $result['data']['payment_url'];
                $this->loading = false; // Stop loading when payment URL is ready
                
                Log::info('PurchasePlan: Payment URL received', [
                    'payment_url' => $this->paymentUrl,
                    'invoice_id' => $result['data']['invoice_id'] ?? null,
                ]);
                
                // Don't redirect - display page with package details and iframe
                // The view will render with $paymentUrl and $plan properties
            } else {
                $this->error = 'Failed to create payment. Please try again.';
                $this->loading = false;
                Log::error('PurchasePlan: Payment creation failed', [
                    'result' => $result,
                ]);
            }
            
        } catch (\Exception $e) {
            $this->error = 'An error occurred while processing your payment. Please try again.';
            $this->loading = false;
            Log::error('PurchasePlan: Error creating payment', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
        }
    }
    
    /**
     * Confirm and create free membership
     */
    public function confirmFreeMembership()
    {
        $this->loading = true;
        
        try {
            $user = Auth::user();
            $orgUser = $user->orgUser;
            
            if (!$orgUser) {
                $this->error = 'User information not found.';
                $this->loading = false;
                return;
            }
            
            $orgId = $orgUser->org_id;
            
            Log::info('PurchasePlan: Creating free membership', [
                'org_id' => $orgId,
                'plan_id' => $this->plan->id,
                'orgUser_id' => $orgUser->id,
            ]);
            
            // Use OrgUserPlanService to create the membership
            $planService = app(\App\Services\OrgUserPlanService::class);
            
            $membershipData = [
                'org_id' => $orgId,
                'orgUser_id' => $orgUser->id,
                'orgPlan_id' => $this->plan->id,
                'invoiceStatus' => \App\Models\OrgUserPlan::INVOICE_STATUS_FREE,
                'invoiceMethod' => 'free',
                'status' => \App\Models\OrgUserPlan::STATUS_ACTIVE,
                'created_by' => $orgUser->id,
                'sold_by' => $orgUser->id,
                'note' => 'Purchased online via customer portal (Free plan)',
            ];
            
            $orgUserPlan = $planService->create($membershipData);
            
            if ($orgUserPlan) {
                Log::info('PurchasePlan: Free membership created successfully', [
                    'membership_id' => $orgUserPlan->id,
                    'uuid' => $orgUserPlan->uuid,
                ]);
                
                // Redirect to success page with reference (format: plan_{plan_uuid}_{orgUser_uuid})
                $ref = 'plan_' . $this->plan->uuid . '_' . $orgUser->uuid;
                return redirect()->route('payment.success', ['ref' => $ref]);
            } else {
                $this->error = 'Failed to create membership. Please try again.';
            }
            
        } catch (\Exception $e) {
            $this->error = 'An error occurred while processing your membership. Please try again.';
            Log::error('PurchasePlan: Error creating free membership', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
        } finally {
            $this->loading = false;
        }
    }
    
    /**
     * Get supported countries list for country code dropdown
     */
    public function getSupportedCountries(): array
    {
        return [
            'US' => ['code' => '1', 'name' => 'United States', 'flag' => '🇺🇸'],
            'CA' => ['code' => '1', 'name' => 'Canada', 'flag' => '🇨🇦'],
            'GB' => ['code' => '44', 'name' => 'United Kingdom', 'flag' => '🇬🇧'],
            'AU' => ['code' => '61', 'name' => 'Australia', 'flag' => '🇦🇺'],
            'DE' => ['code' => '49', 'name' => 'Germany', 'flag' => '🇩🇪'],
            'FR' => ['code' => '33', 'name' => 'France', 'flag' => '🇫🇷'],
            'ES' => ['code' => '34', 'name' => 'Spain', 'flag' => '🇪🇸'],
            'IT' => ['code' => '39', 'name' => 'Italy', 'flag' => '🇮🇹'],
            'JP' => ['code' => '81', 'name' => 'Japan', 'flag' => '🇯🇵'],
            'KR' => ['code' => '82', 'name' => 'South Korea', 'flag' => '🇰🇷'],
            'AE' => ['code' => '971', 'name' => 'United Arab Emirates', 'flag' => '🇦🇪'],
            'SA' => ['code' => '966', 'name' => 'Saudi Arabia', 'flag' => '🇸🇦'],
            'QA' => ['code' => '974', 'name' => 'Qatar', 'flag' => '🇶🇦'],
            'JO' => ['code' => '962', 'name' => 'Jordan', 'flag' => '🇯🇴'],
            'KW' => ['code' => '965', 'name' => 'Kuwait', 'flag' => '🇰🇼'],
            'BH' => ['code' => '973', 'name' => 'Bahrain', 'flag' => '🇧🇭'],
            'OM' => ['code' => '968', 'name' => 'Oman', 'flag' => '🇴🇲'],
            'LB' => ['code' => '961', 'name' => 'Lebanon', 'flag' => '🇱🇧'],
            'IN' => ['code' => '91', 'name' => 'India', 'flag' => '🇮🇳'],
            'EG' => ['code' => '20', 'name' => 'Egypt', 'flag' => '🇪🇬'],
        ];
    }
    
    public function render()
    {
        return view('livewire.customer.purchase-plan', [
            'navigationPages' => $this->navigationPages ?? collect(),
        ])->layout('components.layouts.templates.fitness', [
            'navigationPages' => $this->navigationPages ?? collect(),
        ]);
    }
}
