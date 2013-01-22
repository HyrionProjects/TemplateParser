<?php
require_once 'hyrionParser2.php';
require_once 'Parser_functions.php';
$hyrion_parser = new Hyrion_parser();

$data = array();
$data['test'] = array(
					0 => array('test2' => array(0 => array('test3' => 'q'))),
					1 => array('test2' => array(1 => array('test3' => 'q2'))),
					);
$hyrion_parser->setFunctionClass('Parser_functions');
echo $hyrion_parser->parse($filename='test.php',$data);