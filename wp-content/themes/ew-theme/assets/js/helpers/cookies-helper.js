class CookiesHelper {
  /**
   * Sets cookie, uses cookie duration in days.
   * @param name
   * @param value
   * @param days
   */
  setCookie(name, value, days) {
    let expires = '';
    if (days) {
      const date = new Date();
      date.setTime(date.getTime() + (days * 24 * 60 * 60 * 1000));
      expires = '; expires=' + date.toUTCString();
    }
    document.cookie = name + '=' + (value || '') + expires + '; path=/';
  }

  /**
   * Retrieves cookie by name.
   * @param name
   * @returns {*}
   */
  getCookie(name) {
    const nameEQ = name + '=';
    const ca = document.cookie.split(';');
    for (let i = 0; i < ca.length; i++) {
      let c = ca[i];
      while (c.charAt(0) === ' ') c = c.substring(1, c.length);
      if (c.indexOf(nameEQ) === 0) return c.substring(nameEQ.length, c.length);
    }
    return null;
  }

  /**
   * Removes cookie.
   * @param name
   */
  deleteCookie(name) {
    document.cookie = name + '=; Max-Age=-99999999;';
  }
}

export default new CookiesHelper();
