<?php
namespace Ew\WpHelpers\Classes;

/**
 * Class Request_Validation_Result
 *
 * Helper class used in validation services to
 * validate if received REST request has all required params
 *
 * @package namespace Ew\WpHelpers;
 */
class Request_Validation_Result{

    /**
     * Flag if result is valid.
     *
     * @since 1.0.0
     *
     * @var bool
     */
    private $is_valid;

    /**
     * Array of validation messages.
     *
     * @since 1.0.0
     *
     * @var array
     */
    private $messages;

    /**
     * Request_Validation_Result constructor.
     */
    public function __construct()
    {
        $this->is_valid = true;
        $this->messages = [];
    }

    /**
     * Adds new validation message to
     * array of request validation messages.
     *
     * @since 1.0.0
     *
     * @param string $message
     */
    public function add_message($message){
        $this->messages[] = $message;
    }

	/**
	 * Adds error message - adds message and sets
	 * is valid flag to false.
	 *
	 * @since 1.0.1
	 *
	 * @param string $message
	 */
	public function add_error_message( $message ) {
		$this->messages[] = $message;
		$this->is_valid   = false;
	}

    /**
     * Returns if this result is valid.
     *
     * @since 1.0.0
     *
     * @return bool
     */
    public function is_valid(){
        return $this->is_valid;
    }

    /**
     * Sets is valid flag of this result.
     *
     * @since 1.0.0
     *
     * @param bool $is_valid
     */
    public function set_valid($is_valid){
        $this->is_valid = $this->is_valid && (bool)$is_valid;
    }

    /**
     * Gets all messages from validation result as one string.
     *
     * @since 1.0.0
     *
     * @return string
     */
    public function get_message(){
        return implode(', ', $this->messages);
    }

    /**
     * Gets all messages from validation result.
     *
     * @since 1.0.0
     *
     * @return string[]
     */
    public function get_messages(){
        return $this->messages;
    }

    /**
     * Merges another validation result with this one.
     * This operation combines is_valid flags from both requests and
     * messages from them.
     *
     * @since 1.0.0
     *
     * @param $result_to_merge    Request_Validation_Result
     */
    public function merge($result_to_merge){
    	// If result to merge is valid do nothing - everything is ok with it, there is no messages
        if($result_to_merge->is_valid()) return;

        // Set current result is valid flag.
        $this->set_valid($result_to_merge->is_valid());

        // Append messages from result to merge
        $this->messages = $this->messages + $result_to_merge->get_messages();
    }

}