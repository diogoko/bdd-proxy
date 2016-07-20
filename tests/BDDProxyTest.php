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
  
  function testNameBinding() {
    $t = new NameBinding();
    $p = new BDDProxy($t, 'given|when|then', 'and');
    
    $p->given('my description 1');
    
    $this->assertEquals([['method1']], $t->calls);
  }
  
  function testOneMethodMultipleEqualBindings() {
    $t = new OneMethodMultipleEqualBindings();
    $p = new BDDProxy($t, 'given|when|then', 'and');
    
    $p->given('my description 1');
    
    $this->assertEquals([['method1']], $t->calls);
  }
  
  function testBindingAndCallNormalization() {
    $t = new BindingAndCallNormalization();
    $p = new BDDProxy($t, 'dado|quando|entao', 'e');
    
    $p->entao(' minha  descrição 1 ')
            ->e('minha descricao 2');
    
    $this->assertEquals([['method1'], ['method2']], $t->calls);
  }
  
  function testExceptionFilter() {
    $t = new ExceptionFilter();
    $p = new BDDProxy($t, 'dado|quando|entao', 'e');
    $p->setExceptionFilter(function($exception, $kind, $description, $arguments) {
      return new \Exception("custom message: $kind $description", count($arguments), $exception);
    });
    
    try {
      $p->entao('minha descrição 1', 10, 20);
      $this->fail('Should have thrown exception');
    } catch (\Exception $e) {
      $this->assertEquals('custom message: entao minha descrição 1', $e->getMessage());
      $this->assertEquals(2, $e->getCode());
      $this->assertEquals('original message', $e->getPrevious()->getMessage());
    }
  }
  
  function testMethodCallWithUnderscores() {
    $t = new MethodCallWithUnderscores();
    $p = new BDDProxy($t, 'given|when|then', 'and');
    
    $p->given('my DESCRIPTION_1');
    
    $this->assertEquals([['method1']], $t->calls);
  }
}
