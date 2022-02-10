<?php
$datePickerInfo = (!empty($packageDates) && is_array($packageDates)) ?
    'The retreat runs from '
    . date('M jS', strtotime(reset($packageDates))) . ' 3:00PM until '
    . date('M jS', strtotime(end($packageDates))) . ' 3:00PM' : '';

$data['hotel_availability'] = (!isset($data['hotel_availability'])) ? 1 : $data['hotel_availability'];
?>

<div id="room-<?php echo $formId ?>" class="rooming-form" data-room_id="<?php echo $formId ?>">

    <input type="hidden" name="form_id" value="<?php echo $formId ?>"/>
    <fieldset class="dates-area">

        <h3>Step 1: Choose program dates</h3>
        <div class="row program-dates-area">
            <section class="col col-6">
                <label>Program start date</label>
                <label class="input">
                    <i class="icon-append fa fa-calendar"></i>
                    <input class="program-start-date" name="program_start_date[<?php echo $formId ?>]" type="text"
                           required="required" data-format="<?php echo $suite->datePickerFormat ?>"
                           value="<?php echo isset($data['program_start_date']) ? $data['program_start_date'] : '' ?>"
                           data-info="<?php echo $datePickerInfo ?>"/>
                </label>
            </section>
            <section class="col col-6">
                <label>Program end date</label>
                <label class="input">
                    <i class="icon-append fa fa-calendar"></i>
                    <input class="program-end-date" name="program_end_date[<?php echo $formId ?>]" type="text"
                           required="required" data-format="<?php echo $suite->datePickerFormat ?>"
                           data-info="<?php echo $datePickerInfo ?>"
                           value="<?php echo isset($data['program_end_date']) ? $data['program_end_date'] : '' ?>"/>
                </label>
            </section>
        </div>
        <h3>Step 2: Choose program times</h3>
        <div class="row program-times-area">
            <?php $this->renderPartial('_program_times', array(
                'formId' => $formId, 'data' => $data, 'suite' => $suite, 'isAdmin' => $isAdmin
            )); ?>
        </div>
        <h3>Step 3: Would you like to include hotel nights in your package?</h3>
        <div class="row hotel-availability-area">
            <div class="col col-11">
                <label class="radio">
                    <input type="radio" name="hotel_availability[<?php echo $formId ?>]" value="1"
                        <?php echo ($data['hotel_availability'] == 1) ? 'checked="checked"' : '' ?>/><i></i>Yes, please
                    include the following nights:
                </label>
            </div>
            <div
                class="hotel-dates-area" <?php echo (empty($data['hotel_availability'])) ? 'style="display:none"' : '' ?>>
                <section class="col col-6">
                    <label>Hotel start date</label>
                    <label class="input">
                        <i class="icon-append fa fa-calendar"></i>
                        <input class="hotel-start-date" name="hotel_start_date[<?php echo $formId ?>]" type="text"
                               data-format="<?php echo $suite->datePickerFormat ?>"
                               data-info="<?php echo $datePickerInfo ?>"
                               value="<?php echo isset($data['hotel_start_date']) ? $data['hotel_start_date'] : '' ?>"/>
                    </label>
                </section>
                <section class="col col-6">
                    <label>Hotel end date</label>
                    <label class="input">
                        <i class="icon-append fa fa-calendar"></i>
                        <input class="hotel-end-date" name="hotel_end_date[<?php echo $formId ?>]" type="text"
                               data-format="<?php echo $suite->datePickerFormat ?>"
                               data-info="<?php echo $datePickerInfo ?>"
                               value="<?php echo isset($data['hotel_end_date']) ? $data['hotel_end_date'] : '' ?>"/>
                    </label>
                </section>
            </div>
            <div class="col col-11">
                <label class="radio">
                    <input type="radio" name="hotel_availability[<?php echo $formId ?>]" value="0"
                        <?php echo ($data['hotel_availability'] != 1) ? 'checked="checked"' : '' ?> />
                    <i></i>No, I don't need rooming
                </label>
            </div>
        </div>

    </fieldset>
    <div class="room-type-and-rate-area" <?php
    echo (empty($data['hotel_availability'])) ? 'style="display:none"' : ''
    ?>>
        <?php $this->renderPartial('_room_type', array(
            'formId' => $formId, 'data' => $data, 'suite' => $suite, 'isAdmin' => $isAdmin
        )); ?>
    </div>
    <fieldset class="guest-participants-area" style="display: <?php
    echo (!empty($data['guest_type'])) ? 'block' : 'none'
    ?>">
        <h3>Guest Participants</h3>
        <div class="row">
            <section class="guest-types-area col col-6">
                <?php $this->renderPartial('_guest_participants',
                    array(
                        'formId' => $formId,
                        'personRange' => (!empty($data['personRange'])) ? $data['personRange'] : $suite->getGuestTypeList(),
                        'guestTypes' => (!empty($data['guest_type'])) ? $data['guest_type'] : array(),
                        'guestPrices' => (!empty($data['guestPrices'])) ? $data['guestPrices'] : array(),
                        'suite' => $suite
                    )
                ); ?>
            </section>
            <section class="total-area col col-6">
                <label class="label" for="total">Total</label>
                <label class="input <?php echo (empty($isAdmin)) ? 'state-disabled' : '' ?> ">
                    <i class="icon-prepend fa fa-usd"></i>
                    <input class="input-total" type="text" name="total[<?php echo $formId ?>]"
                           value="<?php echo isset($data['total']) ? $data['total'] : '' ?>" <?php
                    echo (empty($isAdmin)) ? 'disabled' : ''
                    ?> />
                    <input id="input-total-<?php echo $formId ?>" type="hidden" name="total[<?php echo $formId ?>]"
                           value="<?php echo isset($data['total']) ? $data['total'] : '' ?>"/>
                </label>
                <br/>
                <!--<div class="tax-area col row">(Includes tax of $<span class="tax-text"><?php echo isset($data['tax']) ? $data['tax'] : '0' ?></span>-->
                <div class="tax-area col row">(This is an all inclusive rate<span class="tax-text" style="display:none"><?php echo isset($data['tax']) ? $data['tax'] : '0' ?></span>
                    <span class="early-bird-discount-area"
                        style="display:<?php echo empty($data['early_bird_discount']) ? 'none' : 'inline'
                        ?>"> including an early bird discount of <!--and an early bird discount --> $<span class="early-bird-discount-text"><?php
                            echo empty($data['early_bird_discount']) ? 0 : $data['early_bird_discount']
                            ?></span>
                    </span>
                    <span class="surcharge-area"
                          style="display:<?php echo (!empty($data['surcharge'])? 'inline' : 'none') ?>">and surcharge $<span class="surcharge-text"><?php echo empty($data['surcharge']) ? 0 : $data['surcharge'] ?></span></span>)

                    <input type="hidden" name="tax[<?php echo $formId ?>]"
                           value="<?php echo !empty($data['tax']) ? $data['tax'] : '0' ?>"/>

                    <input type="hidden" name="surcharge[<?php echo $formId ?>]"
                           value="<?php echo !empty($data['surcharge']) ? $data['surcharge'] : '0' ?>"/>
                </div>
            </section>
            <?php if ($formId >= 0): ?>
                <section class="promo-area col col-6">
                    <label class="input input-file" <?php if ($formId != 0) echo 'style="display:none"' ?>>
                        <div class="button apply-promo-button">Apply</div>
                        <input class="input-promo" type="text" name="promo[<?php echo $formId ?>]"
                               placeholder="Promo Code"
                               value="<?php echo isset($data['promo']) ? $data['promo'] : isset($_GET['promo']) && $formId == 0 ? $_GET['promo'] : '' ?>"/>
                    </label>
                    <p><span class="promo-price"></span> <span class="promo-message"></span></p>
                </section>
            <?php endif; ?>
        </div>
    </fieldset>
    <div class="message-info"></div>
    <div class="staff-area">
        <?php if (isset($data['staffData'])): ?>
            <?php $this->renderPartial('_staff', array(
                'formId' => $formId, 'staffData' => $data['staffData'], 'data' => $data,
                'staffPrices' => $data['staffPrices']
            )); ?>
        <?php endif; ?>
    </div>
    <div class="bedding-area">
        <?php if (isset($data['bedding'], $data['beddingOptions'])): ?>
            <?php $this->renderPartial('_bedding', array(
                    'formId' => $formId,
                    'data' => $data,
                    'beddingOptions' => $data['beddingOptions'],
                    'additionalBeddingOptions' =>
                        !empty($data['additionalBeddingOptions']) ? $data['additionalBeddingOptions'] : array()
                )
            ); ?>
        <?php endif; ?>
    </div>
    <?php if ($isAdmin && $formId == 0): ?>
        <fieldset>
            <section>
                <label class="label">Internal note</label>
                <label class="textarea">
                    <i class="icon-append fa fa-comment"></i>
                    <textarea rows="4" name="internal_note[<?php echo $formId ?>]"></textarea>
                </label>
            </section>
        </fieldset>
    <?php endif ?>
</div>