<?php

declare(strict_types=1);

namespace Rinvex\Statistics\Models;

use Illuminate\Database\Eloquent\Model;
use Rinvex\Support\Traits\ValidatingTrait;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Geoip extends Model
{
    use HasFactory;
    use ValidatingTrait;

    /**
     * {@inheritdoc}
     */
    protected $fillable = [
        'client_ip',
        'latitude',
        'longitude',
        'country_code',
        'client_ips',
        'is_from_trusted_proxy',
        'division_code',
        'postal_code',
        'timezone',
        'city',
    ];

    /**
     * {@inheritdoc}
     */
    protected $casts = [
        'client_ip' => 'string',
        'latitude' => 'string',
        'longitude' => 'string',
        'country_code' => 'string',
        'client_ips' => 'json',
        'is_from_trusted_proxy' => 'boolean',
        'division_code' => 'string',
        'postal_code' => 'string',
        'timezone' => 'string',
        'city' => 'string',
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
        $this->setTable(config('rinvex.statistics.tables.geoips'));
        $this->mergeRules([
            'client_ip' => 'required|string',
            'latitude' => 'required|string',
            'longitude' => 'required|string',
            'country_code' => 'required|alpha|size:2|country',
            'client_ips' => 'nullable|array',
            'is_from_trusted_proxy' => 'sometimes|boolean',
            'division_code' => 'nullable|string',
            'postal_code' => 'nullable|string',
            'timezone' => 'nullable|string|max:64|timezone',
            'city' => 'nullable|string',
        ]);

        parent::__construct($attributes);
    }

    /**
     * The geoip may have many requests.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function requests(): HasMany
    {
        return $this->hasMany(config('rinvex.statistics.models.request'), 'geoip_id', 'id');
    }
}
