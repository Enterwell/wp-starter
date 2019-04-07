import React from 'react';
import ReactHelper from '../../react/helpers/ReactHelper';
import Home from '../../react/views/Home/Home';

function HomePage($) {
  // Constants declaration
  const COLOR_RED_CLASS = 'red';

  // Elements declaration
  let $title;
  let $exampleBtn;
  let $reactRoot;

  /**
   * Elements initialization
   */
  function initElements() {
    $title = $('.js-home-title');
    $exampleBtn = $('.js-home-btn');
    $reactRoot = $('#react-root');
  }

  /**
   * Registers all the needed events for the page
   */
  function initEvents() {
    $exampleBtn.on('click', changeTitleColor);
  }

  /**
   * Change title color
   */
  function changeTitleColor() {
    $title.addClass(COLOR_RED_CLASS);
    $exampleBtn.prop('disabled', true);
  }

  /**
   * Renders react.
   */
  function renderReact() {
    // Renders the react
    ReactHelper.renderComponent(<Home/>, $reactRoot[0]);
  }

  function init() {
    initElements();
    initEvents();
    renderReact();
  }

  // Functions that are explicitly returned can be called from the rest of the application
  // Specifically, we'll be able to call init function via HomePage.init() outside this file
  return {
    init
  };
}

export default new HomePage($);
