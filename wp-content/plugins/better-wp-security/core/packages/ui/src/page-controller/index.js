/**
 * External dependencies
 */
import styled from '@emotion/styled';
/**
 * WordPress dependencies
 */
import { __, sprintf } from '@wordpress/i18n';
import { SVG, Circle } from '@wordpress/primitives';
import { Flex } from '@wordpress/components';

/**
 * SolidWP dependencies
 */
import { Button } from '@ithemes/ui';

const StyledFlexContainer = styled( Flex )`
	margin-top: 2.5rem;
`;

const PageControlIcon = () => (
	<SVG width="8" height="8" fill="none" xmlns="http://www.w3.org/2000/svg">
		<Circle cx="4" cy="4" r="4" />
	</SVG>
);

export default function PageControl( { currentPage, numberOfPages, setCurrentPage, onClose, allowNavigation = true } ) {
	const next = ( ) => {
		setCurrentPage( currentPage + 1 );
	};

	return (
		<StyledFlexContainer
			expand={ false }
			direction="column"
			gap="2.5rem"
			align="center"
		>
			{ allowNavigation && (
				currentPage < numberOfPages - 1
					? (
						<Button variant="primary" onClick={ next } text={ __( 'Next', 'better-wp-security' ) } />
					)
					: (
						<Button variant="primary" onClick={ onClose } text={ __( 'Done', 'better-wp-security' ) } />
					)
			) }

			<ul
				className="components-guide__page-control"
				aria-label={ __( 'Guide controls', 'better-wp-security' ) }
			>
				{ Array.from( { length: numberOfPages } ).map( ( _, page ) => (
					<li
						key={ page }
						// Set aria-current="step" on the active page, see https://www.w3.org/TR/wai-aria-1.1/#aria-current
						aria-current={ page === currentPage ? 'step' : undefined }
					>
						<Button
							variant="link"
							key={ page }
							icon={ <PageControlIcon /> }
							aria-label={ sprintf(
								/* translators: 1: current page number 2: total number of pages */
								__( 'Page %1$d of %2$d', 'better-wp-security' ),
								page + 1,
								numberOfPages
							) }
							onClick={ () => setCurrentPage( page ) }
							disabled={ ! allowNavigation }
						/>
					</li>
				) ) }
			</ul>
		</StyledFlexContainer>
	);
}
