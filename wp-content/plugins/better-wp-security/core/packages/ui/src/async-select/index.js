/**
 * External dependencies
 */
import { lazy, Suspense } from 'react';
import { ErrorBoundary } from 'react-error-boundary';
import { useTheme } from '@emotion/react';
import { css, cx } from '@emotion/css';

/**
 * WordPress dependencies
 */
import { Spinner } from '@wordpress/components';
import { __ } from '@wordpress/i18n';

/**
 * Async dependencies
 */
const Select = lazy( () => import( 'react-select/async' ) );

function LoadError() {
	return <span>{ __( 'Error when loading. Please refresh.', 'better-wp-security' ) }</span>;
}

const hideInputFocus = css`
	input:focus {
		box-shadow: none;
	}
`;

export default function AsyncSelect( { addErrorBoundary = true, className, ...rest } ) {
	const theme = useTheme();

	const s = (
		<Suspense fallback={ <Spinner /> }>
			<Select { ...rest }
				className={ cx( className, hideInputFocus ) }
				theme={ ( base ) => ( {
					...base,
					colors: {
						...base.colors,
						primary: theme.colors.primary.base,
						primary75: theme.colors.secondary.base,
						primary50: theme.colors.tertiary.base,
						primary25: theme.colors.surface.secondary,
					},
				} ) }
			/>
		</Suspense>
	);

	return addErrorBoundary ? (
		<ErrorBoundary FallbackComponent={ LoadError }>{ s }</ErrorBoundary>
	) : (
		s
	);
}
