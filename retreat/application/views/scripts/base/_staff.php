<fieldset class="guest-participants-area">

    <!-- Group Babysitting -->
    <?php if (!empty($staffData['babysitter'])): ?>
        <h3>Group Babysitting</h3>
        <div class="note">(9:30am-7:30pm)</div><br>
        <div class="row">
            <section class="col col-6">
                <label class="radio">
                    <input type="radio"
                           name="staff[<?php echo $formId ?>][group_babysitting]"
                           value="1"
                           required="required"
                        <?php echo (isset($data['staff']['group_babysitting']) && $data['staff']['group_babysitting'] == 1)?
                            'checked="checked"' : '' ?>>
                    <i></i>Yes</label>
                <label class="radio">
                    <input type="radio"
                           name="staff[<?php echo $formId ?>][group_babysitting]"
                           value="0"
                           required="required"
                        <?php echo (isset($data['staff']['group_babysitting']) && $data['staff']['group_babysitting'] == 0)?
                            'checked="checked"' : '' ?>>
                    <i></i>No</label>
            </section>
        </div>
        <div class="row">
            <section class="col <?php echo empty($data['staff']['group_babysitting'])? 'disabled' : '' ?>">
                <?php if (isset($staffData['babysitter']['dates'])): ?>
                    <?php foreach ($staffData['babysitter']['dates'] as $dayKey => $dayData): ?>
                        <label class="checkbox">
                            <input type="checkbox"
                                   name="staff[<?php echo $formId ?>][babysitter][<?php echo $dayData['name'] ?>]"
                                   value="1"
                                   required="required"
                                <?php
                            echo (!empty($data['staff']['babysitter'][$dayData['name']]))? ' checked="checked"' : '' ?> />
                            <i></i><?php echo !empty($dayData['label'])? $dayData['label'] : $dayKey ?> </label>
                    <?php endforeach; ?>
                <?php endif; ?>
                (Total: <span class="staff-price">$<?php
                    echo !empty($staffPrices['babysitter'])? $staffPrices['babysitter'] : 0; ?></span>)
                <input type="hidden" name="staff[<?php echo $formId ?>][babysitter][total]"
                       value="<?php echo !empty($staffPrices['babysitter'])? $staffPrices['babysitter'] : 0; ?>">
            </section>
        </div>
    <?php endif ?>
    <h3>Private babysitting</h3>
    <div class="row <?php echo !empty($data['staff']['private_babysitting'])? '' : 'disabled' ?> ">
        <section class="col note">Private babysitting is available for $15/hr. Our office will contact you with more information.</section>
    </div>
    <div class="row">
        <section class="col col-6">
            <label class="radio">
                <input type="radio" name="staff[<?php echo $formId ?>][private_babysitting]"
                       value="1"
                       required="required"
                    <?php echo (isset($data['staff']['private_babysitting']) && $data['staff']['private_babysitting'] == 1)?
                        'checked="checked"' : '' ?>>
                <i></i>Yes</label>
            <label class="radio">
                <input type="radio" name="staff[<?php echo $formId ?>][private_babysitting]"
                       value="0"
                       required="required"
                    <?php echo (isset($data['staff']['private_babysitting']) && $data['staff']['private_babysitting'] == 0)?
                        'checked="checked"' : '' ?>>
                <i></i>No</label>
        </section>
    </div>
</fieldset>