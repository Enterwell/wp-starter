function HomePage($) {
  // Constants declaration
  const COLOR_RED_CLASS = 'red';

  // Elements declaration
  let $title;
  let $exampleBtn;

  /**
   * Elements initialization
   */
  function initElements() {
    $title = $('.js-home-title');
    $exampleBtn = $('.js-home-btn');
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

  function init() {
    initElements();
    initEvents();
  }

  // Functions that are explicitly returned can be called from the rest of the application
  // Specifically, we'll be able to call init function via HomePage.init() outside this file
  return {
    init: init,
  };
}

export default new HomePage($);