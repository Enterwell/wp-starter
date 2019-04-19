// Imports
import {action, observable} from 'mobx';
import DateTimeHelper from '../../../helpers/DateTimeHelper';

// Consts
const INTERVAL_IN_MS = 10;

/**
 * Home view model.
 */
class HomeViewModel {
  // Time spent
  @observable timeSpent = 0;
  @observable currentUTCTime = DateTimeHelper.toISOstring(new Date());

  /**
   * Creates the new instance.
   */
  constructor() {
    // Sets the interval
    setInterval(this.onInterval, INTERVAL_IN_MS);
  }

  /**
   * On interval callback.
   */
  @action.bound
  onInterval() {
    // Updates the time spent
    this.timeSpent += INTERVAL_IN_MS;
    this.currentUTCTime = DateTimeHelper.toISOstring(new Date());
  }
}

// Export
export default HomeViewModel;
