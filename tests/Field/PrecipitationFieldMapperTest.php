<?php

declare(strict_types=1);

namespace ChristianBrown\MetOfficeWeather\Tests\Field;

use ChristianBrown\MetOffice\SiteSpecific\Model\HourlyForecastTimeStepInterface;
use ChristianBrown\MetOfficeWeather\Field\PrecipitationFieldMapper;
use ChristianBrown\MetOfficeWeather\OutputTransformerInterface;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(PrecipitationFieldMapper::class)]
final class PrecipitationFieldMapperTest extends TestCase
{
    public function testMapsNothingWhenAbsent(): void
    {
        $step = self::createStub(HourlyForecastTimeStepInterface::class);
        $step->method('getProbOfPrecipitation')
            ->willReturn(null);

        self::assertSame([], (new PrecipitationFieldMapper())->map($step));
    }

    public function testMapsValueWhenPresent(): void
    {
        $step = self::createStub(HourlyForecastTimeStepInterface::class);
        $step->method('getProbOfPrecipitation')
            ->willReturn(20);

        self::assertSame([OutputTransformerInterface::KEY_PRECIPITATION => 20], (new PrecipitationFieldMapper())->map($step));
    }
}
