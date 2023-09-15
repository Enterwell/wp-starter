/**
 * External dependencies
 */
import { map, isPlainObject } from 'lodash';

/**
 * WordPress dependencies
 */
import {
	CheckboxControl,
	TextControl,
	TextareaControl,
	SelectControl,
	Button,
} from '@wordpress/components';
import { __, sprintf } from '@wordpress/i18n';
import { createInterpolateElement, Fragment } from '@wordpress/element';
import { useInstanceId } from '@wordpress/compose';

/**
 * Internal dependencies
 */
import {
	PageHeader,
	PrimaryForm,
	PrimaryFormSection,
} from '@ithemes/security.pages.settings';
import { TextareaListControl } from '@ithemes/security-components';
import { UserRoleList } from '..';
import './style.scss';

const tags = [ 'a', 'i', 'b', 'h2', 'h3', 'h4', 'h5', 'h6', 'p' ];

export default function Notification( {
	notification,
	settings,
	onChange,
	usersAndRoles,
	isSaving,
	isDirty,
	onSubmit,
	onUndo,
	saveLabel = __( 'Save All', 'better-wp-security' ),
	allowCleanSave = false,
	apiError,
} ) {
	const isEnabled = ! notification.optional || settings.enabled;

	return (
		<>
			<PageHeader
				title={ notification.l10n.label }
				description={ notification.l10n.description }
			/>
			<PrimaryForm
				saveLabel={ saveLabel }
				saveDisabled={ ! isDirty && ! allowCleanSave }
				isSaving={ isSaving }
				onSubmit={ onSubmit }
				apiError={ apiError }
				buttons={ [
					<Button
						key="undo"
						onClick={ onUndo }
						disabled={ ! isDirty }
					>
						{ __( 'Undo Changes', 'better-wp-security' ) }
					</Button>,
				] }
			>
				{ notification.optional && (
					<CheckboxControl
						className="itsec-nc-notification__enabled"
						label={ __( 'Enabled', 'better-wp-security' ) }
						checked={ settings.enabled }
						onChange={ ( enabled ) =>
							onChange( { ...settings, enabled } )
						}
					/>
				) }

				{ isEnabled && (
					<>
						<Customize
							notification={ notification }
							settings={ settings }
							onChange={ onChange }
						/>
						<Schedule
							notification={ notification }
							settings={ settings }
							onChange={ onChange }
						/>
						<Recipients
							notification={ notification }
							settings={ settings }
							onChange={ onChange }
							usersAndRoles={ usersAndRoles }
						/>
					</>
				) }
			</PrimaryForm>
		</>
	);
}

function Customize( { notification, settings, onChange } ) {
	const help = (
		<span>
			{ sprintf(
				/* translators: 1. Comma separated list of allowed HTML tags. */
				__(
					'You can use HTML in your message. Allowed HTML includes: %s.',
					'better-wp-security'
				),
				tags.join( ', ' )
			) }{ ' ' }
			{ !! notification.tags &&
				createInterpolateElement(
					/* translators: 1. Example email tag. */
					__(
						'This notification supports email tags. Tags are formatted as follows <tag />.',
						'better-wp-security'
					),
					{
						tag: <code>{ '{{ $tag_name }}' }</code>,
					}
				) }
		</span>
	);

	return (
		<PrimaryFormSection heading={ __( 'Customize', 'better-wp-security' ) }>
			{ notification.subject_editable && (
				<TextControl
					label={ __( 'Subject', 'better-wp-security' ) }
					placeholder={ notification.l10n.subject }
					value={ settings.subject || notification.l10n.subject }
					onChange={ ( subject ) =>
						onChange( { ...settings, subject } )
					}
				/>
			) }

			{ notification.message_editable && (
				<>
					<TextareaControl
						label={ __( 'Message', 'better-wp-security' ) }
						placeholder={ notification.l10n.message }
						rows={ 10 }
						value={ settings.message || notification.l10n.message }
						onChange={ ( message ) =>
							onChange( { ...settings, message } )
						}
						help={ help }
					/>

					{ notification.tags && (
						<dl>
							{ map(
								notification.l10n.tags,
								( description, tag ) => (
									<Fragment key={ tag }>
										<dt>
											<code>{ tag }</code>
										</dt>
										<dd>{ description }</dd>
									</Fragment>
								)
							) }
						</dl>
					) }
				</>
			) }
		</PrimaryFormSection>
	);
}

function Schedule( { notification, settings, onChange } ) {
	const instanceId = useInstanceId(
		Recipients,
		'itsec-nc-notification__schedule'
	);

	if ( ! isPlainObject( notification.schedule ) ) {
		return null;
	}

	return (
		<PrimaryFormSection
			heading={
				<label htmlFor={ instanceId }>
					{ __( 'Schedule', 'better-wp-security' ) }
				</label>
			}
		>
			<SelectControl
				id={ instanceId }
				options={ notification.l10n.schedule }
				value={ settings.schedule }
				onChange={ ( schedule ) =>
					onChange( { ...settings, schedule } )
				}
			/>
		</PrimaryFormSection>
	);
}

function Recipients( { notification, settings, onChange, usersAndRoles } ) {
	const instanceId = useInstanceId(
		Recipients,
		'itsec-nc-notification__recipients'
	);

	switch ( notification.recipient ) {
		case 'user':
			return (
				<PrimaryFormSection
					className="itsec-nc-notification__recipients"
					heading={ __( 'Recipient', 'better-wp-security' ) }
				>
					{ __( 'Site Users', 'better-wp-security' ) }
				</PrimaryFormSection>
			);
		case 'admin':
			return (
				<PrimaryFormSection
					className="itsec-nc-notification__recipients"
					heading={ __( 'Recipient', 'better-wp-security' ) }
				>
					{ __( 'Admin Emails', 'better-wp-security' ) }
				</PrimaryFormSection>
			);
		case 'per-use':
			return (
				<PrimaryFormSection
					className="itsec-nc-notification__recipients"
					heading={ __( 'Recipient', 'better-wp-security' ) }
				>
					{ __( 'Specified when sending', 'better-wp-security' ) }
				</PrimaryFormSection>
			);
		case 'email-list':
			return (
				<PrimaryFormSection
					heading={
						<label htmlFor={ instanceId }>
							{ __( 'Recipient', 'better-wp-security' ) }
						</label>
					}
				>
					<TextareaListControl
						id={ instanceId }
						help={ __(
							'The email address(es) this notification will be sent to. One address per line.',
							'better-wp-security'
						) }
						value={ settings.email_list }
						onChange={ ( emailList ) =>
							onChange( { ...settings, email_list: emailList } )
						}
					/>
				</PrimaryFormSection>
			);
		case 'user-list':
			return (
				<PrimaryFormSection
					className="itsec-nc-notification__recipients itsec-nc-notification__recipients--user-list"
					heading={
						<label htmlFor={ instanceId }>
							{ __( 'Recipient', 'better-wp-security' ) }
						</label>
					}
				>
					<SelectControl
						value={ settings.recipient_type }
						onChange={ ( recipientType ) =>
							onChange( {
								...settings,
								recipient_type: recipientType,
							} )
						}
						id={ instanceId }
						options={ [
							{
								value: 'default',
								label: __( 'Default Recipients', 'better-wp-security' ),
							},
							{
								value: 'custom',
								label: __( 'Custom', 'better-wp-security' ),
							},
						] }
					/>

					{ settings.recipient_type === 'custom' && (
						<UserRoleList
							help={ __(
								'Select which users should be emailed.',
								'better-wp-security'
							) }
							value={ settings.user_list || [] }
							onChange={ ( userList ) =>
								onChange( { ...settings, user_list: userList } )
							}
							usersAndRoles={ usersAndRoles }
						/>
					) }
				</PrimaryFormSection>
			);
	}
}
