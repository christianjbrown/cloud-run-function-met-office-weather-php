<?php

declare(strict_types=1);

namespace ChristianBrown\MetOfficeWeather\Tests\Field;

use ChristianBrown\MetOffice\Enums\WeatherType;
use ChristianBrown\MetOffice\SiteSpecific\Model\HourlyForecastTimeStepInterface;
use ChristianBrown\MetOfficeWeather\Field\WeatherTypeFieldMapper;
use ChristianBrown\MetOfficeWeather\OutputTransformerInterface;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(WeatherTypeFieldMapper::class)]
final class WeatherTypeFieldMapperTest extends TestCase
{
    public function testMapsNothingWhenAbsent(): void
    {
        $step = self::createStub(HourlyForecastTimeStepInterface::class);
        $step->method('getSignificantWeatherCode')
            ->willReturn(null);

        self::assertSame([], (new WeatherTypeFieldMapper())->map($step));
    }

    public function testMapsValueWhenPresent(): void
    {
        $step = self::createStub(HourlyForecastTimeStepInterface::class);
        $step->method('getSignificantWeatherCode')
            ->willReturn(WeatherType::HEAVY_RAIN);

        self::assertSame([OutputTransformerInterface::KEY_TYPE => WeatherType::HEAVY_RAIN->value], (new WeatherTypeFieldMapper())->map($step));
    }
}
