<?php

namespace App\Services;

use App\Models\CmsPage;
use Illuminate\Support\Facades\Cache;

class CmsFooterService
{
    /**
     * Get footer data for the current organization
     * Falls back to default values if not set in CMS
     */
    public static function getFooterData($orgId = null, $portalId = null)
    {
        $orgId = $orgId ?? auth()->user()?->orgUser?->org_id ?? env('CMS_DEFAULT_ORG_ID', env('DEFAULT_ORG_ID', 8));
        $portalId = $portalId ?? env('CMS_DEFAULT_PORTAL_ID', 1);
        
        $cacheKey = "cms_footer_data_{$orgId}_{$portalId}";
        
        return Cache::remember($cacheKey, 3600, function () use ($orgId, $portalId) {
            // Try to get footer data from a special CMS page
            $footerPage = CmsPage::where('org_id', $orgId)
                ->where('slug', 'footer')
                ->where('status', 'published')
                ->first();
            
            if ($footerPage && $footerPage->meta_data) {
                $metaData = is_array($footerPage->meta_data) ? $footerPage->meta_data : json_decode($footerPage->meta_data, true);
                if (isset($metaData['footer_data']) && is_array($metaData['footer_data'])) {
                    return $metaData['footer_data'];
                }
            }
            
            // Return default footer data
            return self::getDefaultFooterData();
        });
    }
    
    /**
     * Get default footer data
     */
    public static function getDefaultFooterData()
    {
        return [
            'quote' => [
                'text' => 'The body benefits from movement, and the mind benefits from stillness.',
                'author' => 'Ancient Wisdom',
                'is_active' => true
            ],
            'about' => [
                'title' => config('app.name', 'SuperHero CrossFit'),
                'description' => 'Discover the perfect balance of strength and serenity. Our mindful approach to fitness nurtures both body and spirit, creating a sanctuary where transformation happens naturally.',
                'is_active' => true,
                'social_links' => [
                    ['icon' => '🧘‍♀️', 'url' => '#'],
                    ['icon' => '🌸', 'url' => '#'],
                    ['icon' => '🕉️', 'url' => '#']
                ]
            ],
            'classes' => [
                'title' => '🌿 Mindful Classes',
                'is_active' => true,
                'items' => [
                    'Yoga Flow',
                    'Meditation Sessions',
                    'Mindful Movement',
                    'Breathwork'
                ]
            ],
            'contact' => [
                'title' => '🏛️ Sacred Space',
                'address' => '123 Serenity Lane',
                'phone' => '+1 (555) 123-PEACE',
                'email' => 'hello@superhero.wodworx.com',
                'is_active' => true
            ],
            'hours' => [
                'title' => 'Sacred Hours',
                'is_active' => true,
                'weekdays' => [
                    'days' => 'Monday - Friday',
                    'time' => '6:00 AM - 9:00 PM',
                    'note' => 'Morning meditation at sunrise'
                ],
                'weekend' => [
                    'days' => 'Saturday - Sunday',
                    'time' => '7:00 AM - 7:00 PM',
                    'note' => 'Extended weekend sessions'
                ]
            ],
            'copyright' => [
                'text' => 'Nurturing transformation with love.',
                'year' => date('Y'),
                'is_active' => true
            ]
        ];
    }
    
    /**
     * Clear footer cache
     */
    public static function clearCache($orgId = null, $portalId = null)
    {
        $orgId = $orgId ?? auth()->user()?->orgUser?->org_id ?? env('CMS_DEFAULT_ORG_ID', env('DEFAULT_ORG_ID', 8));
        $portalId = $portalId ?? env('CMS_DEFAULT_PORTAL_ID', 1);
        
        Cache::forget("cms_footer_data_{$orgId}_{$portalId}");
    }
}

