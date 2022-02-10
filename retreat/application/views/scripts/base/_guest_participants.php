<?php foreach ($personRange as $guestType => $data):
    $min = is_array($data)? reset($data) : 0;
    $max = is_array($data)? end($data) : 0;
    $min = !empty($min)? $min : 0;
    $max = !empty($max)? $max : 100;
    $value = isset($guestTypes, $guestTypes[$guestType])? (int) $guestTypes[$guestType] : 0;
    ?>
    <div class="row">
        <label class="label col col-3"><?php echo $suite->getGuestTypeLabel($guestType) ?></label>
        <section class="col col-6">
            <label class="guest-type input state-disabled">
                <input class="guest-type-input guest-type-<?php echo $guestType ?>" style="padding: 0px 45px;"
                       name="guest_type[<?php echo $formId ?>][<?php echo $guestType ?>]"
                       disabled="disabled"
                       data-min="<?php echo $min ?>"
                       data-max="<?php echo $max ?>"
                       value="<?php echo $value ?>"
                    />
            </label>
        </section>
        <div class="guest-price-<?php echo $guestType ?> guest-price label col" style="padding-right: 0;">$<?php
            echo isset($guestPrices[$guestType])? $guestPrices[$guestType] : 0 ?></div>
    </div>
<?php endforeach; ?>

