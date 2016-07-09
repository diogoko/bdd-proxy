<?php

namespace diogoko\bdd\tests;


class TwoMethodsDifferentKindsSameDescription {
  public $calls;
  
  /**
   * @given my description 1
   */
  function method1() {
    $this->calls[] = ['method1'];
  }
  
  /**
   * @then my description 1
   */
  function method2() {
    $this->calls[] = ['method2'];
  }
}
