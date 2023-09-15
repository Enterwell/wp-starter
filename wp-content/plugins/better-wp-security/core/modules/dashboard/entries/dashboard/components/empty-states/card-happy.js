/**
 * Internal dependencies
 */
import { Happy as Icon } from '@ithemes/security-style-guide';

export default function CardHappy( { title, text } ) {
	return (
		<div className="itsec-empty-state-card itsec-empty-state-card--happy">
			{ title && <h3>{ title }</h3> }
			<Icon />
			{ text && <p>{ text }</p> }
		</div>
	);
}
