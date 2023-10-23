/**
 * External dependencies
 */
import { useDebounceCallback } from '@react-hook/debounce';
import styled from '@emotion/styled';

/**
 * WordPress dependencies
 */
import { useSelect, useDispatch } from '@wordpress/data';
import { __ } from '@wordpress/i18n';
import { useState } from '@wordpress/element';

/**
 * SolidWP dependencies
 */
import {
	Surface,
	Button,
} from '@ithemes/ui';

/**
 * Internal dependencies
 */
import Header, { Title } from '../../components/card/header';
import List from './list';
import AddNew from './add-new';

const StyledSurface = styled( Surface )`
	display: flex;
	flex-direction: column;
	justify-content: space-between;
	height: 100%;
`;

export const StyledFooter = styled( Surface )`
  display: flex;
  flex-wrap: wrap;
  align-items: center;
  flex-shrink: 0;
  justify-content: flex-end;
  position: sticky;
  bottom: 0;
  padding: 0.5rem 1.25rem;
  gap: 0.5rem;
  margin-top: auto;
  border-top: 1px solid ${ ( { theme } ) => theme.colors.border.normal };
`;

function BannedUsers( { card, config } ) {
	const [ isCreating, setCreating ] = useState( false );
	const [ isSaving, setSaving ] = useState( false );
	const { schema, isQuerying } = useSelect( ( select ) => ( {
		schema: select( 'ithemes-security/core' ).getSchema(
			'ithemes-security-ban'
		),
		isQuerying: select( 'ithemes-security/bans' ).isQuerying( 'main' ),
	} ), [] );
	const {
		createBan,
		query,
		invalidateResolutionForStoreSelector: invalidateResolution,
	} = useDispatch( 'ithemes-security/bans' );
	const debouncedQuery = useDebounceCallback( query, 500 );
	const [ selected, select ] = useState( 0 );
	const onSelect = ( selectedId ) => {
		select( selectedId );
		setCreating( false );
	};
	const formId = `itsec-ban-card-create-form__${ card.id }`;
	return (
		<StyledSurface>
			<Header>
				<Title
					card={ card }
					config={ config }
				/>
			</Header>
			{ ! isCreating && (
				<>
					<List
						selected={ isCreating ? false : selected }
						onSelect={ onSelect }
						querying={ isQuerying }
						query={ debouncedQuery }
					/>
				</>
			) }
			{ isCreating && (
				<AddNew
					id={ formId }
					createForm={ isCreating }
					save={ createBan }
					setSaving={ setSaving }
					afterSave={ () => invalidateResolution( 'getBans' ) }
				/>
			) }
			<StyledFooter>
				{ isCreating && (
					<>
						<span>
							<Button
								variant="link"
								disabled={ isSaving }
								onClick={ () => setCreating( false ) }
							>
								{ __( 'Cancel', 'better-wp-security' ) }
							</Button>
						</span>
						<span>
							<Button
								variant="primary"
								form={ formId }
								type="submit"
								isBusy={ isSaving }
								disabled={ isSaving }
							>
								{ __( 'Save', 'better-wp-security' ) }
							</Button>
						</span>
					</>
				) }
				{ ! isCreating && (
					<>
						{ schema?.links
							.filter( ( link ) => link.rel === 'create-form' && ( ! link.targetHints?.allow || link.targetHints.allow.includes( 'POST' ) ) )
							.map( ( createForm ) => (
								<span key={ createForm.href }>
									<Button
										variant="primary"
										onClick={ () =>
											setCreating(
												isCreating ? false : createForm
											)
										}
									>
										{ createForm.title }
									</Button>
								</span>
							) ) }
					</>
				) }
			</StyledFooter>
		</StyledSurface>
	);
}

export const slug = 'banned-users-list';
export const settings = {
	render: BannedUsers,
};
