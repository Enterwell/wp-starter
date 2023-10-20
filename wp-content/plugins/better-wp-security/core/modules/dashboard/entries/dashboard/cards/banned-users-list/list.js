/**
 * External dependencies
 */
import { flatten, get } from 'lodash';
import styled from '@emotion/styled';

/**
 * WordPress dependencies
 */
import { TextareaControl } from '@wordpress/components';
import { useSelect, useDispatch } from '@wordpress/data';
import { __ } from '@wordpress/i18n';
import { dateI18n } from '@wordpress/date';
import { useState } from '@wordpress/element';
import { close as deleteIcon, arrowRight as viewIcon } from '@wordpress/icons';

/**
 * SolidWP dependencies
 */
import {
	MasterDetail,
	MasterDetailBackButton,
	Surface,
	SurfaceVariant,
	Text,
	TextSize,
	TextWeight,
	Button,
	List,
	ListItem,
} from '@ithemes/ui';

/**
 * Internal dependencies
 */
import { getSelf, getTargetHint } from '@ithemes/security-utils';
import Search from './search';

const StyledBansLabels = styled( Text, {
	shouldForwardProp: ( prop ) => prop !== 'accentColor',
} )`
	padding-left: ${ ( { theme: { getSize } } ) => getSize( 1.25 ) };
	border-left: 3px solid ${ ( { theme, accentColor } ) => accentColor || theme.colors.text.accent };
  	display: block;
`;

const StyledBanColumnComment = styled( Text )`
	width: 40%;
`;

function getBanColor( ban ) {
	switch ( ban.created_by?.id ) {
		case 'four_oh_four':
			return '#FF8528';
		case 'brute_force':
			return '#00a32a';
		case 'brute_force_admin_user':
			return '#2271b1';
		case 'recaptcha':
			return '#d63638';
		default:
			return undefined;
	}
}

function MasterRender( { master: ban } ) {
	return (
		<>
			<th scope="row">
				<StyledBansLabels
					as="span"
					text={ ban.label }
					weight={ TextWeight.HEAVY }
					accentColor={ getBanColor( ban ) }
				/>
				{ ban.created_at && (
					<StyledBansLabels
						as="span"
						text={ dateI18n( 'M d, Y g:i A', ban.created_at ) }
						size={ TextSize.SMALL }
						accentColor={ getBanColor( ban ) }
					/>
				) }
			</th>
			<StyledBanColumnComment
				as="td"
				text={ ban.comment }
				size={ TextSize.SMALL }
			/>
		</>
	);
}

const StyledBannedUsersBan = styled( Surface )`
	display: flex;
	flex-direction: column;
	gap: ${ ( { theme: { getSize } } ) => getSize( 0.5 ) };
	height: 100%;
	padding: ${ ( { theme: { getSize } } ) =>
		`${ getSize( 0.875 ) } ${ getSize( 1 ) }` };
`;

const StyledBackButton = styled( MasterDetailBackButton )`
	align-self: start;
`;

const StyledBannedUsersMain = styled.div`
	display: flex;
	justify-content: space-between;
	align-items: flex-start;
	flex-wrap: nowrap;
	gap: ${ ( { theme: { getSize } } ) => getSize( 1 ) };
`;

const StyledDetails = styled.dl`
	display: grid;
	grid-template: min-content / min-content 1fr;
	grid-gap: ${ ( { theme: { getSize } } ) =>
		`${ getSize( 0.5 ) } ${ getSize( 1 ) }` };
	margin: 0;
`;

const StyledDD = styled.dd`
	margin: 0;
	display: inline;
`;

function DetailRender( { master: ban, select } ) {
	const { updateBan, deleteBan } = useDispatch( 'ithemes-security/bans' );
	const { createNotice } = useDispatch( 'core/notices' );
	const { isUpdating, isDeleting } = useSelect( ( mapSelect ) => ( {
		isUpdating: mapSelect( 'ithemes-security/bans' ).isUpdating( ban ),
		isDeleting: mapSelect( 'ithemes-security/bans' ).isDeleting( ban ),
	} ) );
	const [ comment, setComment ] = useState( ban.comment );
	const canEdit = getTargetHint( ban, 'allow', false ).includes( 'PUT' );
	const links = flatten( Object.values( get( ban, '_links', {} ) ) ).filter(
		( link ) => link.media === 'text/html'
	);
	const onSave = async () => {
		const updated = await updateBan( ban, { comment } );

		if ( updated instanceof Error ) {
			createNotice( 'error', updated.message, {
				context: 'ithemes-security',
			} );
		}
	};

	return (
		<StyledBannedUsersBan variant={ SurfaceVariant.SECONDARY }>
			<StyledBackButton
				isSinglePane
				onSelect={ select }
				selectedId={ ban.id }
			/>
			<StyledBannedUsersMain>
				<StyledDetails>
					<Text
						as="dt"
						text={ __( 'IP', 'better-wp-security' ) }
						textTransform="uppercase"
					/>
					<StyledDD>{ ban.label }</StyledDD>
					<Text
						as="dt"
						text={ __( 'Time', 'better-wp-security' ) }
						textTransform="uppercase"
					/>
					<StyledDD>
						{ ban.created_at &&
							dateI18n( 'M d, Y g:i A', ban.created_at ) }
					</StyledDD>
					<Text
						as="dt"
						text={ __( 'Source', 'better-wp-security' ) }
						textTransform="uppercase"
					/>
					<StyledDD>{ ban.created_by && ban.created_by.label }</StyledDD>
					{ ! canEdit && (
						<>
							<Text
								as="dt"
								text={ __( 'Notes', 'better-wp-security' ) }
								textTransform="uppercase"
							/>
							<StyledDD>{ ban.comment }</StyledDD>
						</>
					) }
				</StyledDetails>

				<List>
					{ getTargetHint( ban, 'allow', false ).includes(
						'DELETE'
					) && (
						<ListItem>
							<Button
								variant="tertiary"
								isBusy={ isDeleting }
								onClick={ () => deleteBan( ban ) }
								icon={ deleteIcon }
								text={ __( 'Remove Ban', 'better-wp-security' ) }
							/>
						</ListItem>
					) }

					{ links.map( ( link ) => (
						<ListItem key={ link.href }>
							<Button
								href={ link.href }
								icon={ viewIcon }
								text={ link.title }
								variant="tertiary"
							/>
						</ListItem>
					) ) }
				</List>
			</StyledBannedUsersMain>

			{ canEdit && (
				<TextareaControl
					label={ __( 'Notes', 'better-wp-security' ) }
					value={ comment }
					onChange={ setComment }
					onBlur={ () => comment !== ban.comment && onSave() }
					readOnly={ isUpdating }
					maxLength={ 255 }
					rows={ 3 }
				/>
			) }
		</StyledBannedUsersBan>
	);
}

const StyledBansColumnComment = styled( Text )`
	width: 40%;
`;

export default function BanList( { onSelect, selected, querying, query } ) {
	const { fetchQueryNextPage } = useDispatch( 'ithemes-security/bans' );
	const { bans, hasNext, isQuerying } = useSelect( ( select ) => ( {
		bans: select( 'ithemes-security/bans' ).getBans(),
		hasNext: !! select( 'ithemes-security/bans' ).getQueryHeaderLink(
			'main',
			'next'
		),
		isQuerying: select( 'ithemes-security/bans' ).isQuerying( 'main' ),
	} ) );

	return (
		<MasterDetail
			masters={ bans }
			getId={ ( ban ) => ban.id }
			renderBeginList={ () => (
				<thead>
					<tr>
						<th>
							{ __( 'IP', 'better-wp-security' ) }
						</th>
						<StyledBansColumnComment
							as="th"
							text={ __( 'Notes', 'better-wp-security' ) }
							size={ TextSize.SMALL }
						/>
					</tr>
				</thead>
			) }
			renderDetail={ ( ban ) => (
				<DetailRender
					master={ ban }
					select={ onSelect }
					querying={ querying }
					query={ query }
				/>
			) }
			renderMaster={ ( ban ) => (
				<MasterRender master={ ban } />
			) }
			selectedId={ selected }
			onSelect={ onSelect }
			idProp={ getSelf }
			direction="vertical"
			isBorderless
			hasNext={ hasNext }
			loadNext={ () => fetchQueryNextPage( 'main' ) }
			isQuerying={ isQuerying }
			isSinglePane={ true }
			renderBeforeList={ () => (
				<Search
					query={ query }
					isQuerying={ querying }
				/>
			) }
		/>
	);
}
