<?php

declare(strict_types=1);

namespace ChristianBrown\MetOfficeWeather\Tests\Field;

use ChristianBrown\MetOfficeWeather\Field\DewPointFieldMapper;
use ChristianBrown\MetOfficeWeather\Field\FeelsLikeFieldMapper;
use ChristianBrown\MetOfficeWeather\Field\HumidityFieldMapper;
use ChristianBrown\MetOfficeWeather\Field\OutputFieldMapperInterface;
use ChristianBrown\MetOfficeWeather\Field\PrecipitationFieldMapper;
use ChristianBrown\MetOfficeWeather\Field\PressureFieldMapper;
use ChristianBrown\MetOfficeWeather\Field\TemperatureFieldMapper;
use ChristianBrown\MetOfficeWeather\Field\UvIndexFieldMapper;
use ChristianBrown\MetOfficeWeather\Field\VisibilityFieldMapper;
use ChristianBrown\MetOfficeWeather\Field\WeatherTypeFieldMapper;
use ChristianBrown\MetOfficeWeather\Field\WeatherTypeNameFieldMapper;
use ChristianBrown\MetOfficeWeather\Field\WindDirectionFieldMapper;
use ChristianBrown\MetOfficeWeather\Field\WindGustFieldMapper;
use ChristianBrown\MetOfficeWeather\Field\WindSpeedFieldMapper;

/**
 * The mapper set the entry point registers, in the same order, for tests that
 * exercise the whole response shape.
 */
final class DefaultFieldMappers
{
    /**
     * @return list<OutputFieldMapperInterface>
     */
    public static function create(): array
    {
        return [
            new TemperatureFieldMapper(),
            new FeelsLikeFieldMapper(),
            new HumidityFieldMapper(),
            new PrecipitationFieldMapper(),
            new UvIndexFieldMapper(),
            new VisibilityFieldMapper(),
            new PressureFieldMapper(),
            new DewPointFieldMapper(),
            new WindSpeedFieldMapper(),
            new WindGustFieldMapper(),
            new WindDirectionFieldMapper(),
            new WeatherTypeFieldMapper(),
            new WeatherTypeNameFieldMapper(),
        ];
    }
}
