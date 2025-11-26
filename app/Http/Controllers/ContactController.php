<?php

namespace App\Http\Controllers;

use App\Http\Requests\ContactFormRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Session;

class ContactController extends Controller
{
    /**
     * Handle contact form submission
     * Similar to Yii's SiteController::actionContact()
     */
    public function submit(ContactFormRequest $request)
    {
        try {
            // Get organization ID from session or request
            $orgId = $request->input('org_id') ?? session('org_id') ?? 8;
            
            // Get organization email
            $organization = \App\Models\Org::find($orgId);
            $orgEmail = $organization?->email ?? config('mail.from.address');
            
            // Clean and validate organization email
            if (!empty($orgEmail)) {
                $orgEmail = trim($orgEmail); // Remove whitespace
            }
            
            // Validate organization email
            if (empty($orgEmail) || !filter_var($orgEmail, FILTER_VALIDATE_EMAIL)) {
                // Use fallback email if org email is invalid
                $orgEmail = config('mail.from.address');
                if (empty($orgEmail) || !filter_var($orgEmail, FILTER_VALIDATE_EMAIL)) {
                    // Last resort: use a default email
                    $orgEmail = 'contact@' . parse_url(config('app.url', 'http://localhost'), PHP_URL_HOST);
                }
                Log::warning('Invalid organization email, using fallback', [
                    'org_id' => $orgId,
                    'original_email' => $organization?->email ?? 'null',
                    'fallback_email' => $orgEmail
                ]);
            }
            
            // Log the contact form submission
            Log::info('Contact form submitted', [
                'name' => $request->name,
                'email' => $request->email,
                'subject' => $request->subject,
                'org_id' => $orgId,
                'org_email' => $orgEmail,
                'timestamp' => now(),
                'ip' => $request->ip()
            ]);
            
            // Send email notification
            // Use Mail::raw() for simple text emails (more reliable than Mail::send())
            $emailBody = "Name: {$request->name}\n" .
                        "Email: {$request->email}\n" .
                        "Subject: {$request->subject}\n\n" .
                        "Message:\n{$request->body}";
            
            // Get mail from address (must be valid)
            $fromAddress = config('mail.from.address');
            if (empty($fromAddress) || !filter_var($fromAddress, FILTER_VALIDATE_EMAIL)) {
                $fromAddress = 'noreply@' . parse_url(config('app.url', 'http://localhost'), PHP_URL_HOST);
            }
            
            Mail::raw($emailBody, function ($message) use ($request, $orgEmail, $fromAddress) {
                $message->to($orgEmail)
                    ->subject('Web form submission: ' . $request->subject)
                    ->from($fromAddress, config('mail.from.name', 'Contact Form'))
                    ->replyTo($request->email, $request->name);
            });
            
            Session::flash('success', 'Thank you for contacting us. We will respond to you as soon as possible.');
            
        } catch (\Exception $e) {
            Log::error('Contact form submission failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'email' => $request->email ?? 'unknown',
                'org_id' => $orgId ?? null,
                'org_email' => $orgEmail ?? null
            ]);
            
            Session::flash('error', 'There was an error sending your message. Please try again.');
        }
        
        // Redirect back to the contact page
        return redirect()->back();
    }
}

