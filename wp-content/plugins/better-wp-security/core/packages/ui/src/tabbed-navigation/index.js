/**
 * WordPress dependencies
 */
import { createContext, useContext, useMemo } from '@wordpress/element';

/**
 * Solid dependencies
 */
import { TextSize, TextWeight } from '@ithemes/ui';

/**
 * Internal dependencies
 */
import { StyledNav, StyledTab, StyledTabTitle } from './styles';

const Context = createContext( {
	size: TextSize.LARGE,
} );

export default function TabbedNavigation( { className, size = TextSize.LARGE, children } ) {
	const context = useMemo( () => ( { size } ), [ size ] );

	return (
		<StyledNav className={ className }>
			<Context.Provider value={ context }>
				{ children }
			</Context.Provider>
		</StyledNav>
	);
}

export function NavigationTab( { title, ...props } ) {
	const { size } = useContext( Context );

	return (
		<StyledTab { ...props }>
			<StyledTabTitle size={ size } weight={ TextWeight.HEAVY } text={ title } align="center" />
		</StyledTab>
	);
}
