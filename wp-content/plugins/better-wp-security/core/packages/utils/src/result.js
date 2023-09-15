import { castWPError } from './';

export default class Result {
	type;
	error;
	data;
	success;
	info;
	warning;

	constructor( type, error, data, success = [], info = [], warning = [] ) {
		this.type = type;
		this.error = error;
		this.data = data;
		this.success = success;
		this.info = info;
		this.warning = warning;

		Object.seal( this );
	}

	isSuccess() {
		return this.type === Result.SUCCESS;
	}

	/**
	 * Creates a Result object from an API response.
	 *
	 * @param {Response} response Response object.
	 * @return {Result} The result.
	 */
	static async fromResponse( response ) {
		const getMessages = ( type ) => {
			const header = response.headers?.get( `X-Messages-${ type }` );

			return header ? JSON.parse( header ) : [];
		};

		const json =
			response.status !== 204 && response.json
				? await response.json()
				: null;
		const error = castWPError( json );
		const type = error.hasErrors() ? Result.ERROR : Result.SUCCESS;
		const success = getMessages( 'Success' );
		const info = getMessages( 'Info' );
		const warning = getMessages( 'Warning' );

		return new Result( type, error, json, success, info, warning );
	}
}

Object.defineProperty( Result, 'SUCCESS', {
	value: 'success',
	writable: false,
	enumerable: false,
	configurable: false,
} );

Object.defineProperty( Result, 'ERROR', {
	value: 'error',
	writable: false,
	enumerable: false,
	configurable: false,
} );
