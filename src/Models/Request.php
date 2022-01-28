<?php

declare(strict_types=1);

namespace Rinvex\Statistics\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Rinvex\Support\Traits\ValidatingTrait;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Request extends Model
{
    use HasFactory;
    use ValidatingTrait;

    /**
     * {@inheritdoc}
     */
    protected $fillable = [
        'route_id',
        'agent_id',
        'device_id',
        'platform_id',
        'path_id',
        'geoip_id',
        'user_id',
        'user_type',
        'session_id',
        'status_code',
        'protocol_version',
        'referer',
        'language',
        'is_no_cache',
        'wants_json',
        'is_secure',
        'is_json',
        'is_ajax',
        'is_pjax',
        'created_at',
    ];

    /**
     * {@inheritdoc}
     */
    protected $casts = [
        'route_id' => 'integer',
        'agent_id' => 'integer',
        'device_id' => 'integer',
        'platform_id' => 'integer',
        'path_id' => 'integer',
        'geoip_id' => 'integer',
        'user_id' => 'integer',
        'user_type' => 'string',
        'session_id' => 'string',
        'status_code' => 'integer',
        'protocol_version' => 'string',
        'referer' => 'string',
        'language' => 'string',
        'is_no_cache' => 'boolean',
        'wants_json' => 'boolean',
        'is_secure' => 'boolean',
        'is_json' => 'boolean',
        'is_ajax' => 'boolean',
        'is_pjax' => 'boolean',
        'created_at' => 'datetime',
    ];

    /**
     * {@inheritdoc}
     */
    public $timestamps = false;

    /**
     * {@inheritdoc}
     */
    protected $observables = [
        'validating',
        'validated',
    ];

    /**
     * The default rules that the model will validate against.
     *
     * @var array
     */
    protected $rules = [];

    /**
     * Whether the model should throw a
     * ValidationException if it fails validation.
     *
     * @var bool
     */
    protected $throwValidationExceptions = true;

    /**
     * Create a new Eloquent model instance.
     *
     * @param array $attributes
     */
    public function __construct(array $attributes = [])
    {
        $this->setTable(config('rinvex.statistics.tables.requests'));
        $this->mergeRules([
            'route_id' => 'required|integer',
            'agent_id' => 'required|integer',
            'device_id' => 'required|integer',
            'platform_id' => 'required|integer',
            'path_id' => 'required|integer',
            'geoip_id' => 'required|integer',
            'user_id' => 'nullable|integer',
            'user_type' => 'nullable|string|strip_tags|max:150',
            'session_id' => 'required|string',
            'status_code' => 'required|integer',
            'protocol_version' => 'nullable|string',
            'referer' => 'nullable|string',
            'language' => 'required|string',
            'is_no_cache' => 'sometimes|boolean',
            'wants_json' => 'sometimes|boolean',
            'is_secure' => 'sometimes|boolean',
            'is_json' => 'sometimes|boolean',
            'is_ajax' => 'sometimes|boolean',
            'is_pjax' => 'sometimes|boolean',
            'created_at' => 'required|date',
        ]);

        parent::__construct($attributes);
    }

    /**
     * {@inheritdoc}
     */
    protected static function boot()
    {
        parent::boot();

        static::saved(function (self $request) {
            $request->path()->increment('count');
            $request->route()->increment('count');
            $request->geoip()->increment('count');
            $request->agent()->increment('count');
            $request->device()->increment('count');
            $request->platform()->increment('count');
        });
    }

    /**
     * The request always belongs to a route.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function route(): BelongsTo
    {
        return $this->belongsTo(config('rinvex.statistics.models.route'), 'route_id', 'id', 'route');
    }

    /**
     * The request always belongs to a path.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function path(): BelongsTo
    {
        return $this->belongsTo(config('rinvex.statistics.models.path'), 'path_id', 'id', 'path');
    }

    /**
     * The request always belongs to an agent.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function agent(): BelongsTo
    {
        return $this->belongsTo(config('rinvex.statistics.models.agent'), 'agent_id', 'id', 'agent');
    }

    /**
     * The request always belongs to an geoip.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function geoip(): BelongsTo
    {
        return $this->belongsTo(config('rinvex.statistics.models.geoip'), 'geoip_id', 'id', 'geoip');
    }

    /**
     * The request always belongs to a device.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function device(): BelongsTo
    {
        return $this->belongsTo(config('rinvex.statistics.models.device'), 'device_id', 'id', 'device');
    }

    /**
     * The request always belongs to a platform.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function platform(): BelongsTo
    {
        return $this->belongsTo(config('rinvex.statistics.models.platform'), 'platform_id', 'id', 'platform');
    }

    /**
     * Get the owning user.
     *
     * @return \Illuminate\Database\Eloquent\Relations\MorphTo
     */
    public function user(): MorphTo
    {
        return $this->morphTo('user', 'user_type', 'user_id', 'id');
    }

    /**
     * Get bookings of the given user.
     *
     * @param \Illuminate\Database\Eloquent\Builder $builder
     * @param \Illuminate\Database\Eloquent\Model   $user
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeOfUser(Builder $builder, Model $user): Builder
    {
        return $builder->where('user_type', $user->getMorphClass())->where('user_id', $user->getKey());
    }
}
