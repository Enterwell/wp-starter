/**
 * External dependencies
 */
import { escapeRegExp, isEmpty } from 'lodash';

/**
 * Internal dependencies
 */
import { STORE_NAME } from './';

/**
 * @typedef {{title: string, description: string, route: string}} Item
 */
export default class Engine {
	#search;
	#registry;
	#providers;
	#regex;
	#words;

	/**
	 * Constructs a search engine instance.
	 *
	 * @param {string} search   The search term.
	 * @param {Object} registry The current registry.
	 */
	constructor( search, registry ) {
		this.#search = search;
		this.#registry = registry;
		this.#providers = registry.select( STORE_NAME ).getProviders();
		this._prepare();
	}

	_prepare() {
		this.#words = this.#search
			.split( /\s+/g )
			.map( ( s ) => s.trim().toLowerCase() )
			.filter( ( s ) => !! s );
		const hasTrailingSpace = this.#search.endsWith( ' ' );
		this.#regex = new RegExp(
			this.#words
				.map( ( word, i ) => {
					if ( i + 1 === this.#words.length && ! hasTrailingSpace ) {
						// The last word - ok with the word being "startswith"-like
						return `(?=.*\\b${ escapeRegExp( word ) })`;
					}
					// Not the last word - expect the whole word exactly
					return `(?=.*\\b${ escapeRegExp( word ) }\\b)`;
				} )
				.join( '' ) + '.+',
			'gi'
		);
	}

	_stringMatch( term ) {
		return term && this.#regex.test( term );
	}

	_keywordMatch( keywords ) {
		if ( ! keywords || ! keywords.length ) {
			return false;
		}

		return keywords.some( ( keyword ) =>
			this.#words.some( ( word, i ) => {
				if ( keyword.includes( ' ' ) ) {
					return this._stringMatch( keyword );
				}

				return i === this.#words.length - 1
					? keyword.startsWith( word )
					: word === keyword;
			} )
		);
	}

	/**
	 * Finds search results.
	 *
	 * @return {Array<Object<{title: string, items: Array<Item>, groups: Object<{title: string, items: Array<Item> }>}>, number>} Found results.
	 */
	getResults() {
		if ( this.#search.length < 3 ) {
			return [ {}, 0 ];
		}

		let count = 0;
		const results = [];

		const evaluate = {
			stringMatch: ( string ) => {
				return this._stringMatch( string );
			},
			keywordMatch: ( keywords ) => {
				return this._keywordMatch( keywords );
			},
		};

		for ( const provider of this.#providers ) {
			const template = {
				title: provider.title,
				items: [],
				groups: {},
			};

			count += provider.callback( {
				evaluate,
				results: template,
				registry: this.#registry,
			} );

			if ( template.items.length || ! isEmpty( template.groups ) ) {
				results.push( template );
			}
		}

		return [ results, count ];
	}
}
