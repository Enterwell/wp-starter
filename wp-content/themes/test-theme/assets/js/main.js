// Include jquery as global script
require('exports-loader?!gsap');
require('exports-loader?!./vendors/jquery.gsap.min');
require('exports-loader?!slick-carousel');

import 'animation.gsap';
import 'debug.addIndicators';
import FrontPage from './pages/front-page/front-page';

// Include router
import Router from './utils/router';

(function ($) {
  'use strict';

  // Create new router
  const router = new Router();

  // Define all theme routes
  const themeRoutes = {
    common: {
      init: () => {

        // Common scripts to be initialized on all pages
        console.log('common init fired!');
      },

      finalize: () => {

        // Common scripts to be fired after all init scripts executed
        console.log('common finalize fired!');
      },
    },

    frontPage: {
      init: () => {
        FrontPage.init();
      },
    },
  };

  // Init router
  router.setRoutes(themeRoutes);

  // Apply router
  $(document).ready(() => {

    // Load all router events
    router.loadEvents();

  });

})($);