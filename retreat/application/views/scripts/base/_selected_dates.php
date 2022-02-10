<section class="col">
    <label class="label"><strong>Please select the dates that you will participate</strong></label>
    <?php foreach ($dates as $label => $value): ?>
        <label class="checkbox">
            <input type="checkbox" name="dates[<?php echo $formId ?>][]" value="<?php echo $value ?>" checked />
            <i></i><?php echo $label ?></label>
    <?php endforeach; ?>
</section>



