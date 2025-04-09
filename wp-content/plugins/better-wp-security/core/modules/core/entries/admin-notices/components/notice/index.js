/**
 * External dependencies
 */
import { isEmpty, size, map } from 'lodash';

/**
 * WordPress dependencies
 */
import { Fragment } from '@wordpress/element';
import { useSelect, useDispatch } from '@wordpress/data';

/**
 * iThemes dependencies
 */
import { Button, Heading, SurfaceVariant, Text, TextSize, TextVariant, TextWeight } from '@ithemes/ui';

/**
 * Internal dependencies
 */
import { Markup } from '@ithemes/security-components';
import { StyledHeader, StyledMessage, StyledNotice, StyledFooter, StyledMeta, StyledMetaItem } from './styles';

export default function Notice( { notice } ) {
	return (
		<StyledNotice>
			<StyledHeader severity={ notice.severity }>
				<Heading
					level={ 4 }
					size={ TextSize.NORMAL }
					weight={ TextWeight.HEAVY }
					variant={ TextVariant.DARK }
				>
					<Markup noWrap content={ notice.title || formatMessage( notice.message, notice ) } />
				</Heading>
				{ map(
					notice.actions,
					( action, slug ) =>
						action.style === 'primary' && (
							<PrimaryAction key={ slug } notice={ notice } action={ action } />
						)
				) }
			</StyledHeader>

			{ notice.title && notice.message && (
				<StyledMessage>
					<Text as="p">
						<Markup noWrap content={ formatMessage( notice.message, notice ) } />
					</Text>
				</StyledMessage>
			) }

			{ hasMeta( notice ) && (
				<StyledMeta as="dl" variant={ SurfaceVariant.TERTIARY }>
					{ map(
						notice.meta,
						( meta, key ) =>
							key !== 'created_at' && (
								<Fragment key={ key }>
									<StyledMetaItem
										as="dt"
										weight={ TextWeight.HEAVY }
										variant={ TextVariant.DARK }
										text={ meta.label }
										textTransform="uppercase"
									/>
									<StyledMetaItem
										as="dd"
										size={ TextSize.SMALL }
										variant={ TextVariant.MUTED }
										text={ meta.formatted }
									/>
								</Fragment>
							)
					) }
				</StyledMeta>
			) }

			{ notice.meta.created_at && (
				<StyledFooter>
					<Text
						as="time"
						dateTime={ notice.meta.created_at.value }
						text={ notice.meta.created_at.formatted }
						size={ TextSize.SMALL }
					/>
				</StyledFooter>
			) }
		</StyledNotice>
	);
}

function PrimaryAction( { notice, action } ) {
	const isInProgress = useSelect( ( select ) =>
		select( 'ithemes-security/admin-notices' )
			.getInProgressActions( notice.id )
			.includes( action.id ),
	[ notice.id, action.id ]
	);
	const { doNoticeAction } = useDispatch( 'ithemes-security/admin-notices' );
	// Intentionally uses string-based API because we only want to refresh modules if they are in use.
	const { fetchModules } = useDispatch( 'ithemes-security/modules' ) || {};

	const onClick = async ( e ) => {
		if ( ! action.uri ) {
			e.preventDefault();
			await doNoticeAction( notice.id, action.id );
			fetchModules?.();
		}
	};

	// We don't want to cause a dependency on the settings page entry.
	if ( action.route && window.itsec?.pages?.settings?.history ) {
		return <PrimaryRouteAction route={ action.route } title={ action.title } history={ window.itsec?.pages?.settings?.history } />;
	}

	return (
		<Button href={ action.uri } onClick={ onClick } isBusy={ isInProgress }>
			{ action.title }
		</Button>
	);
}

function PrimaryRouteAction( { title, route, history } ) {
	const onClick = () => history.push( route );

	return (
		<Button onClick={ onClick } href={ history.createHref( route ) }>
			{ title }
		</Button>
	);
}

function hasMeta( notice ) {
	if ( isEmpty( notice.meta ) ) {
		return false;
	}

	if (
		size( notice.meta ) === 1 &&
		notice.meta.hasOwnProperty( 'created_at' )
	) {
		return false;
	}

	return true;
}

function formatMessage( message, notice ) {
	for ( const action in notice.actions ) {
		if ( ! notice.actions.hasOwnProperty( action ) ) {
			continue;
		}

		if ( notice.actions[ action ].uri === '' ) {
			continue;
		}

		message = message.replace(
			'{{ $' + action + ' }}',
			notice.actions[ action ].uri
		);
	}

	return message;
}
