<?php

declare(strict_types=1);

namespace ChristianBrown\MetOfficeWeather\Tests\Field;

use ChristianBrown\MetOffice\SiteSpecific\Model\HourlyForecastTimeStepInterface;
use ChristianBrown\MetOfficeWeather\Field\WindSpeedFieldMapper;
use ChristianBrown\MetOfficeWeather\OutputTransformerInterface;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(WindSpeedFieldMapper::class)]
final class WindSpeedFieldMapperTest extends TestCase
{
    public function testMapsNothingWhenAbsent(): void
    {
        $step = self::createStub(HourlyForecastTimeStepInterface::class);
        $step->method('getWindSpeed10m')
            ->willReturn(null);

        self::assertSame([], (new WindSpeedFieldMapper())->map($step));
    }

    public function testMapsValueWhenPresent(): void
    {
        $step = self::createStub(HourlyForecastTimeStepInterface::class);
        $step->method('getWindSpeed10m')
            ->willReturn(10.0);

        self::assertSame([OutputTransformerInterface::KEY_WIND_SPEED => 10.0 * OutputTransformerInterface::METRES_PER_SECOND_TO_MPH], (new WindSpeedFieldMapper())->map($step));
    }
}
