<?php
$isEnable = (empty($data['room_type']))? false : true;

$soldOut = [];
if (!$isAdmin) {
    $soldOut = [
        'Executive Suite',
        'Signature Suite',
        'Luxury Parlor Suite',
        'Biltmore - Junior Suite',
        'Biltmore - Suite ',
        'Biltmore - Deluxe Room',
        'Biltmore - Junior Suite',
        'Biltmore -  Suite',
        'Omni - Deluxe Room',
    ];
    $unavailable = [
        'Omni - Suite',
        
    ];
}
?>

<fieldset>
    <h3>Room Type</h3>
    <div class="row">
    <section class="room-type-area col col-12">
        <p>The Omni Providence Hotel is most convenient, directly connected to the Rhode Island Convention Center where the National Jewish Retreat programming will take place. This 4-Diamond-rated, luxurious hotel with pristine, comfortable rooms is located in the heart of charming downtown Providence. Also connected to the Providence Place Mall, the hotel is walking distance to most of Providence’s historic attractions and entertainments.

     <br><br>Alternatively, participants in the NJR may opt to stay across the street from the convention center at the historic Biltmore hotel. The charming hotel, renovated in 2014 also offers superb, comfortable accommodations.</p>
    </section>
    <section class="room-type-area col col-6">
            
            <label class="select <?php echo !$isEnable? 'state-disabled' : '' ?>">
                <select class="input-room-type" name="room_type[<?php echo $formId ?>]"
                    <?php echo !$isEnable? ' disabled="disabled"' : '' ?>>
                    <option selected="selected" disabled="disabled">Please select</option>
                    <?php foreach ($suite->getRoomTypeList() as $type => $name): ?>
                        <option <?php
                        echo (isset($data['room_type']) && $data['room_type'] == $type)? 'selected="selected"' : '';
                        echo (in_array($type, array_merge($soldOut,$unavailable))? ' disabled="disabled"' : '')
                        ?> value="<?php echo $type ?>"><?php
                            echo $name . (in_array($type, $soldOut)? ' - SOLD OUT' : '').(in_array($type, $unavailable)? ' (Unavailable)' : '')
                            ?></option>
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