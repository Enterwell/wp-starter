<?php

namespace iThemesSecurity\Lib\Site_Types;

final class Controller {

	/** @var Site_Type|null */
	private $selected_site_type;

	/** @var Answer_Details[] */
	private $previous = [];

	/**
	 * Controller constructor.
	 *
	 * @param Site_Type|null   $selected_site_type
	 * @param Answer_Details[] $previous
	 */
	public function __construct( Site_Type $selected_site_type = null, array $previous = [] ) {
		$this->selected_site_type = $selected_site_type;

		foreach ( $previous as $details ) {
			$this->previous[ $details->get_question()->get_id() ] = $details;
		}
	}

	/**
	 * Gets the selected site type.
	 *
	 * @return Site_Type|null
	 */
	public function get_selected_site_type() {
		return $this->selected_site_type;
	}

	/**
	 * Selects a Site Type.
	 *
	 * @param Site_Type $site_type
	 */
	public function select_site_type( Site_Type $site_type ) {
		$this->selected_site_type = $site_type;
		$this->previous           = [];
	}

	/**
	 * Gets the next question to be asked.
	 *
	 * @return Question|null
	 */
	public function get_next_question() {
		if ( ! $site_type = $this->selected_site_type ) {
			return null;
		}

		foreach ( $site_type->get_questions() as $question ) {
			if ( isset( $this->previous[ $question->get_id() ] ) ) {
				continue;
			}

			if ( $question instanceof Has_Prerequisites && ! $this->has_met_prerequisites( $question ) ) {
				continue;
			}

			return $question;
		}

		return null;
	}

	/**
	 * Answers a question.
	 *
	 * @param Question $question The question being answered.
	 * @param mixed    $answer   The user provided answer.
	 *
	 * @return null|\WP_Error An error if the answer was invalid. Null otherwise.
	 */
	public function answer( Question $question, $answer ) {
		$schema = $question->get_answer_schema();

		$valid = rest_validate_value_from_schema( $answer, $schema, $question->get_id() );

		if ( is_wp_error( $valid ) ) {
			return $valid;
		}

		$sanitized = rest_sanitize_value_from_schema( $answer, $schema, $question->get_id() );

		if ( is_wp_error( $sanitized ) ) {
			return $sanitized;
		}

		if ( $question instanceof Responds ) {
			$handler = new Answer_Handler( $question, $sanitized, ...array_values( $this->previous ) );
			$question->respond( $handler );
			$previous = Answered_Question::from_answer_details( $handler );
		} else {
			$previous = new Answered_Question( $question, $answer );
		}

		$this->previous[ $question->get_id() ] = $previous;

		return null;
	}

	/**
	 * Gets the list of previously answered questions.
	 *
	 * @return Answer_Details[]
	 */
	public function get_previous(): array {
		return array_values( $this->previous );
	}

	/**
	 * Checks if the question has its prerequisites met.
	 *
	 * @param Has_Prerequisites $has_prerequisites
	 *
	 * @return bool
	 */
	private function has_met_prerequisites( Has_Prerequisites $has_prerequisites ): bool {
		foreach ( $has_prerequisites->get_prerequisites() as $question_id => $schema ) {
			if ( ! isset( $this->previous[ $question_id ] ) ) {
				return false;
			}

			$answer = $this->previous[ $question_id ]->get_answer();

			if ( is_wp_error( rest_validate_value_from_schema( $answer, $schema ) ) ) {
				return false;
			}

			if ( is_wp_error( rest_sanitize_value_from_schema( $answer, $schema ) ) ) {
				return false;
			}
		}

		return true;
	}
}
