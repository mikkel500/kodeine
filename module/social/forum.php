<?php

	$i18n = $app->apiLoad('coreI18n')->languageSet('fr')->load('social');

	# REMOVE
	#
	if(sizeof($_POST['del']) > 0){
		foreach($_POST['del'] as $e){
			$app->apiLoad('socialForum')->socialForumRemove(array('id_socialforum' => $e));
		}
		$reload = true;
	}else

	# INSERT or UPDATE
	#
	if($_POST['action']){
		$do 	= true;
		$core	= array(
			'mid_socialforum'	=> array('value' => $_POST['mid_socialforum'],	'zero'	=> true),
			'socialForumName'	=> array('value' => $_POST['socialForumName'],	'check'	=> '.'),
			'socialForumMedia'	=> array('value' => $_POST['socialForumMedia']),
		);

		if(!$app->formValidation(array('@' => $core))) $do = false;

		if($do){
			$result  = $app->apiLoad('socialForum')->socialForumSet(array(
				'debug'				=> false,
				'id_socialforum'	=> $_POST['id_socialforum'],
				'core'				=> $core,
				'field'				=> $_POST['field']
			));
		
			$message = ($result) ? 'OK: Enregistrement effectué' : 'KO: Erreur APP:<br />'.$app->apiLoad('socialForum')->db_error;
			$reload  = 'id_socialforum='.$app->apiLoad('socialForum')->id_socialforum.'&message='.urlencode($message);

		}else{
			$message = 'KO: Attention, les champs ne sont pas remplis correctement.';
		}
	}

	# RELOAD
	#
	if(isset($reload)){
		$url = is_bool($reload) ? 'forum' : 'forum?'.$reload.'&opened='.$_REQUEST['opened'];
		header("Location: ".$url);
		exit();
	}

	# GET
	#
	if($_REQUEST['id_socialforum'] != NULL){
		$data = $app->apiLoad('socialForum')->socialForumGet(array(
			'debug'				=> false,
			'id_socialforum'	=> $_REQUEST['id_socialforum'],
		));

		$title = $data['socialForumName'];
	}else{
		$title = 'Nouveau forum';
	}

	/* + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - 
	+ - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - */
	function fieldTrace($app, $lan, $copy, $data, $e){
		$field	= $app->apiLoad('field')->fieldForm(
			$e['id_field'],
			$data,
			array(
				'name'		=> 'field['.$e['id_field'].']',
				'id'		=> 'field-'.$e['id_field'],
				'style' 	=> 'width:99%; '.$e['__fieldStyle__'],
				'disabled'	=> (($copy) ? 'disabled' : '')
			)
		);

		echo $field;
	}

	$fields = $app->apiLoad('field')->fieldGet(array(
		'socialForum'	=> true,
		'debug'			=> false	
	));

?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <title>Kodeine</title>
	<?php
		echo $app->less('/admin/social/ui/css/forum.less');
		include(COREINC.'/head.php');
	?>
</head>
<body>

<header><?php
	include(COREINC.'/top.php');
	include(__DIR__.'/ui/menu.php')
?></header>

<div class="app"><div class="wrapper">

<div style="float:left; width:49%;" id="list">

	<form action="forum" method="post" id="items">
		<input type="hidden" name="opened" class="opened-memo" value="<?php echo $_REQUEST['opened'] ?>" />
		<ul id="mid-0">...</ul>
	</form>

	<div class="clearfix" style="margin:20px 0px 20px 0;">
		<div style="float:left;">
			<a href="javascript:removeSelection()" class="btn btn-mini">Supprimer la selection</a> ou
			<a href="forum" class="btn btn-mini">Annuler</a>
		</div>
		<div style="float:right;">
			<a href="../field/asso?id_type=socialForum" class="btn btn-mini">Gérer les champs</a>
		</div>
	</div>

</div>



<div style="float:right; width:50%;"><?php

	if(!isset($message) && isset($_GET['message'])) $message = $_GET['message'];
	if($message != NULL){
		list($class, $message) = $app->helperMessage($message);
		echo "<div class=\"message message".ucfirst($class)."\">".$message."</div>";
	}

	?>
	<form action="forum" method="post" id="data">
	<input type="hidden" name="action" value="1" />
	<input type="hidden" name="opened" class="opened-memo" value="<?php echo $_REQUEST['opened'] ?>" />
	<input type="hidden" name="id_socialforum" id="id_socialforum" value="<?php echo $data['id_socialforum'] ?>" />

	<table border="0" cellpadding="3" cellspacing="0" width="100%">
		<tr>
			<td width="100">Parent</td>
			<td><?php
				echo $app->apiLoad('socialForum')->socialForumSelector(array(
					'one'		=> true,
					'name'		=> 'mid_socialforum',
					'noid'		=> $data['id_socialforum'],
					'value'		=> $app->formValue($data['mid_socialforum'], $_POST['mid_socialforum']),
					'empty'		=> true
				));
			?></td>
		</tr>
		<tr>
			<td class="<?php echo $app->formError('socialForumName', 'alertNeedToCheck') ?>">Nom</td>
			<td><input type="text" name="socialForumName" value="<?php echo $app->formValue($data['socialForumName'], $_POST['socialForumName']); ?>" style="width:90%" /></td>
		</tr>
		<tr>
			<td>Image</td>
			<td>
				<input type="text" name="socialForumMedia" id="socialForumMedia" value="<?php echo $app->formValue($data['socialForumMedia'], $_POST['socialForumMedia']); ?>" style="width:75%" />
				<a href="#" onclick="mediaOpen('line', 'socialForumMedia')">choisir</a>
			</td>
		</tr>
		<tr>
			<td colspan="2" height="20">&nbsp;</td>
		</tr>
		<?php if(sizeof($fields) > 0){ ?>
		<tr>
			<td colspan="2" style="background:#A6B5BE; font-weight:bold; padding:4px; height:18px;">Paramètres supplémentaires</td>
		</tr>
		<?php foreach($fields as $f){ ?>
		<tr valign="top">
			<td><?php echo $f['fieldName'] ?></td>
			<td><div class="field-list"><?php

				$tmp = $app->formValue($data['field'][$f['fieldKey']], $_POST['field'][$f['id_field']]);
				fieldTrace($app, $iso, $copy, $tmp, $f);

			?></div></td>
		</tr>
		<?php }} ?>

	</table>

	<div style="margin:20px 0px 20px 0;">
		<a class="btn" onclick="$('#data').submit();">Enregistrer</a> ou
		<a href="./forum" class="button rButton">annuler</a>
	</div>

	</form>
</div>

</div></div>

<?php include(COREINC.'/end.php'); ?>
<script type="text/javascript" src="ui/js/forum.js"></script>
<script>
	opened = [<?php echo str_replace('-', ',', $_GET['opened']) ?>];

	$(function(){
		thread(0,0);
	});

</script>

</body></html>