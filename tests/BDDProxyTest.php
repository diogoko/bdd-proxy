<?php

namespace diogoko\bdd\tests;

use diogoko\bdd\BDDProxy;


class BDDProxyTest extends \PHPUnit\Framework\TestCase {
  /**
   * @expectedException \BadMethodCallException
   * @expectedExceptionMessage Class diogoko\bdd\tests\OneMethodTwoKindsOneDescription does not have any method annotated with: @given undefined description
   */
  function testMethodNotFound() {
    $t = new OneMethodTwoKindsOneDescription();
    $p = new BDDProxy($t, 'given|whezn|then', 'and');
    
    $p->given('undefined description');
  }
  
  function testParameters() {
    $t = new MethodWithParameters();
    $p = new BDDProxy($t, 'given|when|then', 'and');
    
    $p->given('my description 1', 10, 20, 30);
    
    $this->assertEquals([['method1', 10, 20, 30]], $t->calls);
  }
  
  /**
   * @expectedException \LogicException
   * @expectedExceptionMessage Continuation method (and) must be called after step method (given|when|then)
   */
  function testStartWithContinuation() {
    $t = new MultipleMethods();
    $p = new BDDProxy($t, 'given|when|then', 'and');
    
    $p->and('my description 1');
  }
  
  function testValidContinuations() {
    $t = new MultipleMethods();
    $p = new BDDProxy($t, 'given|when|then', 'and');
    
    $p->given('my description 1')
            ->and('my description 2')
            ->and('my description 3')
            ->then('my description 1')
            ->and('my description 2')
            ->and('my description 3');
    
    $this->assertEquals([
        ['given1'],
        ['given2'],
        ['given3'],
        ['then1'],
        ['then2'],
        ['then3'],
    ], $t->calls);
  }
  
  function testOneMethodTwoKindsOneDescription() {
    $t = new OneMethodTwoKindsOneDescription();
    $p = new BDDProxy($t, 'given|when|then', 'and');
    
    $p->given('my description 1')
            ->then('my description 1');
    
    $this->assertEquals([['method1'], ['method1']], $t->calls);
  }
  
  function testOneMethodOneKindTwoDescriptions() {
    $t = new OneMethodOneKindTwoDescriptions();
    $p = new BDDProxy($t, 'given|when|then', 'and');
    
    $p->given('my description 1')
            ->given('my description 2');
    
    $this->assertEquals([['method1'], ['method1']], $t->calls);
  }
  
  function testTwoMethodsDifferentKindsSameDescription() {
    $t = new TwoMethodsDifferentKindsSameDescription();
    $p = new BDDProxy($t, 'given|when|then', 'and');
    
    $p->given('my description 1')
            ->then('my description 1');
    
    $this->assertEquals([['method1'], ['method2']], $t->calls);
  }
  
  function testTwoMethodsSameKindDifferentDescriptions() {
    $t = new TwoMethodsSameKindDifferentDescriptions();
    $p = new BDDProxy($t, 'given|when|then', 'and');
    
    $p->given('my description 1')
            ->given('my description 2');
    
    $this->assertEquals([['method1'], ['method2']], $t->calls);
  }
  
  /**
   * @expectedException \InvalidArgumentException
   * @expectedExceptionMessage Class diogoko\bdd\tests\TwoMethodsSameKindSameDescription has two methods (method1 and method2) with the same annotation: @given my description 1
   */
  function testTwoMethodsSameKindSameDescription() {
    $t = new TwoMethodsSameKindSameDescription();
    new BDDProxy($t, 'given|when|then', 'and');
  }
}