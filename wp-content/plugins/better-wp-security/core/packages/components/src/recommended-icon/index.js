/**
 * External dependencies
 */
import styled from '@emotion/styled';

/**
 * WordPress dependencies
 */
import { Dashicon } from '@wordpress/components';

const StyledRecommendedIcon = styled( Dashicon, { shouldForwardProp: ( ( prop ) => prop !== 'size' && prop !== 'padding' ) } )`
	border-radius: 50%;
	font-size: calc(${ ( { size } ) => size } - (${ ( { padding } ) => padding } * 2));
	padding: ${ ( { padding } ) => padding };
	padding-left: calc(${ ( { padding } ) => padding } + 0.5px);
	background: ${ ( { theme } ) => theme.colors.primary.base };
	color: #ffffff;
`;

export default function RecommendedIcon( { size = 20, className } ) {
	const sizeVar = `var(--itsec-recommended-icon-size, ${ typeof size === 'number' ? `${ size }px` : size })`;
	const padding = `calc(${ sizeVar } / 5)`;

	return (
		<StyledRecommendedIcon className={ className } icon="star-filled" size={ sizeVar } padding={ padding } />
	);
}
