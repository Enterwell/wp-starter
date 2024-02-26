/**
 * WordPress dependencies
 */
import { __, sprintf } from '@wordpress/i18n';
import { dateI18n } from '@wordpress/date';
import { Icon } from '@wordpress/components';
import {
	arrowRight,
	brush as themeIcon,
	plugins as pluginIcon,
	shield,
	starFilled,
	warning,
	wordpress as coreIcon,
} from '@wordpress/icons';

/**
 * iTheme dependencies
 */
import { Button, Notice, Text, TextSize, TextVariant } from '@ithemes/ui';

/**
 * Internal dependencies
 */
import { useGlobalNavigationUrl } from '@ithemes/security-utils';
import Header, { Title } from '../../components/card/header';
import {
	StyledSeverity,
	StyledDetail,
	StyledVulnerability,
	StyledStar,
	StyledSection,
	StyledSectionTitle,
	StyledBlogLink,
	StyledBlogItem,
	StyledBlogIcon,
	StyledBlogText,
} from './styles';

function vulnerabilityIcon( type ) {
	switch ( type ) {
		case 'plugin':
			return pluginIcon;
		case 'theme':
			return themeIcon;
		case 'core':
			return coreIcon;
		default:
			return undefined;
	}
}

function severityColor( score ) {
	switch ( true ) {
		case score < 3:
			return '#B8E6BF';
		case score < 7:
			return '#FFC518';
		case score < 9:
			return '#FFABAF';
		default:
			return '#D63638';
	}
}

function Vulnerability( { vulnerability, isSmall } ) {
	return (
		<StyledVulnerability key={ vulnerability.id }>
			{ isSmall
				? <Icon icon={ vulnerabilityIcon( vulnerability.software.type.slug ) } />
				: (
					<Text
						icon={ vulnerabilityIcon( vulnerability.software.type.slug ) }
						text={ vulnerability.software.type.label }
						textTransform="capitalize" />
				)
			}
			{ vulnerability.software.type.slug !== 'wordpress' && (
				<Text weight={ 500 } text={ vulnerability.software.label || vulnerability.software.slug } />
			) }
			<StyledSeverity backgroundColor={ severityColor( vulnerability.details.score ) }>
				<span>{ vulnerability.details.score }</span>
			</StyledSeverity>
			<StyledDetail text={ vulnerability.details.type.label } />
		</StyledVulnerability>
	);
}

export default function SecuritySummary( { card, config, eqProps } ) {
	const isSmall = eqProps[ 'max-width' ] && eqProps[ 'max-width' ].includes( '400px' );
	const vulnerabilitiesPage = useGlobalNavigationUrl( 'vulnerabilities' );

	return (
		<>
			<Header>
				<Title card={ card } config={ config } />
				<StyledStar icon={ starFilled } />
			</Header>

			{ card.data.news && (
				<StyledSection isSmall={ isSmall }>
					<StyledSectionTitle>
						<Text
							icon={ shield }
							size={ TextSize.LARGE }
							variant={ TextVariant.DARK }
							weight={ 600 }
							text={ __( 'Latest in Solid Security News', 'better-wp-security' ) }
						/>
					</StyledSectionTitle>
					<StyledBlogLink href={ card.data.news.link } target="_blank" rel="noreferrer">
						<StyledBlogItem>
							<StyledBlogText>
								<Text as="p" weight={ 600 } text={ card.data.news.title } truncateLines={ 1 } />
								<Text as="p"
									size={ TextSize.SMALL }
									variant={ TextVariant.MUTED }
									text={ sprintf(
										/* translators: The date of the last vulnerability report. */
										__( 'Published - %s', 'better-wp-security' ),
										dateI18n( 'M d, Y', card.data.news.date )
									) }
								/>
								<Text as="p" size={ TextSize.SMALL } text={ card.data.news.excerpt } truncateLines={ 1 } />
							</StyledBlogText>
							<StyledBlogIcon icon={ arrowRight } />
						</StyledBlogItem>
					</StyledBlogLink>
				</StyledSection>
			) }

			<StyledSection isSmall={ isSmall }>
				<StyledSectionTitle>
					<Text
						icon={ warning }
						size={ TextSize.LARGE }
						variant={ TextVariant.DARK }
						weight={ 600 }
						text={ __( 'Vulnerabilities', 'better-wp-security' ) }
					/>
					{ card.data.vulnerability &&
						<>
							<Text variant={ TextVariant.MUTED }>&#124;</Text>
							<Button
								href={ vulnerabilitiesPage }
								variant="link"
								text={ __( 'Fix Vulnerabilities', 'better-wp-security' ) }
							/>
						</>
					}
				</StyledSectionTitle>
				{ card.data.vulnerability
					? <Vulnerability vulnerability={ card.data.vulnerability } isSmall={ isSmall } />
					: <Notice text={ __( 'Great news! There seem to be no vulnerable software installed!', 'better-wp-security' ) } />
				}
			</StyledSection>
		</>
	);
}

export const slug = 'security-summary';
export const settings = {
	render: SecuritySummary,
	elementQueries: [
		{
			type: 'width',
			dir: 'max',
			px: 400,
		},
	],
};
