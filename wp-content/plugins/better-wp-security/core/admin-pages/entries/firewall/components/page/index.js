/**
 * Internal dependencies
 */
import Header from '../header';
import Nav from '../nav';
import { StyledPage } from './styles';

export default function Page( { children } ) {
	return (
		<StyledPage>
			<Header />
			<Nav />
			{ children }
		</StyledPage>
	);
}
