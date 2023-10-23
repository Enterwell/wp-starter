/**
 * External dependencies
 */
import { get, flatten } from 'lodash';

/**
 * WordPress dependencies
 */
import { compose } from '@wordpress/compose';
import { dispatch, withSelect } from '@wordpress/data';

/**
 * iThemes dependencies
 */
import { Button } from '@ithemes/ui';

/**
 * Internal dependencies
 */
import { FlexSpacer } from '@ithemes/security-components';
import Footer from './';

function FooterSchemaActions( { card, callingRpcs, onComplete, children } ) {
	const onClick = async ( href ) => {
		const response = await dispatch(
			'ithemes-security/dashboard'
		).callDashboardCardRpc( card.id, href );

		if ( onComplete ) {
			onComplete( href, response );
		}
	};

	const rpcs = get( card, [ '_links', 'ithemes-security:rpc' ], [] ),
		links = flatten( Object.values( get( card, '_links', {} ) ) ).filter(
			( link ) => link.media === 'text/html'
		);

	if ( ! rpcs.length && ! links.length && ! children ) {
		return null;
	}

	return (
		<Footer>
			{ links.map( ( link ) => (
				<span key={ link.href }>
					<Button variant="link" href={ link.href }>
						{ link.title }
					</Button>
				</span>
			) ) }
			<FlexSpacer />
			{ rpcs.map( ( link, i ) => (
				<span key={ link.href }>
					<Button
						variant={ i === 0 ? 'primary' : 'secondary' }
						onClick={ () =>
							! callingRpcs.includes( link.href ) &&
							onClick( link.href )
						}
						isBusy={ callingRpcs.includes( link.href ) }
						aria-disabled={ callingRpcs.includes( link.href ) }
					>
						{ link.title }
					</Button>
				</span>
			) ) }
			{ children }
		</Footer>
	);
}

export default compose( [
	withSelect( ( select, props ) => ( {
		callingRpcs: select(
			'ithemes-security/dashboard'
		).getCallingDashboardCardRpcs( props.card.id ),
	} ) ),
] )( FooterSchemaActions );
