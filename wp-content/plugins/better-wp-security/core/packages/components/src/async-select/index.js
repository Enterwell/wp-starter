/**
 * External dependencies
 */
import { lazy, Suspense } from 'react';
import { ErrorBoundary } from 'react-error-boundary';

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

export default function AsyncSelect( { addErrorBoundary = true, ...rest } ) {
	const s = (
		<Suspense fallback={ <Spinner /> }>
			<Select { ...rest } />
		</Suspense>
	);

	return addErrorBoundary ? (
		<ErrorBoundary FallbackComponent={ LoadError }>{ s }</ErrorBoundary>
	) : (
		s
	);
}
