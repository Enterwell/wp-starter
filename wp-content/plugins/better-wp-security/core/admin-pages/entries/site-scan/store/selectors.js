/**
 * External dependencies
 */
import createSelector from 'rememo';
import { findKey, reduce, sortBy } from 'lodash';

/**
 * Internal dependencies
 */
import { STATUS_WAITING, STATUS_BUSY, STATUS_DONE, STATUS_FAILED } from './constant';

/**
 * Returns the list of registered scan components in priority order.
 *
 * @type {function(Object): Array<Object>}
 */
export const getScanComponents = createSelector(
	( state ) => sortBy( state.components, 'priority' ),
	( state ) => ( [ state.components ] )
);

/**
 * Gets a component by its slug.
 *
 * @param {Object} state The application state.
 * @param {string} slug  The component slug.
 * @return {Object} The component.
 */
export function getComponentBySlug( state, slug ) {
	return state.components[ slug ];
}

/**
 * Gets a component group.
 *
 * @param {Object} state The application state.
 * @param {string} group The slug of the group.
 * @return {Object} The component group.
 */
export function getScanGroup( state, group ) {
	return state.componentGroups[ group ];
}

/**
 * Returns the status of a scan component
 *
 * @param {Object} state The application state.
 * @param {string} slug  Component slug.
 * @return {string} Current component status.
 */
export function getScanComponentStatus( state, slug ) {
	return state.componentStatus[ slug ] || STATUS_WAITING;
}

/**
 * Returns a list of issues
 *
 * @param {Object} state The application state.
 * @return {Array} List of issues.
 */
export function getIssues( state ) {
	return state.issues;
}

/**
 * Gets an issue by its self link.
 *
 * @param {Object} state Store data.
 * @param {string} self  Self link.
 * @return {Object|undefined} The issue data.
 */
export function getIssue( state, self ) {
	return state.bySelf[ self ]?.item;
}

/**
 * Checks if a component has any issues.
 *
 * @param {Object} state The application state.
 * @param {string} slug  The component slug.
 * @return {boolean} True if has issues.
 */
export function componentHasIssues( state, slug ) {
	return state.issues.some( ( issue ) => issue.component === slug && ! issue.muted );
}

/**
 * Return a list of component issues by component
 *
 * @param {Object} state The application state.
 * @param {string} slug  Component slug.
 * @return {Array} List of component issues.
 */
export const getIssuesForComponent = createSelector(
	( state, slug ) => state.issues.filter( ( issue ) => issue.component === slug ),
	( state ) => ( [ state.issues ] ) );

/**
 * Returns a list of issues for a component group.
 *
 * @param {Object} state The application state.
 * @param {string} slug  Component slug.
 * @return {Array} List of issues for component group.
 */
export const getIssuesForComponentGroup = createSelector(
	( state, slug ) => state.issues.filter( ( issue ) => getComponentBySlug( state, issue.component )?.group === slug ),
	( state ) => ( [ state.issues ] )
);

/**
 * Returns a list of errors in the latest scan
 *
 * @return {Array} Scan errors.
 */
export const getErrors = createSelector(
	( state ) => Object.values( state.componentErrors ).concat( Object.values( state.groupErrors ) ),
	( state ) => ( [ state.componentErrors, state.groupErrors ] )
);

/**
 * Gets the error for a scan component.
 *
 * @param {Object} state     The application state.
 * @param {string} component The component slug.
 * @return {Error|undefined} Component error.
 */
export function getErrorForComponent( state, component ) {
	const group = state.components[ component ]?.group;
	if ( group ) {
		return state.groupErrors[ group ];
	}

	return state.componentErrors[ component ];
}

/**
 * Gets the scan status.
 *
 * @param {Object} state The application state.
 * @return {boolean} True if scan is running.
 */
export function isScanRunning( state ) {
	return state.isRunning;
}

/**
 * Checks if a scan has been completed.
 *
 * @param {Object} state The application state.
 * @return {boolean} True if a scan has been run and has finished running.
 */
export function hasCompletedScan( state ) {
	return ! isScanRunning( state ) && Object.keys( state.componentStatus ).length > 0;
}

/**
 * Gets the current scan component.
 *
 * @param {Object} state The application state.
 * @return {string} Current component slug.
 */
export function getCurrentScanComponent( state ) {
	return findKey( state.componentStatus, function( status ) {
		return status === STATUS_BUSY;
	} );
}

/**
 * Gets the previous scan component.
 *
 * @param {Object} state The application state.
 * @return {string} Previous component slug.
 */
export function getPreviousScanComponent( state ) {
	const components = getScanComponents( state );
	const index = components.findLastIndex( ( component ) => {
		return getScanComponentStatus( state, component.slug ) === STATUS_DONE || getScanComponentStatus( state, component.slug ) === STATUS_FAILED;
	} );
	return components[ index ]?.slug;
}

/**
 * Get the upcoming scan component.
 *
 * @param {Object} state The application state.
 * @return {string} Next component slug.
 */
export function getUpcomingScanComponent( state ) {
	if ( getCurrentScanComponent( state ) ) {
		return undefined;
	}

	const components = getScanComponents( state );
	const nextIndex = components.findIndex( ( component ) => {
		return getScanComponentStatus( state, component.slug ) === STATUS_WAITING;
	} );
	return components[ nextIndex ]?.slug;
}

/**
 * Gets the available actions that can be taken for an issue.
 *
 * @param {Object} state Application state.
 * @param {Object} issue Issue item.
 * @return {{rel: string, title: string, isDestructive: boolean}[]} List of actions
 */
export function getIssueActions( state, issue ) {
	return reduce( issue._links, ( acc, links, rel ) => {
		return links.reduce( ( relAcc, link ) => {
			if ( ! link.title ) {
				return relAcc;
			}

			relAcc.push( {
				rel,
				title: link.title,
				isDestructive: link.isDestructive || false,
				snackbar: link.snackbar || false,
			} );

			return relAcc;
		}, acc );
	}, [] );
}

/**
 * Checks if an issue action is being applied.
 *
 * @param {Object} state Store state.
 * @param {Object} issue Issue data or self link.
 * @param {string} rel   Link relation.
 * @return {boolean} True if in progress.
 */
export function isApplyingAction( state, issue, rel ) {
	return state.actions.includes( `${ rel }:${ issue.component }:${ issue.id }` );
}
