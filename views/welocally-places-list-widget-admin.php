<style type="text/css">

</style>
<p>
	<div>
	<label for="<?php echo $this->get_field_id( 'title' ); ?>">
		<?php _e('Title:',$this->pluginDomain);?></label>
	<input class="widefat" id="<?php echo $this->get_field_id( 'title' ); ?>" 
		name="<?php echo $this->get_field_name( 'title' ); ?>" type="text" 
		value="<?php echo $instance['title']; ?>" />
	</div>
	<div class="line-spacer-10">&nbsp;</div>	
	<!-- limit -->	
	<?php /*<div>
	<label for="<?php echo $this->get_field_id( 'limit' ); ?>">
		<?php _e('Max Items:',$this->pluginDomain);?></label>
	<select id="<?php echo $this->get_field_id( 'limit' ); ?>"  
		name="<?php echo $this->get_field_name( 'limit' ); ?>">
		<option value="5" <?php if($instance['limit']=='5') echo 'selected';?>>5</option>
		<option value="10" <?php if($instance['limit']=='10') echo 'selected';?>>10</option>
		<option value="20" <?php if($instance['limit']=='20') echo 'selected';?>>20</option>
	</select>
	</div>
	<div class="line-spacer-10">&nbsp;</div>
	*/
	?>
	<!-- order field -->	
	<div>
	<label for="<?php echo $this->get_field_id( 'order_by' ); ?>">
		<?php _e('Order By:',$this->pluginDomain);?></label>
	<select id="<?php echo $this->get_field_id( 'order_by' ); ?>"  
		name="<?php echo $this->get_field_name( 'order_by' ); ?>">
		<option value="ID" <?php if($instance['order_by']=='ID') echo 'selected';?>>Post Id</option>
		<option value="post_date" <?php if($instance['order_by']=='post_date') echo 'selected';?>>Date</option>
		<option value="post_title" <?php if($instance['order_by']=='post_title') echo 'selected';?>>Title</option>
	</select>
	</div>
	<div class="line-spacer-10">&nbsp;</div>
	<!-- order direction -->	
	<div>
	<label for="<?php echo $this->get_field_id( 'order_dir' ); ?>">
		<?php _e('Sort Direction:',$this->pluginDomain);?></label>
	<select id="<?php echo $this->get_field_id( 'order_dir' ); ?>"  
		name="<?php echo $this->get_field_name( 'order_dir' ); ?>">
		<option value="desc" <?php if($instance['order_dir']=='desc') echo 'selected';?>>Decending</option>
		<option value="asc" <?php if($instance['order_dir']=='asc') echo 'selected';?>>Acending</option>
	</select>
	</div>
	<!-- exclude categories, this should be a multiselect -->	
	<div>
	<label for="<?php echo $this->get_field_id( 'exclude_cats' ); ?>">
		<?php _e('Exclude Categories: (comman seperated)',$this->pluginDomain);?></label>
	<input class="widefat" id="<?php echo $this->get_field_id( 'exclude_cats' ); ?>" 
		name="<?php echo $this->get_field_name( 'exclude_cats' ); ?>" type="text" 
		value="<?php echo $instance['exclude_cats']; ?>" />
	</div>
		
</p>