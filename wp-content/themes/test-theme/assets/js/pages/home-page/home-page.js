function HomePage($) {
  // Constants declaration
  const EXAMPLE_CONST = 'example';

  // Elements declaration
  let $homePageTitle;

  /**
   * Elements initialization
   */
  function initElements() {
    $homePageTitle = 'js-home-page-title';
  }

  /**
   * Registers all the needed events for the page
   */
  function initEvents() {

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