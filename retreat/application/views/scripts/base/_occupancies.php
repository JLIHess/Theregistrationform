<?php
$selected = '';
if (isset($data['occupancy'])) {
    $selected = $data['occupancy'];
}

foreach ($occupancies as $label => $value): ?>
    <div class="occupancy">
        <label class="radio">
            <input type="radio" name="occupancy[<?php echo $formId ?>]" value="<?php echo $value ?>" <?php
                echo ($selected == $value)? ' checked' : '' ?> required="required" />
            <i></i><?php echo $label ?></label>
    </div>
<?php endforeach; ?>
