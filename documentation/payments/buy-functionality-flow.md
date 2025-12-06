# Buy Functionality - Complete Flow Documentation

## Overview

The "Buy" functionality allows customers to purchase packages (plans) through the customer portal. The system supports both **paid plans** (via MyFatoorah payment gateway) and **free plans** (zero-price packages). The implementation follows the same data structure and business logic as the FOH (Front of House) system for consistency.

## Table of Contents

1. [User Flow](#user-flow)
2. [Technical Flow](#technical-flow)
3. [Components Involved](#components-involved)
4. [Database Records](#database-records)
5. [API Integration](#api-integration)
6. [Error Handling](#error-handling)
7. [Performance Optimizations](#performance-optimizations)

---

## User Flow

### 1. Clicking "Buy" Button

**Location**: Package listing pages (e.g., `/packages`, `/pricing`)

**Button HTML**:
```html
<a href="/org-plan/index?plan={{ $planUuid }}" class="btn btn-primary">
    Buy <i class="fas fa-arrow-right ms-1"></i>
</a>
```

**Behavior**:
- If user is **not authenticated**: Redirects to login page with `redirect` parameter
- If user is **authenticated**: Redirects directly to purchase page

### 2. Authentication Check

**Route**: `/org-plan/index?plan={planUuid}`

**Middleware**: `auth` (web guard)

**Process**:
1. User clicks "Buy" button
2. System checks if user is authenticated
3. If not authenticated:
   - Redirects to `/login?redirect=/org-plan/index?plan={planUuid}`
   - After successful login, user is redirected back to purchase page
4. If authenticated:
   - Proceeds to load purchase page

### 3. Purchase Page Loading

**Component**: `App\Livewire\Customer\PurchasePlan`

**Initial State**:
- Shows loading spinner: "Loading plan details..."
- Sets `$loading = true`

**Process**:
1. Load navigation pages (cached for 1 hour)
2. Extract plan UUID from query parameter
3. Validate plan UUID exists
4. Load plan details from database
5. Determine if plan is free or paid

### 4. Plan Type Detection

#### Free Plans (Price = 0)
- **Condition**: `price == 0 || price == 0.00 || price < 0.1`
- **Display**: Shows "Complete order" confirmation page
- **Features**:
  - Package details summary
  - Country code dropdown (if phone number needed)
  - Phone number field (disabled, shows existing number)
  - "Complete order" button
- **No Payment Gateway**: Skips MyFatoorah integration

#### Paid Plans (Price > 0)
- **Condition**: `price >= 0.1`
- **Display**: Shows package details + payment iframe
- **Features**:
  - Package details summary (strong colors, bold text)
  - Embedded MyFatoorah payment iframe
  - "Back to Packages" button
- **Payment Gateway**: Integrates with MyFatoorah via `wodworx-pay` API

### 5. Active Membership Check (Paid Plans Only)

**Purpose**: Prevents duplicate purchases of the same plan

**Check Logic**:
```php
// Check for active, upcoming, or pending membership for this specific plan
$existingMembership = OrgUserPlan::where('orgUser_id', $orgUser->id)
    ->where('orgPlan_id', $this->plan->id)
    ->whereIn('status', [
        OrgUserPlan::STATUS_ACTIVE,
        OrgUserPlan::STATUS_UPCOMING,
        OrgUserPlan::STATUS_PENDING
    ])
    ->where('isDeleted', false)
    ->first();
```

**Result**:
- If active membership exists: Shows error "You already have an active membership for this plan."
- If no active membership: Proceeds to payment creation

### 6. Payment Creation (Paid Plans)

**Service**: `App\Services\MyFatoorahPaymentApiService`

**Process**:
1. Get user's `orgUser` record
2. Get organization's payment gateway settings
3. Prepare payment data:
   ```php
   $paymentData = [
       'org_id' => $orgId,
       'payment_method_id' => 2, // Visa/Mastercard
       'invoice_value' => (float) $plan->price,
       'customer_name' => $orgUser->fullName, // From orgUser table (same as FOH)
       'currency_iso' => $orgSettings->currency ?? 'KWD',
       'language' => 'en',
       'customer_email' => $orgUser->email ?? $user->email,
       'membership_data' => [...], // For callback
   ];
   ```
4. Call `wodworx-pay` API: `POST /api/myfatoorah/create-payment`
5. Receive payment URL from API
6. Display payment iframe on same page

**User Data Source** (Same as FOH):
- **Name**: `orgUser->fullName` (primary source)
- **Email**: `orgUser->email` (fallback to `user->email`)
- **Phone**: `orgUser->phoneCountry` + `orgUser->phoneNumber` (formatted as `+(country) number`)

### 7. Payment Processing

**Payment Gateway**: MyFatoorah (via `wodworx-pay` service)

**Flow**:
1. User completes payment in embedded iframe
2. MyFatoorah processes payment
3. MyFatoorah sends callback to `wodworx-pay` service
4. `wodworx-pay` creates database records:
   - `orgInvoice` record
   - `orgInvoicePayment` record
   - `orgUserPlan` record (membership)
5. `wodworx-pay` redirects user to success page

### 8. Free Plan Confirmation

**Action**: User clicks "Complete order" button

**Method**: `confirmFreeMembership()`

**Process**:
1. Get user's `orgUser` record
2. Use `OrgUserPlanService` to create membership:
   ```php
   $membershipData = [
       'org_id' => $orgId,
       'orgUser_id' => $orgUser->id,
       'orgPlan_id' => $plan->id,
       'invoiceStatus' => OrgUserPlan::INVOICE_STATUS_FREE,
       'invoiceMethod' => 'free',
       'status' => OrgUserPlan::STATUS_ACTIVE,
       'created_by' => $orgUser->id,
       'sold_by' => $orgUser->id,
       'note' => 'Purchased online via customer portal (Free plan)',
       'startDateLoc' => now()->format('Y-m-d'),
   ];
   ```
3. Create membership using `OrgUserPlanService::create()`
4. Redirect to success page: `/pay/org-plan-success?ref=plan_{plan_uuid}_{orgUser_uuid}`

### 9. Success Page

**Route**: `/pay/org-plan-success?ref={reference}`

**Component**: `App\Livewire\Customer\PaymentSuccess`

**Reference Format**: `plan_{plan_uuid}_{orgUser_uuid}`

**Display**:
- Green success banner: "Success! Thank you for your Payment."
- Plan name (highlighted)
- Organization name
- Black text on green background (centered card design)

---

## Technical Flow

### Component: `PurchasePlan.php`

#### Mount Method Flow

```php
public function mount($plan = null)
{
    // 1. Show loading state
    $this->loading = true;
    
    // 2. Load navigation pages (cached)
    $this->loadNavigationPages();
    
    // 3. Get plan UUID from query
    $this->planUuid = $plan ?? request()->query('plan');
    
    // 4. Validate plan UUID
    if (!$this->planUuid) {
        $this->error = 'No plan specified.';
        return;
    }
    
    // 5. Check authentication
    if (!Auth::check()) {
        return redirect()->route('login', ['redirect' => request()->fullUrl()]);
    }
    
    // 6. Load plan details
    $this->loadPlan();
    
    // 7. Check if plan is free
    if ($this->plan->price < 0.1) {
        $this->showFreeConfirmation = true;
        $this->loading = false;
        return;
    }
    
    // 8. Check existing membership (paid plans only)
    $this->checkExistingMembership();
    
    // 9. Check MyFatoorah availability
    if (!$this->isMyFatoorahAvailable()) {
        $this->error = 'Online payment is not available.';
        return;
    }
    
    // 10. Create payment (paid plans)
    $this->createPayment();
}
```

#### Payment Creation Method

```php
protected function createPayment()
{
    try {
        $user = Auth::user();
        $orgUser = $user->orgUser;
        
        // Get payment gateway settings
        $orgSettings = OrgSettingsPaymentGateway::getMyFatoorahForOrg($orgId);
        
        // Prepare membership data for callback
        $membershipData = [
            'org_id' => $orgId,
            'orgUser_id' => $orgUser->id,
            'orgPlan_id' => $this->plan->id,
            'invoiceStatus' => OrgUserPlan::INVOICE_STATUS_PAID,
            'invoiceMethod' => 'online',
            'status' => OrgUserPlan::STATUS_ACTIVE,
            'created_by' => $orgUser->id,
            'sold_by' => $orgUser->id,
            'note' => 'Purchased online via customer portal',
            'startDateLoc' => now()->format('Y-m-d'),
        ];
        
        // Prepare payment data
        $paymentData = [
            'org_id' => $orgId,
            'payment_method_id' => 2,
            'invoice_value' => (float) $this->plan->price,
            'customer_name' => $orgUser->fullName, // Same as FOH
            'currency_iso' => $orgSettings->currency ?? 'KWD',
            'language' => 'en',
            'membership_data' => $membershipData,
        ];
        
        // Call API
        $paymentService = app(MyFatoorahPaymentApiService::class);
        $result = $paymentService->createPayment($paymentData);
        
        if ($result['success']) {
            $this->paymentUrl = $result['data']['payment_url'];
            $this->loading = false;
        }
    } catch (\Exception $e) {
        $this->error = 'An error occurred.';
        $this->loading = false;
    }
}
```

---

## Components Involved

### 1. Frontend Components

#### `resources/views/partials/cms/sections/packages.blade.php`
- **Purpose**: Displays package listing with "Buy" buttons
- **Buy Button**: Links to `/org-plan/index?plan={uuid}`

#### `resources/views/livewire/customer/purchase-plan.blade.php`
- **Purpose**: Purchase page view
- **Features**:
  - Loading spinner
  - Package details summary
  - Payment iframe (paid plans)
  - Free plan confirmation form
  - Error messages

#### `resources/views/livewire/customer/payment-success.blade.php`
- **Purpose**: Success page after purchase
- **Features**:
  - Success message
  - Plan details
  - Organization name

### 2. Backend Components

#### `App\Livewire\Customer\PurchasePlan`
- **Purpose**: Main purchase flow logic
- **Key Methods**:
  - `mount()`: Initial page load
  - `loadPlan()`: Load plan from database
  - `checkExistingMembership()`: Prevent duplicates
  - `createPayment()`: Create payment via API
  - `confirmFreeMembership()`: Create free membership

#### `App\Services\MyFatoorahPaymentApiService`
- **Purpose**: Communication with `wodworx-pay` API
- **Key Methods**:
  - `createPayment()`: Create payment and get URL
  - `verifyPayment()`: Verify payment status
  - `getPaymentMethods()`: Get available payment methods

#### `App\Services\OrgUserPlanService`
- **Purpose**: Create memberships (same as FOH)
- **Key Method**:
  - `create()`: Create `orgUserPlan` record with proper date calculations

### 3. Models

#### `App\Models\OrgPlan`
- **Purpose**: Package/plan data
- **Key Fields**: `uuid`, `name`, `price`, `duration`, `type`

#### `App\Models\OrgUser`
- **Purpose**: Customer data (same as FOH)
- **Key Fields**: `fullName`, `email`, `phoneCountry`, `phoneNumber`

#### `App\Models\OrgUserPlan`
- **Purpose**: Membership records
- **Key Fields**: `status`, `invoiceStatus`, `startDate`, `endDate`

#### `App\Models\OrgSettingsPaymentGateway`
- **Purpose**: Payment gateway configuration
- **Key Fields**: `api_key`, `token`, `currency`

---

## Database Records

### Payment Flow (Paid Plans)

**Important**: Database records are created **AFTER** successful payment in the callback, not during payment URL generation.

#### 1. Payment URL Generation (No DB Records)
- User clicks "Buy"
- System calls `wodworx-pay` API
- API returns payment URL
- **No database records created yet**

#### 2. After Successful Payment (Callback)
- MyFatoorah processes payment
- `wodworx-pay` receives callback
- `wodworx-pay` creates:
  - `orgInvoice` record
  - `orgInvoicePayment` record
  - `orgUserPlan` record (membership)

### Free Plan Flow

**Database records created immediately** when user clicks "Complete order":

#### `orgUserPlan` Record
```php
[
    'org_id' => $orgId,
    'orgUser_id' => $orgUser->id,
    'orgPlan_id' => $plan->id,
    'invoiceStatus' => OrgUserPlan::INVOICE_STATUS_FREE,
    'invoiceMethod' => 'free',
    'status' => OrgUserPlan::STATUS_ACTIVE,
    'created_by' => $orgUser->id,
    'sold_by' => $orgUser->id,
    'note' => 'Purchased online via customer portal (Free plan)',
    'startDateLoc' => now()->format('Y-m-d'),
]
```

**Created via**: `OrgUserPlanService::create()` (same as FOH)

---

## API Integration

### Endpoint: `POST /api/myfatoorah/create-payment`

**Base URL**: Configured in `config/services.php`:
```php
'myfatoorah' => [
    'base_url' => env('MYFATOORAH_PAYMENT_SERVICE_URL', 'https://781c91b1c7e5.ngrok-free.app'),
    'api_key' => env('MYFATOORAH_API_KEY'),
    'token' => env('MYFATOORAH_API_TOKEN'),
],
```

### Request Headers
```php
[
    'Authorization' => 'Bearer ' . $apiKey,
    'X-API-Token' => $apiToken,
    'Content-Type' => 'application/json',
    'Accept' => 'application/json',
]
```

### Request Body
```json
{
    "org_id": 8,
    "payment_method_id": 2,
    "invoice_value": 250.00,
    "customer_name": "Sujood Malkawi",
    "customer_email": "sujood@example.com",
    "currency_iso": "KWD",
    "language": "en",
    "membership_data": {
        "org_id": 8,
        "orgUser_id": 125286,
        "orgPlan_id": 4870,
        "invoiceStatus": 1,
        "invoiceMethod": "online",
        "status": 2,
        "created_by": 125286,
        "sold_by": 125286,
        "note": "Purchased online via customer portal",
        "startDateLoc": "2025-12-06"
    }
}
```

### Response
```json
{
    "success": true,
    "message": "Payment URL generated successfully",
    "data": {
        "payment_url": "https://demo.MyFatoorah.com/En/KWT/PayInvoice/Checkout?invoiceKey=...",
        "invoice_id": 6345211,
        "customer_reference": "WX_693466bee1b7e"
    }
}
```

---

## Error Handling

### Common Errors

#### 1. "No plan specified"
- **Cause**: Missing `plan` query parameter
- **Solution**: Ensure Buy button includes plan UUID

#### 2. "Plan not found"
- **Cause**: Invalid plan UUID or plan doesn't exist
- **Solution**: Verify plan exists in database

#### 3. "You already have an active membership for this plan"
- **Cause**: User already has active/upcoming/pending membership for same plan
- **Solution**: User must wait for current membership to expire or cancel it

#### 4. "Online payment is not available at this time"
- **Cause**: MyFatoorah not configured for organization
- **Solution**: Configure payment gateway in `orgSettingsPaymentGateway` table

#### 5. "Failed to create payment"
- **Cause**: API error or invalid payment data
- **Solution**: Check logs, verify API credentials, check payment gateway settings

### Error Display

Errors are displayed in a red alert box on the purchase page:
```html
<div class="alert alert-danger">
    <h4 class="alert-heading">Error</h4>
    <p>{{ $error }}</p>
    <a href="{{ route('home') }}" class="btn btn-primary">Return to Home</a>
</div>
```

---

## Performance Optimizations

### 1. Navigation Pages Caching
```php
$this->navigationPages = Cache::remember(
    "navigation_pages_{$orgId}",
    3600, // 1 hour
    function () use ($orgId) {
        return CmsPage::where('org_id', $orgId)
            ->where('status', 'published')
            ->where('show_in_navigation', true)
            ->orderBy('sort_order')
            ->get();
    }
);
```

### 2. Deferred Payment Creation
- Payment API call happens **after** page render
- Uses `wire:init` to defer API call
- Page displays immediately with loading state
- Payment URL updates when ready

### 3. Loading State Management
- Initial loading spinner shows immediately
- Loading stops when:
  - Payment URL received (paid plans)
  - Free confirmation shown (free plans)
  - Error occurs

---

## Data Consistency with FOH

### User Data Source
- **Name**: `orgUser->fullName` (same as FOH)
- **Email**: `orgUser->email` (same as FOH)
- **Phone**: `orgUser->phoneCountry` + `orgUser->phoneNumber` (same as FOH)

### Membership Creation
- Uses `OrgUserPlanService::create()` (same as FOH)
- Same date format: `Y-m-d` for `startDateLoc`/`endDateLoc`
- Same status values: `STATUS_ACTIVE`, `INVOICE_STATUS_PAID`, etc.

### Business Logic
- Same validation rules
- Same membership status checks
- Same invoice status handling

---

## Testing Checklist

### Paid Plans
- [ ] User can click "Buy" button
- [ ] Redirects to login if not authenticated
- [ ] Shows loading spinner
- [ ] Displays package details
- [ ] Shows payment iframe
- [ ] Payment processes successfully
- [ ] Redirects to success page after payment
- [ ] Membership created in database

### Free Plans
- [ ] User can click "Buy" button
- [ ] Shows "Complete order" page
- [ ] Displays package details
- [ ] Shows country code dropdown
- [ ] Shows phone number (disabled)
- [ ] "Complete order" button works
- [ ] Membership created immediately
- [ ] Redirects to success page

### Error Cases
- [ ] Missing plan UUID shows error
- [ ] Invalid plan UUID shows error
- [ ] Active membership shows error
- [ ] Missing payment gateway shows error
- [ ] API errors are handled gracefully

---

## Related Documentation

- [Customer Login Flow](../customers/customer-login-yii-reference.md)
- [FOH Membership Creation](../memberships/FOH-membership-creation-analysis.md)
- [Active Members Query](../customers/customer-list/active-members-analysis.md)

---

## Summary

The "Buy" functionality provides a seamless purchase experience for customers:

1. **Authentication**: Redirects to login if needed
2. **Plan Detection**: Handles free and paid plans differently
3. **Payment Integration**: Uses MyFatoorah via `wodworx-pay` API
4. **Data Consistency**: Uses same data source and business logic as FOH
5. **Performance**: Optimized with caching and deferred loading
6. **Error Handling**: Comprehensive error messages and fallbacks

The implementation ensures consistency with the FOH system while providing a modern, user-friendly purchase experience.

