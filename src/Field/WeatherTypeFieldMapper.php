<?php

declare(strict_types=1);

namespace ChristianBrown\MetOfficeWeather\Field;

use ChristianBrown\MetOffice\SiteSpecific\Model\HourlyForecastTimeStepInterface;
use ChristianBrown\MetOfficeWeather\OutputTransformerInterface;

final class WeatherTypeFieldMapper implements OutputFieldMapperInterface
{
    /**
     * @return mixed[]
     */
    public function map(HourlyForecastTimeStepInterface $step): array
    {
        $value = $step->getSignificantWeatherCode();
        if (null === $value) {
            return [];
        }

        return [OutputTransformerInterface::KEY_TYPE => $value->value];
    }
}
