<?php

namespace iThemesSecurity\User_Groups;

final class Default_Matcher implements Matcher {

	/** @var Matchables_Source */
	private $source;

	/**
	 * Default_Matcher constructor.
	 *
	 * @param Matchables_Source $source
	 */
	public function __construct( Matchables_Source $source ) { $this->source = $source; }

	public function matches( Match_Target $target, $groups ) {
		foreach ( $groups as $group ) {
			try {
				$matchable = $this->source->find( $group );
			} catch ( Matchable_Not_Found $e ) {
				continue;
			}

			if ( $matchable->matches( $target ) ) {
				return true;
			}
		}

		return false;
	}
}

