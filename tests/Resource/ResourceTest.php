<?php

namespace Coinbase\Wallet\Tests\Resource;

use Coinbase\Wallet\Resource\Resource;
use Coinbase\Wallet\Resource\Checkout;

class ResourceTest extends \PHPUnit_Framework_TestCase
{
    public function testConstructorId()
    {
        $resource = new Resource(null, '/v2/widgets/WIDGET_ID');
        $this->assertEquals('WIDGET_ID', $resource->getId());
    }

    public function testUpdateAttributes()
    {
      $checkout = new Checkout();
      $this->assertNotSame("this is the description", $checkout->getDescription());
      $this->assertNotSame("this is the name", $checkout->getName());
      $attrs = array(
          'name'                    => 'this is the name',
          'description'             => 'this is the description',
          'customer_defined_amount' => true,
          'not_existant_attr'       => 'some other thing'
      );
      $checkout->updateAttributes($attrs);
      $this->assertSame("this is the description", $checkout->getDescription());
      $this->assertSame("this is the name", $checkout->getName());
      $this->assertSame(true, $checkout->isCustomerDefinedAmount());
    }

    public function testAttrHashConstructor()
    {
      $attrs = array(
          'name'               => 'this is the name',
          'description'        => 'this is the description',
          'not_existant_attr'  => 'some other thing'
      );
      $checkout = new Checkout($attrs);
      $this->assertSame("this is the description", $checkout->getDescription());
      $this->assertSame("this is the name", $checkout->getName());
    }
}
