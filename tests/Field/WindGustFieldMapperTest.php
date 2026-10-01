<?php

declare(strict_types=1);

namespace ChristianBrown\MetOfficeWeather\Tests\Field;

use ChristianBrown\MetOffice\SiteSpecific\Model\HourlyForecastTimeStepInterface;
use ChristianBrown\MetOfficeWeather\Field\WindGustFieldMapper;
use ChristianBrown\MetOfficeWeather\OutputTransformerInterface;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(WindGustFieldMapper::class)]
final class WindGustFieldMapperTest extends TestCase
{
    public function testMapsNothingWhenAbsent(): void
    {
        $step = self::createStub(HourlyForecastTimeStepInterface::class);
        $step->method('getMax10mWindGust')
            ->willReturn(null);

        self::assertSame([], (new WindGustFieldMapper())->map($step));
    }

    public function testMapsValueWhenPresent(): void
    {
        $step = self::createStub(HourlyForecastTimeStepInterface::class);
        $step->method('getMax10mWindGust')
            ->willReturn(12.0);

        self::assertSame([OutputTransformerInterface::KEY_WIND_GUST => 12.0 * OutputTransformerInterface::METRES_PER_SECOND_TO_MPH], (new WindGustFieldMapper())->map($step));
    }
}
