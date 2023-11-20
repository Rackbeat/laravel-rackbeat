<?php
namespace Rackbeat\Utils;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Exception\ServerException;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\MessageFormatter;
use GuzzleHttp\Middleware;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Http;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use Rackbeat\Exceptions\RackbeatClientException;
use Rackbeat\Exceptions\RackbeatRequestException;
use Spatie\GuzzleRateLimiterMiddleware\RateLimiterMiddleware;

class Request
{
    /**
     * @var \GuzzleHttp\Client
     */
    public $client;

	public array $options = [];
	public array $headers = [];
	public bool $enableLog = false;
	public ?string $logPath = null;

    /**
     * Request constructor.
     *
     * @param null  $token
     * @param array $options
     * @param array $headers
     * @param bool  $enable_log
     * @param null  $log_path
     */
    public function __construct($token = null, array $options = [], array $headers = [], bool $enable_log = false, $log_path = null)
    {
		$this->options = $options;
	    $this->headers = array_merge( $headers, [
		    'User-Agent'    => Config::get( 'rackbeat.user_agent' ),
		    'Accept'        => 'application/json; charset=utf8',
		    'Content-Type'  => 'application/json; charset=utf8',
		    'Authorization' => 'Bearer ' . ( $token ?? Config::get( 'rackbeat.token' ) ),
	    ] );

	    $this->enableLog = $enable_log;
	    $this->logPath   = $log_path;

		// For legacy purposes (in case $client is used in integration directly..)
		$this->client = $this->getClient()->buildClient();
    }

    /**
     * @param $callback
     *
     * @return mixed
     * @throws \Rackbeat\Exceptions\RackbeatClientException
     * @throws \Rackbeat\Exceptions\RackbeatRequestException
     */
    public function handleWithExceptions( $callback )
    {
        try {
            return $callback();
        } catch ( RequestException $exception ) {
            $message = $exception->getMessage();
            $code    = $exception->getCode();

            if ( $exception->response ) {
                $message = (string) $exception->response->body();
                $code    = $exception->response->status();
            }

	        throw new RackbeatRequestException( $message, $code );
        } catch (\Exception $exception) {
            $message = $exception->getMessage();
            $code = $exception->getCode();

            throw new RackbeatClientException($message, $code);
        }
    }

    public function createThrottleMiddleware()
    {
        return RateLimiterMiddleware::perMinute(Config::get('rackbeat.api_limit', 480), new RateLimiterStore());
    }

    public function createLoggerMiddleware($log_path = null)
    {
        $log_path = $log_path ?? 'guzzle-logger.log';

        $logger = new Logger('GuzzleCustomLogger');
        $location = storage_path('logs/' . $log_path);
        $logger->pushHandler(new StreamHandler($location, Logger::DEBUG));

        $format =
            '{method} {uri} - {target} - {hostname} HTTP/{version} .......... ' .
            'REQUEST HEADERS: {req_headers} ....... REQUEST: {req_body} ' .
            '......... RESPONSE HEADERS: {res_headers} ........... RESPONSE: {code} - {res_body}';
        return Middleware::log(
            $logger,
            new MessageFormatter($format)
        );
    }

	public function getClient(): PendingRequest
	{
		return Http::withHeaders( $this->headers )
		           ->baseUrl( Config::get( 'rackbeat.base_uri' ) )
		           ->withMiddleware( $this->createThrottleMiddleware() )
		           ->withOptions( $this->options )
				   ->retry( 3, 5 * 1000, function ( \Throwable $throwable ) { return $throwable->getCode() >= 500 || $throwable->getCode() === 429; } )
		           ->when( $this->enableLog, function ( PendingRequest $request ) {
			           return $request->withMiddleware( $this->createLoggerMiddleware( $this->logPath ) );
		           } );
	}
}
