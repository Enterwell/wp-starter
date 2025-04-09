/**
 * External dependencies
 */
import { css } from '@emotion/css';

/**
 * WordPress dependencies
 */
import { __ } from '@wordpress/i18n';
import { Dropdown, MenuItem, NavigableMenu, Button } from '@wordpress/components';
import { check as selectedIcon } from '@wordpress/icons';

const choices = [
	{
		value: 'administrator',
		label: __( 'Administrator Capabilities', 'better-wp-security' ),
	},
	{
		value: 'editor',
		label: __( 'Editor Capabilities & greater (recommended)', 'better-wp-security' ),
	},
	{
		value: 'everyone',
		label: __( 'Everyone', 'better-wp-security' ),
	},
];

const fill = css`
	width: 100%;
	justify-content: center;
`;

export default function SimpleUserGroupControl( { value = 'editor', onChange } ) {
	const selected = choices.find( ( maybe ) => maybe.value === value );

	return (
		<Dropdown
			contentClassName="itsec-apply-css-vars"
			renderToggle={ ( { isOpen, onToggle } ) => (
				<Button
					variant="tertiary"
					aria-expanded={ isOpen }
					onClick={ onToggle }
					text={ selected?.label }
					className={ fill }
				/>
			) }
			renderContent={ ( { onClose } ) => (
				<NavigableMenu>
					{ choices.map( ( choice ) => (
						<MenuItem
							key={ choice.value }
							isSelected={ value === choice.value }
							onClick={ () => {
								onChange( choice.value );
								onClose();
							} }
							icon={ value === choice.value && selectedIcon }
							role="menuitemradio"
						>
							{ choice.label }
						</MenuItem>
					) ) }
				</NavigableMenu>
			) }
		/>
	);
}
