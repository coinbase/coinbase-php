<?php

namespace Coinbase\Wallet\ActiveRecord;

use Coinbase\Wallet\Resource\Address;
use Coinbase\Wallet\Resource\Buy;
use Coinbase\Wallet\Resource\Deposit;
use Coinbase\Wallet\Resource\ResourceCollection;
use Coinbase\Wallet\Resource\Sell;
use Coinbase\Wallet\Resource\Transaction;
use Coinbase\Wallet\Resource\Withdrawal;

trait AccountActiveRecord
{
    use BaseActiveRecord;

    /**
     * Issues a refresh request to the API.
     */
    public function refresh(array $params = [])
    {
        $this->getClient()->refreshAccount($this, $params);
    }

    /**
     * Issues an refresh request to the API.
     */
    public function update(array $params = [])
    {
        $this->getClient()->updateAccount($this, $params);
    }

    /**
     * Issues an API request to make the current account primary.
     */
    public function makePrimary(array $params = [])
    {
        $this->getClient()->setPrimaryAccount($this, $params);
    }

    /**
     * Issues an API request to delete the current account.
     */
    public function delete(array $params = [])
    {
        $this->getClient()->deleteAccount($this, $params);
    }

    // addresses

    /**
     * Fetches account addresses from the API.
     *
     * @return ResourceCollection|Address[] The addresses
     */
    public function getAddresses(array $params = [])
    {
        return $this->getClient()->getAccountAddresses($this, $params);
    }

    /**
     * Fetches a specific account address from the API.
     *
     * @return Address The address
     */
    public function getAddress($addressId, array $params = [])
    {
        return $this->getClient()->getAccountAddress($this, $addressId, $params);
    }

    /**
     * Issues an API request to create a new account address.
     */
    public function createAddress(Address $address, array $params = [])
    {
        $this->getClient()->createAccountAddress($this, $address, $params);
    }

    // transactions

    /**
     * Fetches account transactions from the API.
     *
     * @return ResourceCollection|Transaction[] The transactions
     */
    public function getTransactions(array $params = [])
    {
        return $this->getClient()->getAccountTransactions($this, $params);
    }

    /**
     * Fetches a specific account transaction from the API.
     *
     * @return Transaction The transaction
     */
    public function getTransaction($transactionId, array $params = [])
    {
        return $this->getClient()->getAccountTransaction($this, $transactionId, $params);
    }

    /**
     * Issues an API request to create a new account transaction.
     */
    public function createTransaction(Transaction $transaction, array $params = [])
    {
        $this->getClient()->createAccountTransaction($this, $transaction, $params);
    }

    // buys

    /**
     * Fetches account buys from the API.
     *
     * @return ResourceCollection|Buy[] The buys
     */
    public function getBuys(array $params = [])
    {
        return $this->getClient()->getAccountBuys($this, $params);
    }

    /**
     * Fetches a specific account buy from the API.
     *
     * @return Buy The buy
     */
    public function getBuy($buyId, array $params = [])
    {
        return $this->getClient()->getAccountBuy($this, $buyId, $params);
    }

    /**
     * Issues an API request to create a new account buy.
     */
    public function createBuy(Buy $buy, array $params = [])
    {
        $this->getClient()->createAccountBuy($this, $buy, $params);
    }

    /**
     * Issues an API request to commit an account buy.
     */
    public function commitBuy(Buy $buy, array $params = [])
    {
        $this->getClient()->commitBuy($buy, $params);
    }

    // sells

    /**
     * Fetches account sells from the API.
     *
     * @return ResourceCollection|Sell[] The sells
     */
    public function getSells(array $params = [])
    {
        return $this->getClient()->getSells($this, $params);
    }

    /**
     * Fetches a specific account sell from the API.
     *
     * @return Sell The sell
     */
    public function getSell($sellId, array $params = [])
    {
        return $this->getClient()->getAccountSell($this, $sellId, $params);
    }

    /**
     * Issues an API request to create a new account sell.
     */
    public function createSell(Sell $sell, array $params = [])
    {
        $this->getClient()->createAccountSell($this, $sell, $params);
    }

    /**
     * Issues an API request to commit an account sell.
     */
    public function commitSell(Sell $sell, array $params = [])
    {
        $this->getClient()->commitSell($sell, $params);
    }

    // deposits

    /**
     * Fetches account deposits from the API.
     *
     * @return ResourceCollection|Deposit[] The deposits
     */
    public function getDeposits(array $params = [])
    {
        return $this->getClient()->getAccountDeposits($this, $params);
    }

    /**
     * Fetches a specific account deposit from the API.
     *
     * @return Deposit The deposit
     */
    public function getDeposit($depositId, array $params = [])
    {
        return $this->getClient()->getAccountDeposit($this, $depositId, $params);
    }

    /**
     * Issues an API request to create a new account deposit.
     */
    public function createDeposit(Deposit $deposit, array $params = [])
    {
        $this->getClient()->createAccountDeposit($this, $deposit, $params);
    }

    /**
     * Issues an API request to commit an account deposit.
     */
    public function commitDeposit(Deposit $deposit, array $params = [])
    {
        $this->getClient()->commitDeposit($deposit, $params);
    }

    // withdrawals

    /**
     * Fetches account withdrawals from the API.
     *
     * @return ResourceCollection|Withdrawal[] The withdrawals
     */
    public function getWithdrawals(array $params = [])
    {
        return $this->getClient()->getAccountWithdrawals($this, $params);
    }

    /**
     * Fetches a specific account withdrawal from the API.
     *
     * @return Withdrawal The withdrawal
     */
    public function getWithdrawal($withdrawalId, array $params = [])
    {
        return $this->getClient()->getAccountWithdrawal($this, $withdrawalId, $params);
    }

    /**
     * Issues an API request to create a new account withdrawal.
     */
    public function createWithdrawal(Withdrawal $withdrawal, array $params = [])
    {
        $this->getClient()->createAccountWithdrawal($this, $withdrawal, $params);
    }

    /**
     * Issues an API request to commit an account withdrawal.
     */
    public function commitWithdrawal(Withdrawal $withdrawal, array $params = [])
    {
        $this->getClient()->commitWithdrawal($withdrawal, $params);
    }
}
