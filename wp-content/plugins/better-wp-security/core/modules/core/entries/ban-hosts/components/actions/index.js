/**
 * WordPress dependencies
 */
import { __ } from '@wordpress/i18n';
import { useSelect } from '@wordpress/data';

/**
 * SolidWP dependencies
 */
import { Button } from '@ithemes/ui';

/**
 * Internal dependencies
 */
import { StyledActions } from './styles';

export default function BanHostsActions( {
	isCreating,
	isSaving,
	setCreating,
	formId,
} ) {
	const { schema } = useSelect( ( select ) => ( {
		schema: select( 'ithemes-security/core' ).getSchema(
			'ithemes-security-ban'
		),
	} ), [] );
	return (
		<StyledActions>
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
		</StyledActions>
	);
}
