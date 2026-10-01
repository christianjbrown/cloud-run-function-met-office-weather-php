<?php

declare(strict_types=1);

namespace ChristianBrown\MetOfficeWeather\Tests\Field;

use ChristianBrown\MetOffice\SiteSpecific\Model\HourlyForecastTimeStepInterface;
use ChristianBrown\MetOfficeWeather\Field\PressureFieldMapper;
use ChristianBrown\MetOfficeWeather\OutputTransformerInterface;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(PressureFieldMapper::class)]
final class PressureFieldMapperTest extends TestCase
{
    public function testMapsNothingWhenAbsent(): void
    {
        $step = self::createStub(HourlyForecastTimeStepInterface::class);
        $step->method('getMslp')
            ->willReturn(null);

        self::assertSame([], (new PressureFieldMapper())->map($step));
    }

    public function testMapsValueWhenPresent(): void
    {
        $step = self::createStub(HourlyForecastTimeStepInterface::class);
        $step->method('getMslp')
            ->willReturn(101320);

        self::assertSame([OutputTransformerInterface::KEY_PRESSURE => 101320 / OutputTransformerInterface::PASCALS_PER_HECTOPASCAL], (new PressureFieldMapper())->map($step));
    }
}
