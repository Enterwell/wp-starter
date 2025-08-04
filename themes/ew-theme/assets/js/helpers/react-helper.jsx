// Imports
import React from 'react';
import {AppContainer} from 'react-hot-loader';
import {render} from 'react-dom';
import {Provider} from 'mobx-react';

/**
 * React helper class.
 */
class ReactHelper {
  /**
     * Renders the react component.
     *
     * @param {*} component Root react component
     * @param {*} root Element in which React will render
     * @param {object} rootStoreObj Object that will be exploded as props to Mobx Provider
     */
  renderComponent(component, root, rootStoreObj = {}) {
    // Renders the react
    render(
      <AppContainer>
        <Provider {...rootStoreObj}>
          <React.Fragment>
            {component}
          </React.Fragment>
        </Provider>
      </AppContainer>,
      root
    );
  }
}

// Exports
export default new ReactHelper();
