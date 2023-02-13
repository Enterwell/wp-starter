/**
 * External dependencies
 */
import classnames from 'classnames';

/**
 * Internal dependencies
 */
import './style.scss';

TabBody.Row = function( { name, children } ) {
	return (
		<div className={ `itsec-user-groups-group-tab__row itsec-user-groups-group-tab__row--${ name }` }>
			{ children }
		</div>
	);
};

export default function TabBody( { name, isLoading, children } ) {
	const className = classnames( 'itsec-user-groups-group-tab', {
		[ `itsec-user-groups-group-tab--${ name }` ]: name,
		'itsec-user-groups-group-tab--is-loading': isLoading,
	} );

	return (
		<div className={ className }>
			{ children }
		</div>
	);
}
