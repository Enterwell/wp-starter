/**
 * WordPress dependencies
 */
import { __ } from '@wordpress/i18n';

/**
 * Solid dependencies
 */
import { Badge } from '@ithemes/ui';

/**
 * Internal dependencies
 */
import { PatchstackMark, MarkPro } from '@ithemes/security-style-guide';

export default function Provider( { provider } ) {
	return (
		<Badge { ...forProvider( provider ) } />
	);
}

function forProvider( provider ) {
	switch ( provider ) {
		case 'patchstack':
			return {
				text: 'Patchstack',
				icon: <PatchstackMark />,
			};
		case 'solid':
			return {
				text: 'Solid Security',
				icon: <MarkPro />,
			};
		default:
			return {
				text: __( 'Custom Rule', 'better-wp-security' ),
				icon: userIcon,
			};
	}
}

const userIcon = (
	<svg width="10" height="13" viewBox="0 0 10 13" fill="none" xmlns="http://www.w3.org/2000/svg">
		<path fillRule="evenodd" clipRule="evenodd" d="M9.76133 11.0773C9.7724 10.9901 9.78226 10.9027 9.78893 10.8155C9.79782 10.7025 9.80226 10.5865 9.80226 10.4674C9.80226 9.27326 9.32787 8.12798 8.48345 7.28356C7.63903 6.43914 6.49376 5.96475 5.29957 5.96475C4.10538 5.96475 2.9601 6.43914 2.11568 7.28356C1.27126 8.12798 0.796875 9.27326 0.796875 10.4674C0.796875 10.5865 0.801319 10.7026 0.810207 10.8155C0.816873 10.9027 0.825673 10.9901 0.837805 11.0773C2.1415 12.0028 3.70075 12.5 5.29957 12.5C6.89839 12.5 8.45764 12.0028 9.76133 11.0773ZM7.07169 1.23194C6.66166 0.822659 6.12242 0.568184 5.54583 0.511865C4.96923 0.455547 4.39094 0.60087 3.90947 0.92308C3.428 1.24529 3.07312 1.72445 2.9053 2.27895C2.73747 2.83345 2.76707 3.42898 2.98906 3.96411C3.21105 4.49923 3.61169 4.94084 4.12274 5.21371C4.63379 5.48659 5.22364 5.57385 5.79181 5.46063C6.35998 5.34741 6.87132 5.04071 7.23874 4.59279C7.60616 4.14486 7.80692 3.5834 7.80683 3.00406C7.80716 2.6748 7.74238 2.34873 7.61622 2.04459C7.49005 1.74046 7.30499 1.46428 7.07169 1.23194Z" fill="#6C6C6C" />
	</svg>
);
