/**
 * External dependencies
 */
import { isNumber, startsWith } from 'lodash';
import classnames from 'classnames';

/**
 * Internal dependencies
 */
import './style.scss';

export default function Spinner( { size, color, paused } ) {
	const style = {};

	if ( size ) {
		style[ '--itsec-size' ] = isNumber( size ) ? `${ size }px` : size;
	}

	if ( color ) {
		style[ '--itsec-color' ] = startsWith( color, '--' )
			? `var(${ color })`
			: color;
	}

	return (
		<div
			style={ style }
			className={ classnames( 'itsec-spinner', {
				'itsec-spinner--paused': paused,
			} ) }
		>
			<div />
			<div />
		</div>
	);
}
