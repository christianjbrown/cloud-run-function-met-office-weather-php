<?php

declare(strict_types=1);

namespace ChristianBrown\MetOfficeWeather;

use ChristianBrown\MetOffice\SiteSpecific\Model\HourlyForecastTimeStepInterface;
use ChristianBrown\MetOfficeWeather\Field\OutputFieldMapperInterface;

use function gmdate;

use const DATE_ATOM;

final class OutputTransformer implements OutputTransformerInterface
{
    /**
     * @var iterable<OutputFieldMapperInterface>
     */
    private iterable $fieldMappers;

    /**
     * @param iterable<OutputFieldMapperInterface> $fieldMappers Applied in order, so the order is the key order of the response
     */
    public function __construct(iterable $fieldMappers)
    {
        $this->fieldMappers = $fieldMappers;
    }

    /**
     * @return mixed[]
     */
    public function transform(HourlyForecastTimeStepInterface $step): array
    {
        $validFrom = $step->getTime();
        $validTo = $validFrom + self::WINDOW_SECONDS;

        // The valid-window fields are always emitted; every other field comes from
        // a mapper that contributes its keys only when the step has a value.
        $data = [
            self::KEY_VALID_FROM => $validFrom,
            self::KEY_VALID_FROM_ISO8601 => gmdate(DATE_ATOM, $validFrom),
            self::KEY_VALID_TO => $validTo,
            self::KEY_VALID_TO_ISO8601 => gmdate(DATE_ATOM, $validTo),
        ];

        foreach ($this->fieldMappers as $fieldMapper) {
            $data += $fieldMapper->map($step);
        }

        return $data;
    }
}
