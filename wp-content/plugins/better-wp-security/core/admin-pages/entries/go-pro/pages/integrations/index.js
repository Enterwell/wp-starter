/**
 * WordPress dependencies
 */
import { __ } from '@wordpress/i18n';
import { Flex } from '@wordpress/components';

/**
 * Internal dependencies
 */
import { CardGrid, Header, Integration } from '../../components';
import './style.scss';

export default function Integrations( { integrations } ) {
	return (
		<Flex className="itsec-go-pro-integrations" direction="column" gap={ 8 }>
			<Header title={ __( 'Additional Security Integrations', 'better-wp-security' ) } subtitle={ __( 'Complete your WordPress security strategy with client reports and complete backups.', 'better-wp-security' ) } />
			<CardGrid>
				{ integrations.map( ( integration, i ) => (
					<Integration key={ i } { ...integration } />
				) ) }
			</CardGrid>
		</Flex>
	);
}
