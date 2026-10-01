<?php

declare(strict_types=1);

namespace ChristianBrown\MetOfficeWeather\Tests\Field;

use ChristianBrown\MetOffice\SiteSpecific\Model\HourlyForecastTimeStepInterface;
use ChristianBrown\MetOfficeWeather\Field\HumidityFieldMapper;
use ChristianBrown\MetOfficeWeather\OutputTransformerInterface;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(HumidityFieldMapper::class)]
final class HumidityFieldMapperTest extends TestCase
{
    public function testMapsNothingWhenAbsent(): void
    {
        $step = self::createStub(HourlyForecastTimeStepInterface::class);
        $step->method('getScreenRelativeHumidity')
            ->willReturn(null);

        self::assertSame([], (new HumidityFieldMapper())->map($step));
    }

    public function testMapsValueWhenPresent(): void
    {
        $step = self::createStub(HourlyForecastTimeStepInterface::class);
        $step->method('getScreenRelativeHumidity')
            ->willReturn(65.5);

        self::assertSame([OutputTransformerInterface::KEY_HUMIDITY => 65.5], (new HumidityFieldMapper())->map($step));
    }
}
