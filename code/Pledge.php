<?php

class Pledge extends DataObject
{
	static $db = array(
		'UserHash'  => 'Varchar(255)',
		'UserAgent' => 'Varchar(255)',
		'IP'		=> 'Varchar(15)'
	);
}
