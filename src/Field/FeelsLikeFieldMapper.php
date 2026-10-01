<?php

declare(strict_types=1);

namespace ChristianBrown\MetOfficeWeather\Field;

use ChristianBrown\MetOffice\SiteSpecific\Model\HourlyForecastTimeStepInterface;
use ChristianBrown\MetOfficeWeather\OutputTransformerInterface;

final class FeelsLikeFieldMapper implements OutputFieldMapperInterface
{
    /**
     * @return mixed[]
     */
    public function map(HourlyForecastTimeStepInterface $step): array
    {
        $value = $step->getFeelsLikeTemperature();
        if (null === $value) {
            return [];
        }

        return [OutputTransformerInterface::KEY_TEMPERATURE_FEELS_LIKE => $value];
    }
}
