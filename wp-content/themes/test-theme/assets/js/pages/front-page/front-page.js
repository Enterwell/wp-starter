function FrontPage ($) {
  function init() {
    console.log('init front page');
  }

  // Explicitly return methods
  // this will set them to public
  return {
    init: init
  };
}

export default new FrontPage($);