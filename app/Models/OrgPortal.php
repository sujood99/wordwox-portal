<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class OrgPortal extends Model
{
    use SoftDeletes;

    protected $table = 'orgPortal';

    protected $fillable = [
        'uuid',
        'org_id',
        'orgLocation_id',
        'subdomain',
        'baseUrl',
        'status',
    ];

    /**
     * Get the organization this portal belongs to
     */
    public function org(): BelongsTo
    {
        return $this->belongsTo(Org::class);
    }

    // Note: CMS pages no longer have orgPortal_id, so these relationships are removed
    // Pages are now only associated with organizations (org_id)

    /**
     * Scope for active portals
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }
}
