<?php

namespace Coinbase\Wallet\Exception;

use Coinbase\Wallet\Enum\ErrorCode;
use Coinbase\Wallet\Value\Error;
use GuzzleHttp\Exception\RequestException;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

class HttpException extends RuntimeException
{
    /** @var Error[] */
    private $errors;
    private $request;
    private $response;

    /**
     * Wraps an API exception in the appropriate domain exception.
     *
     * @param RequestException $e The API exception
     *
     * @return HttpException
     */
    public static function wrap(RequestException $e)
    {
        $response = $e->getResponse();

        if ($errors = self::errors($response)) {
            $class = self::exceptionClass($response, $errors[0]);
            $message = implode(', ', array_map('strval', $errors));
        } else {
            $class = self::exceptionClass($response);
            $message = $e->getMessage();
        }

        return new $class($message, $errors, $e->getRequest(), $response, $e);
    }

    public function __construct($message, array $errors, RequestInterface $request, ResponseInterface $response, \Exception $previous)
    {
        parent::__construct($message, 0, $previous);

        $this->errors = $errors;
        $this->request = $request;
        $this->response = $response;
    }

    public function getErrors()
    {
        return $this->errors;
    }

    public function getError()
    {
        if (isset($this->errors[0])) {
            return $this->errors[0];
        }
    }

    public function getRequest()
    {
        return $this->request;
    }

    public function getResponse()
    {
        return $this->response;
    }

    public function getStatusCode()
    {
        return $this->response->getStatusCode();
    }

    /** @return Error[] */
    private static function errors(ResponseInterface $response = null)
    {
        $data = $response ? json_decode($response->getBody(), true) : null;

        if (isset($data['errors'])) {
            // api errors
            $map = function(array $e) { return new Error($e['id'], $e['message']); };
            $errors = array_map($map, $data['errors']);
        } elseif (isset($data['error'])) {
            // oauth error
            $errors = [
                new Error($data['error'], $data['error_description']),
            ];
        } else {
            // no errors
            $errors = [];
        }

        return $errors;
    }

    private static function exceptionClass(ResponseInterface $response, Error $error = null)
    {
        if ($error) {
            switch ($error->getId()) {
                case ErrorCode::PARAM_REQUIRED:
                    return ParamRequiredException::class;
                case ErrorCode::INVALID_REQUEST:
                    return InvalidRequestException::class;
                case ErrorCode::PERSONAL_DETAILS_REQUIRED:
                    return PersonalDetailsRequiredException::class;
                case ErrorCode::AUTHENTICATION_ERROR:
                    return AuthenticationException::class;
                case ErrorCode::UNVERIFIED_EMAIL:
                    return UnverifiedEmailException::class;
                case ErrorCode::INVALID_TOKEN:
                    return InvalidTokenException::class;
                case ErrorCode::REVOKED_TOKEN:
                    return RevokedTokenException::class;
                case ErrorCode::EXPIRED_TOKEN:
                    return ExpiredTokenException::class;
            }
        }

        switch ($response->getStatusCode()) {
            case 400:
                return BadRequestException::class;
            case 401:
                return UnauthorizedException::class;
            case 402:
                return TwoFactorRequiredException::class;
            case 403:
                return InvalidScopeException::class;
            case 404:
                return NotFoundException::class;
            case 422:
                return ValidationException::class;
            case 429:
                return RateLimitException::class;
            case 500:
                return InternalServerException::class;
            case 503:
                return ServiceUnavailableException::class;
            default:
                return HttpException::class;
        }
    }
}
