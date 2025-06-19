<?php
namespace HostCloud\Core;

class ErrorHandler {
    public static function handleException(\Throwable $e): void {
        // Log the error
        error_log('[' . date('Y-m-d H:i:s') . '] ' . 
                 $e->getMessage() . 
                 ' in ' . $e->getFile() . 
                 ' on line ' . $e->getLine() . 
                 '\nStack trace:\n' . $e->getTraceAsString());
        
        // Don't send response if headers already sent
        if (headers_sent()) {
            return;
        }
        
        // Set appropriate headers
        header('Content-Type: application/json');
        
        // Set HTTP status code
        $statusCode = $e->getCode() >= 400 && $e->getCode() < 600 ? $e->getCode() : 500;
        http_response_code($statusCode);
        
        // Prepare error response
        $response = [
            'error' => [
                'code' => $statusCode,
                'message' => 'An error occurred',
                'details' => $e->getMessage()
            ]
        ];
        
        // In development, include more details
        if (ini_get('display_errors')) {
            $response['error']['file'] = $e->getFile();
            $response['error']['line'] = $e->getLine();
            $response['error']['trace'] = $e->getTrace();
        }
        
        echo json_encode($response, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
        exit;
    }
    
    public static function register(): void {
        // Set exception handler
        set_exception_handler([self::class, 'handleException']);
        
        // Convert errors to ErrorException
        set_error_handler(function($errno, $errstr, $errfile, $errline) {
            if (!(error_reporting() & $errno)) {
                return false;
            }
            throw new \ErrorException($errstr, 0, $errno, $errfile, $errline);
        });
        
        // Set shutdown function to catch fatal errors
        register_shutdown_function(function() {
            $error = error_get_last();
            if ($error !== null && in_array($error['type'], [E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR])) {
                $exception = new \ErrorException(
                    $error['message'], 0, $error['type'], $error['file'], $error['line']
                );
                self::handleException($exception);
            }
        });
    }
}
