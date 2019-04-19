// Imports
import moment from 'moment';

/**
 * Date time helper class
 */
class DateTimeHelper {
  /**
   * Converts the given date to ISO string
   *
   * @param date
   * @returns {string}
   */
  toISOstring(date) {
    let momentDate = moment(date);
    return momentDate.toISOString();
  }
}

export default new DateTimeHelper();