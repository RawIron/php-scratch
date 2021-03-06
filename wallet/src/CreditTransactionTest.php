<?php
 
namespace RawIron\Wallet;

use RawIron\Wallet\Engines\MemoryEngine;
use RawIron\Wallet\Engines\WalletStore;
use RawIron\Wallet\Logger;
use RawIron\Wallet\Currency;
use RawIron\Wallet;

use PHPUnit\Framework\TestCase;


class CreditTransactionTest extends TestCase {

  private function wallet() {
    $currencies = Currency::getCurrencies();
    $engine = new MemoryEngine($currencies);
    $userId  = 123;
    $session = new WalletStore($userId, $engine);
    $logger  = new Logger();
    $wallet  = new Wallet\CreditTransaction($session, $currencies, $logger);
    return $wallet;
  }


  public function test_sub_nsf() {
    $wallet = $this->wallet();
    $rc = $wallet->sub(24,'premium');
    $this->assertFalse($rc);
  }

  public function test_sub_debits() {
    $wallet = $this->wallet();
    $wallet->sub(24,'premium');
    $this->assertEquals(-24, $wallet->getTransactionBalance('premium'));
  }

  public function test_add() {
    $wallet = $this->wallet();
    $rc = $wallet->add(24,'premium');
    $this->assertTrue($rc);
  }

  public function test_add_credits() {
    $wallet = $this->wallet();
    $wallet->add(24,'premium');
    $this->assertEquals(24, $wallet->getTransactionBalance('premium'));
  }

  public function test_add_balance() {
    $wallet = $this->wallet();
    $wallet->add(24,'premium');
    $wallet->commit();
    $this->assertEquals(24, $wallet->getAccountBalance('premium'));
  }

  public function test_two_currency_balance() {
    $wallet = $this->wallet();
    $wallet->add(24,'premium');
    $wallet->add(24,'premium');
    $wallet->add(400,'coins');
    $wallet->sub(120,'coins');

    $balance = $wallet->getTransactionBalance('premium');
    $this->assertEquals(48, $balance);
    $balance = $wallet->getTransactionBalance('coins');
    $this->assertEquals(280, $balance);

    $balance = $wallet->getAccountBalance('premium');
    $this->assertEquals(0, $balance);
    $balance = $wallet->getAccountBalance('coins');
    $this->assertEquals(0, $balance);

    $wallet->commit();

    $balance = $wallet->getTransactionBalance('premium');
    $this->assertEquals(0, $balance);
    $balance = $wallet->getTransactionBalance('coins');
    $this->assertEquals(0, $balance); 

    $balance = $wallet->getAccountBalance('premium');
    $this->assertEquals(48, $balance);
    $balance = $wallet->getAccountBalance('coins');
    $this->assertEquals(280, $balance);
  }

}
