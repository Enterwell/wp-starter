/**
 * WordPress dependencies
 */
import { registerPlugin } from '@wordpress/plugins';
import { __, setLocaleData } from '@wordpress/i18n';
import { dispatch } from '@wordpress/data';
import apiFetch from '@wordpress/api-fetch';

// Silence warnings until JS i18n is stable.
setLocaleData( { '': {} }, 'ithemes-security-pro' );

/**
 * Internal dependencies
 */
import { store } from '@ithemes/security.pages.site-scan';
import { siteScannerStore, vulnerabilitiesStore } from '@ithemes/security.packages.data';
import App from './site-scan/app.js';

function severityLevel( score ) {
	switch ( true ) {
		case score < 3:
			return 'low';
		case score < 7:
			return 'medium';
		case score < 9:
			return 'high';
		default:
			return 'critical';
	}
}

async function googleStatus( id ) {
	return apiFetch( {
		method: 'GET',
		path: `/ithemes-security/v1/site-scanner/scans/${ id }/issues?entry=blacklist`,
	} );
}

function transform( apiData ) {
	const issue = {
		id: apiData.id,
		severity: severityLevel( apiData.details?.score ),
		meta: apiData,
		_links: apiData._links,
	};

	if ( apiData.id === 'google' ) {
		issue.component = apiData.entry;
		issue.title = apiData.description;
	} else {
		issue.component = apiData.software.type.slug;
		issue.title = apiData.software.label || apiData.software.slug || __( 'WordPress', 'better-wp-security' );
		issue.description = apiData.details.type.label;
		issue.muted = apiData.resolution?.slug === 'muted';
	}
	return issue;
}

dispatch( store ).registerScanComponent( {
	slug: 'plugin',
	priority: 15,
	label: __( 'Plugins', 'better-wp-security' ),
	description: __( 'Check for plugins with known vulnerabilities', 'better-wp-security' ),
	group: 'site-scanner',
} );
dispatch( store ).registerScanComponent( {
	slug: 'theme',
	priority: 16,
	label: __( 'Themes', 'better-wp-security' ),
	description: __( 'Check for themes with known vulnerabilities', 'better-wp-security' ),
	group: 'site-scanner',
} );
dispatch( store ).registerScanComponent( {
	slug: 'wordpress',
	priority: 17,
	label: __( 'WordPress Core', 'better-wp-security' ),
	description: __( 'Check for known WordPress Core vulnerabilities', 'better-wp-security' ),
	group: 'site-scanner',
} );
dispatch( store ).registerScanComponent( {
	slug: 'blacklist',
	priority: 18,
	label: __( 'Google Safe Browsing', 'better-wp-security' ),
	description: __( 'Check if your site is safe according to Google Safe Browsing', 'better-wp-security' ),
	group: 'site-scanner',
} );
dispatch( store ).registerScanComponentGroup( {
	slug: 'site-scanner',
	components: [ 'plugin', 'theme', 'wordpress', 'blacklist' ],
	async execute() {
		const scan = await dispatch( siteScannerStore ).runScan();
		const results = await googleStatus( scan.id );
		const issues = results.filter( ( issue ) => issue.status !== 'clean' );
		const vulnerabilities = await dispatch( vulnerabilitiesStore ).query( 'siteScanner', {
			resolution: [ '', 'muted' ],
		} );
		const siteScannerIssues = vulnerabilities.concat( issues );
		return siteScannerIssues.map( transform );
	},

	transform,
} );

registerPlugin( 'itsec-site-scanner-site-scan', {
	render() {
		return <App />;
	},
} );
