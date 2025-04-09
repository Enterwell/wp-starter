/**
 * External dependencies
 */
import { reduce } from 'lodash';

/**
 * WordPress dependencies
 */
import { Flex } from '@wordpress/components';
import { __, _n, sprintf } from '@wordpress/i18n';
import { brush as themeIcon, plugins as pluginIcon, wordpress as coreIcon } from '@wordpress/icons';

/**
 * SolidWP dependencies
 */
import { Heading, Text, TextSize, TextVariant, TextWeight } from '@ithemes/ui';

/**
 * Internal dependencies
 */
import { StyledVulnerabilityBadge, StyledVulnerabilityCard } from './styles';

/**
 * Display the identified vulnerabilities for a piece of Software in a card.
 *
 * @param {Object} props
 * @param {Object} props.software The software the vulnerability is associated with.
 * @param {number} props.critical Number of critical vulnerabilities found.
 * @param {number} props.high     Number of high vulnerabilities found.
 * @param {number} props.medium   Number of medium vulnerabilities found.
 * @param {number} props.low      Number of low vulnerabilities found.
 * @param {number} props.maxScore The highest CVSS number of the vulnerabilities.
 */
export default function SoftwareVulnerabilityCard( { software, critical, high, medium, low, maxScore } ) {
	return (
		<StyledVulnerabilityCard score={ maxScore }>
			<Flex direction="column" gap={ 4 } justify="space-between" expanded={ false }>
				<Heading level={ 4 } text={ software.label || software.slug } size={ TextSize.EXTRA_LARGE } weight={ TextWeight.NORMAL } />
				<Text icon={ getVulnerabilityIcon( software.type.slug ) } text={ software.type.label } variant={ TextVariant.DARK } />
			</Flex>
			<Flex direction="column" gap={ 3 } justify="space-between" expanded={ false }>
				<Heading level={ 5 } text={ __( 'Found vulnerabilities', 'better-wp-security' ) } variant={ TextVariant.DARK } weight={ TextWeight.HEAVY } />
				<Flex gap={ 4 } justify="flex-start">
					<Badge score={ maxScore } />
					<TotalFound critical={ critical } high={ high } medium={ medium } low={ low } maxScore={ maxScore } />
				</Flex>
				<RemainingFound maxScore={ maxScore } critical={ critical } high={ high } medium={ medium } low={ low } />
			</Flex>
		</StyledVulnerabilityCard>
	);
}

function Badge( { score } ) {
	return <StyledVulnerabilityBadge score={ score } text={ getSeverityLabel( score ) } variant={ TextVariant.DARK } weight={ TextWeight.HEAVY } />;
}

function TotalFound( { critical, high, medium, low, maxScore } ) {
	return <Text text={ getTotalFound( { critical, high, medium, low, maxScore } ) } variant={ TextVariant.MUTED } />;
}

function getTotalFound( { critical, high, medium, low, maxScore } ) {
	if ( maxScore < 3 ) {
		/* translators: 1. Count of vulnerabilities. */
		return sprintf( _n( '%d low severity issue found', '%d low severity issues found', low, 'better-wp-security' ), low );
	}
	if ( maxScore < 7 ) {
		/* translators: 1. Count of vulnerabilities. */
		return sprintf( _n( '%d medium severity issue found', '%d medium severity issues found', medium, 'better-wp-security' ), medium );
	}
	if ( maxScore < 9 ) {
		/* translators: 1. Count of vulnerabilities. */
		return sprintf( _n( '%d high severity issue found', '%d high severity issues found', high, 'better-wp-security' ), high );
	}
	/* translators: 1. Count of vulnerabilities. */
	return sprintf( _n( '%d critical severity issue found', '%d critical severity issues found', critical, 'better-wp-security' ), critical );
}

function RemainingFound( { maxScore, ...counts } ) {
	const severity = getSeverity( maxScore );
	const remaining = reduce( counts, ( acc, count, kind ) => kind === severity ? acc : acc + count, 0 );

	if ( ! remaining ) {
		return null;
	}

	return (
		<Text
			/* translators: 1. Count of vulnerabilities */
			text={ sprintf( _n( '%d additional lower severity issue found', '%d additional lower severity issues found', remaining, 'better-wp-security' ), remaining ) }
			variant={ TextVariant.MUTED }
		/>
	);
}

function getSeverity( score ) {
	if ( score < 3 ) {
		return 'low';
	}
	if ( score < 7 ) {
		return 'medium';
	}
	if ( score < 9 ) {
		return 'high';
	}
	return 'critical';
}

function getSeverityLabel( score ) {
	if ( score < 3 ) {
		return __( 'Low', 'better-wp-security' );
	}
	if ( score < 7 ) {
		return __( 'Medium', 'better-wp-security' );
	}
	if ( score < 9 ) {
		return __( 'High', 'better-wp-security' );
	}
	return __( 'Critical', 'better-wp-security' );
}

function getVulnerabilityIcon( type ) {
	switch ( type ) {
		case 'plugin':
			return pluginIcon;
		case 'theme':
			return themeIcon;
		case 'wordpress':
			return coreIcon;
		default:
			return undefined;
	}
}
