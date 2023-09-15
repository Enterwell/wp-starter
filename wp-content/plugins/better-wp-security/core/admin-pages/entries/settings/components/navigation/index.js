/**
 * External dependencies
 */
import classnames from 'classnames';
import { Link, NavLink, useParams } from 'react-router-dom';

/**
 * WordPress dependencies
 */
import { createSlotFill, Dashicon } from '@wordpress/components';
import { Children } from '@wordpress/element';

/**
 * Internal dependencies
 */
import { useCurrentChildPages, usePages } from '../../page-registration';
import { useChildPath } from '../../utils';
import './style.scss';

export default function Navigation( {
	guided = false,
	allowBack = false,
	allowForward = false,
	children,
} ) {
	const pages = usePages( { location: 'primary' } );
	const { root, page: active } = useParams();
	const childPath = useChildPath();

	const activeIndex = pages.findIndex( ( item ) => item.id === active );

	return (
		<nav>
			<ul
				className={ classnames( 'itsec-nav', {
					'itsec-nav--guided': guided,
				} ) }
			>
				{ pages.map( ( item, i ) => {
					let asLink = ! guided;

					if ( allowBack && i <= activeIndex ) {
						asLink = true;
					} else if ( allowForward && i >= activeIndex ) {
						asLink = true;
					}

					let isActive = active === item.id;

					if (
						isActive &&
						item.ignore &&
						item.ignore.find( ( path ) =>
							childPath.startsWith( path )
						)
					) {
						isActive = false;
					}

					const icon = guided
						? 'yes-alt'
						: item.icon || 'admin-generic';

					return (
						<li
							key={ item.id }
							className={ classnames( 'itsec-nav__item', {
								'itsec-nav__item--active': isActive,
								'itsec-nav__item--completed':
									guided && i < activeIndex,
							} ) }
						>
							<span className="itsec-nav__item-title">
								{ ! asLink ? (
									<>
										<Dashicon icon={ icon } />
										<span className="itsec-nav__item-title-text">
											{ item.title }
										</span>
									</>
								) : (
									<Link to={ `/${ root }/${ item.id }` }>
										<Dashicon icon={ icon } />
										<span className="itsec-nav__item-title-text">
											{ item.title }
										</span>
									</Link>
								) }
							</span>

							{ ! isActive ? null : (
								<>
									{ children }
									<ChildPages item={ item } />
								</>
							) }
						</li>
					);
				} ) }
			</ul>
		</nav>
	);
}

function ChildPages( { item } ) {
	const childPages = useCurrentChildPages();

	return (
		<NavigationSlot fillProps={ { item } }>
			{ ( fills ) => {
				if (
					Children.count( fills ) === 0 &&
					childPages.length === 0
				) {
					return null;
				}

				return (
					<ul className="itsec-nav__children">
						{ childPages.map( ( { to, title, ...rest } ) => (
							<li key={ to }>
								<NavLink to={ to } { ...rest }>
									{ title }
								</NavLink>
							</li>
						) ) }

						{ Children.map( fills, ( child, j ) => (
							<li key={ j }>{ child }</li>
						) ) }
					</ul>
				);
			} }
		</NavigationSlot>
	);
}

const { Fill: NavigationFill, Slot: NavigationSlot } = createSlotFill(
	'Navigation'
);

export { NavigationFill };
