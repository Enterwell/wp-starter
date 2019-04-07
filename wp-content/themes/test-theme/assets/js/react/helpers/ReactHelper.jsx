// Imports
import React from 'react';
import {AppContainer} from 'react-hot-loader';
import {render} from 'react-dom';

/**
 * React helper class.
 */
class ReactHelper {
  /**
     * Renders the react component.
     *
     * @param {*} component
     * @param {*} root
     */
  renderComponent(component, root) {
    // Renders the react
    render(
      <AppContainer>
        <React.Fragment>
          {component}
        </React.Fragment>
      </AppContainer>,
      root
    );
  }
}

// Exports
export default new ReactHelper();
