import {createElement} from '@wordpress/element';

/**
 * Capitalise string
 * @param string
 * @returns {string}
 */
const capitaliseString = string => string.charAt(0).toUpperCase() + string.slice(1);

/**
 * Uncapitalise string
 * @param string
 * @returns {string}
 */
const uncapitaliseString = string => string.charAt(0).toLowerCase() + string.slice(1);

/**
 * Get prefixed attribute key
 * @param {string} prefix Unique component prefix
 * @param {string} attribute Attribute name
 * @returns {string}
 */
export const getAttrKey = (prefix, attribute) => {
	return `${prefix}${capitaliseString(attribute)}`;
};

/**
 * Get prefixed attribute from blocks attributes object
 * @param {string} prefix Unique component prefix
 * @param {string} attribute Attribute name
 * @param {object} attributes Block attributes object
 * @returns {*}
 */
export const getAttr = (prefix, attribute, attributes) => {
	return attributes[getAttrKey(prefix, attribute)];
};

/**
 * Wrapper for component exports
 * Extends attributes functionality
 * @param Component
 * @returns {function(*=): *}
 */
export const withAttr = (Component) => function ExtendedComponent(props) {

	// Return component AS IS for the ones with no props, prefix or attributes
	if(!props || !props.prefix || !props.attributes) {
		console.warn('Provide block attributes to component while instantiating it, together with unique prefix.');

		return createElement(Component, props ? {...props} : {}, props.children ? props.children : null);
	}

	/**
	 * Wrapper for attributes object
	 * Removes prefixes for usage inside component
	 */
	const attributes = (() => {
		let newAttributes = {};

		// Remove prefix from each attribute
		Object.keys(props.attributes).forEach(key => {
			// Remove prefix from attr and uncapitalise from camelCase
			const newKey = uncapitaliseString(key.replace(props.prefix, ''));

			// Add newly keyed attribute to new array
			newAttributes[newKey] = props.attributes[key];
		});

		return newAttributes;
	})();

	/**
	 * Wrapper for setAttributes function
	 * Adds prefix to each attribute before setting it
	 * @param attributes
	 */
	const setAttributes = (attributes) => {
		let newAttributes = {};

		// Add prefix to each attribute
		Object.keys(attributes).forEach(key => {
			newAttributes[getAttrKey(props.prefix, key)] = attributes[key];
		});

		// Set new prefixed attributes with native setAttributes function
		props.setAttributes(newAttributes);
	};

	// Return component with extended functionality
	return createElement(Component, {...props, attributes, setAttributes}, props.children ? props.children : null);
};