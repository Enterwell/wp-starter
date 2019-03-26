<?php
/**
 * Created by PhpStorm.
 * User: Matej
 * Date: 21.4.2017.
 * Time: 11:00
 */
namespace Ew\WpHelpers\Exceptions;

/**
 * Interface for exception handler class.
 *
 * Codes handler is clas that maps every exception code to a HTTP code
 * and message.
 * This way for each exception we can have its own description to be returned
 * to the API user.
 *
 * Interface IException_Codes_Handler
 * @package Ew\WpHelpers
 */
interface IException_Codes_Handler{

    /**
     * Gets http code from exception code.
     *
     * @param   int     $code
     *
     * @return  int
     */
    function get_exception_code_http_code($code);

    /**
     * Gets exception description from exception code.
     *
     * @param   int     $code
     *
     * @return  string
     */
    function get_exception_code_description($code);

}