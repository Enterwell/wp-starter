/**
 * WordPress dependencies
 */
import { arrowRight as goIcon } from '@wordpress/icons';

/**
 * Solid dependencies
 */
import { SurfaceVariant, TextSize, TextVariant, TextWeight } from '@ithemes/ui';

/**
 * Internal dependencies
 */
import {
	StyledSelectableCard,
	StyledTitle,
	StyledDescription,
	StyledIconContainer,
	StyledIcon,
	StyledGoIcon,
	StyledButtonWrapper,
} from './styles';

export default function SelectableCard( {
	onClick,
	title,
	description,
	icon,
	direction = 'horizontal',
	className,
	disabled,
	isSelected,
} ) {
	return (
		<StyledButtonWrapper
			onClick={ onClick }
			className={ className }
			variant="none"
			disabled={ disabled }
			aria-pressed={ isSelected }
		>
			<StyledSelectableCard direction={ direction }>
				<StyledIconContainer variant={ SurfaceVariant.SECONDARY }>
					<StyledIcon icon={ icon } size={ 30 } />
				</StyledIconContainer>
				<StyledTitle
					level={ 4 }
					size={ TextSize.LARGE }
					weight={ TextWeight.HEAVY }
					text={ title }
				/>
				{ description && (
					<StyledDescription
						variant={ TextVariant.MUTED }
						text={ description }
						align={ direction === 'vertical' ? 'center' : 'left' }
					/>
				) }
				{ direction === 'horizontal' && <StyledGoIcon icon={ goIcon } /> }
			</StyledSelectableCard>
		</StyledButtonWrapper>
	);
}
