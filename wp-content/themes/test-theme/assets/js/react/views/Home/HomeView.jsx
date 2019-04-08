import React from 'react';
import { hot } from 'react-hot-loader/root';
import {inject, observer} from 'mobx-react';

/**
 * Home view.
 */
@inject('homeVm') @observer
class HomeView extends React.Component {
  /**
   * View model.
   */
  vm;

  /**
   * Creates new instance of the class.
   */
  constructor(props) {
    super();

    // Sets the vm
    this.vm = props.homeVm;
  }

  /**
   * Render function.
   */
  render() {
    return (
      <div>
        <h1>React radi</h1>
        <img
          style={{display: 'block', margin: '1rem auto'}}
          src="https://i.giphy.com/hGjse6hh37kU8.gif"
          alt="boom"
        />
        <p>
          Ovdje ste već {this.vm.timeSpent / 1000} sekundi.
          <br/>
          To znam jer MobX. 😎
        </p>
      </div>
    );
  }
}

// Export component as hot-exported (enableing hot module reload)
export default hot(HomeView);
