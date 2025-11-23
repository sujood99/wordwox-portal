<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Auth;

class SetUserLanguage
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next)
    {
        // Skip for public signature routes
        if ($request->is('public/signature/*')) {
            return $next($request);
        }

        try {
            if (Auth::check()) {
                $user = Auth::user();
                
                // Check if user has orgUser and org relationship exists
                if ($user->orgUser && $user->orgUser->org) {
                    $effectiveLanguage = $user->getEffectiveLanguage();
                    
                    // Validate that we got a string
                    if (!is_string($effectiveLanguage)) {
                        \Log::warning('getEffectiveLanguage returned non-string value', [
                            'value' => $effectiveLanguage,
                            'type' => gettype($effectiveLanguage),
                            'user_id' => $user->id,
                        ]);
                        $effectiveLanguage = 'en-US';
                    }
                    
                    // Convert to simple language code for Laravel locale (en-US -> en, ar-SA -> ar)
                    $localeCode = explode('-', $effectiveLanguage)[0];
                    
                    // Set the application locale
                    App::setLocale($localeCode);
                    
                    // Store in session for consistency
                    session(['locale' => $localeCode, 'effective_language' => $effectiveLanguage]);
                } else {
                    // User doesn't have orgUser or org, try to get org from env file
                    $defaultOrgId = env('CMS_DEFAULT_ORG_ID', env('DEFAULT_ORG_ID', null));
                    
                    if ($defaultOrgId) {
                        try {
                            $org = \App\Models\Org::find($defaultOrgId);
                            if ($org && $org->orgSettingsFeatures) {
                                $orgFeatures = $org->orgSettingsFeatures;
                                
                                if ($orgFeatures->isLanguageFeatureEnabled()) {
                                    $enabledLanguages = $orgFeatures->getEnabledLanguages();
                                    if (is_array($enabledLanguages) && !empty($enabledLanguages)) {
                                        $effectiveLanguage = $enabledLanguages[0];
                                        $localeCode = explode('-', $effectiveLanguage)[0];
                                        App::setLocale($localeCode);
                                        session(['locale' => $localeCode, 'effective_language' => $effectiveLanguage]);
                                    } else {
                                        App::setLocale(config('app.locale', 'en'));
                                    }
                                } else {
                                    App::setLocale(config('app.locale', 'en'));
                                }
                            } else {
                                App::setLocale(config('app.locale', 'en'));
                            }
                        } catch (\Exception $e) {
                            \Log::warning('Failed to load org from env, using default locale', [
                                'org_id' => $defaultOrgId,
                                'error' => $e->getMessage(),
                            ]);
                            App::setLocale(config('app.locale', 'en'));
                        }
                    } else {
                        // No default org in env, use default locale
                        App::setLocale(config('app.locale', 'en'));
                    }
                }
            } else {
                // Not authenticated, try to get org from env file
                $defaultOrgId = env('CMS_DEFAULT_ORG_ID', env('DEFAULT_ORG_ID', null));
                
                if ($defaultOrgId) {
                    try {
                        $org = \App\Models\Org::find($defaultOrgId);
                        if ($org && $org->orgSettingsFeatures) {
                            $orgFeatures = $org->orgSettingsFeatures;
                            
                            if ($orgFeatures->isLanguageFeatureEnabled()) {
                                $enabledLanguages = $orgFeatures->getEnabledLanguages();
                                if (is_array($enabledLanguages) && !empty($enabledLanguages)) {
                                    $effectiveLanguage = $enabledLanguages[0];
                                    $localeCode = explode('-', $effectiveLanguage)[0];
                                    App::setLocale($localeCode);
                                    session(['locale' => $localeCode, 'effective_language' => $effectiveLanguage]);
                                } else {
                                    App::setLocale(config('app.locale', 'en'));
                                }
                            } else {
                                App::setLocale(config('app.locale', 'en'));
                            }
                        } else {
                            App::setLocale(config('app.locale', 'en'));
                        }
                    } catch (\Exception $e) {
                        \Log::warning('Failed to load org from env, using default locale', [
                            'org_id' => $defaultOrgId,
                            'error' => $e->getMessage(),
                        ]);
                        App::setLocale(config('app.locale', 'en'));
                    }
                } else {
                    // No default org in env, use default locale
                    App::setLocale(config('app.locale', 'en'));
                }
            }
        } catch (\Exception $e) {
            // Fallback to default locale if something goes wrong
            App::setLocale(config('app.locale', 'en'));
            \Log::warning('Failed to set user language, using default', [
                'error' => $e->getMessage(),
                'user_id' => Auth::check() ? Auth::user()?->id : null,
                'trace' => $e->getTraceAsString(),
            ]);
        }

        return $next($request);
    }
}