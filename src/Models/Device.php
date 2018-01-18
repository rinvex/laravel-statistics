<?php

declare(strict_types=1);

namespace Rinvex\Statistics\Models;

use Illuminate\Database\Eloquent\Model;
use Rinvex\Cacheable\CacheableEloquent;
use Rinvex\Support\Traits\ValidatingTrait;
use Rinvex\Statistics\Contracts\DeviceContract;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Device extends Model implements DeviceContract
{
    use ValidatingTrait;
    use CacheableEloquent;

    /**
     * {@inheritdoc}
     */
    protected $fillable = [
        'family',
        'model',
        'brand',
    ];

    /**
     * {@inheritdoc}
     */
    protected $casts = [
        'family' => 'string',
        'model' => 'string',
        'brand' => 'string',
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
        'family' => 'required|string',
        'model' => 'nullable|string',
        'brand' => 'nullable|string',
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

        $this->setTable(config('rinvex.statistics.tables.devices'));
    }

    /**
     * The device may have many requests.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function requests(): HasMany
    {
        return $this->hasMany(config('rinvex.statistics.models.request'), 'device_id', 'id');
    }
}
