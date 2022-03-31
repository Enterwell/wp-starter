/**
 * Capitalise string
 * @param string
 * @returns {string}
 */
const capitaliseString = string => string.charAt(0).toUpperCase() + string.slice(1);

/**
 * Get prefixed attribute from blocks attributes object
 * @param {string} prefix Unique component prefix
 * @param {string} attribute Attribute name
 * @param {object} attributes Block attributes object
 * @returns {*}
 */
export const getAttr = (prefix, attribute, attributes) => {
	return attributes[`${prefix}${capitaliseString(attribute)}`];
};

/**
 * Get prefixed attribute key
 * @param {string} prefix Unique component prefix
 * @param {string} attribute Attribute name
 * @returns {string}
 */
export const getAttrKey = (prefix, attribute) => {
	return `${prefix}${capitaliseString(attribute)}`;
};