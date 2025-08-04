export const LOADING_FINISHED_EVENT = 'ew-ready';
const LOADING_HIDE_TIMEOUT = 200;

/**
 * Helper that handles loading.
 * @param $
 * @returns {{init(): void, ready(): void}}
 * @constructor
 */
function LoadingHelper($) {
  let $loadingContainer;

  function initElements() {
    $loadingContainer = $('.c-page-loading-container');
  }

  function initEvents() {
    $(window).on(LOADING_FINISHED_EVENT, () => {
      $loadingContainer.addClass('c-page-loading-container--hide');
      setTimeout(() => {
        $loadingContainer.addClass('c-page-loading-container--remove');
      }, LOADING_HIDE_TIMEOUT);
    });
  }

  return {
    init() {
      initElements();
      initEvents();
    },
    hide() {
      setTimeout(() => {
        $(window).trigger(LOADING_FINISHED_EVENT);
      }, LOADING_HIDE_TIMEOUT);
    },
  };
}

export default new LoadingHelper(jQuery);
