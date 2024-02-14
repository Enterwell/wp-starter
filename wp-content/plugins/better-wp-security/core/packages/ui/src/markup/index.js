/**
 * External dependencies
 */
import { Markup } from 'interweave';
import { Link } from 'react-router-dom';

export default function MarkupComponent( { transform, ...rest } ) {
	return (
		<Markup
			{ ...rest }
			transform={ ( node, children ) => {
				if ( transform ) {
					const transformed = transform( node, children );

					if ( transformed !== undefined ) {
						return transformed;
					}
				}

				if (
					node.tagName.toLowerCase() === 'a' &&
					node.dataset.itsecPath &&
					! rest.noHtml
				) {
					return (
						<Link to={ node.dataset.itsecPath }>{ children }</Link>
					);
				}
			} }
		/>
	);
}
