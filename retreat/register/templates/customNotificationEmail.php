<?php
  // This template was created to send a custom order notification email at the request of Shmuly Karp.
?>
<!DOCTYPE html>
<html>
	<body>
		Names: <br />
		<?php foreach($templateVariables['order']['guests'] as $guests) : ?>
			<?php echo $guests['user']['first_name'].' '.$guests['user']['last_name']; ?><br />
		<?php endforeach; ?>
		<br />
		<?php if(isset($templateVariables['order']['customer']['address']['city'])) : ?>
			City: <?php echo $templateVariables['order']['customer']['address']['city'].', '.$templateVariables['order']['customer']['address']['state']; ?>
		<?php endif; ?>
		<br>
		Shliach: <?php echo htmlentities($templateVariables['order']['customer']['shliach']); ?><br />
		Referred by: <?php echo htmlentities($templateVariables['order']['customer']['referred_by']); ?><br />
		Registration ID: <?php echo $templateVariables['order']['id']; ?><br />
		<p>Summary of Order</p>
		<table style="border-collapse: collapse; border-spacing: 0;" >
			<tbody>
				<tr>
					<?php if(count($templateVariables['order']['rooms']) > 1) : ?>
						<th style="border: 1px solid rgb(204, 204, 204); padding: 10px 5px; background-color: rgb(214, 214, 214); font-family: 'Arial Bold', Arial, sans-serif; font-size: 14px; font-weight: bold;">
						</th>
					<?php endif; ?>

					<th style="border: 1px solid rgb(204, 204, 204); padding: 10px 5px; background-color: rgb(214, 214, 214); font-family: 'Arial Bold', Arial, sans-serif; font-size: 14px; font-weight: bold;">
						Dates
					</th>
					<th style="border: 1px solid rgb(204, 204, 204); padding: 10px 5px; background-color: rgb(214, 214, 214); font-family: 'Arial Bold', Arial, sans-serif; font-size: 14px; font-weight: bold;">
						Room info
					</th>
					<th style="border: 1px solid rgb(204, 204, 204); padding: 10px 5px; background-color: rgb(214, 214, 214); font-family: 'Arial Bold', Arial, sans-serif; font-size: 14px; font-weight: bold;">
						Guests
					</th>
					<th style="border: 1px solid rgb(204, 204, 204); padding: 10px 5px; background-color: rgb(214, 214, 214); font-family: 'Arial Bold', Arial, sans-serif; font-size: 14px; font-weight: bold;">
						Price
					</th>
				</tr>
				<?php foreach($templateVariables['order']['rooms'] as $roomNumber => $room) : ?>
				<?php 
					// Get the number of guests.
					$adults = $teens = $children = $toddlers = $infants = 0;
					foreach($room['guests'] as $guestId) {
						if($templateVariables['order']['guests'][$guestId]['user_type_id'] <= 2) $adults += 1;
						elseif($templateVariables['order']['guests'][$guestId]['user_type_id'] == 3) $teens += 1;
						elseif($templateVariables['order']['guests'][$guestId]['user_type_id'] == 4) $children += 1;
						elseif($templateVariables['order']['guests'][$guestId]['user_type_id'] == 23) $toddlers += 1;
						elseif($templateVariables['order']['guests'][$guestId]['user_type_id'] == 5) $infants += 1;
					}
				?>
					<tr>
						<?php if(count($templateVariables['order']['rooms']) > 1) : ?>
							<td style="border: 1px solid rgb(204, 204, 204); padding: 5px; font-family: 'Arial Regular', Arial, sans-serif; font-size: 10px; vertical-align: middle; text-align: center;">
								Room #<?php echo $roomNumber + 1; ?>
							</td>
						<?php endif; ?>
						<td style="border: 1px solid rgb(204, 204, 204); padding: 5px; font-family: 'Arial Regular', Arial, sans-serif; font-size: 10px; vertical-align: middle; text-align: center;">
							<?php if(($room['program_start_date'] == $room['hotel_start_date']) and ($room['program_end_date'] == $room['hotel_end_date'])) : ?>
								<?php echo date('m/d/y h:ia', strtotime($room['program_start_date'])); ?> - <?php echo date('m/d/y h:ia', strtotime($room['program_end_date'])); ?>
							<?php else : ?>
								Hotel dates:<br />
								<?php if ((strtotime($room['hotel_start_date']) > 0) && (strtotime($room['hotel_end_date']) > 0)): ?>
									<?php echo date('m/d/y h:ia', strtotime($room['hotel_start_date'])) . ' - '
										. date('m/d/y h:ia', strtotime($room['hotel_end_date'])); ?>
								<?php else: ?>
									None
								<?php endif; ?><br/>
								Program dates:<br />
								<?php echo date('m/d/y h:ia', strtotime($room['program_start_date'])); ?> - <?php echo date('m/d/y h:ia', strtotime($room['program_end_date'])); ?><br />
							<?php endif; ?>
						</td>
						<td style="border: 1px solid rgb(204, 204, 204); padding: 5px; font-family: 'Arial Regular', Arial, sans-serif; font-size: 10px; vertical-align: middle; text-align: center;">
							<?php echo $room['room_type']; ?><br />
							<?php echo $room['occupancy']; ?><br />
							<?php echo $room['bed_type']; ?>

						</td>
						<td style="border: 1px solid rgb(204, 204, 204); padding: 5px; font-family: 'Arial Regular', Arial, sans-serif; font-size: 10px; vertical-align: middle; text-align: center;">
							<?php if($adults > 0) : ?>
								<?php echo $adults; ?> Adult<?php if($adults > 1) echo 's'; ?><br />
							<?php endif; ?>
							<?php if($teens > 0) : ?>
								<?php echo $teens; ?> Teen<?php if($teens > 1) echo 's'; ?><br />
							<?php endif; ?>
							<?php if($children > 0) : ?>
								<?php echo $children; ?> Child<?php if($children > 1) echo 'ren'; ?><br />
							<?php endif; ?>
							<?php if($toddlers > 0) : ?>
								<?php echo $toddlers; ?> Toddler<?php if($toddlers > 1) echo 's'; ?><br />
							<?php endif; ?>
							<?php if($infants > 0) : ?>
								<?php echo $infants; ?> Infant<?php if($infants > 1) echo 's'; ?><br />
							<?php endif; ?>
						</td>
						<td style="border: 1px solid rgb(204, 204, 204); padding: 5px; font-family: 'Arial Regular', Arial, sans-serif; font-size: 10px; vertical-align: middle; text-align: center;">
							$<?php echo number_format(($room['price'] / 100), 2); ?>
						</td>

					<tr>
				<?php endforeach; ?>
				<?php if($templateVariables['order']['tax'] > 0) : ?>
					<tr>
						<td colspan="<?php echo ((count($templateVariables['order']['rooms']) > 1) ? '4' : '3'); ?>" style="border:1px solid rgb(204,204,204); padding:5px; font-family:'Arial Regular',Arial,sans-serif; font-size:10px; vertical-align:middle; text-align:left;">
							Occupancy Tax
						</td>
						<td style="border:1px solid rgb(204,204,204); padding:5px; font-family:'Arial Regular',Arial,sans-serif; font-size:10px; vertical-align:middle; text-align:right;">
							$<?php echo number_format(($templateVariables['order']['tax'] / 100), 2); ?>
						</td>
					</tr>
				<?php endif; ?>
				<?php if($templateVariables['order']['cme_price'] > 0) : ?>
					<tr>
						<td colspan="<?php echo ((count($templateVariables['order']['rooms']) > 1) ? '4' : '3'); ?>" style="border:1px solid rgb(204,204,204); padding:5px; font-family:'Arial Regular',Arial,sans-serif; font-size:10px; vertical-align:middle; text-align:left;">
							CME Credits
						</td>
						<td style="border:1px solid rgb(204,204,204); padding:5px; font-family:'Arial Regular',Arial,sans-serif; font-size:10px; vertical-align:middle; text-align:right;">
							$<?php echo number_format(($templateVariables['order']['cme_price'] / 100), 2); ?>
						</td>
					</tr>
				<?php endif; ?>
				<?php if($templateVariables['order']['sponsorship_amount'] > 0) : ?>
					<tr>
						<td colspan="<?php echo ((count($templateVariables['order']['rooms']) > 1) ? '4' : '3'); ?>" style="border:1px solid rgb(204,204,204); padding:5px; font-family:'Arial Regular',Arial,sans-serif; font-size:10px; vertical-align:middle; text-align:left;">
							Sponsorship (<?php echo $templateVariables['order']['sponsorship_type']; ?>)
						</td>
						<td style="border:1px solid rgb(204,204,204); padding:5px; font-family:'Arial Regular',Arial,sans-serif; font-size:10px; vertical-align:middle; text-align:right;">
							$<?php echo number_format(($templateVariables['order']['sponsorship_amount'] / 100), 2); ?>
						</td>
					</tr>
				<?php endif; ?>
				<tr>
					<td colspan="<?php echo ((count($templateVariables['order']['rooms']) > 1) ? '5' : '4'); ?>" style="border: 1px solid rgb(204, 204, 204); padding: 5px; background-color: rgb(153, 153, 153); color: #ffffff; font-family: 'Arial Regular', Arial, sans-serif; font-size: 10px; vertical-align: middle; text-align: right;">
						Total: $<?php echo number_format(($templateVariables['order']['total'] / 100), 2); ?>
					</td>
				</tr>
			</tbody>
		</table>
	</body>
</html>
