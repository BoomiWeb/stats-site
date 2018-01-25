<?php global $emsm_admin; ?>

<div class="wrap emsm-admin-settings">
	
	<h1>EM Social Media Settings</h1>

	<form method="post" action="">
		<?php wp_nonce_field('update_settings', 'emsm_admin'); ?>
	
		<table id="emsm-social-media-table" class="form-table">
			<tbody id="emsm-sortable">
				
				<?php foreach ($emsm_admin->social_media as $slug => $sm) : ?>

					<tr id="emsm-<?php echo $slug; ?>" class="ui-state-default">
						<th scope="row">
							<i class="fa fa-arrows-v" aria-hidden="true"></i>
							<input type="text" name="social_media_options[<?php echo $slug; ?>][name]" id="<?php echo $slug; ?>-name" value="<?php echo $sm['name']; ?>" />
						</th>
						<td>

							<input name="social_media_options[<?php echo $slug; ?>][url]" id="<?php echo $slug; ?>-url" class="regular-text code" type="url" value="<?php echo $sm['url']; ?>" />
							<span class="<?php echo $slug; ?>-icon icon-img"><span class="icon-txt">Icon: </span><span class="icon-img-fa"><i class="fa <?php echo $sm['icon']; ?>"></i></span></span>
							
							<a class="emsm-select-icon" data-input-id="<?php echo $slug; ?>">Select Icon</a>
							
							<a href="" class="emsm-delete" data-rowid="emsm-<?php echo $slug; ?>"><i class="fa fa-trash"></i></a>
	
							<input type="hidden" name="social_media_options[<?php echo $slug; ?>][icon]" id="<?php echo $slug; ?>-icon" value="<?php echo $sm['icon']; ?>" />
							<input type="hidden" name="social_media_options[<?php echo $slug; ?>][order]" id="<?php echo $slug; ?>-order" class="order" value="<?php echo $sm['order']; ?>" />
						</td>
					</tr>
				
				<?php endforeach; ?>
				
			</tbody>
		</table>
	
		<input type="button" name="add-field" id="emsm-add-field" class="button button-secondary add-field" value="Add Social Media">
	
		<p class="submit"><input type="submit" name="submit" id="submit" class="button button-primary" value="Save Changes"></p>
	</form>

	<div id="emsm-icons-overlay">
		<ul class="fa-icons-list">
			<?php foreach ($emsm_admin->icons as $icon) : ?>
					<li id="<?php echo $icon['class']; ?>"><a href="#" data-icon="<?php echo $icon['class']; ?>"><i class="fa <?php echo $icon['class']; ?>"></i><?php echo $icon['name']; ?></a></li>
			<?php endforeach; ?>
		</ul>
	</div>
	
</div>
