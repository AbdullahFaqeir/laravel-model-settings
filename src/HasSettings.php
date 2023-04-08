<?php

namespace AbdullahFaqeir\ModelSettings;

use JsonException;
use Illuminate\Support\Arr;

/**
 * @mixin \Illuminate\Database\Eloquent\Model
 * @property array settings
 */
trait HasSettings
{
    /**
     * Boot the HasSettings trait.
     *
     * @return void
     */
    public static function bootHasSettings(): void
    {
        self::creating(static function (self $model) {
            if (!$model->settings) {
                $model->settings = $model->getDefaultSettings();
            }
        });

        self::saving(static function (self $model) {
            if ($model->settings && property_exists($model, 'allowedSettings') && is_array($model->allowedSettings)) {
                $model->settings = Arr::only($model->settings, $model->allowedSettings);
            }
        });
    }

    /**
     * Get the model's default settings.
     *
     * @return array
     */
    public function getDefaultSettings(): array
    {
        return (isset($this->defaultSettings) && is_array($this->defaultSettings)) ? $this->defaultSettings : [];
    }

    /**
     * Get the settings attribute.
     *
     * @param string|null $settings
     *
     * @return mixed
     */
    public function getSettingsAttribute(?string $settings): mixed
    {
        try {
            return json_decode($settings, true, 512, JSON_THROW_ON_ERROR);
        } catch (JsonException) {
            return [];
        }
    }

    /**
     * Set the settings attribute.
     *
     * @param array|null $settings
     *
     * @return void
     */
    public function setSettingsAttribute(?array $settings): void
    {
        try {
            $this->attributes['settings'] = json_encode($settings, JSON_THROW_ON_ERROR);
        } catch (JsonException) {
            $this->attributes['settings'] = '{}';
        }
    }

    /**
     * The model's settings.
     *
     * @param string|null $key
     * @param mixed|null  $default
     *
     * @return \AbdullahFaqeir\ModelSettings\Settings|mixed
     */
    public function settings(?string $key = null, mixed $default = null): mixed
    {
        return $key ? $this->settings()
                           ->get($key, $default) : new Settings($this);
    }

    /**
     * Map settings() to another alias specified with $mapSettingsTo.
     *
     * @param $method
     * @param $parameters
     *
     * @return mixed
     */
    public function __call($method, $parameters)
    {
        if (isset($this->mapSettingsTo) && $method === $this->mapSettingsTo) {
            return $this->settings(...$parameters);
        }

        return is_callable(['parent', '__call']) ? parent::__call($method, $parameters) : null;
    }
}
