// Imports
import axios from 'axios';

/**
 * Api service class.
 *
 * @class ApiService
 */
class ApiService {
  /**
   * Constructor for api service.
   * @param apiData
   */
  constructor(apiData = {}) {
    this.apiUrl = apiData.apiUrl || '/wp-json/wp-podravka-juhe/v1/';
    this.apiNonce = apiData.apiNonce || '';
  }

  /**
   * Gets request url.
   * @param requestUrl
   * @return {*}
   * @private
   */
  _getRequestUrl(requestUrl) {
    return requestUrl[0] === '/' ? requestUrl : this.apiUrl + requestUrl;
  }

  /**
   * API request method.
   *
   * @param {string} method
   * @param {string} requestUrl
   * @param {object} data
   * @param {object} headers
   * @private
   */
  _request(method, requestUrl, data, headers = {}) {
    // Get full url
    const url = this._getRequestUrl(requestUrl);

    // Create axios args
    const requestArgs = {
      url,
      method,
      data,
      headers: { ...headers },
    };

    // Add api nonce
    if (this.apiNonce) {
      requestArgs.headers['x-wp-nonce'] = this.apiNonce;
    }

    return axios(requestArgs);
  }

  /**
   * Makes the POST HTTP request.
   *
   * @static
   * @param {string} url
   * @param {object} data
   * @param {object} headers
   * @returns {promise} response - Response
   * @memberof ApiService
   */
  post(url, data, headers = {}) {
    return this._request('post', url, data, headers);
  }

  /**
   * Makes the PUT HTTP request.
   *
   * @static
   * @param {string} url
   * @param {object} data
   * @param {object} headers
   * @returns {promise} response - Response
   * @memberof ApiService
   */
  put(url, data, headers = {}) {
    return this._request('put', url, data, headers);
  }

  /**
   * Makes the DELETE HTTP request.
   *
   * @static
   * @param {string} url
   * @param {object} data
   * @param {object} headers
   * @returns {promise} response - Response
   * @memberof ApiService
   */
  delete(url, data, headers = {}) {
    return this._request('delete', url, data, headers);
  }

  /**
   * Creates the GET HTTP request.
   *
   * @static
   * @param {string} url
   * @param {object} headers
   * @returns {promise} response - Response
   * @memberof ApiService
   */
  get(url, headers = {}) {
    return this._request('get', url, {}, headers);
  }
}

// Export
export default ApiService;
