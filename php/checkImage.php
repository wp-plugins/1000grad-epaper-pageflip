<?php

$link = urldecode( $_POST['ep_link'] );
$value = array();

( false === @file_get_contents($link . "epaper/preview.jpg") ) ?
                $value['img_exists_small'] = false : $value['img_exists_small'] = true;

( false === @file_get_contents($link . "epaper/preview_large.png") ) ?
                $value['img_exists_large'] = false : $value['img_exists_large'] = true;


echo json_encode($value);
?>
