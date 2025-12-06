# Customer Signup - Yii vs Laravel Implementation

## Overview

This document explains how customer signup works in both the Yii2 project (https://superhero.wodworx.com/site/signup) and the Laravel portal project, and clarifies the relationship between the two systems.

## ⚠️ Important: What Gets Created During Signup

### ✅ Created During Signup
- **`OrgUser` record**: Customer information is stored in the `orgUser` table
  - `fullName`, `email` (or `phoneCountry`/`phoneNumber`), `isCustomer=true`
  - All customer profile data

### ❌ NOT Created During Signup
- **`User` record**: NO authentication account is created during signup
  - The `user` table remains empty for new customers
  - The `orgUser.user_id` field remains `NULL`

### ✅ Created During First Login
- **`User` record**: Authentication account is created automatically when user logs in for the first time
  - This happens in the login flow, not the signup flow
  - The system checks if a `User` exists, and creates one if it doesn't

**Summary**: Signup creates `OrgUser` only. `User` is created during first login.

## Yii2 Project Signup (https://superhero.wodworx.com/site/signup)

### Form Structure

The Yii2 signup page contains the following fields:

1. **Full Name** (Required)
   - Text input field
   - Required for all registrations

2. **Login Method** (Required)
   - Radio button selection: **Email** or **Phone**
   - Determines which fields are required and how the user will authenticate

3. **Email** (Conditionally Required)
   - Required when "Email" login method is selected
   - Optional when "Phone" login method is selected
   - Must be unique within the organization

4. **Country Code** (Conditionally Required)
   - Dropdown with country list (e.g., Afghanistan (+93), United States (+1), etc.)
   - Required when "Phone" login method is selected
   - Optional when "Email" login method is selected
   - Stores ISO country code (e.g., "US", "GB", "AE")

5. **Phone Number** (Conditionally Required)
   - Text input field
   - Required when "Phone" login method is selected
   - Optional when "Email" login method is selected
   - Must be unique within the organization (combined with country code)

6. **Signup Button**
   - Submits the form to create the customer account

### Form Behavior

- **Dynamic Field Requirements**: Based on the selected login method (Email vs Phone), different fields become required
- **Country Code Dropdown**: Extensive list of countries with dialing codes (e.g., "+93" for Afghanistan, "+1" for United States)
- **Validation**: Performed on the server side in Yii2

### Data Storage

The Yii2 system creates records in the `orgUser` table with:
- `fullName`: Customer's full name
- `email`: Email address (if email login method)
- `phoneCountry`: **Numeric dialing code** (e.g., "1", "44", "971") - converted from ISO code
- `phoneNumber`: Phone number without country prefix
- `isCustomer`: Set to `true`
- `addMemberInviteOption`: Set based on login method (1=Email, 2=SMS)

## Laravel Portal Signup Implementation

### Current Implementation

**File**: `app/Livewire/Customer/CustomerSignup.php`  
**Route**: `/customer/signup`  
**View**: `resources/views/livewire/customer/customer-signup.blade.php`

### Form Structure

The Laravel signup form matches the Yii2 structure:

1. **Full Name** (Required)
2. **Login Method** (Email/Phone radio buttons)
3. **Email** (Conditionally required)
4. **Phone Country & Number** (Conditionally required)
5. **Additional Optional Fields**:
   - Date of Birth
   - Gender (Male/Female)
   - Address

### Implementation Details

```73:156:app/Livewire/Customer/CustomerSignup.php
    public function register()
    {
        // Validate based on login method (same as member creation)
        $rules = [
            'fullName' => ['required', 'string', 'min:2', 'max:255', new UniqueOrgUserFullName($this->orgId)],
            'dob' => 'nullable|date|before:today',
            'gender' => 'nullable|in:1,2',
            'address' => 'nullable|string|max:500',
        ];
        
        if ($this->loginMethod === 'email') {
            // For email login: email required and unique, phone optional (no uniqueness check)
            $rules['email'] = ['required', 'email', 'max:255', new UniqueOrgUserEmail($this->orgId)];
            $rules['phoneCountry'] = 'nullable|string|min:1|max:4';
            $rules['phoneNumber'] = ['nullable', 'string', 'regex:/^[0-9\-\+\(\)\s]+$/', 'min:7', 'max:15'];
        } else {
            // For phone login: phone required and unique, email optional (no uniqueness check)
            $rules['phoneCountry'] = 'required|string|min:1|max:4';
            $rules['phoneNumber'] = ['required', 'string', 'regex:/^[0-9\-\+\(\)\s]+$/', 'min:7', 'max:15', new UniqueOrgUserPhone($this->orgId, $this->phoneCountry)];
            $rules['email'] = ['nullable', 'email', 'max:255'];
        }
        
        $this->validate($rules);
        
        try {
            // Convert ISO country code to dialing code (same as member creation)
            $phoneCountryCode = $this->convertIsoToDialingCode($this->phoneCountry);
            
            // Prepare user data (same structure as member creation)
            // Ensure phone data is only stored when phone signup, email data when email signup
            $userData = [
                'org_id' => $this->orgId,
                'fullName' => trim($this->fullName),
                'phoneCountry' => ($this->loginMethod === 'phone' && $phoneCountryCode) ? $phoneCountryCode : null, // Store dialing code only for phone signup
                'phoneNumber' => ($this->loginMethod === 'phone' && $this->phoneNumber) ? $this->phoneNumber : null, // Store phone only for phone signup
                'email' => ($this->loginMethod === 'email' && $this->email) ? $this->email : null, // Store email only for email signup
                'dob' => $this->dob ?: null,
                'gender' => $this->gender ? (int)$this->gender : null,
                'address' => $this->address ?: null,
                'isCustomer' => true,
                'addMemberInviteOption' => $this->loginMethod === 'phone' ? 2 : 1, // 1=Email, 2=SMS (matching Yii2 constants)
            ];
            
            // Log what will be stored
            Log::info('Creating OrgUser with signup data', [
                'login_method' => $this->loginMethod,
                'email' => $userData['email'],
                'phone_country' => $userData['phoneCountry'],
                'phone_number' => $userData['phoneNumber'],
                'add_member_invite_option' => $userData['addMemberInviteOption'],
            ]);
            
            // Create OrgUser directly (same as member creation)
            $orgUser = \App\Models\OrgUser::create($userData);
            
            Log::info('Customer registration successful', [
                'org_user_id' => $orgUser->id,
                'org_id' => $this->orgId,
                'full_name' => $orgUser->fullName,
                'login_method' => $this->loginMethod,
                'stored_email' => $orgUser->email,
                'stored_phone_country' => $orgUser->phoneCountry,
                'stored_phone_number' => $orgUser->phoneNumber,
            ]);
            
            $this->registrationSuccess = true;
            $this->message = 'Registration successful! Please check your ' . ($this->loginMethod === 'email' ? 'email' : 'phone') . ' for verification.';
            
            // Redirect to login after a short delay
            session()->flash('registration_success', true);
            return $this->redirect(route('login'), navigate: false);
            
        } catch (\Illuminate\Validation\ValidationException $e) {
            $this->message = 'Validation failed. Please check your input.';
            throw $e;
        } catch (\Exception $e) {
            Log::error('Customer registration failed', [
                'error' => $e->getMessage(),
                'org_id' => $this->orgId,
                'trace' => $e->getTraceAsString()
            ]);
            $this->message = 'Registration failed: ' . $e->getMessage();
        }
    }
```

### Key Features

1. **Direct Database Creation**: Creates `OrgUser` records directly in the database (no API call to Yii2)
2. **Same Validation Rules**: Uses the same validation rules as the FOH member creation system
3. **Country Code Conversion**: Converts ISO country codes (e.g., "US") to numeric dialing codes (e.g., "1") for storage
4. **Login Method Preference**: Stores `addMemberInviteOption` based on selected login method
5. **Conditional Data Storage**: Only stores email OR phone based on login method selection

## Architecture: Shared Database, Separate Applications

### Current Setup

Both systems operate independently but share the same database:

```
┌─────────────────┐                    ┌─────────────────┐
│   Yii2 Project  │                    │ Laravel Portal  │
│  (superhero.    │                    │  (portal)        │
│   wodworx.com)  │                    │                  │
└────────┬────────┘                    └────────┬─────────┘
         │                                      │
         │  Both create OrgUser records        │
         │  directly in shared database        │
         │                                      │
         └──────────────┬───────────────────────┘
                        │
                        ▼
              ┌─────────────────┐
              │  Shared Database  │
              │   (orgUser)      │
              └─────────────────┘
```

### No API Integration

**Important**: The Laravel portal does **NOT** call a Yii2 API endpoint for customer registration. Instead:

1. Both systems create records directly in the `orgUser` table
2. Both systems use the same database schema
3. Both systems follow the same data validation patterns
4. The Laravel implementation is designed to be **compatible** with Yii2 patterns, not dependent on Yii2 APIs

### Why No API?

1. **Shared Database**: Both applications connect to the same database
2. **Data Consistency**: Direct database access ensures immediate consistency
3. **Performance**: No HTTP overhead for database operations
4. **Simplicity**: No need for API authentication, rate limiting, or error handling between systems

## Data Flow Comparison

### Yii2 Signup Flow

```
User fills form
    ↓
Yii2 validates input
    ↓
Yii2 creates OrgUser record ONLY
    ↓
OrgUser saved to database
    ↓
❌ User table: NO record created
    ↓
User redirected to login
```

**Important**: Yii2 signup **ONLY** creates an `OrgUser` record. It does **NOT** create a `User` record. The `User` record is created later during the first login attempt.

### Laravel Signup Flow

```
User fills form
    ↓
Livewire validates input
    ↓
Laravel creates OrgUser record ONLY
    ↓
OrgUser saved to database
    ↓
❌ User table: NO record created
    ↓
User redirected to login
```

Both flows are identical in structure - the only difference is the framework handling the request. **Both systems only create `OrgUser` records during signup, not `User` records.**

## Key Differences

### 1. Additional Fields in Laravel

The Laravel signup form includes optional fields not present in the Yii2 form:
- Date of Birth
- Gender
- Address

These are stored in the `OrgUser` record but are not required.

### 2. Country List

Both systems use country dropdowns, but:
- **Yii2**: Uses a comprehensive list with all countries
- **Laravel**: Uses a curated list of 14 common countries (US, CA, GB, AU, DE, FR, ES, IT, JP, KR, AE, SA, QA, JO)

### 3. Validation Rules

Both systems use organization-level uniqueness validation:
- `UniqueOrgUserFullName`: Ensures full name is unique per organization
- `UniqueOrgUserEmail`: Ensures email is unique per organization
- `UniqueOrgUserPhone`: Ensures phone + country is unique per organization

## Post-Registration Flow

### What Happens During Signup (Yii2 and Laravel)

**Both systems follow the same pattern:**

1. **OrgUser Record Created**: ✅ Customer record exists in database
   - ✅ `orgUser` table: Record created with customer information
   - ✅ Fields populated: `fullName`, `email` (or `phoneCountry`/`phoneNumber`), `isCustomer=true`
   - ❌ `user` table: **NO record created during signup**

2. **User Account NOT Created During Signup**: 
   - Signup **ONLY** creates an `OrgUser` record
   - No `User` account is created at this stage
   - The `user` table remains empty for this customer
   - The `orgUser.user_id` field remains `NULL`

3. **User Redirected to Login**: After successful signup, user is redirected to the login page

### What Happens During First Login

4. **User Account Auto-Created During First Login**: 
   - When user logs in for the first time, a `User` account is automatically created
   - This happens in the login flow, not the signup flow
   - The system checks: `User::where('orgUser_id', $orgUser->id)->first()`
   - If no `User` exists, it creates one automatically
   - See `customer-login-yii-reference.md` for details

### Verification: Yii2 Signup Behavior

Based on the Yii2 login implementation pattern (documented in `customer-login-yii-reference.md`), the Yii2 signup process:

1. ✅ Creates `OrgUser` record
2. ❌ Does **NOT** create `User` record
3. ✅ User must login to get OTP
4. ✅ `User` record is created during login (if it doesn't exist)

This is confirmed by the login code pattern:
```php
// Yii Pattern (from customer-login-yii-reference.md)
$user = User::find()
    ->where(['orgUser_id' => $orgUser->id])
    ->one();

if (!$user) {
    // User account is created HERE during login, not during signup
    $user = new User();
    // ... create user account
}
```

### Database State After Signup

**After Signup:**
```
orgUser table:
  ✅ id: 123
  ✅ fullName: "John Doe"
  ✅ email: "john@example.com"
  ✅ phoneCountry: "1"
  ✅ phoneNumber: "5551234567"
  ✅ isCustomer: true
  ❌ user_id: NULL (no User account yet)

user table:
  ❌ No record exists for this customer
```

**After First Login:**
```
orgUser table:
  ✅ id: 123
  ✅ user_id: 456 (now linked to User account)

user table:
  ✅ id: 456
  ✅ orgUser_id: 123
  ✅ fullName: "John Doe"
  ✅ email: "john@example.com"
  ✅ phoneNumber: "5551234567"
  ✅ phoneCountry: "1"
  ✅ status: 10 (active)
  ✅ otp: 1234 (for verification)
```

## Integration Points

### Shared Models

Both systems use the same database tables:
- `orgUser`: Customer records
- `user`: Authentication accounts (created on first login)
- `org`: Organization information

### Shared Validation

Both systems use the same validation logic:
- Organization-level uniqueness
- Phone number format validation
- Email format validation
- Country code to dialing code conversion

### Shared Constants

Both systems use the same constants for `addMemberInviteOption`:
- `1` = Email
- `2` = SMS
- `3` = All
- `4` = Create Password

## Recommendations

### If You Need API Integration

If you want the Laravel portal to call a Yii2 API instead of direct database access:

1. **Create Yii2 API Endpoint**: `/api/customer/register` or similar
2. **Implement HTTP Client**: Use Laravel's `Http` facade or Guzzle
3. **Handle API Errors**: Implement proper error handling and retry logic
4. **Add Authentication**: Secure the API endpoint with tokens or API keys
5. **Update CustomerSignup Component**: Modify `register()` method to call API instead of direct database creation

### Current Approach (Recommended)

The current direct database approach is recommended because:
- ✅ Simpler architecture
- ✅ Better performance (no HTTP overhead)
- ✅ Immediate data consistency
- ✅ No API authentication complexity
- ✅ Works even if Yii2 API is down

## Conclusion

The Yii2 project and Laravel portal are **separate applications** that share the same database. The Laravel signup implementation follows the same patterns as the Yii2 implementation but operates independently. There is **no API integration** between the two systems for customer registration - both create records directly in the shared database.

This architecture provides:
- **Data Consistency**: Both systems see the same customer data immediately
- **Independence**: Each system can operate without the other
- **Simplicity**: No API layer to maintain
- **Performance**: Direct database access is faster than HTTP API calls

