import {TimelineLite} from 'gsap';

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
  let timeline;

  function initElements() {
    $loadingContainer = $('.c-page-loading-container');
    timeline = new TimelineLite({paused: true});
    timeline.to($loadingContainer, 1, {opacity: 0});
    timeline.to($loadingContainer, 0, {scale: 0});
  }

  function initEvents() {
    $(window).on(LOADING_FINISHED_EVENT, () => {
      timeline.play();
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

export default new LoadingHelper($);
