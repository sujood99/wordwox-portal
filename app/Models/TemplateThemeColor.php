<?php

namespace App\Models;

use App\Traits\Tenantable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TemplateThemeColor extends Model
{
    use HasFactory, Tenantable;

    protected $table = 'template_theme_colors';

    protected $fillable = [
        'org_id',
        'template',
        'primary_color',
        'secondary_color',
        'text_dark',
        'text_gray',
        'text_base',
        'text_light',
        'text_footer',
        'bg_white',
        'bg_packages',
        'bg_coaches',
        'bg_footer',
        'primary_hover',
        'secondary_hover',
    ];

    /**
     * Get the organization that owns this theme color configuration
     */
    public function org(): BelongsTo
    {
        return $this->belongsTo(Org::class, 'org_id');
    }

    /**
     * Get default theme colors for fitness template
     */
    public static function getDefaults(): array
    {
        return [
            'primary_color' => '#ff6b6b',
            'secondary_color' => '#4ecdc4',
            'text_dark' => '#2c3e50',
            'text_gray' => '#6c757d',
            'text_base' => '#333',
            'text_light' => '#ffffff',
            'text_footer' => '#ffffff',
            'bg_white' => '#ffffff',
            'bg_packages' => '#f2f4f6',
            'bg_coaches' => '#f8f9fa',
            'bg_footer' => '#2c3e50',
            'primary_hover' => '#ff5252',
            'secondary_hover' => '#3db8a8',
        ];
    }

    /**
     * Get or create theme colors for an organization and template
     */
    public static function getOrCreateForOrg(int $orgId, string $template = 'fitness'): self
    {
        return static::firstOrCreate(
            ['org_id' => $orgId, 'template' => $template],
            static::getDefaults()
        );
    }
}
