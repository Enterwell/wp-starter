/**
 * External dependencies
 */
import { isEmpty, map } from 'lodash';

/**
 * WordPress dependencies
 */
import { Dropdown, NavigableMenu, IconButton, Button, Spinner } from '@wordpress/components';
import { __ } from '@wordpress/i18n';
import { compose } from '@wordpress/compose';
import { withDispatch, withSelect } from '@wordpress/data';

/**
 * Internal dependencies
 */
import './style.scss';

function NoticeActions( { notice, doAction, inProgress } ) {
	const actions = [];

	for ( const slug in notice.actions ) {
		if ( ! notice.actions.hasOwnProperty( slug ) ) {
			continue;
		}

		const action = notice.actions[ slug ];

		if ( action.style === 'close' ) {
			actions.push( (
				<li key={ slug } >
					<IconButton icon="dismiss" label={ action.title } onClick={ () => doAction( notice.id, slug ) } isBusy={ inProgress.includes( slug ) } />
				</li>
			) );
		}
	}

	const generic = getGenericActions( notice );

	if ( ! isEmpty( generic ) ) {
		actions.push( (
			<li key="more">
				<Dropdown
					position="bottom right"
					className="itsec-admin-notice-list-actions__more-menu"
					contentClassName="itsec-admin-notice-list-actions__more-menu-items"
					renderToggle={ ( { isOpen, onToggle } ) => (
						<IconButton
							icon="ellipsis"
							label={ __( 'More Actions', 'better-wp-security' ) }
							onClick={ onToggle }
							aria-haspopup={ true }
							aria-expanded={ isOpen }
						/>
					) }
					renderContent={ () => (
						<NavigableMenu role="menu">
							{ map( generic, ( action, slug ) => (
								action.uri ?
									<Button key={ slug } href={ action.uri }>{ action.title }</Button> :
									<Button key={ slug } onClick={ () => doAction( notice.id, slug ) } disabled={ inProgress.includes( slug ) }>
										{ action.title }
										{ inProgress.includes( slug ) && <Spinner /> }
									</Button>
							) ) }
						</NavigableMenu>
					) }
				/>

			</li>
		) );
	}

	return <ul className="itsec-admin-notice-list-actions">{ actions }</ul>;
}

export default compose( [
	withDispatch( ( dispatch ) => ( {
		doAction: dispatch( 'ithemes-security/admin-notices' ).doNoticeAction,
	} ) ),
	withSelect( ( select, ownProps ) => ( {
		inProgress: select( 'ithemes-security/admin-notices' ).getInProgressActions( ownProps.notice.id ),
	} ) ),
] )( NoticeActions );

function getGenericActions( notice ) {
	const generic = {};

	for ( const slug in notice.actions ) {
		if ( ! notice.actions.hasOwnProperty( slug ) ) {
			continue;
		}

		const action = notice.actions[ slug ];

		if ( action.style === 'close' ) {
			continue;
		}

		if ( action.style === 'primary' ) {
			continue;
		}

		generic[ slug ] = action;
	}

	return generic;
}
