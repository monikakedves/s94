<?php
$post_id = get_the_ID();
$public_pin = get_post_meta($post_id, 'public_pin', true);
$owner_pin  = get_post_meta($post_id, 'owner_pin', true);
$cookie_name = 's94_pet_auth_' . $post_id;

$user_ip = $_SERVER['REMOTE_ADDR'];
$lockout_key = 's94_pet_lock_' . md5($user_ip);
$failed_attempts = (int) get_transient($lockout_key);
$is_locked = ($failed_attempts >= 3);

$alert_message = '';

if (isset($_GET['action']) && $_GET['action'] === 'logout') {
	setcookie($cookie_name, '', time() - 3600, '/');
	wp_safe_redirect(get_permalink($post_id));
	exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['pet_profile_nonce']) && wp_verify_nonce($_POST['pet_profile_nonce'], 'secure_pet_action')) {
	$action = sanitize_text_field($_POST['action']);

	if ($action === 'login' && !$is_locked) {
		$submitted_pin = sanitize_text_field($_POST['auth_pin']);
		$role_toggle = isset($_POST['role_toggle']) ? sanitize_text_field($_POST['role_toggle']) : '';

		if ($role_toggle === 'finder' && $submitted_pin === $public_pin && !empty($public_pin)) {
			setcookie($cookie_name, 'finder', time() + 900, '/');
			delete_transient($lockout_key);
			wp_safe_redirect(get_permalink($post_id));
			exit;
		} elseif ($role_toggle === 'owner' && $submitted_pin === $owner_pin && !empty($owner_pin)) {
			setcookie($cookie_name, 'owner', time() + 900, '/');
			delete_transient($lockout_key);
			wp_safe_redirect(get_permalink($post_id));
			exit;
		} else {
			$failed_attempts++;
			set_transient($lockout_key, $failed_attempts, 24 * HOUR_IN_SECONDS);
			$attempts_left = 3 - $failed_attempts;
			if ($attempts_left <= 0) {
				$is_locked = true;
			} else {
				$alert_message = '<div class="woocommerce-error" style="margin-bottom: 2rem;">Incorrect PIN. ' . $attempts_left . ' attempts remaining before a 24-hour security lockout.</div>';
			}
		}
	} elseif ($action === 'save_profile' && isset($_COOKIE[$cookie_name]) && $_COOKIE[$cookie_name] === 'owner') {
		$text_fields = ['owner_name', 'owner_phone', 'owner_email', 'neighbourhood', 'emergency_contact', 'microchip', 'vet_details', 'favourite_food', 'breed', 'sex', 'birth_year', 'birth_month', 'birth_day', 'pet_type', 'pregnancy_status', 'spayed_neutered', 'markings', 'insurance_info', 'vaccinated', 'good_with_children'];
		$textarea_fields = ['intro', 'allergies', 'medications', 'flight_risks', 'temperament', 'additional_details', 'vaccine_details'];

		foreach ($text_fields as $field) {
			if (isset($_POST[$field])) update_post_meta($post_id, $field, sanitize_text_field($_POST[$field]));
		}
		foreach ($textarea_fields as $field) {
			if (isset($_POST[$field])) update_post_meta($post_id, $field, sanitize_textarea_field($_POST[$field]));
		}

		if (!function_exists('wp_handle_upload')) {
			require_once(ABSPATH . 'wp-admin/includes/file.php');
			require_once(ABSPATH . 'wp-admin/includes/image.php');
			require_once(ABSPATH . 'wp-admin/includes/media.php');
		}

		for ($i = 1; $i <= 3; $i++) {
			$file_key = 'pet_photo_' . $i;
			if (!empty($_FILES[$file_key]['name'])) {
				$upload_overrides = array('test_form' => false);
				$movefile = wp_handle_upload($_FILES[$file_key], $upload_overrides);

				if ($movefile && !isset($movefile['error'])) {
					$attachment = array(
						'post_mime_type' => $movefile['type'],
						'post_title'     => sanitize_file_name($_FILES[$file_key]['name']),
						'post_content'   => '',
						'post_status'    => 'inherit'
					);
					$attach_id = wp_insert_attachment($attachment, $movefile['file'], $post_id);
					$attach_data = wp_generate_attachment_metadata($attach_id, $movefile['file']);
					wp_update_attachment_metadata($attach_id, $attach_data);
					update_post_meta($post_id, 'photo_' . $i, $attach_id);
				}
			}
		}
		wp_safe_redirect(get_permalink($post_id) . '?saved=1');
		exit;
	} elseif ($action === 'delete_profile' && isset($_COOKIE[$cookie_name]) && $_COOKIE[$cookie_name] === 'owner') {
		wp_trash_post($post_id);
		wp_safe_redirect(home_url() . '?deleted=1');
		exit;
	}
}

if ($is_locked && empty($alert_message)) {
	$alert_message = '<div class="woocommerce-error" style="margin-bottom: 2rem;">Security Alert: Too many failed attempts. Access from this network is blocked for 24 hours.</div>';
}

if (isset($_GET['saved']) && $_GET['saved'] == '1') {
	$alert_message = '<div class="woocommerce-message" style="margin-bottom: 2rem;">Profile updated successfully.</div>';
}

$current_state = 'login';
$auth_role = isset($_COOKIE[$cookie_name]) ? $_COOKIE[$cookie_name] : '';

if (!empty($auth_role)) {
	setcookie($cookie_name, $auth_role, time() + 900, '/');
	if ($auth_role === 'finder') {
		$current_state = 'view';
	} elseif ($auth_role === 'owner') {
		$current_state = (isset($_GET['state']) && $_GET['state'] === 'view') ? 'view_owner' : 'edit';
	}
}

add_filter('document_title_parts', function ($title) {
	global $current_state;
	$pet_name = get_the_title();
	$title['title'] = ($current_state === 'edit') ? sprintf("Editing %s's ID Card", $pet_name) : sprintf("%s's ID Card", $pet_name);
	return $title;
});

get_header();
?>

<style>
	.pet-card {
		background: #fff;
		padding: 1.25rem 1.5rem;
		border-radius: var(--radius-large);
		box-shadow: 0 4px 15px rgba(0, 0, 0, 0.02);
		box-sizing: border-box;
		display: flex;
		flex-direction: column;
		justify-content: flex-start;
		text-align: center;
		height: auto;
	}

	.pet-card-label {
		display: block;
		color: var(--text-light);
		margin-bottom: 0.4rem;
		text-transform: uppercase;
		font-size: 0.85rem;
		letter-spacing: 1px;
		font-weight: 700;
		opacity: 0.6;
	}

	.pet-card-val {
		font-size: 1.15rem;
		font-weight: 600;
		color: var(--text-main);
		line-height: 1.4;
		display: block;
		word-break: break-word;
	}

	.pet-card-val a {
		color: var(--brand-denim);
		text-decoration: none;
		transition: color 0.2s;
		display: inline;
		word-break: break-word;
	}

	.pet-card-val a:hover {
		color: var(--brand-indigo);
		text-decoration: underline;
	}

	.copy-icon {
		cursor: pointer;
		color: var(--text-light);
		transition: color 0.2s;
		width: 18px;
		height: 18px;
		display: inline-block;
		vertical-align: middle;
		margin-top: -3px;
		margin-left: 5px;
	}

	.copy-icon:hover {
		color: var(--brand-pink);
	}

	.role-toggle-wrap {
		display: inline-flex;
		background: #eaeaea;
		padding: 6px;
		border-radius: var(--radius-pill);
		margin-bottom: 1.5rem;
	}

	.role-toggle-wrap label {
		cursor: pointer;
		margin: 0;
	}

	.role-toggle-wrap input[type="radio"] {
		display: none;
	}

	.role-toggle-wrap span {
		display: inline-block;
		padding: 0.7rem 1.5rem;
		border-radius: var(--radius-pill);
		font-weight: 700;
		color: var(--text-light);
		transition: all 0.3s;
	}

	.role-toggle-wrap input[type="radio"]:checked+span {
		background: #fff;
		color: var(--text-main);
		box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
	}

	.styled-file-input {
		display: flex;
		align-items: center;
		padding: 1rem;
		border: 2px dashed #eaeaea;
		border-radius: var(--radius-small);
		background: #fafafa;
		cursor: pointer;
		transition: border-color 0.3s;
	}

	.styled-file-input:hover {
		border-color: var(--brand-pink);
	}

	.pet-photo-grid {
		display: grid;
		grid-template-columns: 2fr 1fr;
		grid-template-rows: 1fr 1fr;
		gap: 15px;
		margin-bottom: 2rem;
		width: 100%;
	}

	.pet-photo-main {
		grid-column: 1 / 2;
		grid-row: 1 / 3;
	}

	.pet-photo-sub-1 {
		grid-column: 2 / 3;
		grid-row: 1 / 2;
	}

	.pet-photo-sub-2 {
		grid-column: 2 / 3;
		grid-row: 2 / 3;
	}

	.pet-photo {
		width: 100%;
		height: 100%;
		object-fit: cover;
		border-radius: var(--radius-large);
		cursor: zoom-in;
		display: block;
		background: #eaeaea;
	}

	.pet-photo-main .pet-photo {
		aspect-ratio: 16/10;
	}

	.pet-photo-sub-1 .pet-photo,
	.pet-photo-sub-2 .pet-photo {
		aspect-ratio: 6/4;
	}

	.info-grid {
		display: flex;
		flex-wrap: wrap;
		gap: 1.5rem;
		margin-bottom: 1.5rem;
		align-items: stretch;
	}

	.info-grid:last-of-type {
		margin-bottom: 0;
	}

	.info-grid>* {
		flex: 1 1 var(--min-w, 225px);
	}

	.profile-section-title {
		font-size: 1.5rem;
		font-weight: 700;
		margin: 2.5rem 0 1.5rem 0;
		letter-spacing: -0.5px;
		text-align: left;
	}

	.profile-split-layout {
		display: grid;
		grid-template-columns: minmax(0, 2fr) minmax(0, 1fr);
		gap: 3rem;
	}

	.profile-sidebar-sticky {
		position: sticky;
		top: 2rem;
	}

	.contact-item {
		text-align: left;
		margin-bottom: 1.5rem;
	}

	.contact-item:last-child {
		margin-bottom: 0;
	}

	.action-buttons-wrap {
		display: flex;
		flex-wrap: wrap;
		gap: 1rem;
		border-top: 2px solid #eaeaea;
		padding-top: 2rem;
		align-items: center;
		justify-content: space-between;
	}

	.action-buttons-wrap .btn-group {
		display: flex;
		gap: 1rem;
		flex-wrap: wrap;
	}

	.action-buttons-wrap .btn,
	.action-buttons-wrap .btn-outline {
		margin: 0;
	}

	.pastel-pill {
		display: inline-flex;
		align-items: center;
		box-sizing: border-box;
		height: 36px;
		padding: 0.5rem 1.2rem;
		border-radius: var(--radius-pill);
		font-weight: 700;
		letter-spacing: 1px;
		text-transform: uppercase;
		font-size: 0.85rem;
		background: #ffffff;
		border: 2px solid #eaeaea;
		color: var(--text-main);
	}

	#custom-lightbox {
		position: fixed;
		top: 0;
		left: 0;
		width: 100vw;
		height: 100vh;
		background-color: rgba(29, 42, 59, 0.98);
		z-index: 999999;
		display: flex;
		align-items: center;
		justify-content: center;
		opacity: 0;
		pointer-events: none;
		transition: opacity 0.3s ease;
	}

	#custom-lightbox.active {
		opacity: 1;
		pointer-events: auto;
	}

	#custom-lightbox .close-btn {
		position: fixed;
		top: 20px;
		right: 30px;
		color: #fff;
		font-size: 45px;
		cursor: pointer;
		line-height: 1;
		z-index: 1000000;
	}

	#custom-lightbox img {
		max-width: 90vw;
		max-height: 90vh;
		object-fit: contain;
		border-radius: var(--radius-small);
	}

	@media(max-width: 992px) {
		.profile-split-layout {
			grid-template-columns: 1fr;
			gap: 2rem;
		}

		.profile-main-col {
			order: 2;
		}

		.profile-sidebar-col {
			order: 1;
			margin-bottom: 1rem;
		}

		.profile-sidebar-sticky {
			position: relative;
			top: 0;
		}
	}

	@media(max-width: 768px) {
		.pet-photo-grid {
			grid-template-columns: 1fr 1fr;
			grid-template-rows: auto;
		}

		.pet-photo-main {
			grid-column: 1 / 3;
			grid-row: 1 / 2;
		}

		.pet-photo-sub-1 {
			grid-column: 1 / 2;
			grid-row: 2 / 3;
		}

		.pet-photo-sub-2 {
			grid-column: 2 / 3;
			grid-row: 2 / 3;
		}
	}
</style>

<div class="page-layout-wrapper no-sidebar">
	<div class="page-body-container" style="background: transparent; box-shadow: none; padding: 0; max-width: none; margin: 0 auto;">

		<?php echo $alert_message; ?>

		<?php if (isset($_GET['deleted']) && $_GET['deleted'] == '1') : ?>
			<div style="text-align: center; padding: 4rem 2rem; background: #fff; border-radius: var(--radius-large);">
				<h1 style="margin-bottom: 1rem;">Profile Deleted</h1>
				<p style="color: var(--text-light); margin-bottom: 2rem;">This pet profile has been securely moved to the trash and is no longer public.</p>
				<a href="<?php echo home_url(); ?>" class="btn">Return Home</a>
			</div>

		<?php elseif ($current_state === 'login' && !$is_locked) : ?>

			<header class="section-header" style="text-align: center; justify-content: center; margin-bottom: 3rem;">
				<h1 class="section-title" style="font-size: 3rem; margin-bottom: 0.5rem;">Digital Pet Profile</h1>
			</header>

			<div class="pet-card" style="text-align: center; max-width: 500px; margin: 0 auto; padding: 2rem;">
				<h3 style="font-size: 1.5rem; margin-top: 0; margin-bottom: 1.5rem;">Access Profile</h3>
				<form method="POST" action="">
					<?php wp_nonce_field('secure_pet_action', 'pet_profile_nonce'); ?>
					<input type="hidden" name="action" value="login">

					<div class="role-toggle-wrap">
						<label><input type="radio" name="role_toggle" value="finder" checked><span>I found this pet</span></label>
						<label><input type="radio" name="role_toggle" value="owner"><span>It's my pet</span></label>
					</div>

					<input type="password" name="auth_pin" placeholder="Enter 6-Digit PIN" maxlength="6" required style="padding: 1rem 1.5rem; border: 2px solid #eaeaea; border-radius: var(--radius-pill); font-family: inherit; font-size: 1.15rem; width: 100%; text-align: center; margin-bottom: 1.5rem; letter-spacing: 2px; font-weight: 700; outline: none;">
					<button type="submit" class="btn" style="width: 100%;">Unlock Profile</button>
				</form>
			</div>

		<?php elseif ($current_state === 'edit') : ?>

			<header class="section-header" style="text-align: left; justify-content: flex-start; margin-bottom: 3rem;">
				<div style="display: block;">
					<h1 class="section-title" style="font-size: 3rem; margin-bottom: 0.5rem;">Edit Pet Profile</h1>
					<p style="color: var(--text-light); font-size: 1.15rem;">Active Record for <?php the_title(); ?></p>
				</div>
			</header>

			<form method="POST" action="" enctype="multipart/form-data" class="woocommerce-checkout pet-card" style="text-align: left; display: block; padding: 2rem;">
				<?php wp_nonce_field('secure_pet_action', 'pet_profile_nonce'); ?>
				<input type="hidden" name="action" value="save_profile">

				<h3 class="profile-section-title" style="margin-top: 0;">Core Pet Identity</h3>
				<div class="info-grid">
					<div class="form-row">
						<label>Pet Type</label>
						<select name="pet_type" class="input-text">
							<option value="">Select...</option>
							<option value="Dog" <?php selected(get_post_meta($post_id, 'pet_type', true), 'Dog'); ?>>Dog</option>
							<option value="Cat" <?php selected(get_post_meta($post_id, 'pet_type', true), 'Cat'); ?>>Cat</option>
							<option value="Rabbit" <?php selected(get_post_meta($post_id, 'pet_type', true), 'Rabbit'); ?>>Rabbit</option>
							<option value="Ferret" <?php selected(get_post_meta($post_id, 'pet_type', true), 'Ferret'); ?>>Ferret</option>
							<option value="Guinea Pig" <?php selected(get_post_meta($post_id, 'pet_type', true), 'Guinea Pig'); ?>>Guinea Pig</option>
							<option value="Bird" <?php selected(get_post_meta($post_id, 'pet_type', true), 'Bird'); ?>>Bird</option>
							<option value="Equine" <?php selected(get_post_meta($post_id, 'pet_type', true), 'Equine'); ?>>Equine</option>
							<option value="Tortoise" <?php selected(get_post_meta($post_id, 'pet_type', true), 'Tortoise'); ?>>Tortoise</option>
							<option value="Other" <?php selected(get_post_meta($post_id, 'pet_type', true), 'Other'); ?>>Other</option>
						</select>
					</div>
					<div class="form-row"><label>Breed</label><input type="text" name="breed" class="input-text" value="<?php echo esc_attr(get_post_meta($post_id, 'breed', true)); ?>"></div>
					<div class="form-row">
						<label>Sex</label>
						<select name="sex" id="pet_sex" class="input-text">
							<option value="">Select...</option>
							<option value="Male" <?php selected(get_post_meta($post_id, 'sex', true), 'Male'); ?>>Male</option>
							<option value="Female" <?php selected(get_post_meta($post_id, 'sex', true), 'Female'); ?>>Female</option>
							<option value="Unknown" <?php selected(get_post_meta($post_id, 'sex', true), 'Unknown'); ?>>Unknown</option>
						</select>
					</div>
					<div class="form-row"><label>Birth Year (YYYY)</label><input type="number" name="birth_year" class="input-text" value="<?php echo esc_attr(get_post_meta($post_id, 'birth_year', true)); ?>"></div>
					<div class="form-row"><label>Birth Month (MM)</label><input type="number" name="birth_month" class="input-text" value="<?php echo esc_attr(get_post_meta($post_id, 'birth_month', true)); ?>"></div>
					<div class="form-row"><label>Birth Day (DD)</label><input type="number" name="birth_day" class="input-text" value="<?php echo esc_attr(get_post_meta($post_id, 'birth_day', true)); ?>"></div>
					<div class="form-row">
						<label>Spayed / Neutered</label>
						<select name="spayed_neutered" class="input-text">
							<option value="">Select...</option>
							<option value="Yes" <?php selected(get_post_meta($post_id, 'spayed_neutered', true), 'Yes'); ?>>Yes</option>
							<option value="No" <?php selected(get_post_meta($post_id, 'spayed_neutered', true), 'No'); ?>>No</option>
						</select>
					</div>
					<div class="form-row" id="preg_wrap" style="display: none;">
						<label>Pregnancy Status</label>
						<select name="pregnancy_status" class="input-text">
							<option value="">Select...</option>
							<option value="Pregnant" <?php selected(get_post_meta($post_id, 'pregnancy_status', true), 'Pregnant'); ?>>Pregnant</option>
							<option value="Not Pregnant" <?php selected(get_post_meta($post_id, 'pregnancy_status', true), 'Not Pregnant'); ?>>Not Pregnant</option>
						</select>
					</div>
					<div class="form-row"><label>Distinctive Markings</label><input type="text" name="markings" class="input-text" value="<?php echo esc_attr(get_post_meta($post_id, 'markings', true)); ?>"></div>
				</div>

				<h3 class="profile-section-title">Contact Information</h3>
				<div class="info-grid">
					<div class="form-row"><label>Owner Name</label><input type="text" name="owner_name" class="input-text" value="<?php echo esc_attr(get_post_meta($post_id, 'owner_name', true)); ?>"></div>
					<div class="form-row"><label>Owner Phone</label><input type="text" name="owner_phone" class="input-text" value="<?php echo esc_attr(get_post_meta($post_id, 'owner_phone', true)); ?>"></div>
					<div class="form-row"><label>Owner Email</label><input type="email" name="owner_email" class="input-text" value="<?php echo esc_attr(get_post_meta($post_id, 'owner_email', true)); ?>"></div>
					<div class="form-row"><label>Neighbourhood</label><input type="text" name="neighbourhood" class="input-text" value="<?php echo esc_attr(get_post_meta($post_id, 'neighbourhood', true)); ?>"></div>
					<div class="form-row"><label>Emergency Contact</label><input type="text" name="emergency_contact" class="input-text" value="<?php echo esc_attr(get_post_meta($post_id, 'emergency_contact', true)); ?>"></div>
				</div>

				<div class="form-row" style="margin-bottom: 1.5rem;"><label>Pet Intro</label><textarea name="intro" rows="3"><?php echo esc_textarea(get_post_meta($post_id, 'intro', true)); ?></textarea></div>

				<h3 class="profile-section-title">Medical & Vet Details</h3>
				<div class="info-grid">
					<div class="form-row"><label>Microchip Number</label><input type="text" name="microchip" class="input-text" value="<?php echo esc_attr(get_post_meta($post_id, 'microchip', true)); ?>"></div>
					<div class="form-row"><label>Vet Details</label><input type="text" name="vet_details" class="input-text" value="<?php echo esc_attr(get_post_meta($post_id, 'vet_details', true)); ?>"></div>
					<div class="form-row"><label>Insurance Info</label><input type="text" name="insurance_info" class="input-text" value="<?php echo esc_attr(get_post_meta($post_id, 'insurance_info', true)); ?>"></div>
					<div class="form-row">
						<label>Vaccination Status</label>
						<select name="vaccinated" class="input-text">
							<option value="">Select...</option>
							<option value="Yes" <?php selected(get_post_meta($post_id, 'vaccinated', true), 'Yes'); ?>>Yes</option>
							<option value="No" <?php selected(get_post_meta($post_id, 'vaccinated', true), 'No'); ?>>No</option>
							<option value="Partial" <?php selected(get_post_meta($post_id, 'vaccinated', true), 'Partial'); ?>>Partial</option>
							<option value="Unknown" <?php selected(get_post_meta($post_id, 'vaccinated', true), 'Unknown'); ?>>Unknown</option>
						</select>
					</div>
				</div>
				<div class="info-grid" style="--min-w: 300px;">
					<div class="form-row"><label>Vaccine Details</label><textarea name="vaccine_details" rows="3"><?php echo esc_textarea(get_post_meta($post_id, 'vaccine_details', true)); ?></textarea></div>
					<div class="form-row"><label>Known Allergies</label><textarea name="allergies" rows="3"><?php echo esc_textarea(get_post_meta($post_id, 'allergies', true)); ?></textarea></div>
					<div class="form-row"><label>Daily Medications</label><textarea name="medications" rows="3"><?php echo esc_textarea(get_post_meta($post_id, 'medications', true)); ?></textarea></div>
				</div>

				<h3 class="profile-section-title">Behaviour & Care</h3>
				<div class="info-grid" style="--min-w: 300px;">
					<div class="form-row"><label>Flight Risks / Triggers</label><textarea name="flight_risks" rows="3"><?php echo esc_textarea(get_post_meta($post_id, 'flight_risks', true)); ?></textarea></div>
					<div class="form-row"><label>Social Temperament</label><textarea name="temperament" rows="3"><?php echo esc_textarea(get_post_meta($post_id, 'temperament', true)); ?></textarea></div>
					<div class="form-row">
						<label>Good with Children?</label>
						<select name="good_with_children" class="input-text">
							<option value="">Select...</option>
							<option value="Yes" <?php selected(get_post_meta($post_id, 'good_with_children', true), 'Yes'); ?>>Yes</option>
							<option value="No" <?php selected(get_post_meta($post_id, 'good_with_children', true), 'No'); ?>>No</option>
							<option value="Older Children Only" <?php selected(get_post_meta($post_id, 'good_with_children', true), 'Older Children Only'); ?>>Older Children Only</option>
							<option value="Unknown" <?php selected(get_post_meta($post_id, 'good_with_children', true), 'Unknown'); ?>>Unknown</option>
						</select>
					</div>
					<div class="form-row"><label>Favourite Food</label><input type="text" name="favourite_food" class="input-text" value="<?php echo esc_attr(get_post_meta($post_id, 'favourite_food', true)); ?>"></div>
					<div class="form-row"><label>Additional Details</label><textarea name="additional_details" rows="3"><?php echo esc_textarea(get_post_meta($post_id, 'additional_details', true)); ?></textarea></div>
				</div>

				<h3 class="profile-section-title">Photos</h3>
				<div class="info-grid">
					<div class="form-row"><label>Main Photo</label>
						<div class="styled-file-input"><input type="file" name="pet_photo_1" accept="image/*"></div>
					</div>
					<div class="form-row"><label>Photo 2</label>
						<div class="styled-file-input"><input type="file" name="pet_photo_2" accept="image/*"></div>
					</div>
					<div class="form-row"><label>Photo 3</label>
						<div class="styled-file-input"><input type="file" name="pet_photo_3" accept="image/*"></div>
					</div>
				</div>

				<div class="action-buttons-wrap">
					<div class="btn-group">
						<button type="submit" class="btn">Save Profile</button>
						<a href="?state=view" class="btn btn-outline" style="border-color: var(--text-main); color: var(--text-main);">View Profile</a>
						<a href="?action=logout" class="btn btn-outline" style="border-color: #eaeaea; color: var(--text-light);">Log Out</a>
					</div>
					<button type="submit" name="action" value="delete_profile" class="btn btn-outline" style="border-color: #ff4d4f; color: #ff4d4f;" onclick="return confirm('Are you sure you want to delete this profile? This cannot be undone.');">Delete Profile</button>
				</div>
			</form>

			<script>
				document.addEventListener('DOMContentLoaded', function() {
					const sexSel = document.getElementById('pet_sex');
					const pregWrap = document.getElementById('preg_wrap');

					function togglePreg() {
						pregWrap.style.display = (sexSel && sexSel.value === 'Female') ? 'block' : 'none';
					}
					if (sexSel) {
						sexSel.addEventListener('change', togglePreg);
						togglePreg();
					}
				});
			</script>

			<?php elseif ($current_state === 'view' || $current_state === 'view_owner') :

			$b_year = get_post_meta($post_id, 'birth_year', true);
			$b_month = get_post_meta($post_id, 'birth_month', true);
			$b_day = get_post_meta($post_id, 'birth_day', true);
			$age_display = '';

			if (!empty($b_year)) {
				$m = !empty($b_month) ? str_pad($b_month, 2, '0', STR_PAD_LEFT) : '01';
				$d = !empty($b_day) ? str_pad($b_day, 2, '0', STR_PAD_LEFT) : '01';
				try {
					$bdate = new DateTime("$b_year-$m-$d");
					$today = new DateTime();
					$diff = $today->diff($bdate);
					if ($diff->y > 0) {
						$age_display = $diff->y . ' yr' . ($diff->y > 1 ? 's' : '');
					} elseif ($diff->m > 0 && !empty($b_month)) {
						$age_display = $diff->m . ' mo' . ($diff->m > 1 ? 's' : '');
					} elseif (empty($b_month)) {
						$age_display = $diff->y > 0 ? $diff->y . ' yr' . ($diff->y > 1 ? 's' : '') : '< 1 yr';
					} else {
						$age_display = '< 1 mo';
					}
				} catch (Exception $e) {
				}
			}

			$pet_type = get_post_meta($post_id, 'pet_type', true);
			$pet_icon_slug = empty($pet_type) ? 'other' : sanitize_title(explode('/', $pet_type)[0]);

			$breed = get_post_meta($post_id, 'breed', true);
			$sex = get_post_meta($post_id, 'sex', true);
			$pregnancy = get_post_meta($post_id, 'pregnancy_status', true);
			$spayed = get_post_meta($post_id, 'spayed_neutered', true);
			$vaxx = get_post_meta($post_id, 'vaccinated', true);

			$img1 = get_post_meta($post_id, 'photo_1', true);
			$img2 = get_post_meta($post_id, 'photo_2', true);
			$img3 = get_post_meta($post_id, 'photo_3', true);

			if ($img1 || $img2 || $img3) : ?>
				<div class="pet-photo-grid" style="margin-top: 1rem;">
					<?php if ($img1): $url1 = wp_get_attachment_image_url($img1, 'full'); ?>
						<div class="pet-photo-main"><img src="<?php echo esc_url(wp_get_attachment_image_url($img1, 'large')); ?>" data-full="<?php echo esc_url($url1); ?>" class="pet-photo lb-trigger" alt=""></div>
					<?php endif; ?>
					<?php if ($img2): $url2 = wp_get_attachment_image_url($img2, 'full'); ?>
						<div class="pet-photo-sub-1"><img src="<?php echo esc_url(wp_get_attachment_image_url($img2, 'medium_large')); ?>" data-full="<?php echo esc_url($url2); ?>" class="pet-photo lb-trigger" alt=""></div>
					<?php endif; ?>
					<?php if ($img3): $url3 = wp_get_attachment_image_url($img3, 'full'); ?>
						<div class="pet-photo-sub-2"><img src="<?php echo esc_url(wp_get_attachment_image_url($img3, 'medium_large')); ?>" data-full="<?php echo esc_url($url3); ?>" class="pet-photo lb-trigger" alt=""></div>
					<?php endif; ?>
				</div>
			<?php endif; ?>

			<div style="text-align: left; margin-bottom: 2.5rem; margin-top: 1.5rem;">
				<div style="display: flex; flex-wrap: wrap; gap: 10px; margin-bottom: 1.2rem; justify-content: flex-start; align-items: center;">
					<?php if ($pet_type && $pet_type !== 'Other'): ?>
						<span class="pastel-pill" style="width: 36px; padding: 0; justify-content: center; flex-shrink: 0;">
							<span style="width: 20px; height: 20px; background-color: var(--text-main); -webkit-mask-image: url('<?php echo esc_url(get_template_directory_uri() . '/assets/images/' . esc_attr($pet_icon_slug) . '.svg'); ?>'); mask-image: url('<?php echo esc_url(get_template_directory_uri() . '/assets/images/' . esc_attr($pet_icon_slug) . '.svg'); ?>'); -webkit-mask-size: contain; mask-size: contain; -webkit-mask-repeat: no-repeat; mask-repeat: no-repeat; -webkit-mask-position: center; mask-position: center;"></span>
						</span>
					<?php endif; ?>
					<?php if (!empty($sex) && $sex !== 'Unknown'): ?>
						<span class="pastel-pill">
							<?php echo esc_html($sex); ?>
							<?php if ($sex === 'Female' && $pregnancy === 'Pregnant') echo ' (Pregnant)'; ?>
						</span>
					<?php endif; ?>
					<?php if (!empty($breed)): ?>
						<span class="pastel-pill"><?php echo esc_html($breed); ?></span>
					<?php endif; ?>
					<?php if (!empty($age_display)): ?>
						<span class="pastel-pill"><?php echo esc_html($age_display); ?></span>
					<?php endif; ?>
				</div>
				<h1 style="font-size: 3.5rem; font-weight: 700; margin: 0; letter-spacing: -1px; text-align: left;"><?php the_title(); ?></h1>
			</div>

			<div class="profile-split-layout">

				<div class="profile-main-col">
					<?php $intro = get_post_meta($post_id, 'intro', true);
					if (!empty($intro)): ?>
						<div class="pet-card" style="margin-bottom: 1.5rem; text-align: left; display: block; height: auto;">
							<span class="pet-card-label">Intro</span>
							<div class="pet-card-val" style="display: block; text-align: left;"><?php echo esc_html($intro); ?></div>
						</div>
					<?php endif; ?>

					<?php
					$markings = get_post_meta($post_id, 'markings', true);
					$vet = get_post_meta($post_id, 'vet_details', true);
					$chip = get_post_meta($post_id, 'microchip', true);
					$insurance = get_post_meta($post_id, 'insurance_info', true);
					$vaxx_details = get_post_meta($post_id, 'vaccine_details', true);
					$allergies = get_post_meta($post_id, 'allergies', true);
					$medications = get_post_meta($post_id, 'medications', true);

					if (!empty($markings) || (!empty($spayed) && $spayed !== 'Unknown') || (!empty($vaxx) && $vaxx !== 'Unknown') || !empty($vet) || !empty($chip) || !empty($insurance) || !empty($vaxx_details) || !empty($allergies) || !empty($medications)):
					?>
						<h3 class="profile-section-title">Core Identity & Medical Details</h3>

						<div class="info-grid">
							<?php if (!empty($markings)): ?><div class="pet-card"><span class="pet-card-label">Markings</span>
									<div class="pet-card-val"><?php echo esc_html($markings); ?></div>
								</div><?php endif; ?>
							<?php if (!empty($spayed) && $spayed !== 'Unknown'): ?><div class="pet-card"><span class="pet-card-label">Spayed / Neutered</span>
									<div class="pet-card-val"><?php echo esc_html($spayed); ?></div>
								</div><?php endif; ?>
							<?php if (!empty($vaxx) && $vaxx !== 'Unknown'): ?><div class="pet-card"><span class="pet-card-label">Vaccinated</span>
									<div class="pet-card-val"><?php echo esc_html($vaxx); ?></div>
								</div><?php endif; ?>
							<?php if (!empty($vet)): ?><div class="pet-card"><span class="pet-card-label">Vet Details</span>
									<div class="pet-card-val"><?php echo esc_html($vet); ?></div>
								</div><?php endif; ?>
							<?php if (!empty($chip)): ?><div class="pet-card"><span class="pet-card-label">Microchip</span>
									<div class="pet-card-val"><?php echo esc_html($chip); ?></div>
								</div><?php endif; ?>
							<?php if (!empty($insurance)): ?><div class="pet-card"><span class="pet-card-label">Insurance Info</span>
									<div class="pet-card-val"><?php echo esc_html($insurance); ?></div>
								</div><?php endif; ?>
						</div>

						<?php if (!empty($vaxx_details) || !empty($allergies) || !empty($medications)): ?>
							<div class="info-grid" style="--min-w: 300px;">
								<?php if (!empty($vaxx_details)): ?><div class="pet-card"><span class="pet-card-label">Vaccine Details</span>
										<div class="pet-card-val" style="display: block;"><?php echo esc_html($vaxx_details); ?></div>
									</div><?php endif; ?>
								<?php if (!empty($allergies)): ?><div class="pet-card"><span class="pet-card-label">Known Allergies</span>
										<div class="pet-card-val" style="display: block;"><?php echo esc_html($allergies); ?></div>
									</div><?php endif; ?>
								<?php if (!empty($medications)): ?><div class="pet-card"><span class="pet-card-label">Daily Medications</span>
										<div class="pet-card-val" style="display: block;"><?php echo esc_html($medications); ?></div>
									</div><?php endif; ?>
							</div>
						<?php endif; ?>
					<?php endif; ?>

					<?php
					$flight_risks = get_post_meta($post_id, 'flight_risks', true);
					$temperament = get_post_meta($post_id, 'temperament', true);
					$favourite_food = get_post_meta($post_id, 'favourite_food', true);
					$good_with_children = get_post_meta($post_id, 'good_with_children', true);
					$additional_details = get_post_meta($post_id, 'additional_details', true);

					if (!empty($flight_risks) || !empty($temperament) || !empty($favourite_food) || (!empty($good_with_children) && $good_with_children !== 'Unknown') || !empty($additional_details)):
					?>
						<h3 class="profile-section-title">Behaviour & Care</h3>
						<div class="info-grid" style="--min-w: 300px;">
							<?php if (!empty($flight_risks)): ?><div class="pet-card"><span class="pet-card-label">Flight Risks / Triggers</span>
									<div class="pet-card-val" style="display: block;"><?php echo esc_html($flight_risks); ?></div>
								</div><?php endif; ?>
							<?php if (!empty($temperament)): ?><div class="pet-card"><span class="pet-card-label">Social Temperament</span>
									<div class="pet-card-val" style="display: block;"><?php echo esc_html($temperament); ?></div>
								</div><?php endif; ?>
							<?php if (!empty($good_with_children) && $good_with_children !== 'Unknown'): ?><div class="pet-card"><span class="pet-card-label">Good with Children?</span>
									<div class="pet-card-val" style="display: block;"><?php echo esc_html($good_with_children); ?></div>
								</div><?php endif; ?>
							<?php if (!empty($favourite_food)): ?><div class="pet-card"><span class="pet-card-label">Favourite Food</span>
									<div class="pet-card-val" style="display: block;"><?php echo esc_html($favourite_food); ?></div>
								</div><?php endif; ?>
							<?php if (!empty($additional_details)): ?><div class="pet-card"><span class="pet-card-label">Additional Details</span>
									<div class="pet-card-val" style="display: block;"><?php echo esc_html($additional_details); ?></div>
								</div><?php endif; ?>
						</div>
					<?php endif; ?>

				</div>

				<div class="profile-sidebar-col">
					<div class="profile-sidebar-sticky">
						<?php
						$owner_name = get_post_meta($post_id, 'owner_name', true);
						$phone = get_post_meta($post_id, 'owner_phone', true);
						$email = get_post_meta($post_id, 'owner_email', true);
						$neighbourhood = get_post_meta($post_id, 'neighbourhood', true);
						$emergency_contact = get_post_meta($post_id, 'emergency_contact', true);

						if (!empty($owner_name) || !empty($phone) || !empty($email) || !empty($neighbourhood) || !empty($emergency_contact)):
						?>
							<div class="pet-card" style="padding: 2.5rem 2rem;">
								<h3 style="margin-top: 0; font-size: 1.5rem; margin-bottom: 1.5rem; text-align: left; width: 100%;">Contact Info</h3>

								<?php if (!empty($owner_name)): ?>
									<div class="contact-item">
										<span class="pet-card-label">Owner</span>
										<span class="pet-card-val"><?php echo esc_html($owner_name); ?></span>
									</div>
								<?php endif; ?>

								<?php if (!empty($phone)):
									$copy_svg = '<svg class="copy-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" onclick="copyData(this, \'%s\')"><rect x="9" y="9" width="13" height="13" rx="2" ry="2"></rect><path d="M5 15H4a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h9a2 2 0 0 1 2 2v1"></path></svg>';
								?>
									<div class="contact-item">
										<span class="pet-card-label">Phone</span>
										<span class="pet-card-val">
											<a href="tel:<?php echo esc_attr(preg_replace('/[^0-9+]/', '', $phone)); ?>"><?php echo esc_html($phone); ?></a><?php echo sprintf($copy_svg, esc_js($phone)); ?>
										</span>
									</div>
								<?php endif; ?>

								<?php if (!empty($email)): ?>
									<div class="contact-item">
										<span class="pet-card-label">Email</span>
										<span class="pet-card-val">
											<a href="mailto:<?php echo esc_attr($email); ?>"><?php echo esc_html($email); ?></a><?php echo sprintf($copy_svg, esc_js($email)); ?>
										</span>
									</div>
								<?php endif; ?>

								<?php if (!empty($neighbourhood)): ?>
									<div class="contact-item">
										<span class="pet-card-label">Neighbourhood</span>
										<span class="pet-card-val"><?php echo esc_html($neighbourhood); ?></span>
									</div>
								<?php endif; ?>

								<?php if (!empty($emergency_contact)): ?>
									<div class="contact-item">
										<span class="pet-card-label">Emergency Contact</span>
										<span class="pet-card-val"><?php echo esc_html($emergency_contact); ?></span>
									</div>
								<?php endif; ?>
							</div>
						<?php endif; ?>

						<?php if ($current_state === 'view_owner') : ?>
							<div style="text-align: center; margin-top: 1.5rem;">
								<a href="?state=edit" class="btn btn-outline" style="width: 100%;">Edit Profile</a>
							</div>
						<?php endif; ?>
					</div>
				</div>

			</div>

			<?php
			$tag_product_link = get_permalink(129);
			$custom_image_url = 'https://studio94.uk/wp-content/uploads/2026/09/tag-scaled.jpg';

			if ($tag_product_link) :
				$pet_name = get_the_title();
				$possessive_suffix = (strtolower(substr(trim($pet_name), -1)) === 's') ? "'" : "'s";
			?>
				<div style="background: var(--surface-color, #f9f9f9); border-radius: var(--radius-large); display: flex; flex-wrap: wrap; margin-top: 2rem; margin-bottom: 0; overflow: hidden; box-shadow: 0 4px 15px rgba(0,0,0,0.02);">
					<div style="flex: 1 1 50%; padding: 4rem 3rem; display: flex; flex-direction: column; justify-content: center; text-align: left; box-sizing: border-box; min-width: 300px;">
						<h3 style="margin-top: 0; font-size: 2.5rem; margin-bottom: 1rem; color: var(--text-main); letter-spacing: -0.5px;">Love <?php echo esc_html($pet_name) . $possessive_suffix; ?> Smart Tag?</h3>
						<p style="color: var(--text-light); margin-bottom: 2.5rem; font-size: 1.15rem; line-height: 1.6; max-width: 90%;">
							Keep your own best friend safe with a secure, scannable digital profile. No monthly fees, instant updates, and total peace of mind.
						</p>
						<div>
							<a href="<?php echo esc_url($tag_product_link); ?>" class="btn" style="margin: 0; display: inline-flex; font-size: 1.1rem; padding: 1rem 2rem;">Get a Tag for Your Pet</a>
						</div>
					</div>
					<div style="flex: 1 1 50%; min-height: 350px; background-image: url('<?php echo esc_url($custom_image_url); ?>'); background-size: cover; background-position: center; min-width: 300px;">
					</div>
				</div>
			<?php endif; ?>

		<?php endif; ?>

	</div>
</div>

<div id="custom-lightbox">
	<div class="close-btn">&times;</div>
	<div class="lightbox-content-wrapper">
		<div class="lb-nav lb-prev">&#10094;</div>
		<img class="lightbox-main-img" src="" alt="">
		<div class="lb-nav lb-next">&#10095;</div>
	</div>
</div>

<script>
	function copyData(btn, text) {
		navigator.clipboard.writeText(text).then(function() {
			btn.style.color = 'var(--brand-pink)';
			setTimeout(() => btn.style.color = '', 2000);
		});
	}

	document.addEventListener('DOMContentLoaded', function() {
		const lightbox = document.getElementById('custom-lightbox');
		const lbImg = lightbox ? lightbox.querySelector('.lightbox-main-img') : null;
		const btnPrev = lightbox ? lightbox.querySelector('.lb-prev') : null;
		const btnNext = lightbox ? lightbox.querySelector('.lb-next') : null;

		if (lightbox && lbImg) {
			let lbCurrentIndex = 0;

			function showImage() {
				const triggers = document.querySelectorAll('.lb-trigger');
				if (triggers.length === 0) return;

				if (lbCurrentIndex < 0) lbCurrentIndex = triggers.length - 1;
				if (lbCurrentIndex >= triggers.length) lbCurrentIndex = 0;

				const activeNodeUrl = triggers[lbCurrentIndex].getAttribute('data-full');
				if (activeNodeUrl) {
					lbImg.setAttribute('src', activeNodeUrl);
				}
			}

			document.querySelectorAll('.lb-trigger').forEach((img, index) => {
				img.addEventListener('click', function() {
					lbCurrentIndex = index;
					showImage();
					lightbox.classList.add('active');
					document.body.style.overflow = 'hidden';
				});
			});

			if (btnPrev) btnPrev.addEventListener('click', (e) => {
				e.stopPropagation();
				lbCurrentIndex--;
				showImage();
			});
			if (btnNext) btnNext.addEventListener('click', (e) => {
				e.stopPropagation();
				lbCurrentIndex++;
				showImage();
			});

			lightbox.addEventListener('click', function(e) {
				if (e.target === lightbox || e.target.classList.contains('lightbox-content-wrapper') || e.target.classList.contains('close-btn')) {
					lightbox.classList.remove('active');
					document.body.style.overflow = 'auto';
				}
			});

			document.addEventListener('keydown', function(e) {
				if (!lightbox.classList.contains('active')) return;
				if (e.key === 'Escape') {
					lightbox.classList.remove('active');
					document.body.style.overflow = 'auto';
				}
				if (e.key === 'ArrowLeft') {
					lbCurrentIndex--;
					showImage();
				}
				if (e.key === 'ArrowRight') {
					lbCurrentIndex++;
					showImage();
				}
			});
		}
	});
</script>

<?php get_footer(); ?>