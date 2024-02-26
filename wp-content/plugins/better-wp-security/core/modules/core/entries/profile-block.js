/**
 * WordPress dependencies
 */
import { registerBlockType } from '@wordpress/blocks';

/**
 * Internal dependencies
 */
import edit from './profile-block/edit';
import metadata from './profile-block/block.json';

registerBlockType( metadata.name, {
	icon: {
		foreground: '#6817c5',
		src: <svg width="10" height="12" viewBox="0 0 10 12" fill="none" xmlns="http://www.w3.org/2000/svg">
			<path fillRule="evenodd" clipRule="evenodd" d="M9.99882 5.67069C9.99957 5.62994 10 5.58913 10 5.54833V2.22496C10 2.22496 8.5511 1.35318 7.6021 0.924993C6.65316 0.49681 5.17773 0.0489895 5.01483 0V0.00876323L4.99992 0.00211166C4.99992 0.00211166 3.32004 0.467037 2.30969 0.925047C1.36451 1.35349 0 2.2315 0 2.2315V5.55492C0 8.38797 1.85489 11.0704 4.47053 11.9165C4.81425 12.0278 5.18564 12.0278 5.52931 11.9165C8.10927 11.0819 9.94909 8.46072 9.99882 5.67069Z" fill="#6817C5" />
			<path fillRule="evenodd" clipRule="evenodd" d="M0.000215147 2.22783L0 2.22794V2.48593C1.53409 5.30842 5.34531 9.06472 8.07171 10.3048C8.63597 9.71784 9.09739 9.02391 9.42718 8.26753C7.26613 7.96225 2.03146 4.64743 0.136209 2.14844C0.049672 2.20292 0.000215147 2.2347 0.000215147 2.2347V2.22783Z" fill="#9675F7" />
		</svg>,
	},
	edit,
} );
