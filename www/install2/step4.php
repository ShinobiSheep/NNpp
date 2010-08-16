<?php
require_once './lib/installpage.php';
require_once('./lib/config.php');

$page = new Installpage();
$page->title = "Admin user setup";

$cfg = new Config();

if (!$cfg->isInitialized()) {
	header("Location: index.php");
	die();
}

$cfg = $cfg->getSession();

if  ($page->isPostBack()) {
	$cfg->doCheck = true;
	
	$cfg->ADMIN_USER = trim($_POST['user']);
	$cfg->ADMIN_PASS = trim($_POST['pass']);
	$cfg->ADMIN_EMAIL = trim($_POST['email']);
	
	if ($cfg->ADMIN_USER == '' || $cfg->ADMIN_PASS == '' || $cfg->ADMIN_EMAIL == '') {
		$cfg->error = true;
	} else {
		define('DB_HOST', $cfg->DB_HOST);
		define('DB_USER', $cfg->DB_USER);
		define('DB_PASSWORD', $cfg->DB_PASSWORD);
		define('DB_NAME', $cfg->DB_NAME);
		define('WWW_DIR', $cfg->WWW_DIR);
		require_once($cfg->WWW_DIR.'/lib/framework/db.php');
		require_once($cfg->WWW_DIR.'/lib/users.php');
		
		$user = new Users();
		if (!$user->isValidUsername($cfg->ADMIN_USER)) {
			$cfg->error = true;
			$cfg->ADMIN_USER = '';
		} else {
			$usrCheck = $user->getByUsername($cfg->ADMIN_USER);
			if ($usrCheck) {
				$cfg->error = true;
				$cfg->ADMIN_USER = '';
			}
		}
		if (!$user->isValidEmail($cfg->ADMIN_EMAIL)) {
			$cfg->error = true;
			$cfg->ADMIN_EMAIL = '';
		}
		
		if (!$cfg->error) {
			$cfg->adminCheck = $user->add($cfg->ADMIN_USER, $cfg->ADMIN_PASS, $cfg->ADMIN_EMAIL, 2, '');
			if (!is_numeric($cfg->adminCheck)) {
				$cfg->error = true;
			} else {
				$user->login($cfg->adminCheck, "", 1);
			}
		}
	}
	
	if (!$cfg->error) {
		$cfg->setSession();
	}
}

$page->smarty->assign('cfg', $cfg);

$page->smarty->assign('page', $page);

$page->content = $page->smarty->fetch('step4.tpl');
$page->render();

?>