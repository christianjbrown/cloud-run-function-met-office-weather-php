<?php

declare(strict_types=1);

namespace ChristianBrown\MetOfficeWeather\Field;

use ChristianBrown\MetOffice\SiteSpecific\Model\HourlyForecastTimeStepInterface;
use ChristianBrown\MetOfficeWeather\OutputTransformerInterface;

final class WindSpeedFieldMapper implements OutputFieldMapperInterface
{
    /**
     * @return mixed[]
     */
    public function map(HourlyForecastTimeStepInterface $step): array
    {
        $value = $step->getWindSpeed10m();
        if (null === $value) {
            return [];
        }

        return [OutputTransformerInterface::KEY_WIND_SPEED => $value * OutputTransformerInterface::METRES_PER_SECOND_TO_MPH];
    }
}
