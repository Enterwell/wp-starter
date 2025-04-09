import { StyledHeader } from '../styles';
import { Text, TextSize } from '@ithemes/ui';

export function WelcomeFlowBanner( { text } ) {
	return (
		<StyledHeader variant="dark">
			<Text size={ TextSize.HUGE } variant="white" text={ text } />
		</StyledHeader>
	);
}

export { CardOne } from './welcome';
export { CardTwo, CardThree } from './features';
export { CardFour } from './learn-more';
