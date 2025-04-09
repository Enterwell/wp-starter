/**
 * External dependencies
 */
import { NavLink, useParams } from 'react-router-dom';

/**
 * WordPress dependencies
 */
import { createSlotFill } from '@wordpress/components';
import { Fragment } from '@wordpress/element';

/**
 * Solid dependencies
 */
import { SecondaryNavigation, SecondaryNavigationItem, ListItem, TextVariant } from '@ithemes/ui';

/**
 * Internal dependencies
 */
import { withNavigate } from '@ithemes/security-hocs';
import { useCurrentChildPages, usePages } from '../../page-registration';
import { StyledChildLink, StyledChildPages } from './styles';

export default function Navigation( { orientation, children } ) {
	const pages = usePages( { location: 'primary' } );
	const { root, page } = useParams();

	return (
		<SecondaryNavigation orientation={ orientation }>
			{ pages.map( ( item ) => (
				<Fragment key={ item.id }>
					<NavLink
						key={ item.id }
						to={ `/${ root }/${ item.id }` }
						component={ withNavigate( SecondaryNavigationItem ) }
					>
						{ item.title }
					</NavLink>
					{ item.id === page && <ChildPages /> }
				</Fragment>
			) ) }
			{ children }
		</SecondaryNavigation>
	);
}

function ChildPages() {
	const childPages = useCurrentChildPages();

	if ( ! childPages.length ) {
		return null;
	}

	return (
		<StyledChildPages gap={ 3 }>
			{ childPages.map( ( { title, to, id } ) => (
				<ListItem key={ id }>
					<NavLink
						to={ to }
						component={ withNavigate( StyledChildLink ) }
						as="a"
						text={ title }
						variant={ TextVariant.MUTED }
					/>
				</ListItem>
			) ) }
		</StyledChildPages>
	);
}

const { Fill: NavigationFill } = createSlotFill(
	'Navigation'
);

export { NavigationFill };
