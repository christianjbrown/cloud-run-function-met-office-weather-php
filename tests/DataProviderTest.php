<?php

declare(strict_types=1);

namespace ChristianBrown\MetOfficeWeather\Tests;

use ChristianBrown\Database\ClimateMeasurementRecorderInterface;
use ChristianBrown\Database\Entity\MetOfficeWeather;
use ChristianBrown\MetOffice\Coordinates;
use ChristianBrown\MetOffice\SiteSpecific\Api\HourlyForecastApiInterface;
use ChristianBrown\MetOffice\SiteSpecific\Model\ForecastInterface;
use ChristianBrown\MetOffice\SiteSpecific\Model\ForecastTimeStepInterface;
use ChristianBrown\MetOffice\SiteSpecific\Model\HourlyForecastTimeStepInterface;
use ChristianBrown\MetOfficeWeather\DataProvider;
use ChristianBrown\MetOfficeWeather\DataProviderInterface;
use ChristianBrown\MetOfficeWeather\OutputTransformerInterface;
use ChristianBrown\UserFriendlyException\UserFriendlyException;
use DateTimeImmutable;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\Exception;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ServerRequestInterface;
use RuntimeException;
use Symfony\Component\Clock\MockClock;

use function ini_set;
use function sys_get_temp_dir;
use function tempnam;
use function unlink;

#[CoversClass(DataProvider::class)]
final class DataProviderTest extends TestCase
{
    private const int NOW = 1752580800;

    /**
     * @throws Exception
     */
    public function testClimateWriteFailureIsSwallowed(): void
    {
        $request = self::createStub(ServerRequestInterface::class);
        $now = self::NOW;

        $step = $this->createHourlyStep($now - 3600, 12.3, 81.0);
        $forecast = $this->createForecast([$step]);

        $hourlyForecastApi = self::createStub(HourlyForecastApiInterface::class);
        $hourlyForecastApi->method('getForecast')
            ->willReturn($forecast);

        $outputTransformer = self::createStub(OutputTransformerInterface::class);
        $outputTransformer->method('transform')
            ->willReturn(['test-output']);

        $climateMeasurementRecorder = self::createStub(ClimateMeasurementRecorderInterface::class);
        $climateMeasurementRecorder->method('record')
            ->willThrowException(new RuntimeException('test-database-failure'));

        $dataProvider = new DataProvider($hourlyForecastApi, $outputTransformer, $climateMeasurementRecorder, new Coordinates(51.5, -0.18), new MockClock('@'.self::NOW));

        // The write failure is logged via error_log() for Cloud Logging; divert it
        // to a temp file so the strict-output check does not see it as unexpected
        // output.
        $errorLog = (string) tempnam(sys_get_temp_dir(), 'data-provider-test');
        $previousErrorLog = (string) ini_set('error_log', $errorLog);

        try {
            $actual = $dataProvider->getData($request);
        } finally {
            ini_set('error_log', $previousErrorLog);
            unlink($errorLog);
        }

        self::assertSame(['test-output'], $actual);
    }

    /**
     * @throws Exception
     */
    public function testFallsBackToEarliestWhenAllStepsAreInTheFuture(): void
    {
        $request = self::createStub(ServerRequestInterface::class);
        $now = self::NOW;

        $earliest = $this->createHourlyStep($now + 3600);
        $latest = $this->createHourlyStep($now + 7200);

        // Unsorted so the earliest-selection depends on the sort, not input order.
        $forecast = $this->createForecast([$latest, $earliest]);

        $hourlyForecastApi = self::createStub(HourlyForecastApiInterface::class);
        $hourlyForecastApi->method('getForecast')
            ->willReturn($forecast);

        $outputTransformer = self::createMock(OutputTransformerInterface::class);
        $outputTransformer->expects(self::once())
            ->method('transform')
            ->with($earliest)
            ->willReturn(['test-output']);

        $dataProvider = new DataProvider($hourlyForecastApi, $outputTransformer, self::createStub(ClimateMeasurementRecorderInterface::class), new Coordinates(51.5, -0.18), new MockClock('@'.self::NOW));

        self::assertSame(['test-output'], $dataProvider->getData($request));
    }

    /**
     * A warm instance serves many requests, so "now" must be read per request.
     *
     * @throws Exception
     */
    public function testReadsNowOnEveryRequest(): void
    {
        $request = self::createStub(ServerRequestInterface::class);
        $first = $this->createHourlyStep(self::NOW);
        $second = $this->createHourlyStep(self::NOW + 3600);

        $hourlyForecastApi = self::createStub(HourlyForecastApiInterface::class);
        $hourlyForecastApi->method('getForecast')
            ->willReturn($this->createForecast([$first, $second]));

        $outputTransformer = self::createMock(OutputTransformerInterface::class);
        $outputTransformer->expects(self::exactly(2))
            ->method('transform')
            ->willReturnCallback(static fn (HourlyForecastTimeStepInterface $step): array => [$step->getTime()]);

        $clock = new MockClock('@'.self::NOW);
        $dataProvider = new DataProvider($hourlyForecastApi, $outputTransformer, self::createStub(ClimateMeasurementRecorderInterface::class), new Coordinates(51.5, -0.18), $clock);

        self::assertSame([self::NOW], $dataProvider->getData($request));

        $clock->sleep(3600);

        self::assertSame([self::NOW + 3600], $dataProvider->getData($request));
    }

    /**
     * @throws Exception
     */
    public function testRecordsSelectedStepClimate(): void
    {
        $request = self::createStub(ServerRequestInterface::class);
        $now = self::NOW;

        $step = $this->createHourlyStep($now - 3600, 12.3, 81.0);
        $forecast = $this->createForecast([$step]);

        $hourlyForecastApi = self::createStub(HourlyForecastApiInterface::class);
        $hourlyForecastApi->method('getForecast')
            ->willReturn($forecast);

        $outputTransformer = self::createStub(OutputTransformerInterface::class);
        $outputTransformer->method('transform')
            ->willReturn(['test-output']);

        $climateMeasurementRecorder = self::createMock(ClimateMeasurementRecorderInterface::class);
        $climateMeasurementRecorder->expects(self::once())
            ->method('record')
            ->with(
                self::callback(
                    static function (MetOfficeWeather $reading): bool {
                        self::assertSame(12.3, $reading->getTemperature());
                        self::assertSame(81.0, $reading->getHumidity());
                        self::assertInstanceOf(DateTimeImmutable::class, $reading->getRecordedAt());

                        return true;
                    }
                )
            );

        $dataProvider = new DataProvider($hourlyForecastApi, $outputTransformer, $climateMeasurementRecorder, new Coordinates(51.5, -0.18), new MockClock('@'.self::NOW));

        self::assertSame(['test-output'], $dataProvider->getData($request));
    }

    /**
     * @throws Exception
     */
    public function testSelectsCurrentStep(): void
    {
        $request = self::createStub(ServerRequestInterface::class);
        $now = self::NOW;

        $olderPast = $this->createHourlyStep($now - 7200);
        $currentPast = $this->createHourlyStep($now - 3600);
        $future = $this->createHourlyStep($now + 3600);

        // Deliberately unsorted so the sort step is exercised.
        $forecast = $this->createForecast([$future, $olderPast, $currentPast]);

        $hourlyForecastApi = self::createMock(HourlyForecastApiInterface::class);
        $hourlyForecastApi->expects(self::once())
            ->method('getForecast')
            ->with(new Coordinates(51.5, -0.18))
            ->willReturn($forecast);

        $outputTransformer = self::createMock(OutputTransformerInterface::class);
        $outputTransformer->expects(self::once())
            ->method('transform')
            ->with($currentPast)
            ->willReturn(['test-output']);

        $dataProvider = new DataProvider($hourlyForecastApi, $outputTransformer, self::createStub(ClimateMeasurementRecorderInterface::class), new Coordinates(51.5, -0.18), new MockClock('@'.self::NOW));

        self::assertSame(['test-output'], $dataProvider->getData($request));
    }

    /**
     * @throws Exception
     */
    public function testThrowsWhenNoStepsAvailable(): void
    {
        $request = self::createStub(ServerRequestInterface::class);

        $forecast = $this->createForecast([]);

        $hourlyForecastApi = self::createStub(HourlyForecastApiInterface::class);
        $hourlyForecastApi->method('getForecast')
            ->willReturn($forecast);

        $outputTransformer = self::createStub(OutputTransformerInterface::class);

        $dataProvider = new DataProvider($hourlyForecastApi, $outputTransformer, self::createStub(ClimateMeasurementRecorderInterface::class), new Coordinates(51.5, -0.18), new MockClock('@'.self::NOW));

        $this->expectException(UserFriendlyException::class);
        $this->expectExceptionMessage(DataProviderInterface::ERROR_NO_FORECAST);

        $dataProvider->getData($request);
    }

    /**
     * @throws Exception
     */
    public function testThrowsWhenSelectedStepIsNotHourly(): void
    {
        $request = self::createStub(ServerRequestInterface::class);
        $now = self::NOW;

        $step = self::createStub(ForecastTimeStepInterface::class);
        $step->method('getTime')
            ->willReturn($now - 3600);

        $forecast = $this->createForecast([$step]);

        $hourlyForecastApi = self::createStub(HourlyForecastApiInterface::class);
        $hourlyForecastApi->method('getForecast')
            ->willReturn($forecast);

        $outputTransformer = self::createStub(OutputTransformerInterface::class);

        $dataProvider = new DataProvider($hourlyForecastApi, $outputTransformer, self::createStub(ClimateMeasurementRecorderInterface::class), new Coordinates(51.5, -0.18), new MockClock('@'.self::NOW));

        $this->expectException(UserFriendlyException::class);
        $this->expectExceptionMessage(DataProviderInterface::ERROR_NO_FORECAST);

        $dataProvider->getData($request);
    }

    /**
     * @param ForecastTimeStepInterface[] $timeSteps
     *
     * @throws Exception
     */
    private function createForecast(array $timeSteps): ForecastInterface
    {
        $forecast = self::createStub(ForecastInterface::class);
        $forecast->method('getTimeSteps')
            ->willReturn($timeSteps);

        return $forecast;
    }

    /**
     * @throws Exception
     */
    private function createHourlyStep(int $time, ?float $temperature = null, ?float $humidity = null): HourlyForecastTimeStepInterface
    {
        $step = self::createStub(HourlyForecastTimeStepInterface::class);
        $step->method('getTime')
            ->willReturn($time);
        $step->method('getScreenTemperature')
            ->willReturn($temperature);
        $step->method('getScreenRelativeHumidity')
            ->willReturn($humidity);

        return $step;
    }
}
