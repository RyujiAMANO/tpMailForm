<?php
if(defined('PENGIN_PATH') === false){
	define('PENGIN_PATH', XOOPS_ROOT_PATH . '/modules/pengin');
	define('TP_MODULE_PATH', XOOPS_ROOT_PATH . '/modules');
	define('PENGIN_URL', XOOPS_URL . '/modules/pengin');	
	define('TP_JS_VENDOR_URL', XOOPS_URL . '/js/vendor');
	require_once PENGIN_PATH . '/Pengin.php';	
}
