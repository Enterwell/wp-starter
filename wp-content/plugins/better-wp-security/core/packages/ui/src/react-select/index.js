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
import { useCallback, useMemo } from '@wordpress/element';

/**
 * Async dependencies
 */
const ReactSelect = lazy( () => import( 'react-select' ) );
const ReactAsyncSelect = lazy( () => import( 'react-select/async' ) );
const ReactCreatableSelect = lazy( () => import( 'react-select/creatable' ) );

function LoadError() {
	return <span>{ __( 'Error when loading. Please refresh.', 'better-wp-security' ) }</span>;
}

const inputTweaks = css`
	input {
		min-height: 0;
	}

	input:focus {
		box-shadow: none;
	}
`;

function useApplyTheme() {
	const theme = useTheme();

	return useCallback( ( base ) => ( {
		...base,
		colors: {
			...base.colors,
			primary: theme.colors.primary.base,
			primary75: theme.colors.secondary.base,
			primary50: theme.colors.tertiary.base,
			primary25: theme.colors.surface.secondary,
		},
	} ), [ theme ] );
}

function useApplyStyles() {
	return useMemo( () => {
		return {
			control: ( base, state ) => ( {
				...base,
				minHeight: 36,
				borderColor: state.isFocused ? base.borderColor : 'rgb(148, 148, 148)',
				borderRadius: 2,
			} ),
			dropdownIndicator: ( base ) => ( {
				...base,
				padding: 6,
			} ),
			clearIndicator: ( base ) => ( {
				...base,
				padding: 6,
			} ),
			loadingIndicator: ( base ) => ( {
				...base,
				padding: 6,
			} ),
			valueContainer: ( base ) => ( {
				...base,
				paddingTop: 0,
				paddingBottom: 0,
			} ),
			input: ( base ) => ( {
				...base,
				paddingTop: 0,
				paddingBottom: 0,
			} ),
		};
	}, [] );
}

export function Select( { addErrorBoundary = true, className, ...rest } ) {
	const applyTheme = useApplyTheme();
	const applyStyles = useApplyStyles();

	const s = (
		<Suspense fallback={ <Spinner /> }>
			<ReactSelect { ...rest }
				className={ cx( className, inputTweaks ) }
				theme={ applyTheme }
				styles={ applyStyles }
			/>
		</Suspense>
	);

	return addErrorBoundary ? (
		<ErrorBoundary FallbackComponent={ LoadError }>{ s }</ErrorBoundary>
	) : (
		s
	);
}

export function AsyncSelect( { addErrorBoundary = true, className, ...rest } ) {
	const applyTheme = useApplyTheme();
	const applyStyles = useApplyStyles();

	const s = (
		<Suspense fallback={ <Spinner /> }>
			<ReactAsyncSelect { ...rest }
				className={ cx( className, inputTweaks ) }
				theme={ applyTheme }
				styles={ applyStyles }
			/>
		</Suspense>
	);

	return addErrorBoundary ? (
		<ErrorBoundary FallbackComponent={ LoadError }>{ s }</ErrorBoundary>
	) : (
		s
	);
}

export function CreatableSelect( { addErrorBoundary = true, className, ...rest } ) {
	const applyTheme = useApplyTheme();
	const applyStyles = useApplyStyles();

	const s = (
		<Suspense fallback={ <Spinner /> }>
			<ReactCreatableSelect { ...rest }
				className={ cx( className, inputTweaks ) }
				theme={ applyTheme }
				styles={ applyStyles }
			/>
		</Suspense>
	);

	return addErrorBoundary ? (
		<ErrorBoundary FallbackComponent={ LoadError }>{ s }</ErrorBoundary>
	) : (
		s
	);
}
