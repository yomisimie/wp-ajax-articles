/**
 * 
 * @param array $args
 * Format like ['argument' => $value] for parsing into WP_Query args
 * @param array $taxonomies
 * format like ['taxonomy_name' => ['field' => $field_type, 'terms' => $value]] for parsing into WP_Query tax_query
 * @return void 
 */
add_action('wp_ajax_nopriv_displayPostsAjax', 'displayPostsAjax');
add_action('wp_ajax_displayPostsAjax', 'displayPostsAjax');
function displayPostsAjax($args = [], $taxonomies = [])
{
	// Check if ajax call or direct function call
	$ajaxCall = !empty($_SERVER['PHP_SELF']) && strtolower($_SERVER['PHP_SELF']) == '/wp-admin/admin-ajax.php';
	// If ajax call get data from GET params instead of function arguments
	// Replace the 2 function arguments with GET params
	if($ajaxCall)
	{
		$args = $_GET['args'];
		$taxonomies = $_GET['taxonomies'];
	}

	// Set default arguments for when no arguments are given
	$per_page = 8;
	$page = 1;

	// Set default tax query so we can fill later with arguments
	$tax_query = ['relation' => 'AND'];

	// Check if we have $args as function arguments
	// Fill query args with the data
	if(!empty($args)) {
		$per_page = isset($args['per_page']) ? $args['per_page'] : $per_page;
		$page = isset($args['page']) ? $args['page'] : $page;
	}
	// Check if we have $taxonomies as function arguments
	// Fill tax query with the data
	if(!empty($taxonomies)) {
		foreach ($taxonomies as $key => $taxonomy) {
			$tax_query[] = [
				'taxonomy'	=> $key,
				'field'		=> $taxonomy['field'],
				'terms'		=> $taxonomy['value']
			];
		}
	}

	// Calculate offset (number of posts skipped)
	$offset = ($page - 1) * $per_page;

	// Filter all posts by taxonomies
	$args = [
		'post_status'		=> 'publish',
		'tax_query'			=> $tax_query,
	];

	// First query to count all posts and get number of pages
	$all = new WP_Query($args);
	$pages = ceil($all->found_posts / $per_page);

	// Add specific arguments for pagination
	$args['offset'] = $offset;
	$args['posts_per_page'] = $per_page;

	// Second query to display posts by pagination
	$query = new WP_Query($args);

	if($query->have_posts()) {
		// Set hidden inputs with different arguments you need to save
		?>
		<input type="hidden" name="per_page" id="per_page" value="<?= $per_page; ?>">
		<?php
		while($query->have_posts()) {
			$query->the_post();

			// Add here the template to display
			// Use echo to display (we use echo with ajax too)
			echo get_the_title() . "<br/>";
		}
		wp_reset_postdata();
		// Display pagination here
		echo "<div class='pagination'>";
		for($i = 1; $i <= $pages; $i++) {
			// Check for current page
			// Cast $page as int because it comes as string from ajax
			if((int)$page == $i) {
				echo "<span class='page current'>{$i}</span>";
			} else {
				echo "<a class='page' data-page='{$i}'>{$i}</a>";
			}
		}
		echo "</div>";
	}
	// If ajax we need to kill the function, else it returns a 0
	if($ajaxCall) {
		wp_die();
	}
}
