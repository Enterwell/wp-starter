const Router = function () {
  this.defaults = {
    defaultFunctionName: 'init',
  };

  this.hooks = {
    commonEvents: 'common',
    finalizeEvent: 'finalize',
  };

  this.routes = {};
};

Router.prototype.setRoutes = function (routes) {
  this.routes = routes;
};

Router.prototype.fire = function (scope, functionName, args) {
  // Set function to fire name
  functionName = functionName || this.defaults.defaultFunctionName;

  // Decide weather to fire function with function event
  const toFireFunction = functionName !== '' && this.routes[scope] && typeof this.routes[scope][functionName] === 'function';

  // Fire function
  if (toFireFunction)
    this.routes[scope][functionName](args);
};

Router.prototype.loadEvents = function () {
  // Fire common events
  this.fire(this.hooks.commonEvents);

  // Get body route attributes
  const bodyRouteAttributes = document.body.getAttribute('data-route');

  // If there is any of body route attributes
  if (bodyRouteAttributes) {
    // Get all of them (split by whitespace)
    const bodyRouteAttributesParts = bodyRouteAttributes.replace(/-/g, '_').split(/\s+/);

    // Fire functions for each
    for (let i = 0; i < bodyRouteAttributesParts.length; i++) {
      const dataAttrValue = bodyRouteAttributesParts[i];

      this.fire(dataAttrValue);
      this.fire(dataAttrValue, this.hooks.finalizeEvent);
    }
  }

  // Fire common finalize event handlers
  this.fire(this.hooks.commonEvents, this.hooks.finalizeEvent);
};

export default Router;
