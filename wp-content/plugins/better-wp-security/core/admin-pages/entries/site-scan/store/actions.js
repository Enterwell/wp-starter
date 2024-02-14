/**
 * WordPress dependencies
 */
import apiFetch from '@wordpress/api-fetch';

/**
 * Internal dependencies
 */
import { getLink } from '@ithemes/security-utils';
import { STATUS_WAITING } from './constant';

/**
 * @typedef Execute {function(): Promise<Array<Object>>} An async function that returns a list of issues.
 */

/**
 * Register an item for scan
 *
 * @param {Object}  args             The component args.
 * @param {string}  args.slug        The component slug. It must be globally unique.
 * @param {number}  args.priority    A number between 0 and 100 indicating the component position. 0 is run first.
 * @param {string}  args.label       The label displayed in the component.
 * @param {string}  args.description A description displayed on hover.
 * @param {Execute} [args.execute]   An optional function called on scan start. Resolves to an array of Component
 *                                   Issue or throws an error. If omitted, the component must be included in a group.
 * @return {Object} Action.
 */
export function registerScanComponent( args ) {
	return {
		type: REGISTER_COMPONENT,
		args,
	};
}

/**
 * Group individual scan components
 *
 * @param {Object}  args            The group args.
 * @param {string}  args.slug       A unique slug identifying this group.
 * @param {Array}   args.components An array of component slugs.
 * @param {Execute} args.execute    A function called for all group components on site scan. Resolves to an array
 *                                  of Component Issue or throws an error.
 * @return {Object} Action.
 */
export function registerScanComponentGroup( args ) {
	return {
		type: REGISTER_COMPONENT_GROUP,
		args,
	};
}

/**
 * Runs a scan
 *
 * @return {Array} list of issues | Error.
 */
export const startScan = () => async ( { select, dispatch } ) => {
	dispatch( { type: START_SCAN } );

	const components = select.getScanComponents();
	const completedGroups = {};

	for ( let i = 0; i < components.length; i++ ) {
		const component = components[ i ];
		const status = select.getScanComponentStatus( component.slug );

		if ( status !== STATUS_WAITING ) {
			continue;
		}

		dispatch( {
			type: START_COMPONENT_SCAN,
			component: component.slug,
		} );

		if ( component.group ) {
			if ( completedGroups[ component.group ] === undefined ) {
				try {
					completedGroups[ component.group ] = await Promise.all( [
						select.getScanGroup( component.group ).execute(),
						// Force scans to take at least 2.5s
						wait( 2_500 ),
					] ).then( ( all ) => all[ 0 ] );
				} catch ( error ) {
					dispatch( failedGroupScan( component.group, error ) );
					continue;
				}
			} else {
				await wait( 2_500 );
			}

			const issues = completedGroups[ component.group ];

			if ( Array.isArray( issues ) ) {
				dispatch( finishComponentScan(
					component.slug,
					issues.filter( ( issue ) => issue.component === component.slug )
				) );
			}
		} else {
			try {
				const issues = await Promise.all( [
					component.execute(),
					wait( 2_500 ),
				] ).then( ( all ) => all[ 0 ] );
				dispatch( finishComponentScan( component.slug, issues ) );
			} catch ( error ) {
				dispatch( failedComponentScan( component.slug, error ) );
			}
		}

		if ( i < components.length - 1 ) {
			await wait( 3_750 );
		} else {
			await wait( 2_000 );
		}
	}

	dispatch( { type: FINISH_SCAN } );
	return select.getIssues();
};

/**
 * Finishes a scan.
 *
 * @param {string} component The component slug.
 * @param {Array}  issues    Issues for component.
 * @return {Object} Action.
 */
function finishComponentScan( component, issues ) {
	return {
		type: FINISH_COMPONENT_SCAN,
		component,
		issues,
	};
}

/**
 * Records failed component scan.
 *
 * @param {string} component The component slug.
 * @param {Object} error     The error.
 * @return {Object} Action.
 */
function failedComponentScan( component, error ) {
	return {
		type: FAILED_COMPONENT_SCAN,
		component,
		error,
	};
}

/**
 * Records failed component group scan.
 *
 * @param {string} group The group slug.
 * @param {Object} error The group error.
 * @return {Object} Action.
 */
function failedGroupScan( group, error ) {
	return {
		type: FAILED_GROUP_SCAN,
		group,
		error,
	};
}

/**
 * Applies action to issue.
 *
 * @param {Object} issue The issue data.
 * @param {string} rel   The action link relation.
 * @return {Object} @return Action.
 */
export const applyIssueAction = ( issue, rel ) => async ( { dispatch, select } ) => {
	const actionLink = getLink( issue, rel );

	if ( ! actionLink ) {
		return;
	}

	dispatch( { type: START_ACTION, rel, issue } );

	try {
		const response = await apiFetch( {
			url: actionLink,
			method: 'POST',
		} );
		dispatch( { type: FINISH_ACTION, rel, issue } );

		const component = select.getComponentBySlug( issue.component );
		const group = select.getScanGroup( component.group );

		const newIssue = group?.transform?.( response ) ?? response;
		dispatch( { type: RECEIVE_ISSUE, issue: newIssue } );

		return response;
	} catch ( error ) {
		dispatch( { type: FAILED_ACTION, rel, issue, error } );
		return error;
	}
};

export const REGISTER_COMPONENT = 'REGISTER_COMPONENT';
export const REGISTER_COMPONENT_GROUP = 'REGISTER_COMPONENT_GROUP';
export const START_COMPONENT_SCAN = 'START_SCAN_COMPONENT';
export const FAILED_COMPONENT_SCAN = 'FAILED_COMPONENT_SCAN';
export const FINISH_COMPONENT_SCAN = 'FINISH_COMPONENT_SCAN';
export const FAILED_GROUP_SCAN = 'FAILED_GROUP_SCAN';
export const START_SCAN = 'START_SCAN';
export const FINISH_SCAN = 'FINISH_SCAN';
export const START_ACTION = 'START_ACTION';
export const FINISH_ACTION = 'FINISH_ACTION';
export const RECEIVE_ISSUE = 'RECEIVE_ISSUE';
export const FAILED_ACTION = 'FAILED_ACTION';

const wait = ( timeToDelay ) => new Promise( ( resolve ) => setTimeout( resolve, timeToDelay ) );
