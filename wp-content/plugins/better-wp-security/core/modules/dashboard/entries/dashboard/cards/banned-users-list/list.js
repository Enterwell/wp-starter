/**
 * External dependencies
 */
import classnames from 'classnames';
import { flatten, get } from 'lodash';

/**
 * WordPress dependencies
 */
import { Button, TextareaControl, Icon } from '@wordpress/components';
import { useSelect, useDispatch } from '@wordpress/data';
import { __ } from '@wordpress/i18n';
import { dateI18n } from '@wordpress/date';
import { Fragment, useState } from '@wordpress/element';

/**
 * Internal dependencies
 */
import { getSelf, getTargetHint } from '@ithemes/security-utils';
import MasterDetail from '../../components/master-detail';

function MasterRender( { master: ban } ) {
	return (
		<Fragment>
			<th
				scope="row"
				className={ classnames(
					'itsec-card-banned-users__bans--column-label',
					`itsec-card-banned-users__ban--actor-type-${
						ban.created_by ? ban.created_by.type : 'none'
					}`,
					ban.created_by &&
						`itsec-card-banned-users__ban--actor-id-${ ban.created_by.id }`
				) }
			>
				<span className="itsec-card-banned-users__bans-label">
					{ ban.label }
				</span>
				{ ban.created_at && (
					<span className="itsec-card-banned-users__bans-date">
						{ dateI18n( 'M d, Y g:i A', ban.created_at ) }
					</span>
				) }
			</th>
			<td className="itsec-card-banned-users__bans--column-comment">
				{ ban.comment }
			</td>
		</Fragment>
	);
}

function DetailRender( { master: ban } ) {
	const { updateBan, deleteBan } = useDispatch( 'ithemes-security/bans' );
	const { createNotice } = useDispatch( 'core/notices' );
	const { isUpdating, isDeleting } = useSelect( ( select ) => ( {
		isUpdating: select( 'ithemes-security/bans' ).isUpdating( ban ),
		isDeleting: select( 'ithemes-security/bans' ).isDeleting( ban ),
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
		<div className="itsec-card-banned-users__ban">
			<div className="itsec-card-banned-users__ban-top">
				<dl>
					<dt>{ __( 'Host', 'better-wp-security' ) }</dt>
					<dd>{ ban.label }</dd>
					<dt>{ __( 'Time', 'better-wp-security' ) }</dt>
					<dd>
						{ ban.created_at &&
							dateI18n( 'M d, Y g:i A', ban.created_at ) }
					</dd>
					<dt>{ __( 'Source', 'better-wp-security' ) }</dt>
					<dd>{ ban.created_by && ban.created_by.label }</dd>
					{ ! canEdit && (
						<>
							<dt>{ __( 'Notes', 'better-wp-security' ) }</dt>
							<dd>{ ban.comment }</dd>
						</>
					) }
				</dl>

				<ul className="itsec-card-banned-users__ban-actions">
					{ getTargetHint( ban, 'allow', false ).includes(
						'DELETE'
					) && (
						<li>
							<Button
								variant="link"
								isBusy={ isDeleting }
								onClick={ () => deleteBan( ban ) }
								icon="dismiss"
							>
								{ __( 'Remove Ban', 'better-wp-security' ) }
							</Button>
						</li>
					) }

					{ links.map( ( link ) => (
						<li key={ link.href }>
							<a href={ link.href }>
								<Icon icon="arrow-right-alt" />
								{ link.title }
							</a>
						</li>
					) ) }
				</ul>
			</div>

			{ canEdit && (
				<TextareaControl
					className="itsec-card-banned-users__ban-notes"
					label={ __( 'Notes', 'better-wp-security' ) }
					value={ comment }
					onChange={ setComment }
					onBlur={ () => comment !== ban.comment && onSave() }
					readOnly={ isUpdating }
					maxLength={ 255 }
					rows={ 4 }
				/>
			) }
		</div>
	);
}

export default function List( { isSmall, onSelect, selected } ) {
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
			detailRender={ DetailRender }
			masterRender={ MasterRender }
			selectedId={ selected }
			select={ onSelect }
			idProp={ getSelf }
			direction="vertical"
			borderless
			isSmall={ isSmall }
			hasNext={ hasNext }
			loadNext={ () => fetchQueryNextPage( 'main' ) }
			isQuerying={ isQuerying }
		>
			<thead>
				<tr>
					<th className="itsec-card-banned-users__bans--column-label">
						{ __( 'Host', 'better-wp-security' ) }
					</th>
					<th className="itsec-card-banned-users__bans--column-comment">
						{ __( 'Notes', 'better-wp-security' ) }
					</th>
				</tr>
			</thead>
		</MasterDetail>
	);
}
