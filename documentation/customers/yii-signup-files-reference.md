# Yii2 Customer Portal - Signup Files Reference

## Overview

This document lists all signup-related files in the Yii2 customer portal project located at `/Users/macbook1993/wodworx-customer-portal-yii`.

## Main Signup Files

### 1. Signup Form Model
**File**: `frontend/models/SignupForm.php`

**Purpose**: Main form model for customer signup

**Key Features**:
- Handles form validation for signup
- Supports email or phone-based login (`loginOption`)
- Validates email/phone uniqueness within organization
- Creates `OrgUser` record (NOT `User` record)
- Phone number normalization (removes leading zeros, special characters)

**Key Methods**:
- `register()`: Creates and saves `OrgUser` record
- `getOrgUser()`: Creates new `OrgUser` instance with signup data
- `validateEmail()`: Validates email uniqueness within org
- `validatePhone()`: Validates phone uniqueness within org

**Important**: This form **ONLY** creates `OrgUser` records. It does **NOT** create `User` records.

### 2. Signup View
**File**: `frontend/views/site/signup.php`

**Purpose**: HTML form view for customer signup

**Form Fields**:
- Full Name (required)
- Login Option (Email/Phone radio buttons)
- Email (conditionally required)
- Country Code dropdown (Select2 widget)
- Phone Number (conditionally required)

**UI Components**:
- Uses Yii2 Bootstrap4 ActiveForm
- Uses Kartik Select2 for country dropdown
- Country list from `SysCountry` model

### 3. Site Controller - Signup Action
**File**: `frontend/controllers/SiteController.php`

**Method**: `actionSignup()` (lines 314-353)

**Flow**:
1. Creates `SignupForm` instance with organization ID
2. Loads POST data and validates
3. Calls `$model->register()` to create `OrgUser`
4. Queues OTP job based on login option:
   - Email: `OrgUserOTPSendEmailJob`
   - Phone: `OrgUserOTPSendSMSJob`
5. Redirects to OTP verification page

**Important**: No `User` record is created during signup. Only `OrgUser` is created.

### 4. OrgUser Register Form (Alternative)
**File**: `common/models/forms/OrgUserRegisterForm.php`

**Purpose**: Alternative registration form (may be used by API or other entry points)

**Key Differences from SignupForm**:
- Requires both email AND phone (not conditional)
- Sets `addMemberInviteOption = OrgSettings::ADD_MEMBER_INVITE_OPTION_NONE`
- Also creates `OrgUser` only, not `User`

## Supporting Files

### 5. OrgUser Model
**File**: `common/models/OrgUser.php`

**Relevant Methods**:
- `beforeSave()`: Automatically sets `isCustomer = true` on insert (line 69)
- Handles phone number normalization
- Sets `origin = OrgUser::SOURCE_APP_WL_REGISTER` during signup

### 6. Test Files
- `frontend/tests/unit/models/SignupFormTest.php`: Unit tests for SignupForm
- `frontend/tests/functional/SignupCest.php`: Functional tests for signup flow

## Signup Flow in Yii2

```
User visits /site/signup
    ↓
SiteController::actionSignup()
    ↓
SignupForm loads and validates
    ↓
SignupForm::register()
    ↓
OrgUser::save() (creates OrgUser record)
    ↓
✅ OrgUser created in database
    ↓
❌ User record NOT created
    ↓
OTP job queued (Email or SMS)
    ↓
Redirect to OTP verification page
```

## Key Implementation Details

### 1. Only OrgUser is Created

**Confirmed**: The signup process **ONLY** creates an `OrgUser` record. No `User` record is created.

**Evidence**:
- `SignupForm::register()` only calls `$this->orgUser->save()`
- No `User::create()` or `new User()` calls in signup flow
- `User` record is created later during first login (see `customer-login-yii-reference.md`)

### 2. OrgUser Fields Set During Signup

```php
// From SignupForm::getOrgUser()
$this->_orgUser->org_id = $this->org_id;
$this->_orgUser->fullName = $this->fullName;
$this->_orgUser->phoneCountry = $this->phoneCountry;
$this->_orgUser->phoneNumber = $this->phoneNumber;
$this->_orgUser->email = $this->email;
$this->_orgUser->isDeleted = false;
$this->_orgUser->origin = OrgUser::SOURCE_APP_WL_REGISTER;
$this->_orgUser->dob = $this->dob;
$this->_orgUser->gender = (1 or 2 based on input);
// isCustomer is set to true automatically in OrgUser::beforeSave()
```

### 3. Phone Number Normalization

```php
// From SignupForm::beforeValidate()
$this->phoneNumber = ltrim($this->phoneNumber, '0'); // Remove leading zeros
$this->phoneNumber = preg_replace('/[^0-9]/', '', $this->phoneNumber); // Remove special chars
```

### 4. Validation Rules

**Email Login**:
- `fullName`: Required
- `loginOption`: Required, must be 'email'
- `email`: Required, must be valid email, must be unique within org
- `phoneCountry`, `phoneNumber`: Optional

**Phone Login**:
- `fullName`: Required
- `loginOption`: Required, must be 'phone'
- `phoneCountry`: Required
- `phoneNumber`: Required, must be unique within org
- `email`: Optional

### 5. OTP Job Dispatch

After successful signup, an OTP job is queued:

**Email Login**:
```php
Yii::$app->queue->priority(10)->push(new OrgUserOTPSendEmailJob([
    'id' => $model->orgUser->id
]));
```

**Phone Login**:
```php
Yii::$app->queue->priority(10)->push(new OrgUserOTPSendSMSJob([
    'id' => $model->orgUser->id
]));
```

## Comparison with Laravel Implementation

| Feature | Yii2 | Laravel |
|---------|------|---------|
| Creates OrgUser | ✅ Yes | ✅ Yes |
| Creates User | ❌ No | ❌ No |
| Login Method Selection | ✅ Email/Phone | ✅ Email/Phone |
| Phone Normalization | ✅ Yes | ✅ Yes |
| Country Code Conversion | ✅ Yes (stored as dialing code) | ✅ Yes (stored as dialing code) |
| OTP After Signup | ✅ Queued job | ❌ Not implemented (redirects to login) |
| isCustomer Auto-Set | ✅ Yes (beforeSave) | ✅ Yes (explicit) |

## File Locations Summary

```
wodworx-customer-portal-yii/
├── frontend/
│   ├── models/
│   │   └── SignupForm.php                    # Main signup form model
│   ├── views/
│   │   └── site/
│   │       └── signup.php                    # Signup form view
│   ├── controllers/
│   │   └── SiteController.php                # Signup action (actionSignup)
│   └── tests/
│       ├── unit/models/
│       │   └── SignupFormTest.php            # Unit tests
│       └── functional/
│           └── SignupCest.php                 # Functional tests
└── common/
    ├── models/
    │   ├── OrgUser.php                        # OrgUser model (sets isCustomer)
    │   └── forms/
    │       └── OrgUserRegisterForm.php        # Alternative registration form
    └── jobs/
        └── user/
            ├── OrgUserOTPSendEmailJob.php     # Email OTP job
            └── OrgUserOTPSendSMSJob.php       # SMS OTP job
```

## Key Takeaways

1. **Signup creates OrgUser only**: No `User` record is created during signup
2. **User created during login**: `User` record is created automatically during first login
3. **OTP sent after signup**: Yii2 queues OTP job immediately after signup
4. **Phone normalization**: Leading zeros and special characters are removed
5. **isCustomer auto-set**: Automatically set to `true` in `OrgUser::beforeSave()`
6. **Conditional validation**: Email or phone required based on `loginOption`

## Related Documentation

- `customer-signup-yii-reference.md`: General signup comparison between Yii2 and Laravel
- `customer-login-yii-reference.md`: How User account is created during login
- `customer-creation-implementation.md`: FOH member creation (similar pattern)

