<?php
	global $wpdb;
	// Create the query using placeholders
	$results = $wpdb->get_results(
		$wpdb->prepare(
			"SELECT id, email, last_updated FROM {$wpdb->prefix}cev_user_log WHERE 1 = %d",
			1
		),
		ARRAY_A
	);
	?>
<section id="cev_content_user" class="cev_tab_section">
	<?php if ($results) : ?>
		<!-- Filter dropdown -->
		<div id="filterForm">
			<div class="filter_select">
				<select id="bulk_action" name="bulk_action">
					<option value="">Bulk Action</option>
					<option value="delete">Delete</option>
				</select>
				<button class="apply_bulk_action">Apply</button>
			</div>
		</div>

		<div id="userLog">
			<table id="userLogTable" class="display">
				<thead>
					<tr>
						<th><input type="checkbox" id="select_all" /></th>
						<th>Email</th>
						<th>Last Updated</th>
						<th>Delete</th>
					</tr>
				</thead>
				<tbody>
					<?php foreach ($results as $row) : ?>
						<tr>
							<td><input type="checkbox" class="row_checkbox" value="<?php echo esc_attr($row['id']); ?>" /></td>
							<td><?php echo esc_html($row['email']); ?></td>
							<td>
								<?php
								if (!empty($row['last_updated'])) {
									$last_updated_date = new DateTime($row['last_updated']);
									echo esc_html($last_updated_date->format('F j, Y H:i'));
								} else {
									echo 'N/A';
								}
								?>
							</td>
							<td>
								<button class="delete_button" data-id="<?php echo esc_attr($row['id']); ?>">
									<img src="<?php echo esc_url(cev_pro()->plugin_dir_url() . 'assets/images/bin.png'); ?>" alt="Delete">
								</button>
							</td>
						</tr>
					<?php endforeach; ?>
				</tbody>
			</table>
		</div>
	<?php else : ?>
		<div class="no_user">
			<div class="no_user_content">
				<img src="<?php echo esc_url(cev_pro()->plugin_dir_url() . 'assets/images/nouser.png'); ?>" alt="nouser">
				<span>No user found!</span>
			</div>
		</div>
	<?php endif; ?>
</section>

