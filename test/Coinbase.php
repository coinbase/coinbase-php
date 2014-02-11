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

        $coinbase = new Coinbase("");
        $coinbase->setRequestor($requestor);
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

        $coinbase = new Coinbase("");
        $coinbase->setRequestor($requestor);
        $address = $coinbase->getReceiveAddress();
        $this->assertEqual($address, 'muVu2JZo8PbewBHRp6bpqFvVD87qvqEHWA');
    }

    function testGetAllAddresses()
    {

        $requestor = new MockCoinbase_Requestor();
        $requestor->returns('doCurlRequest', array( "statusCode" => 200, "body" => '
        {
          "addresses": [
            {
              "address": {
                "address": "moLxGrqWNcnGq4A8Caq8EGP4n9GUGWanj4",
                "callback_url": null,
                "label": "My Label",
                "created_at": "2013-05-09T23:07:08-07:00"
              }
            },
            {
              "address": {
                "address": "mwigfecvyG4MZjb6R5jMbmNcs7TkzhUaCj",
                "callback_url": null,
                "label": null,
                "created_at": "2013-05-09T17:50:37-07:00"
              }
            }
          ],
          "total_count": 2,
          "num_pages": 1,
          "current_page": 1
        }'));

        $coinbase = new Coinbase("");
        $coinbase->setRequestor($requestor);
        $addresses = $coinbase->getAllAddresses()->addresses;
        $this->assertEqual($addresses[0]->address, 'moLxGrqWNcnGq4A8Caq8EGP4n9GUGWanj4');
        $this->assertEqual($addresses[0]->label, 'My Label');
        $this->assertEqual($addresses[1]->address, 'mwigfecvyG4MZjb6R5jMbmNcs7TkzhUaCj');
    }

    function testSingleError()
    {
        $requestor = new MockCoinbase_Requestor();
        $requestor->returns('doCurlRequest', array( "statusCode" => 200, "body" => '
        {
          "success": false,
          "error": "error1"
        }'));

        $coinbase = new Coinbase("");
        $coinbase->setRequestor($requestor);

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

        $coinbase = new Coinbase("");
        $coinbase->setRequestor($requestor);

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

        $coinbase = new Coinbase("");
        $coinbase->setRequestor($requestor);

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

        $coinbase = new Coinbase("");
        $coinbase->setRequestor($requestor);
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

        $coinbase = new Coinbase("");
        $coinbase->setRequestor($requestor);
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

        $coinbase = new Coinbase("");
        $coinbase->setRequestor($requestor);
        $response = $coinbase->createButton("test", "1.23", "USD", "Order123", array(
            "style" => "custom_large",
            "description" => "Sample description"
        ));
        $this->assertEqual($response->button->code, '93865b9cae83706ae59220c013bc0afd');
        $this->assertEqual($response->embedHtml, "<div class=\"coinbase-button\" data-code=\"93865b9cae83706ae59220c013bc0afd\"></div><script src=\"https://coinbase.com/assets/button.js\" type=\"text/javascript\"></script>");
    }

    function testCreateOrderFromButtonCode()
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

        $coinbase = new Coinbase("");
        $coinbase->setRequestor($requestor);
        $response = $coinbase->createButton("test", "1.23", "USD", "Order123", array(
            "style" => "custom_large",
            "description" => "Sample description"
        ));

        $this->assertEqual($response->button->code, '93865b9cae83706ae59220c013bc0afd');
        $buttonCode = $response->button->code;

        $requestor = new MockCoinbase_Requestor();
        $requestor->returns('doCurlRequest', array( "statusCode" => 200, "body" => '
        {
          "success": true,
          "order": {
            "id": "7RTTRDVP",
            "created_at": "2013-11-09T22:47:10-08:00",
            "status": "new",
            "total_btc": {
              "cents": 100000000,
              "currency_iso": "BTC"
            },
            "total_native": {
              "cents": 100000000,
              "currency_iso": "BTC"
            },
            "custom": "Order123",
            "receive_address": "mgrmKftH5CeuFBU3THLWuTNKaZoCGJU5jQ",
            "button": {
              "type": "buy_now",
              "name": "test",
              "description": "Sample description",
              "id": "93865b9cae83706ae59220c013bc0afd"
            },
            "transaction": null
          }
        }'));

        $coinbase = new Coinbase("");
        $coinbase->setRequestor($requestor);
        $response = $coinbase->createOrderFromButtonCode($buttonCode);
        $this->assertEqual($response->order->button->id, $buttonCode);
        $this->assertEqual($response->order->status, 'new');
    }

    function testCreateButtonWithOptions()
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

        $coinbase = new Coinbase("");
        $coinbase->setRequestor($requestor);
        $response = $coinbase->createButtonWithOptions("test", "1.23", "USD", "Order123", array(
            "name" => "test",
            "price_string" => "1.23",
            "price_currency_iso" => "USD",
            "custom" => "Order123",
            "style" => "custom_large",
            "description" => "Sample description"
        ));
        $this->assertEqual($response->button->code, '93865b9cae83706ae59220c013bc0afd');
        $this->assertEqual($response->embedHtml, "<div class=\"coinbase-button\" data-code=\"93865b9cae83706ae59220c013bc0afd\"></div><script src=\"https://coinbase.com/assets/button.js\" type=\"text/javascript\"></script>");
    }

    function testCreateUser()
    {

        $requestor = new MockCoinbase_Requestor();
        $requestor->returns('doCurlRequest', array( "statusCode" => 200, "body" => '
        {
          "success": true,
          "user": {
            "id": "501a3d22f8182b2754000011",
            "name": "New User",
            "email": "newuser@example.com",
            "receive_address": "mpJKwdmJKYjiyfNo26eRp4j6qGwuUUnw9x"
          }
        }'));

        $coinbase = new Coinbase("");
        $coinbase->setRequestor($requestor);
        $response = $coinbase->createUser("newuser@example.com", "test123!");
        $this->assertEqual($response->success, true);
        $this->assertEqual($response->user->email, "newuser@example.com");
        $this->assertEqual($response->user->receive_address, "mpJKwdmJKYjiyfNo26eRp4j6qGwuUUnw9x");
    }

    function testBuy()
    {

        $requestor = new MockCoinbase_Requestor();
        $requestor->returns('doCurlRequest', array( "statusCode" => 200, "body" => '
        {
          "success": true,
          "transfer": {
            "_type": "AchDebit",
            "code": "6H7GYLXZ",
            "created_at": "2013-01-28T16:08:58-08:00",
            "fees": {
              "coinbase": {
                "cents": 14,
                "currency_iso": "USD"
              },
              "bank": {
                "cents": 15,
                "currency_iso": "USD"
              }
            },
            "status": "created",
            "payout_date": "2013-02-01T18:00:00-08:00",
            "btc": {
              "amount": "1.00000000",
              "currency": "BTC"
            },
            "subtotal": {
              "amount": "13.55",
              "currency": "USD"
            },
            "total": {
              "amount": "13.84",
              "currency": "USD"
            }
          }
        }'));

        $coinbase = new Coinbase("");
        $coinbase->setRequestor($requestor);
        $response = $coinbase->buy("1");
        $this->assertEqual($response->success, true);
        $this->assertEqual($response->transfer->code, "6H7GYLXZ");
    }

    function testSell()
    {

        $requestor = new MockCoinbase_Requestor();
        $requestor->returns('doCurlRequest', array( "statusCode" => 200, "body" => '
        {
          "success": true,
          "transfer": {
            "_type": "AchCredit",
            "code": "RD2OC8AL",
            "created_at": "2013-01-28T16:32:35-08:00",
            "fees": {
              "coinbase": {
                "cents": 14,
                "currency_iso": "USD"
              },
              "bank": {
                "cents": 15,
                "currency_iso": "USD"
              }
            },
            "status": "created",
            "payout_date": "2013-02-01T18:00:00-08:00",
            "btc": {
              "amount": "1.00000000",
              "currency": "BTC"
            },
            "subtotal": {
              "amount": "13.50",
              "currency": "USD"
            },
            "total": {
              "amount": "13.21",
              "currency": "USD"
            }
          }
        }'));

        $coinbase = new Coinbase("");
        $coinbase->setRequestor($requestor);
        $response = $coinbase->sell("1");
        $this->assertEqual($response->success, true);
        $this->assertEqual($response->transfer->code, "RD2OC8AL");
    }

    function testGetContacts()
    {

        $requestor = new MockCoinbase_Requestor();
        $requestor->returns('doCurlRequest', array( "statusCode" => 200, "body" => '
        {
          "contacts": [
            {
              "contact": {
                "email": "user1@example.com"
              }
            },
            {
              "contact": {
                "email": "user2@example.com"
              }
            }
          ],
          "total_count": 2,
          "num_pages": 1,
          "current_page": 1
        }'));

        $coinbase = new Coinbase("");
        $coinbase->setRequestor($requestor);
        $response = $coinbase->getContacts("user");
        $this->assertEqual($response->contacts, array( "user1@example.com", "user2@example.com" ));
    }

    function testPaginatedResource()
    {

        $requestor = new MockCoinbase_Requestor();
        $requestor->returns('doCurlRequest', array( "statusCode" => 200, "body" => '
        {
          "current_user": {
            "id": "5011f33df8182b142400000e",
            "email": "user2@example.com",
            "name": "User Two"
          },
          "balance": {
            "amount": "50.00000000",
            "currency": "BTC"
          },
          "total_count": 2,
          "num_pages": 1,
          "current_page": 1,
          "transactions": [
            {
              "transaction": {
                "id": "5018f833f8182b129c00002f",
                "created_at": "2012-08-01T02:34:43-07:00",
                "amount": {
                  "amount": "-1.10000000",
                  "currency": "BTC"
                },
                "request": true,
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
                }
              }
            },
            {
              "transaction": {
                "id": "5018f833f8182b129c00002e",
                "created_at": "2012-08-01T02:36:43-07:00",
                "hsh": "9d6a7d1112c3db9de5315b421a5153d71413f5f752aff75bf504b77df4e646a3",
                "amount": {
                  "amount": "-1.00000000",
                  "currency": "BTC"
                },
                "request": false,
                "status": "complete",
                "sender": {
                  "id": "5011f33df8182b142400000e",
                  "name": "User Two",
                  "email": "user2@example.com"
                },
                "recipient_address": "37muSN5ZrukVTvyVh3mT5Zc5ew9L9CBare"
              }
            }
         ]
        }'));

        $coinbase = new Coinbase("");
        $coinbase->setRequestor($requestor);
        $response = $coinbase->getTransactions();
        $this->assertEqual($response->transactions[0]->id, '5018f833f8182b129c00002f');
        $this->assertEqual($response->transactions[1]->id, '5018f833f8182b129c00002e');
    }

    function testInvalidAuthenticationObjectThrowsException()
    {
        try {
            $invalidAuthenticationObject = new stdClass();
            $invalidAuthenticationObject->foo = "bar";
            $coinbase = new Coinbase($invalidAuthenticationObject);
            $this->fail('Expected Coinbase_ApiException to be thrown, but it was not.');
        } catch (Coinbase_ApiException $e) {
            $this->assertEqual($e->getMessage(), 'Could not determine API authentication scheme');
        }
    }
}
