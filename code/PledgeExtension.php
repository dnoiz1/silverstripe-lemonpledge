<?php

class PledgeExtension extends Extension
{
	static function getPledgeCount()
	{
		return (int) Pledge::get('Pledge')->Count();
	}

	function __construct()
	{
		Requirements::JavaScript(THIRDPARTY_DIR . '/jquery/jquery.min.js');
		Requirements::JavaScript('lemonpledge/javascript/lemonpledge.js');
	}

	public function Content()
	{
	    if(stripos($this->owner->data()->Content, '$PledgeCount') !== false) {
			$c = $this->owner->data()->Content;
			$countspan = sprintf('<span class="pledge-count">%s</span>',  PledgeExtension::getPledgeCount());
    	    return str_ireplace('$PledgeCount', $countspan, $c);
        }
		return $this->owner->data()->Content;
	}
}
