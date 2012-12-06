<?php

class PledgePage extends Page
{
	static $db = array(
		'ButtonLabel'	=> 'Varchar(255)'
	);

	static $defaults = array(
		'ButtonLabel'	=> 'Click to Pledge',
		'Content'		=> '<p>Current Pledges: $PledgeCount</p><p>$PlegeButton</p>'
	);

	public function getCMSFields()
	{
		$f = parent::getCMSFields();
		$f->insertBefore(new TextField('ButtonLabel', 'Button Label'), 'Content');
		$f->insertBefore(new LiteralField('', 'You can use $PledgeButton or $PledgeCount in Content'), 'Content');
		return $f;
	}
}

class PledgePage_controller extends Page_controller
{
	public function index()
	{
        if($this->Content && $this->PledgeButton()) {
            if(stripos($this->Content, '$PledgeButton') !== false) {
                $c = str_ireplace('$PledgeButton', $this->PledgeButton()->forTemplate(), $this->Content());
            }
            return array(
                'Content' => DBField::create_field('HTMLText', $c),
                'Form'    => ''
            );
        }
        return array(
            'Content'   => DBField::create_field('HTMLText', $this->Content),
			'Form'		=> $this->PledgeButton()
        );
	}

	public function PledgeButton()
	{
		if($last = Session::get('LastPledgeTime')) {
			// 30 seconds
			if(date('U') - $last < 30) return new Form($this, 'pledge', new FieldList(), new FieldList());
		}

		$f = new FieldList();
		$a = new FieldList(
			new FormAction('pledge', $this->ButtonLabel)
		);

		return new Form($this, 'pledge', $f, $a);
	}

	public function pledge()
	{
		$ip = $_SERVER['REMOTE_ADDR'];
		$ua = $_SERVER['HTTP_USER_AGENT'];

		$hash = md5($ip . $ua);

		$do = Pledge::get_one('Pledge', sprintf("UserHash = '%s' AND Created BETWEEN NOW() - INTERVAL 30 SECOND AND NOW()", $hash));

		if($do) {
			return $this->httpError(403);
		}

		$p = Pledge::create();
		$p->UserHash = $hash;
		$p->write();

		Session::set('LastPledgeTime', Date('U'));

		return Director::RedirectBack();
	}

	public function getPledgeCount()
	{
		return PledgeExtension::getPledgeCount();
	}
}
