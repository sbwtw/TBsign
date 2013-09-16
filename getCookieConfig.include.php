<?php

return array(

	'tokenUrl' => 'https://passport.baidu.com/v2/api/?getapi&tpl=tb&apiver=v3&tt=%s&class=login&logintype=dialogLogin&callback=bd__cbs__sbw'
	'verifyUrl' => 'https://passport.baidu.com/v2/api/?logincheck&token=%s&tpl=tb&apiver=v3&username=%s&isphone=false&callback=bd__cbs__sbw',
	'loginUrl' => 'https://passport.baidu.com/v2/api/?login',
	'finalUrl' => 'http://tieba.baidu.com',
	'timeOut' => '5000',
	'args' => array('userName','password','cookie','token','verifyAddress','verifyCode','step'),

);

?>
