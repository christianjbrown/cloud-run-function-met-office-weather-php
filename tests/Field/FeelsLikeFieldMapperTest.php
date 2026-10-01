<?php

declare(strict_types=1);

namespace ChristianBrown\MetOfficeWeather\Tests\Field;

use ChristianBrown\MetOffice\SiteSpecific\Model\HourlyForecastTimeStepInterface;
use ChristianBrown\MetOfficeWeather\Field\FeelsLikeFieldMapper;
use ChristianBrown\MetOfficeWeather\OutputTransformerInterface;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(FeelsLikeFieldMapper::class)]
final class FeelsLikeFieldMapperTest extends TestCase
{
    public function testMapsNothingWhenAbsent(): void
    {
        $step = self::createStub(HourlyForecastTimeStepInterface::class);
        $step->method('getFeelsLikeTemperature')
            ->willReturn(null);

        self::assertSame([], (new FeelsLikeFieldMapper())->map($step));
    }

    public function testMapsValueWhenPresent(): void
    {
        $step = self::createStub(HourlyForecastTimeStepInterface::class);
        $step->method('getFeelsLikeTemperature')
            ->willReturn(17.2);

        self::assertSame([OutputTransformerInterface::KEY_TEMPERATURE_FEELS_LIKE => 17.2], (new FeelsLikeFieldMapper())->map($step));
    }
}
