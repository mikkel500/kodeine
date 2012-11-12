<?php
	# Group
	#
	if(isset($_GET['group'])){
		$list = $app->dbOne("SELECT COUNT(*) as H FROM k_user WHERE id_group IN(".$_GET['group'].")");
		$out['group'] = $list['H'];
	}else{
		$out['group'] = 0;
	}
	
	# Search
	#
	if(isset($_GET['search'])){
		$total = 0;
		foreach(explode(',', $_GET['search']) as $e){
			$f = @$app->apiLoad('user')->userSearch(array(
				'id_search' => $e,
				'debug' 	=> false
			));
			if($app->apiLoad('user')->total > 0) $total += $app->apiLoad('user')->total;
		}
		$out['search'] = $total;
	}else{
		$out['search'] = 0;
	}

	# List
	#
	if(isset($_GET['list'])){
		$list = $app->dbOne("SELECT COUNT(*) as H FROM k_newsletterlistmail
		INNER JOIN k_newslettermail ON k_newsletterlistmail.id_newslettermail = k_newslettermail.id_newslettermail
		WHERE id_newsletterlist IN(".$_GET['list'].") AND flag='VALID'");

		$out['list'] = $list['H'];
	}else{
		$out['list'] = 0;
	}


	$out['total'] = /*$out['type'] + */$out['group'] + $out['list'];


	# OUT
	#
	if(isset($_GET['pre'])){
		$app->pre($out);
	}else{
		header('Content-type: application/json');
		echo json_encode($out);
	}

?>