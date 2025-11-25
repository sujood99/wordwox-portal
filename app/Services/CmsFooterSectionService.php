<?php

namespace App\Services;

use App\Models\CmsSection;
use App\Models\CmsPage;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;

class CmsFooterSectionService
{
    /**
     * Get all footer sections for an organization
     * Footer sections are independent - no page association required
     */
    public static function getFooterSections($orgId, $portalId = 1): Collection
    {
        $cacheKey = "footer_sections_{$orgId}_{$portalId}";
        
        return Cache::remember($cacheKey, 3600, function() use ($orgId) {
            return CmsSection::where('container', 'footer')
                ->where('is_active', true)
                ->orderBy('sort_order')
                ->get();
        });
    }
    
    /**
     * Get a specific footer section by type
     * Footer sections are independent - no page association required
     */
    public static function getFooterSectionByType($orgId, $type, $portalId = 1): ?CmsSection
    {
        return CmsSection::where('container', 'footer')
            ->where('type', $type)
            ->where('is_active', true)
            ->first();
    }
    
    /**
     * Get social media links from footer sections
     */
    public static function getSocialLinks($orgId, $portalId = 1): array
    {
        $section = self::getFooterSectionByType($orgId, 'social_links', $portalId);
        
        if (!$section || !$section->data) {
            return [];
        }
        
        return $section->data;
    }
    
    /**
     * Create or update footer section
     * Footer sections can exist without a page - just use container='footer'
     */
    public static function createOrUpdateFooterSection($orgId, $data): CmsSection
    {
        // Create or update section without requiring a page
        $section = CmsSection::updateOrCreate(
            [
                'container' => 'footer',
                'type' => $data['type'] ?? 'custom',
            ],
            [
                'cms_page_id' => null, // Footer sections don't need page association
                'name' => $data['name'] ?? 'Footer Section',
                'title' => $data['title'] ?? null,
                'content' => $data['content'] ?? null,
                'data' => $data['data'] ?? [],
                'is_active' => $data['is_active'] ?? true,
            ]
        );
        
        self::clearCache($orgId, 1);
        
        return $section;
    }
    
    /**
     * Clear cache for footer sections
     */
    public static function clearCache($orgId, $portalId = 1): void
    {
        Cache::forget("footer_sections_{$orgId}_{$portalId}");
    }
}
