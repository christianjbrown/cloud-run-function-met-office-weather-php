<?php

declare(strict_types=1);

date_default_timezone_set('UTC');

use ChristianBrown\Database\ClimateMeasurementRecorder;
use ChristianBrown\Database\EntityManagerFactory;
use ChristianBrown\CloudRunFunction\CloudRunFunction;
use ChristianBrown\CloudRunFunction\CloudRunFunctionInterface;
use ChristianBrown\CloudRunFunction\FunctionConfigTransformer;
use ChristianBrown\MetOffice\Coordinates;
use ChristianBrown\MetOffice\MetOffice;
use ChristianBrown\MetOfficeWeather\CloudRunFunctionFactoryInterface;
use ChristianBrown\MetOfficeWeather\ConfigInterface;
use ChristianBrown\MetOfficeWeather\ConfigTransformer;
use ChristianBrown\MetOfficeWeather\DataProvider;
use ChristianBrown\MetOfficeWeather\Field\DewPointFieldMapper;
use ChristianBrown\MetOfficeWeather\Field\FeelsLikeFieldMapper;
use ChristianBrown\MetOfficeWeather\Field\HumidityFieldMapper;
use ChristianBrown\MetOfficeWeather\Field\PrecipitationFieldMapper;
use ChristianBrown\MetOfficeWeather\Field\PressureFieldMapper;
use ChristianBrown\MetOfficeWeather\Field\TemperatureFieldMapper;
use ChristianBrown\MetOfficeWeather\Field\UvIndexFieldMapper;
use ChristianBrown\MetOfficeWeather\Field\VisibilityFieldMapper;
use ChristianBrown\MetOfficeWeather\Field\WeatherTypeFieldMapper;
use ChristianBrown\MetOfficeWeather\Field\WeatherTypeNameFieldMapper;
use ChristianBrown\MetOfficeWeather\Field\WindDirectionFieldMapper;
use ChristianBrown\MetOfficeWeather\Field\WindGustFieldMapper;
use ChristianBrown\MetOfficeWeather\Field\WindSpeedFieldMapper;
use ChristianBrown\MetOfficeWeather\OutputTransformer;
use ChristianBrown\MetOfficeWeather\RequestHandler;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Symfony\Component\Clock\NativeClock;

function run(ServerRequestInterface $request): ResponseInterface
{
    $env = getenv();
    $functionConfigTransformer = new FunctionConfigTransformer();
    $configTransformer = new ConfigTransformer($functionConfigTransformer);
    $config = $configTransformer->transform($env);

    // The MetOffice client construction happens inside the factory (not here), so
    // that RequestHandler::handle() wraps it in the same try/catch as
    // CloudRunFunction::run() and a failure there returns the framework's JSON error
    // envelope rather than escaping as a bare 500.
    $cloudFunctionFactory = new class ($config) implements CloudRunFunctionFactoryInterface {
        private ConfigInterface $config;

        public function __construct(ConfigInterface $config)
        {
            $this->config = $config;
        }

        public function create(): CloudRunFunctionInterface
        {
            $config = $this->config;

            $metOffice = new MetOffice();
            $hourlyApi = $metOffice->siteSpecific($config->getApiKey())->getHourlyForecastApi();

            // One mapper per output field; the order here is the key order of the
            // JSON response. A new field is a new mapper class plus one line here.
            $outputTransformer = new OutputTransformer([
                new TemperatureFieldMapper(),
                new FeelsLikeFieldMapper(),
                new HumidityFieldMapper(),
                new PrecipitationFieldMapper(),
                new UvIndexFieldMapper(),
                new VisibilityFieldMapper(),
                new PressureFieldMapper(),
                new DewPointFieldMapper(),
                new WindSpeedFieldMapper(),
                new WindGustFieldMapper(),
                new WindDirectionFieldMapper(),
                new WeatherTypeFieldMapper(),
                new WeatherTypeNameFieldMapper(),
            ]);

            // Record each observed reading to the shared climate-history table.
            // The write is best-effort — DataProvider isolates it so a failure
            // never disturbs the response.
            $entityManager = (new EntityManagerFactory($config->getDatabaseDsn()))->getEntityManager();
            $climateMeasurementRecorder = new ClimateMeasurementRecorder($entityManager);

            $coordinates = new Coordinates($config->getLatitude(), $config->getLongitude());
            $dataProvider = new DataProvider($hourlyApi, $outputTransformer, $climateMeasurementRecorder, $coordinates, new NativeClock());

            return new CloudRunFunction($dataProvider, $config->getFunctionConfig());
        }
    };

    $requestHandler = new RequestHandler($cloudFunctionFactory, $config->getFunctionConfig());

    return $requestHandler->handle($request);
}
