/**
 * External dependencies
 */
import { useLocation, useParams } from 'react-router-dom';
import { identity } from 'lodash';

/**
 * WordPress dependencies
 */
import { useDispatch, useSelect } from '@wordpress/data';
import { Button } from '@wordpress/components';
import { __ } from '@wordpress/i18n';

/**
 * Internal dependencies
 */
import {
	Breadcrumbs,
	PageHeader,
	useBreadcrumbTrail,
	useNavigateTo,
} from '@ithemes/security.pages.settings';
import { useMemo } from '@wordpress/element';
import { createPath } from 'history';

export default function SingleGroupHeader( { groupId, moduleFilter } ) {
	const { root } = useParams();
	const location = useLocation();
	const { type, isDeleting, label, navIds, help, module } = useSelect(
		( select ) => ( {
			type: select(
				'ithemes-security/user-groups-editor'
			).getMatchableType( groupId ),
			isDeleting: select( 'ithemes-security/user-groups' ).isDeleting(
				groupId
			),
			label: select(
				'ithemes-security/user-groups-editor'
			).getEditedMatchableLabel( groupId ),
			navIds: select(
				'ithemes-security/user-groups-editor'
			).getMatchableNavIds(),
			help: select( 'ithemes-security/modules' ).getModule(
				'user-groups'
			)?.help,
			module: select( 'ithemes-security/modules' ).getModule(
				moduleFilter
			),
		} ),
		[ groupId, moduleFilter ]
	);
	const { deleteGroup } = useDispatch(
		'ithemes-security/user-groups-editor'
	);
	const navigateTo = useNavigateTo();

	const title = label || __( 'Untitled', 'better-wp-security' );
	const trail = useBreadcrumbTrail( module?.label || title );

	const canDelete = type === 'user-group';
	const onDelete = async () => {
		if ( ! ( ( await deleteGroup( groupId ) ) instanceof Error ) ) {
			let redirect;
			const i = navIds.findIndex( ( navId ) => navId === groupId );

			if ( i !== -1 ) {
				if ( i + 1 < navIds.length ) {
					redirect = i + 1;
				} else {
					redirect = i - 1;
				}
			}

			navigateTo(
				`/${ root }/user-groups/${ navIds[ redirect ] || '' }`
			);
		}
	};

	return (
		<PageHeader
			title={ title }
			help={ help }
			breadcrumbs={
				<Breadcrumbs
					trail={ useMemo(
						() =>
							[
								...trail,
								module && {
									to: createPath( location ),
									title: module.title,
								},
							].filter( identity ),
						[ trail, module ]
					) }
				/>
			}
		>
			{ canDelete && (
				<Button
					onClick={ onDelete }
					isBusy={ isDeleting }
					variant="link"
					isDestructive
				>
					{ __( 'Delete Group', 'better-wp-security' ) }
				</Button>
			) }
		</PageHeader>
	);
}
