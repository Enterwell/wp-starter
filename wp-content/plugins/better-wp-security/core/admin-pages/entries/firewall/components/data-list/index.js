/**
 * SolidWP dependencies
 */
import {
	SurfaceVariant,
	Text,
	TextSize,
	TextVariant,
	TextWeight,
} from '@ithemes/ui';

/**
 * Internal dependencies
 */
import {
	StyledListContainer,
	StyledListItem,
	StyledHeadingRow,
	StyledBodyRow,
	StyledEmptyState,
	StyledEmptySurface,
	StyledSettingsLink,
} from './styles';

export function DataListDescription( props ) {
	return <Text as="p" size={ TextSize.SMALL } { ...props } />;
}

export function DataListGroup( { heading, children } ) {
	return (
		<StyledListItem>
			{ heading && (
				<StyledHeadingRow
					variant={ SurfaceVariant.SECONDARY }
				>
					<Text weight={ 600 } text={ heading } />
				</StyledHeadingRow>
			) }
			{ children }
		</StyledListItem>
	);
}

export function DataListItem( { text, count, hasHeading } ) {
	return (
		<StyledBodyRow hasHeading={ hasHeading }>
			<Text
				variant={ TextVariant.DARK }
				weight={ 600 }
				text={ text }
			/>
			<Text variant={ TextVariant.DARK } text={ count } />
		</StyledBodyRow>
	);
}

export function DataListEmptyState( { title, description, actionText, actionLink } ) {
	return (
		<StyledEmptyState>
			<StyledEmptySurface>
				<Text
					variant={ TextVariant.MUTED }
					weight={ 600 }
					text={ title }
				/>
				<Text
					variant={ TextVariant.MUTED }
					text={ description }
				/>
			</StyledEmptySurface>
			<StyledSettingsLink variant="link" text={ actionText } href={ actionLink } />
		</StyledEmptyState>
	);
}

export default function DataList( { title, children } ) {
	return (
		<StyledListContainer>
			<Text
				size={ TextSize.LARGE }
				variant={ TextVariant.DARK }
				weight={ TextWeight.HEAVY }
				text={ title }
			/>
			{ children }
		</StyledListContainer>
	);
}
