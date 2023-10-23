/**
 * External dependencies
 */
import { isEmpty, map } from 'lodash';

/**
 * WordPress dependencies
 */
import { Dropdown } from '@wordpress/components';
import { __ } from '@wordpress/i18n';
import { useSelect, useDispatch } from '@wordpress/data';
import { closeSmall as closeIcon, moreHorizontalMobile as moreIcon } from '@wordpress/icons';

/**
 * iThemes dependencies
 */
import { Button, List, ListItem } from '@ithemes/ui';

/**
 * Internal dependencies
 */
import { StyledMenu } from './styles';

export default function NoticeActions( { notice } ) {
	const { inProgress } = useSelect( ( select ) => ( {
		inProgress: select( 'ithemes-security/admin-notices' ).getInProgressActions( notice.id ),
	} ), [ notice.id ] );
	const { doNoticeAction } = useDispatch( 'ithemes-security/admin-notices' );

	const actions = [];

	for ( const slug in notice.actions ) {
		if ( ! notice.actions.hasOwnProperty( slug ) ) {
			continue;
		}

		const action = notice.actions[ slug ];

		if ( action.style === 'close' ) {
			actions.push(
				<ListItem key={ slug }>
					<Button
						icon={ closeIcon }
						label={ action.title }
						onClick={ () => doNoticeAction( notice.id, slug ) }
						isBusy={ inProgress.includes( slug ) }
						variant="tertiary"
					/>
				</ListItem>
			);
		}
	}

	const generic = getGenericActions( notice );

	if ( ! isEmpty( generic ) ) {
		actions.push(
			<ListItem key="more">
				<Dropdown
					popoverProps={ { position: 'bottom left' } }
					renderToggle={ ( { isOpen, onToggle } ) => (
						<Button
							icon={ moreIcon }
							label={ __( 'More Actions', 'better-wp-security' ) }
							onClick={ onToggle }
							aria-haspopup={ true }
							aria-expanded={ isOpen }
							variant="tertiary"
						/>
					) }
					renderContent={ () => (
						<StyledMenu>
							{ map( generic, ( action, slug ) =>
								action.uri ? (
									<Button key={ slug } href={ action.uri } text={ action.title } />
								) : (
									<Button
										key={ slug }
										onClick={ () =>
											doNoticeAction( notice.id, slug )
										}
										disabled={ inProgress.includes( slug ) }
										isBusy={ inProgress.includes( slug ) }
										text={ action.title }
									/>
								)
							) }
						</StyledMenu>
					) }
				/>
			</ListItem>
		);
	}

	return <List>{ actions }</List>;
}

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
