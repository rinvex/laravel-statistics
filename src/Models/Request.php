<?php

declare(strict_types=1);

namespace Rinvex\Statistics\Models;

use Illuminate\Database\Eloquent\Model;
use Rinvex\Cacheable\CacheableEloquent;
use Rinvex\Support\Traits\ValidatingTrait;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Request extends Model
{
    use ValidatingTrait;
    use CacheableEloquent;

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
        'session_id',
        'status_code',
        'method',
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
        'session_id' => 'string',
        'status_code' => 'integer',
        'method' => 'string',
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
    protected $rules = [
        'route_id' => 'required|integer',
        'agent_id' => 'required|integer',
        'device_id' => 'required|integer',
        'platform_id' => 'required|integer',
        'path_id' => 'required|integer',
        'geoip_id' => 'required|integer',
        'user_id' => 'nullable|integer',
        'session_id' => 'required|string',
        'status_code' => 'required|integer',
        'method' => 'required|string',
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
    ];

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
        parent::__construct($attributes);

        $this->setTable(config('rinvex.statistics.tables.requests'));
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
        return $this->belongsTo(config('rinvex.statistics.models.route'), 'route_id', 'id');
    }

    /**
     * The request always belongs to a path.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function path(): BelongsTo
    {
        return $this->belongsTo(config('rinvex.statistics.models.path'), 'path_id', 'id');
    }

    /**
     * The request always belongs to an agent.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function agent(): BelongsTo
    {
        return $this->belongsTo(config('rinvex.statistics.models.agent'), 'agent_id', 'id');
    }

    /**
     * The request always belongs to an geoip.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function geoip(): BelongsTo
    {
        return $this->belongsTo(config('rinvex.statistics.models.geoip'), 'geoip_id', 'id');
    }

    /**
     * The request always belongs to a device.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function device(): BelongsTo
    {
        return $this->belongsTo(config('rinvex.statistics.models.device'), 'device_id', 'id');
    }

    /**
     * The request always belongs to a platform.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function platform(): BelongsTo
    {
        return $this->belongsTo(config('rinvex.statistics.models.platform'), 'platform_id', 'id');
    }

    /**
     * Request may belongs to a user.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function user(): belongsTo
    {
        return $this->belongsTo(config('auth.providers.users.model'), 'user_id', 'id');
    }
}
