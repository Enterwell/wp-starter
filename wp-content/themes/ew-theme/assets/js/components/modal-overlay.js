/*!
 * jQuery lightweight plugin boilerplate
 * Original author: @ajpiano
 * Further changes, comments: @addyosmani
 * Licensed under the MIT license
 */

// the semi-colon before the function invocation is a safety
// net against concatenated scripts and/or other plugins
// that are not closed properly.
function ModalOverlay($, window) {
  // Create the defaults once
  const pluginName = 'ModalOverlay';

  /**
   * Modal plugin default options.
   * This options can be extended in plugin function call.
   */
  const defaults = {
    modalCloseClass: 'c-modal-close',
    modalContentClass: 'c-modal-content',
    bodyModalVisibleClass: 'c-modal-visible',
    modalOpenedClass: 'c-modal-opened',
    modalClosingClass: 'c-modal-closing',
    modalSafariFixClass: 'js-is-safari',
    modalPaddingFixClass: 'js-modal-padding',
    modalContentOpenTimeout: 400,
    onModalClose: undefined,
  };

  /**
   * Esx key.
   *
   * @since 1.0.0
   *
   * @type {number}
   */
  const ESC_KEY = 27;

  /**
   * Plugin function.
   *
   *
   * @param element       DOM element to call jQuery plugin for.
   *                      This is modal to show.
   * @param options       Modal plugin options.
   * @constructor
   */
  function Modal(element, options) {
    // Init base vars
    this.self = this;

    // jQuery has an extend method that merges the
    // contents of two or more objects, storing the
    // result in the first object. The first object
    // is generally empty because we don't want to alter
    // the default options for future instances of the plugin
    this.options = $.extend({}, defaults, options);

    // Is safari
    this.isSafari = navigator.userAgent.indexOf('Safari') > -1;

    // Set window
    this.$window = $(window);

    // Set document body
    this.$body = $('body');

    this.$paddingElements = $(`.${this.options.modalPaddingFixClass}`);

    // Set modal container
    this.$modalContainer = $(element);

    // Get modal id
    this.modalId = this.$modalContainer.attr('id');

    this.scrollbarWidth = 0;
    this.isBodyOverflowing = false;

    this.openModal = this.openModal.bind(this);
    this.closeModal = this.closeModal.bind(this);
    this.onKeyUp = this.onKeyUp.bind(this);

    // Set event handlers for modal
    this.setModalEventHandlers();
  }

  /**
   * Function that sets handlers for modal-related events.
   *
   * @since   1.0.0
   */
  Modal.prototype.setModalEventHandlers = function () {
    // Get elements that open modal
    this.$modalOpenElements = this.$body.find(`[data-modal=${this.modalId}]`);

    // Get elements that close modal
    this.$modalCloseElements = this.$modalContainer.find(`.${this.options.modalCloseClass}`);

    // Handle modal close
    this.$modalCloseElements.on('click', this.closeModal);

    // Open modal on click
    this.$modalOpenElements.on('click', this.openModal);

    // On key up
    $(document).keyup(this.onKeyUp);
  };

  /**
   * Handler on key up event.
   *
   * @since   1.0.0
   */
  Modal.prototype.onKeyUp = function (e) {
    // Close modal on ESC KEY
    if (e.keyCode === ESC_KEY) this.$modalCloseElements.click();
  };

  Modal.prototype.getScrollbarWidth = function () {
    const scrollDiv = document.createElement('div');
    scrollDiv.style.cssText = 'position: absolute;top: -9999px;width: 50px;height: 50px;overflow: scroll;';
    document.body.appendChild(scrollDiv);
    const scrollbarWidth = scrollDiv.getBoundingClientRect().width - scrollDiv.clientWidth;
    document.body.removeChild(scrollDiv);
    return scrollbarWidth;
  };

  Modal.prototype.setScrollbar = function () {
    const rect = document.body.getBoundingClientRect();
    this.isBodyOverflowing = rect.left + rect.right < window.innerWidth;
    this.scrollbarWidth = this.getScrollbarWidth();

    if(!this.isBodyOverflowing || !this.scrollbarWidth) return;

    // Adjust body padding
    const actualPadding = document.body.style.paddingRight;
    const calculatedPadding = $(document.body).css('padding-right');


    $(document.body)
      .data('padding-right', actualPadding)
      .css('padding-right', `${parseFloat(calculatedPadding) + this.scrollbarWidth}px`);

    this.$paddingElements
      .data('padding-right', actualPadding)
      .css('padding-right', `${parseFloat(calculatedPadding) + this.scrollbarWidth}px`);
  };

  /**
   * Function that opens modal.
   *
   * @since   1.0.0
   */
  Modal.prototype.openModal = function (e) {
    if (e) {
      e.preventDefault();
      e.stopPropagation();
    }

    this.setScrollbar();

    // Set body classes
    this.$body.addClass(this.options.bodyModalVisibleClass);
    this.$modalContainer.addClass(this.options.bodyModalVisibleClass);

    // On click outside of modal, close modal
    this.$modalContainer.on('click', (event) => {
      const $modal = this.$modalContainer.find('.c-modal-content');

      if (!$.makeArray($(event.target).parents()).includes($modal[0]))
        this.closeModal();
    });
  };

  Modal.prototype.restoreScrollbar = function () {
    // Restore body padding
    const padding = $(document.body).data('padding-right');
    $(document.body).removeData('padding-right');
    document.body.style.paddingRight = padding || '';

    this.$paddingElements.each((i) => {
      const $element = $(this.$paddingElements[i]);
      const paddingRight = $element.data('padding-right');
      $element.removeData('padding-right');
      $element.css('padding-right', paddingRight || '');
    }).data('padding-right');
  };

  /**
   * Function that closes the modal.
   *
   * @since   1.0.0
   */
  Modal.prototype.closeModal = function (e) {
    if (e) {
      e.preventDefault();
      e.stopPropagation();
    }

    // Call on modal close handler
    if (this.options.onModalClose) {
      this.options.onModalClose();
    }

    const self = this;
    this.$modalContainer.removeClass(this.options.bodyModalVisibleClass);

    setTimeout(() => {
      self.restoreScrollbar();
      self.$body.removeClass(this.options.bodyModalVisibleClass);
    }, this.options.modalContentOpenTimeout);
  };

  /**
   * Function that unloads all modal event handlers.
   *
   * @since 1.0.0
   */
  Modal.prototype.unload = function () {
    this.$modalCloseElements.off('click', this.closeModal);

    this.$modalOpenElements.off('click', this.openModal);
  };

  // You don't need to change something below:
  // A really lightweight plugin wrapper around the constructor,
  // preventing against multiple instantiations and allowing any
  // public function (ie. a function whose name doesn't start
  // with an underscore) to be called via the jQuery plugin,
  // e.g. $(element).defaultPluginName('functionName', arg1, arg2)
  /* eslint-disable */
  $.fn[pluginName] = function (options) {
    const args = arguments;

    // Is the first parameter an object (options), or was omitted,
    // instantiate a new instance of the plugin.
    if (options === undefined || typeof options === 'object') {
      return this.each(function () {
        // Only allow the plugin to be instantiated once,
        // so we check that the element has no plugin instantiation yet
        if (!$.data(this, 'plugin_' + pluginName)) {
          // if it has no instance, create a new one,
          // pass options to our plugin constructor,
          // and store the plugin instance
          // in the elements jQuery data object.
          $.data(this, 'plugin_' + pluginName, new Modal(this, options));
        }
      });

      // If the first parameter is a string and it doesn't start
      // with an underscore or "contains" the `init`-function,
      // treat this as a call to a public method.
    } else if (typeof options === 'string' && options[0] !== '_' && options !== 'init') {
      // Cache the method call
      // to make it possible
      // to return a value
      let returns;

      this.each(function () {
        const instance = $.data(this, 'plugin_' + pluginName);

        // Tests that there's already a plugin-instance
        // and checks that the requested public method exists
        if (instance instanceof Modal && typeof instance[options] === 'function') {
          // Call the method of our plugin instance,
          // and pass it the supplied arguments.
          returns = instance[options](...Array.prototype.slice.call(args, 1));
        }

        // Allow instances to be destroyed via the 'destroy' method
        if (options === 'destroy') {
          $.data(this, 'plugin_' + pluginName, null);
        }
      });

      // If the earlier cached method
      // gives a value back return the value,
      // otherwise return this to preserve chainability.
      return returns !== undefined ? returns : this;
    }
  };
  /* eslint-enable */
}

ModalOverlay(jQuery, window);
