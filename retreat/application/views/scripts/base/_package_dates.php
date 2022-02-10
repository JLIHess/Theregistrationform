<fieldset class="date-items-area">
    <label class="label">Which nights do you want to book a room for?</label>
    <div class="row">
        <?php foreach ($dates as $label => $value): ?>
            <label class="checkbox">
                <input type="checkbox" name="dates[<?php echo $formId ?>][]" value="<?php echo $value ?>" checked />
                <i></i><?php echo $label ?></label>
        <?php endforeach; ?>
    </div>
</fieldset>