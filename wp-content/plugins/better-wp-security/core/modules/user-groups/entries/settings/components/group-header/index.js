/**
 * Internal dependencies
 */
import './style.scss';

export function GroupHeader( { label, children } ) {
	return (
		<div className="itsec-user-group-header">
			<h4 className="itsec-user-group-header__label">{ label }</h4>
			{ children }
		</div>
	);
}

export SingleGroupHeader from './single';
export NewGroupHeader from './new';
export MultiGroupHeader from './multi';
