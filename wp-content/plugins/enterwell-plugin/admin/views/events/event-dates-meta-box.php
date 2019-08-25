<?php
/**
 * @var \EwStarter\Event $event
 */
?>

<div class="wrap post-info-table">
    <table class="form-table">
        <tbody>
        <tr>
            <th scope="row">
                Event start date:
            </th>
            <td>
                <input type="text" name="ew_event_start_date"
                       value="<?php echo $event->start_date->format( 'd.m.Y.' ) ?>"/>
            </td>
        </tr>
        <tr>
            <th scope="row">
                Event end date:
            </th>
            <td>
                <input type="text" name="ew_event_end_date"
                       value="<?php echo $event->end_date->format( 'd.m.Y.' ) ?>"/>
            </td>
        </tr>
        </tbody>
    </table>
</div>
