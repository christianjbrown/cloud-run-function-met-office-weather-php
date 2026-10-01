<?php

declare(strict_types=1);

namespace ChristianBrown\MetOfficeWeather\Field;

use ChristianBrown\MetOffice\SiteSpecific\Model\HourlyForecastTimeStepInterface;

/**
 * Maps one output field (or one closely tied group) of the response from a
 * forecast step. Adding a field to the response means adding a mapper and
 * registering it where the OutputTransformer is built.
 */
interface OutputFieldMapperInterface
{
    /**
     * @return array<string, mixed> The keys to merge into the response, empty when the step has no value for this field
     */
    public function map(HourlyForecastTimeStepInterface $step): array;
}
