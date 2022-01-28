<?php

declare(strict_types=1);

namespace Rinvex\Statistics\Models;

use Illuminate\Database\Eloquent\Model;
use Rinvex\Support\Traits\ValidatingTrait;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Path extends Model
{
    use HasFactory;
    use ValidatingTrait;

    /**
     * {@inheritdoc}
     */
    protected $fillable = [
        'host',
        'locale',
        'path',
        'method',
        'parameters',
    ];

    /**
     * {@inheritdoc}
     */
    protected $casts = [
        'host' => 'string',
        'locale' => 'string',
        'path' => 'string',
        'method' => 'string',
        'parameters' => 'json',
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
        $this->setTable(config('rinvex.statistics.tables.paths'));
        $this->mergeRules([
            'host' => 'required|string',
            'locale' => 'required|string',
            'path' => 'required|string',
            'method' => 'required|string',
            'parameters' => 'nullable|array',
        ]);

        parent::__construct($attributes);
    }

    /**
     * The path may have many requests.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function requests(): HasMany
    {
        return $this->hasMany(config('rinvex.statistics.models.request'), 'path_id', 'id');
    }
}
