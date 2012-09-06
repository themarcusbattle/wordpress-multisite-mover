<?php 

	require_once('host.php');
	
	$db = new PDO("mysql:host=$host;dbname=$db_name", $db_user, $db_pass);  
	$db->setAttribute( PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC );
	
	if((substr($new_path,-1,1) != '/') && (strlen($new_path) > 0)) {
		$new_path =  '/' . $new_path . '/';
	} else {
		$new_path = '/';
	}

	$new_url = $new_domain . $new_path;
	//echo $new_url;
	
	// retrieve all sites
	$sql		= "SELECT blog_id,domain,path FROM " . $table_prefix . "blogs";
	$stmt 	= $db->query($sql);
	$sites 	= $stmt->fetchAll();
	
	$old_domain = $sites[0]['domain'];
	$old_path = $sites[0]['path'];

	// Update [table_prefix]_site & [table_prefix]_sitemeta
	$sql = "UPDATE " . $table_prefix . "site SET domain = :new_domain, path = :new_path";
	$stmt = $db->prepare($sql);
	$stmt->execute(array('new_domain' => $new_domain,'new_path' => $new_path));

	$sql = "UPDATE " . $table_prefix . "sitemeta SET meta_value = replace(meta_value,:old_url,:new_url)";
	$stmt = $db->prepare($sql);
	$stmt->execute(array('old_url' => $old_domain . $old_path,'new_url' => $new_domain . $new_path));

	$sql = "UPDATE " . $table_prefix . "sitemeta SET meta_value = replace(meta_value,:old_domain,:new_domain)";
	$stmt = $db->prepare($sql);
	$stmt->execute(array('old_domain' => $old_domain,'new_domain' => $new_domain));
	
	$sql = "UPDATE " . $table_prefix . "usermeta SET meta_value = replace(meta_value,:old_domain,:new_domain)";
	$stmt = $db->prepare($sql);
	$stmt->execute(array('old_domain' => $old_domain,'new_domain' => $new_domain));
	
	
	foreach($sites as $site) {
		
		if($site['blog_id'] != 1) {
			$cur_prefix = $table_prefix . $site['blog_id'] . "_";
		} else {
			$cur_prefix = $table_prefix;
		}
	
		// Update [table_prefix]blogs with new domain and path
		if($old_path == '/') {
			
			$sql = "UPDATE " . $table_prefix . "blogs 
				SET 
					domain = :new_domain,
					path = :new_path
				WHERE blog_id = :blog_id";
				$stmt = $db->prepare($sql);
			$stmt->execute(array(
				'blog_id' => $site['blog_id'],
				'new_domain' => $new_domain,
				'new_path' => $new_path . substr($site['path'],1)
			));
			
		} else {
			
			$sql = "UPDATE " . $table_prefix . "blogs 
				SET 
					domain = :new_domain,
					path = replace(path,:old_path,:new_path)
				WHERE blog_id = :blog_id";
			
			$stmt = $db->prepare($sql);
			$stmt->execute(array(
				'blog_id' => $site['blog_id'],
				'new_domain' => $new_domain,
				'old_path' => $old_path,
				'new_path' => $new_path
			));
		}
		
		// Update [cur_prefix]_blog with new url
		$sql = "UPDATE " . $cur_prefix . "options SET option_value = replace(option_value,:old_url,:new_url)";
		$stmt = $db->prepare($sql);
		$stmt->execute(array(
			'old_url' => $old_domain . substr_replace($old_path,"",-1),
			'new_url' => substr_replace($new_url,"",-1)		
		));
		
		// Update [cur_prefix]_posts with new url
		/* $sql = "UPDATE " . $cur_prefix . "posts SET guid = replace(guid,:old_url,:new_url)";
		$stmt = $db->prepare($sql);
		$stmt->execute(array(
			'old_url' => $old_domain . $site['path'],
			'new_url' => substr_replace($new_url,"",-1) . $site['path']		
		)); */
		
		$sql = "UPDATE " . $cur_prefix . "posts SET guid = replace(guid,:old_url,:new_url)";
		$stmt = $db->prepare($sql);
		$stmt->execute(array(
			'old_url' => $old_domain . $site['path'],
			'new_url' => substr_replace($new_url,"",-1) . $site['path']		
		));
		
		echo $sql . "<br>";
		//echo $old_domain . $site['path'] . "<br>";
	}



exit;

/* $sql = "UPDATE hpu_site SET domain = replace(domain,$moving_from,$moving_to);";
$db->query($sql);


$sql = "UPDATE hpu_3_options SET option_value = replace(option_value,'" . $moving_from . "','" . $moving_to . "')";
$db->query($sql);  */

$sql = "UPDATE hpu_3_postmeta SET meta_value = replace(meta_value,'" . $moving_from . "','" . $moving_to . "')";
$db->query($sql);

$sql = "UPDATE hpu_3_posts SET guid = replace(guid,'http://" . $moving_from . "','http://" . $moving_to ."')";
$db->query($sql);

$sql = "UPDATE hpu_3_posts SET post_content = replace(post_content,'http://" . $moving_from . "','http://" . $moving_to ."')";
$db->query($sql);

$sql = "UPDATE hpu_3_comments SET comment_author_url = replace(comment_author_url,'http://" . $moving_from . "','http://" . $moving_to ."')";
$db->query($sql);

/*

$sql = "UPDATE hpu_site SET domain = replace(domain,$moving_from,$moving_to);";
$db->query($sql);
// Update options table
$sql = "UPDATE hpu_blogs SET domain = replace(domain,$moving_from,$moving_to);";
$db->query($sql);
$sql = "UPDATE hpu_2_options SET option_value = replace(option_value,'http://' . $moving_from,'http://' . $moving_to);";
$db->query($sql);
// Update posts table
$sql = "UPDATE hpu_2_posts SET guid = replace(guid,'http://' . $moving_from,'http://' . $moving_to);";
$db->query($sql);
$sql = "UPDATE hpu_2_posts SET post_content = replace(post_content,'http://' . $moving_from,'http://' . $moving_to);";
$db->query($sql);

*/
// convert back to oldenburg

?>
