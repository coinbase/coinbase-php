<?php
require_once(dirname(__FILE__) . '/simpletest/autorun.php');
require_once(dirname(__FILE__) . '/../lib/Coinbase.php');

Mock::generate("Coinbase_Requestor");

class TestOfCoinbase extends UnitTestCase {

    function testGetBalance()
    {

        $requestor = new MockCoinbase_Requestor();
        $requestor->returns('doCurlRequest', array( "statusCode" => 200, "body" => '
        {
          "amount":8590.032,
          "currency":"BTC"
        }'));

        $coinbase = new Coinbase("", $requestor);
        $balance = $coinbase->getBalance();
        $this->assertEqual($balance, '8590.032');
    }

    function testGetReceiveAddress()
    {

        $requestor = new MockCoinbase_Requestor();
        $requestor->returns('doCurlRequest', array( "statusCode" => 200, "body" => '
        {
          "success": true,
          "address": "muVu2JZo8PbewBHRp6bpqFvVD87qvqEHWA",
          "callback_url": null
        }'));

        $coinbase = new Coinbase("", $requestor);
        $address = $coinbase->getReceiveAddress();
        $this->assertEqual($address, 'muVu2JZo8PbewBHRp6bpqFvVD87qvqEHWA');
    }

    function testSingleError()
    {
        $requestor = new MockCoinbase_Requestor();
        $requestor->returns('doCurlRequest', array( "statusCode" => 200, "body" => '
        {
          "success": false,
          "error": "error1"
        }'));

        $coinbase = new Coinbase("", $requestor);

        try {
            $balance = $coinbase->getBalance();
            $this->fail("Coinbase_ApiException was expected here");
        } catch (Coinbase_ApiException $e) {
            $this->assertEqual($e->getMessage(), 'error1');
        }
    }

    function testSingleArrayError()
    {
        $requestor = new MockCoinbase_Requestor();
        $requestor->returns('doCurlRequest', array( "statusCode" => 200, "body" => '
        {
          "success": false,
          "errors": [
            "error1"
          ]
        }'));

        $coinbase = new Coinbase("", $requestor);

        try {
            $balance = $coinbase->getBalance();
            $this->fail("Coinbase_ApiException was expected here");
        } catch (Coinbase_ApiException $e) {
            $this->assertEqual($e->getMessage(), 'error1');
        }
    }

    function testMultipleArrayError()
    {
        $requestor = new MockCoinbase_Requestor();
        $requestor->returns('doCurlRequest', array( "statusCode" => 200, "body" => '
        {
          "success": false,
          "errors": [
            "error1",
            "error2"
          ]
        }'));

        $coinbase = new Coinbase("", $requestor);

        try {
            $balance = $coinbase->getBalance();
            $this->fail("Coinbase_ApiException was expected here");
        } catch (Coinbase_ApiException $e) {
            $this->assertEqual($e->getMessage(), 'error1, error2');
        }
    }

    function testSendMoney()
    {

        $requestor = new MockCoinbase_Requestor();
        $requestor->returns('doCurlRequest', array( "statusCode" => 200, "body" => '
        {
          "success": true,
          "transaction": {
            "id": "501a1791f8182b2071000087",
            "created_at": "2012-08-01T23:00:49-07:00",
            "hsh": "9d6a7d1112c3db9de5315b421a5153d71413f5f752aff75bf504b77df4e646a3",
            "notes": "Sample transaction for you!",
            "amount": {
              "amount": "-1.23400000",
              "currency": "BTC"
            },
            "request": false,
            "status": "pending",
            "sender": {
              "id": "5011f33df8182b142400000e",
              "name": "User Two",
              "email": "user2@example.com"
            },
            "recipient": {
              "id": "5011f33df8182b142400000a",
              "name": "User One",
              "email": "user1@example.com"
            },
            "recipient_address": "37muSN5ZrukVTvyVh3mT5Zc5ew9L9CBare"
          }
        }'));

        $coinbase = new Coinbase("", $requestor);
        $response = $coinbase->sendMoney("user1@example.com", "1.234", "Sample transaction for you");
        $this->assertEqual($response->success, true);
        $this->assertEqual($response->transaction->id, '501a1791f8182b2071000087');
        $this->assertEqual($response->transaction->hsh, '9d6a7d1112c3db9de5315b421a5153d71413f5f752aff75bf504b77df4e646a3');
        $this->assertEqual($response->transaction->request, false);
    }

    function testRequestMoney()
    {

        $requestor = new MockCoinbase_Requestor();
        $requestor->returns('doCurlRequest', array( "statusCode" => 200, "body" => '
        {
          "success": true,
          "transaction": {
            "id": "501a3554f8182b2754000003",
            "created_at": "2012-08-02T01:07:48-07:00",
            "hsh": null,
            "notes": "Sample request for you!",
            "amount": {
              "amount": "1.23400000",
              "currency": "BTC"
            },
            "request": true,
            "status": "pending",
            "sender": {
              "id": "5011f33df8182b142400000a",
              "name": "User One",
              "email": "user1@example.com"
            },
            "recipient": {
              "id": "5011f33df8182b142400000e",
              "name": "User Two",
              "email": "user2@example.com"
            }
          }
        }'));

        $coinbase = new Coinbase("", $requestor);
        $response = $coinbase->requestMoney("user1@example.com", "1.234", "Sample transaction for you");
        $this->assertEqual($response->success, true);
        $this->assertEqual($response->transaction->id, '501a3554f8182b2754000003');
        $this->assertEqual($response->transaction->hsh, null);
        $this->assertEqual($response->transaction->request, true);

        $requestor->returns('doCurlRequest', array( "statusCode" => 200, "body" => '
        {
          "success": true
        }'));
        $this->assertEqual($coinbase->resendRequest('501a3554f8182b2754000003')->success, true);
        $this->assertEqual($coinbase->cancelRequest('501a3554f8182b2754000003')->success, true);
        $this->assertEqual($coinbase->completeRequest('501a3554f8182b2754000003')->success, true);
    }

    function testCreateButton()
    {

        $requestor = new MockCoinbase_Requestor();
        $requestor->returns('doCurlRequest', array( "statusCode" => 200, "body" => '
        {
            "success": true,
            "button": {
                "code": "93865b9cae83706ae59220c013bc0afd",
                "type": "buy_now",
                "style": "custom_large",
                "text": "Pay With Bitcoin",
                "name": "test",
                "description": "Sample description",
                "custom": "Order123",
                "price": {
                    "cents": 123,
                    "currency_iso": "USD"
                }
            }
        }'));

        $coinbase = new Coinbase("", $requestor);
        $response = $coinbase->createButton("test", "1.23", "USD", "Order123", array(
            "style" => "custom_large",
            "description" => "Sample description"
        ));
        $this->assertEqual($response->button->code, '93865b9cae83706ae59220c013bc0afd');
        $this->assertEqual($response->embedHtml, "<div class=\"coinbase-button\" data-code=\"93865b9cae83706ae59220c013bc0afd\"></div><script src=\"https://coinbase.com/assets/button.js\" type=\"text/javascript\"></script>");
    }
}