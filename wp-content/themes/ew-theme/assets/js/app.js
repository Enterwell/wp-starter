import '../styles/app.scss';

import LoadingHelper from './helpers/loading-helper';
/**
 * Global script for whole website
 * Runs in every loaded page
 */
(function App($) {
  // Declare elements here

  /**
   * Elements initialization
   */
  function initElements() {
    LoadingHelper.init();
  }

  /**
   * Registers all the needed global events
   */
  function initEvents() {
    $(window).on('load', () => LoadingHelper.hide());
  }

  (function init() {
    initElements();
    initEvents();
  })();
})($);
