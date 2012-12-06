<?php

class PledgePage extends Page
{
	static $db = array(
		'ButtonLabel'	=> 'Varchar(255)',
		'HitOrClick'	=> 'Boolean'
	);

	static $defaults = array(
		'ButtonLabel'	=> 'Click to Pledge',
		'Content'		=> '<p>Current Pledges: $PledgeCount</p><p>$PledgeButton</p>'
	);

	public function getCMSFields()
	{
		$f = parent::getCMSFields();
		$f->addFieldsToTab('Root.Content.Main', new FieldSet(
			new TextField('ButtonLabel', 'Button Label'),
			new LiteralField('', 'You can use $PledgeButton or $PledgeCount in Content'),
			new CheckBoxField('HitOrClick', 'Require a button click to Increment?')
		));
		return $f;
	}
}

class PledgePage_controller extends Page_controller
{
	public function index()
	{
        Requirements::JavaScript(THIRDPARTY_DIR . '/jquery/jquery.min.js');
        Requirements::JavaScript('lemonpledge/javascript/lemonpledge.js');

		if($this->HitOrClick) $this->pledge();

		$c = false;

        if($this->Content && $this->PledgeButton()) {
            if(stripos($this->Content, '$PledgeButton') !== false) {
                $c = str_ireplace('$PledgeButton', ($this->HitOrClick) ? '' :  $this->PledgeButton()->forTemplate(), $this->Content);
            }
        }

        if(stripos($this->Content, '$PledgeCount') !== false) {
            $c = (!$c) ? $this->Content : $c;
            $countspan = sprintf('<span class="pledge-count">%s</span>',  $this->getPledgeCount());
           	$c = str_ireplace('$PledgeCount', $countspan, $c);
        }

		if($c) {
	        return array(
		        'Content' => DBField::create('HTMLText', $c),
	            'Form'    => ''
	        );
		}

        return array(
            'Content'   => DBField::create('HTMLText', $this->Content),
			'Form'		=> ($this->HitOrClick) ? $this->PledgeButton() : ''
        );
	}

	public function PledgeButton()
	{
		if($last = Session::get('LastPledgeTime')) {
			// 30 seconds
			if(date('U') - $last < 30) return new Form($this, 'pledge', new FieldSet(), new FieldSet());
		}

		$f = new FieldSet();
		$a = new FieldSet(
			new FormAction('doPledge', $this->ButtonLabel)
		);

		return new Form($this, 'pledge', $f, $a);
	}

	public function pledge()
	{
		$ip = $_SERVER['REMOTE_ADDR'];
		$ua = $_SERVER['HTTP_USER_AGENT'];
		$hash = md5($ip . $ua);

		$do = Pledge::get_one('Pledge', sprintf("UserHash = '%s' AND Created BETWEEN DATETIME('now', '-30 seconds') AND DATETIME('now')", $hash));

		if($do) {
			return false;
		}

		$p = new Pledge();
		$p->UserHash = $hash;
		$p->IP = $ip;
		$p->UserAgent = $ua;
		$p->write();

		Session::set('LastPledgeTime', Date('U'));

		return true;
	}

	public function doPledge()
	{
		return ($this->pledge()) ? Director::redirectBack() : $this->httpError(404);
	}

	public function getPledgeCount()
	{
		return DB::Query('SELECT COUNT(ID) FROM Pledge')->value();
	}
}
