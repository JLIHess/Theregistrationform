<?php
$isAdmin = (isset($_SESSION['admin'])) ? true : false;
$disabledBeddingOptions = [];
if (!$isAdmin) {
    $disabledBeddingOptions = [
        'rollaway',
    ];
}

if(!empty($beddingOptions)): ?>
    <fieldset>
        <h3>Bedding options</h3>
        <div class="row">
            <section class="col col-6">
                <?php foreach ($beddingOptions as $key => $item): ?>
                    <label class="radio">
                        <input type="radio" name="bedding[<?php echo $formId ?>]" value="<?php echo $item ?>"
                               <?php
                        echo (isset($data['bedding']) && $data['bedding'] == $item)? 'checked="checked"' : ''
                        ?>>
                        <i></i><?php echo $item ?></label>
                <?php endforeach; ?>
            </section>
        </div>
        <?php if (!empty($additionalBeddingOptions) && is_array($additionalBeddingOptions)): ?>
            <div class="row additional-bed-options">
                <section class="col">
                    <label>Additional:</label>
                    <?php foreach ($additionalBeddingOptions as $key => $label):
                        if (in_array($key, $disabledBeddingOptions)) continue;
                        ?>
                        <label class="checkbox">
                            <input type="checkbox"
                                   name="additional_bedding[<?php echo $formId ?>][<?php echo $key ?>]"
                                   value="1" <?php
                            echo (!empty($data['additional_bedding'][$key]))? ' checked="checked"' : '' ?> />
                            <i></i><?php echo $label ?> </label>
                    <?php endforeach; ?>
                </section>
            </div>
        <?php endif; ?>
    </fieldset>
<?php endif; ?>