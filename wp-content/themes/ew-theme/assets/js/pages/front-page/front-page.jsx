import React from 'react';
import ReactHelper from '../../helpers/react-helper';
import FrontPageView from '../../react/views/front-page/front-page-view';
import FrontPageViewModel from '../../react/views/front-page/front-page-view-model';

import '../../components/modal-overlay';

(function FrontPage($) {
  // Constants declaration
  const COLOR_RED_CLASS = 'red';

  // Elements declaration
  let $title;
  let $exampleBtn;
  let $reactRoot;
  let $frontPageModal;

  /**
   * Elements initialization
   */
  function initElements() {
    $title = $('.js-front-title');
    $exampleBtn = $('.js-front-btn');
    $reactRoot = $('#react-root');

    // Init modal overlay
    $frontPageModal = $('#front-page-modal');
    $frontPageModal.ModalOverlay();
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
    // Creates the view model
    const vm = new FrontPageViewModel();

    // Renders the react
    ReactHelper.renderComponent(<FrontPageView/>, $reactRoot[0], {frontPageVm: vm});
  }

  (function init() {
    initElements();
    initEvents();
    renderReact();
  })();
})(jQuery);
