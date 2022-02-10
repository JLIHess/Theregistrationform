<?php
/**
 * @var $suite Model_Suite
 */

$programStartDate = (isset($data['program_start_date']))? $data['program_start_date'] : null;
$programEndDate = (isset($data['program_end_date']))? $data['program_end_date'] : null;
$programStartTime = (isset($data['program_start_time']))? $data['program_start_time'] : null;
$programEndTime = (isset($data['program_end_time']))? $data['program_end_time'] : null;
$programDates = $suite->getProgramDates();

if ($programStartDate == reset($programDates) && $programEndDate == end($programDates)
    && $programStartTime == null && $programEndTime == null
) {
    $programStartTime = $suite->programStartTime;
    $programEndTime = $suite->programEndTime;
}
?>
<section class="col col-6">
    <label>Program Start Time</label>
    <label class="select">
        <select class="program-start-time" name="program_start_time[<?php echo $formId ?>]" required>
            <option selected="selected" disabled="disabled">Please select</option>
            <?php foreach ($suite->getProgramTimeList($programStartDate, null, $programStartTime) as $time): ?>
                <option <?php echo (strtotime($time) == strtotime($programStartTime))? 'selected="selected"' : ''
                ?> value="<?php echo $time ?>"><?php echo $time ?></option>
            <?php endforeach; ?>
        </select>
        <i></i>
    </label>
</section>
<section class="col col-6">
    <label>Program End Time</label>
    <label class="select">
        <select class="program-end-time" name="program_end_time[<?php echo $formId ?>]" required>
            <option selected="selected" disabled="disabled">Please select</option>
            <?php foreach ($suite->getProgramTimeList($programStartDate, $programEndDate, $programStartTime) as $time): ?>
                <option <?php
                echo (strtotime($time) == strtotime($programEndTime))? 'selected="selected"' : ''
                ?> value="<?php echo $time ?>"><?php echo $time ?></option>
            <?php endforeach; ?>
        </select>
        <i></i>
    </label>
</section>
<?php if  ($isAdmin): ?>
    <section class="col col-6">
        <label>Program Start Offset</label>
        <label class="input">
            <input type="text" class="program-start-offset" readonly="readonly"
                   name="program_start_offset[<?php echo $formId ?>]"
                   value="<?php echo !empty($data['program_start_offset'])? $data['program_start_offset'] : '' ?>" />
        </label>
    </section>
    <section class="col col-6">
        <label>Program End Offset</label>
        <label class="input">
            <input type="text" class="program-end-offset" readonly="readonly"
                   name="program_end_offset[<?php echo $formId ?>]"
                   value="<?php echo !empty($data['program_end_offset'])? $data['program_end_offset'] : '' ?>" />
        </label>
    </section>
    <section class="col col-6">
        <label>Program Total Offset</label>
        <label class="input">
            <input type="text" class="program-total-offset" readonly="readonly"
                   name="program_total_offset[<?php echo $formId ?>]"
                   value="<?php echo !empty($data['program_total_offset'])? $data['program_total_offset'] : '' ?>" />
        </label>
    </section>
<?php endif ?>