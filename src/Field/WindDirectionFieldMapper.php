<?php

declare(strict_types=1);

namespace ChristianBrown\MetOfficeWeather\Field;

use ChristianBrown\MetOffice\Enums\WindDirection;
use ChristianBrown\MetOffice\SiteSpecific\Model\HourlyForecastTimeStepInterface;
use ChristianBrown\MetOfficeWeather\OutputTransformerInterface;

final class WindDirectionFieldMapper implements OutputFieldMapperInterface
{
    /**
     * @return mixed[]
     */
    public function map(HourlyForecastTimeStepInterface $step): array
    {
        $degrees = $step->getWindDirectionFrom10m();
        if (null === $degrees) {
            return [];
        }

        return [
            OutputTransformerInterface::KEY_WIND_DIRECTION => WindDirection::fromDegrees($degrees)->value,
            OutputTransformerInterface::KEY_WIND_DIRECTION_DEGREES => $degrees,
        ];
    }
}
