<?php

declare(strict_types=1);

namespace ChristianBrown\MetOfficeWeather\Field;

use ChristianBrown\MetOffice\SiteSpecific\Model\HourlyForecastTimeStepInterface;
use ChristianBrown\MetOfficeWeather\OutputTransformerInterface;

final class PressureFieldMapper implements OutputFieldMapperInterface
{
    /**
     * @return mixed[]
     */
    public function map(HourlyForecastTimeStepInterface $step): array
    {
        $value = $step->getMslp();
        if (null === $value) {
            return [];
        }

        return [OutputTransformerInterface::KEY_PRESSURE => $value / OutputTransformerInterface::PASCALS_PER_HECTOPASCAL];
    }
}
