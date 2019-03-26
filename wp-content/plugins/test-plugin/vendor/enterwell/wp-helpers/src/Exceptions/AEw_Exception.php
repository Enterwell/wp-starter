<?php

// Enterwell namespace
namespace Ew\WpHelpers\Exceptions;
/**
 * Abstract exception class.
 *
 * Class used to implement custom exception handling in
 * controllers.
 * This exception has a reference to custom exception messages
 * handler.
 * Messages handler is class that returns custom message for each exception code.
 * This way we can return multiple error descriptions based on exceptions thrown
 * and doing so we can inform API users about the exceptions.
 *
 *
 * @since       1.0.0
 *
 * @package     Lunch
 * @subpackage  Lunch/classes
 * @author      Matej Bosnjak <matej.bosnjak@enterwell.net>
 */
abstract class AEw_Exception extends \Exception implements \JsonSerializable
{

    /**
     * Exception code.
     *
     * @since 1.0.0
     * @var int
     */
    private $exception_code;

    /**
     * Exception description.
     *
     * @since 1.0.0
     * @var string
     */
    private $exception_description;

    /**
     * Handler for exception codes.
     *
     * @since   1.0.0
     *
     * @var IException_Codes_Handler
     */
    private $exception_codes_handler;

    /**
     * Lunch_Exception constructor.
     *
     * @since   1.0.0
     *
     * @param string $message
     * @param int $code
     * @param \Exception|null $previous
     *
     * @throws \Exception
     */
    public function __construct($message = "", $code = 0, \Exception $previous = null)
    {
        parent::__construct($message, $code, $previous);

        // Sets code and exception description
        $this->exception_code = intval($code);
        $this->exception_description = $message;

        // Sets code handler
        $this->set_exception_codes_handler();

        if(empty($this->exception_codes_handler) || !($this->exception_codes_handler instanceof IException_Codes_Handler))
            throw new \Exception("Exception codes handler is not initialized. Exception is not valid!");
    }

    /**
     * Sets the handler for exception codes.
     *
     * @since   1.0.0
     */
    abstract function set_exception_codes_handler();

    /**
     * Gets exception http code.
     *
     * @since   1.0.0
     *
     * @return int
     */
    public function get_exception_http_code(){
        return $this->exception_codes_handler->get_exception_code_http_code($this->code);
    }

    /**
     * Gets exception data representation.
     *
     * @since   1.0.0
     *
     * @return array
     */
    public function jsonSerialize(){
        return [
            'code' => $this->exception_code,
            'message' => $this->exception_codes_handler->get_exception_code_description($this->code),
            'description' => $this->exception_description
        ];
    }
}

