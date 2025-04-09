/**
 * Internal dependencies
 */
import {
	LogoFreeColor,
	LogoFreeWhite,
	LogoProColor,
	LogoProWhite,
} from '@ithemes/security-style-guide';
import { useConfigContext } from '../../utils';

export default function Logo( { style, className } ) {
	const { installType } = useConfigContext();
	let Component;

	if ( installType === 'pro' ) {
		Component = style === 'white' ? LogoProWhite : LogoProColor;
	} else {
		Component = style === 'white' ? LogoFreeWhite : LogoFreeColor;
	}

	return <Component className={ className } />;
}
