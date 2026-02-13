<?php

namespace App\Helpers;

use Illuminate\Http\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Pagination\LengthAwarePaginator;


class GeneralResponse
{
    /**
     * SUCCESS RESPONSE
     */
    public static function success(
        mixed $data = null,
        ?LengthAwarePaginator $paginator = null,
        string $errorCode = '31', // Data retrieved successfully (default)
        int $statusCode = Response::HTTP_OK,
    ): JsonResponse {
        $result = [
            'errorCode' => $errorCode,
            'errorMessage' => self::errorMessage($errorCode),
            'data' => $data,
        ];

        if ($paginator !== null) {
            $result['meta'] = [
                'current_page' => $paginator->currentPage(),
                'per_page' => $paginator->perPage(),
                'total' => $paginator->total(),
                'last_page' => $paginator->lastPage(),
            ];
        }

        return response()->json([
            'statusCode' => $statusCode,
            'statusMessage' => self::statusMessage($statusCode),
            'statusDescription' => self::statusDescription($statusCode),
            'result' => $result,
        ], $statusCode);
    }

    /**
     * ERROR RESPONSE
     */
    public static function error(
        string $errorCode,
        int $statusCode,
        ?string $statusDescription = null
    ): JsonResponse {
        return response()->json([
            'statusCode' => $statusCode,
            'statusMessage' => self::statusMessage($statusCode),
            'statusDescription' => $statusDescription ?? self::statusDescription($statusCode),
            'result' => [
                'errorCode' => $errorCode,
                'errorMessage' => self::errorMessage($errorCode),
            ],
        ], $statusCode);
    }

    /**
     * ERROR MESSAGE MAPPING
     * @return string
     */
    private static function errorMessage(string $errorCode): string
    {
        return match ($errorCode) {

            // AUTH – SUCCESS
            '01' => 'User register successfully',
            '02' => 'User logout successfully',
            '03' => 'User login successfully',
            '04' => 'Token refreshed successfully',
            '05' => 'OTP verified successfully',
            '06' => 'Password reset successfully',
            '07' => 'Account activated successfully',
            '08' => 'Session validated successfully',
            '09' => 'Authentication successful',

            // AUTH – ERROR (10–19)
            '10' => 'Invalid credentials',
            '11' => 'Token expired',
            '12' => 'Token invalid',
            '13' => 'Token not provided',
            '14' => 'Unauthorized access',
            '15' => 'Permission denied',
            '16' => 'Account is inactive',
            '17' => 'Account is locked',
            '18' => 'Password expired',
            '19' => 'Too many login attempts',

            // VALIDATION – ERROR (20–29)
            '20' => 'Validation failed',
            '21' => 'Required field missing',
            '22' => 'Invalid request format',
            '23' => 'Invalid data type',
            '24' => 'Invalid parameter value',
            '25' => 'Invalid email format',
            '26' => 'Invalid phone number',
            '27' => 'Password mismatch',
            '28' => 'Invalid date format',
            '29' => 'Invalid request header',

            // DATA / CRUD – SUCCESS
            '30' => 'Data created successfully',
            '31' => 'Data retrieved successfully',
            '32' => 'Data updated successfully',
            '33' => 'Data deleted successfully',
            '34' => 'Data processed successfully',

            // DATA / BUSINESS – ERROR
            '40' => 'Data not found',
            '41' => 'Duplicate data',
            '42' => 'Data already exists',
            '43' => 'Failed to create data',
            '44' => 'Failed to update data',
            '45' => 'Failed to delete data',

            default => 'Unknown error occurred',
        };
    }

    protected static function statusMessage(int $statusCode): string
    {
        return match ($statusCode) {
            200 => 'OK',
            201 => 'Created',
            202 => 'Accepted',
            204 => 'No Content',
            400 => 'Bad Request',
            401 => 'Unauthorized',
            403 => 'Forbidden',
            404 => 'Not Found',
            405 => 'Method Not Allowed',
            409 => 'Conflict',
            422 => 'Unprocessable Entity',
            429 => 'Too Many Requests',
            500 => 'Internal Server Error',
            502 => 'Bad Gateway',
            503 => 'Service Unavailable',
            504 => 'Gateway Timeout',
            default => 'Bad Request',
        };
    }

    protected static function statusDescription(int $statusCode): string
    {
        return match ($statusCode) {
            200 => 'Request processed successfully',
            201 => 'Resource created successfully',
            202 => 'Request accepted and is being processed asynchronously',
            204 => 'Request successful with no response body',
            400 => 'Invalid request parameters',
            401 => 'Authentication required or failed token',
            403 => 'User does not have permission to access this resource',
            404 => 'The requested resource was not found on the server',
            405 => 'HTTP method is not allowed for this endpoint',
            409 => 'Data conflict occurred',
            422 => 'Validation failed for the given request',
            429 => 'Rate limit exceeded',
            500 => 'Unexpected error occurred on the server',
            502 => 'Invalid response from upstream server',
            503 => 'Server temporarily unavailable',
            504 => 'Upstream server timeout',
            default => 'Invalid request parameters',
        };
    }
}
