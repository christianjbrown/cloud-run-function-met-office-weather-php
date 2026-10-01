<?php

declare(strict_types=1);

namespace ChristianBrown\MetOfficeWeather\Tests\Field;

use ChristianBrown\MetOffice\Enums\WindDirection;
use ChristianBrown\MetOffice\SiteSpecific\Model\HourlyForecastTimeStepInterface;
use ChristianBrown\MetOfficeWeather\Field\WindDirectionFieldMapper;
use ChristianBrown\MetOfficeWeather\OutputTransformerInterface;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(WindDirectionFieldMapper::class)]
final class WindDirectionFieldMapperTest extends TestCase
{
    public function testMapsNothingWhenAbsent(): void
    {
        $step = self::createStub(HourlyForecastTimeStepInterface::class);
        $step->method('getWindDirectionFrom10m')
            ->willReturn(null);

        self::assertSame([], (new WindDirectionFieldMapper())->map($step));
    }

    public function testMapsValueWhenPresent(): void
    {
        $step = self::createStub(HourlyForecastTimeStepInterface::class);
        $step->method('getWindDirectionFrom10m')
            ->willReturn(90);

        self::assertSame([
            OutputTransformerInterface::KEY_WIND_DIRECTION => WindDirection::fromDegrees(90)->value,
            OutputTransformerInterface::KEY_WIND_DIRECTION_DEGREES => 90,
        ], (new WindDirectionFieldMapper())->map($step));
    }
}
