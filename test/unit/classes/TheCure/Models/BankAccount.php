<?php
namespace TheCure\Models;

class BankAccount extends \TheCure\Models\Model {

	public function transfer_money(BankAccount $account, $amount)
	{
		$this->__object()->balance -= $amount;
		$account->__object()->balance += $amount;
	}

}