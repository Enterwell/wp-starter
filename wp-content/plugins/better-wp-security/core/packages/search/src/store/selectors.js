/**
 * External dependencies
 */
import createSelector from 'rememo';
import { sortBy } from 'lodash';

export const getProviders = createSelector(
	( state ) => sortBy( state.providers, 'priority' ),
	( state ) => state.providers
);
