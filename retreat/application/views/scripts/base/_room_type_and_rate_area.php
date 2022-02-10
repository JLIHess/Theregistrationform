<fieldset>
    <h3>Room Type and Rate</h3>
    <div class="row">
        <section class="room-type-area col col-6">
            <label class="select">
                <select class="input-room-type" name="room_type[<?php echo $formId ?>]" required="required">
                    <?php foreach ($suite->getRoomTypeList() as $type => $name): ?>
                        <option <?php
                            echo (isset($data['room_type']) && $data['room_type'] == $type)? 'selected="selected"' : ''
                        ?> value="<?php echo $type ?>"><?php echo $name ?></option>
                    <?php endforeach; ?>
                </select>
                <i></i>
            </label>
            <div class="occupancy-area">
                <?php if (isset($data['occupancy'], $data['occupancies'])): ?>
                    <?php $this->renderPartial('_occupancies', array(
                        'formId' => $formId, 'data' => $data, 'occupancies' => $data['occupancies'])
                    ); ?>
                <?php endif; ?>
            </div>
            <div class="bedding-area">
                <?php if (isset($data['bedding'], $data['beddingOptions'])): ?>
                    <?php $this->renderPartial('_bedding', array(
                            'formId' => $formId, 'data' => $data, 'beddingOptions' => $data['beddingOptions'])
                    ); ?>
                <?php endif; ?>
            </div>
        </section>
        <section class="room-description-area col col-6">
            <?php if (isset($data['description'])): ?>
                <?php $this->renderPartial('_room_description', array(
                    'formId' => $formId, 'description' => $data['description'])
                ); ?>
            <?php endif; ?>
        </section>
    </div>
</fieldset>