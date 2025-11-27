<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class CmsSection extends BaseWWModel
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'uuid',
        'cms_page_id',
        'name',
        'type',
        'title',
        'subtitle',
        'content',
        'settings',
        'data',
        'template',
        'css_classes',
        'styles',
        'sort_order',
        'is_active',
        'is_visible',
        'responsive_settings',
        'container',
    ];

    protected $casts = [
        'settings' => 'array',
        'data' => 'array',
        'styles' => 'array',
        'responsive_settings' => 'array',
        'is_active' => 'boolean',
        'is_visible' => 'boolean',
    ];

    protected static function boot()
    {
        parent::boot();
        
        // Override BaseWWModel's creating event to prevent org_id from being set
        // CmsSection doesn't have org_id column - organization is through CmsPage
        static::creating(function ($model) {
            // Set timestamps
            if (!$model->isDirty('created_at')) {
                $model->setCreatedAt($model->freshTimestamp());
            }
            if (!$model->isDirty('updated_at')) {
                $model->setUpdatedAt($model->freshTimestamp());
            }
            
            // Set uuid only if not already set
            if (empty($model->uuid)) {
                $model->uuid = Str::uuid();
            }
            
            // Remove org_id if BaseWWModel set it - CmsSection doesn't have this column
            // Organization is accessed through the parent CmsPage
            $model->offsetUnset('org_id');
        });
    }

    /**
     * Get the page this section belongs to
     * 
     * Note: cms_page_id can be null for footer sections (container='footer')
     */
    public function cmsPage(): BelongsTo
    {
        return $this->belongsTo(CmsPage::class, 'cms_page_id');
    }

    /**
     * Alias for cmsPage relationship for backward compatibility
     */
    public function page(): BelongsTo
    {
        return $this->cmsPage();
    }

    /**
     * Scope sections by organization through page relationship
     */
    public function scopeForOrg($query, $orgId)
    {
        return $query->whereHas('cmsPage', function($q) use ($orgId) {
            $q->where('org_id', $orgId);
        });
    }

    /**
     * Scope for active sections
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope for visible sections
     */
    public function scopeVisible($query)
    {
        return $query->where('is_visible', true);
    }

    /**
     * Scope for ordered sections
     */
    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order');
    }

    /**
     * Get sections by type
     */
    public function scopeOfType($query, $type)
    {
        return $query->where('type', $type);
    }

    /**
     * Get the rendered content for this section
     */
    public function getRenderedContentAttribute(): string
    {
        // This could be extended to support different content rendering
        // based on section type (markdown, HTML, etc.)
        return $this->content ?? '';
    }

    /**
     * Override delete to use Laravel's SoftDeletes directly
     * This avoids BaseWWModel's isDeleted column which doesn't exist on cms_sections
     */
    public function delete()
    {
        // Perform a soft delete by setting deleted_at timestamp
        $this->deleted_at = $this->freshTimestamp();
        return $this->save();
    }

    /**
     * Override setAttribute to prevent BaseWWModel from setting isDeleted
     */
    public function setAttribute($key, $value)
    {
        // Skip isDeleted attribute for CmsSection
        if ($key === 'isDeleted') {
            return;
        }
        return parent::setAttribute($key, $value);
    }

    /**
     * Override setDeletedAtAttribute to prevent isDeleted from being set
     */
    public function setDeletedAtAttribute($value)
    {
        // Just set deleted_at without setting isDeleted
        if ($value === null) {
            $this->attributes['deleted_at'] = null;
        } else {
            $this->attributes['deleted_at'] = $this->fromDateTime($value);
        }
    }
}
