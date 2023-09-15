/**
 * External dependencies
 */
import { NavLink, useParams } from 'react-router-dom';

/**
 * WordPress dependencies
 */
import { __ } from '@wordpress/i18n';
import { Dashicon } from '@wordpress/components';

/**
 * Internal dependencies
 */
import { usePages } from '../../page-registration';
import './style.scss';

export default function AdvancedNavigation() {
	const pages = usePages( { location: 'advanced' } );
	const { root } = useParams();

	return (
		<ul className="itsec-settings-advanced-nav">
			<li>
				<NavLink to={ `/${ root }/configure/advanced` }>
					<Dashicon icon="privacy" />
					<span className="itsec-settings-advanced-nav__item-title">
						{ __( 'Advanced', 'better-wp-security' ) }
					</span>
				</NavLink>
			</li>
			{ pages.map( ( item ) => {
				return (
					<li key={ item.id }>
						<NavLink to={ `/${ root }/${ item.id }` }>
							<Dashicon icon={ item.icon } />
							<span className="itsec-settings-advanced-nav__item-title">
								{ item.title }
							</span>
						</NavLink>
					</li>
				);
			} ) }
		</ul>
	);
}
