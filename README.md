# bdd-proxy

Proxy for calling methods using BDD style.

## Usage

```php
class MyTestCase extends \PHPUnit\Framework\TestCase {
  function setUp() {
    $this->bdd = new \diogoko\bdd\BDDProxy($this, 'given|when|then', 'and');
  }

  function testSum() {
    $this->bdd->given('that a is number', 3)
        ->and('that b is number', 5)
        ->when('adding a and b')
        ->then('the result is', 8);
  }

  /**
   * @given that a is number
   */
  function that_a_is_number($x) {
    $this->a = $x;
  }

  /**
   * @given that b is number
   */
  function that_b_is_number($x) {
    $this->b = $x;
  }

  /**
   * @when adding a and b
   */
  function adding_a_and_b($x) {
    $this->result = $this->a + $this->b;
  }

  /**
   * @then the result is
   */
  function the_result_is($result) {
    $this->assertEquals($result, $this->result);
  }
}
```
