<?php

namespace diogoko\bdd\tests;


class TwoMethodsSameKindSameDescription {
  public $calls;
  
  /**
   * @given my description 1
   */
  function method1() {
    $this->calls[] = ['method1'];
  }
  
  /**
   * @given my description 1
   */
  function method2() {
    $this->calls[] = ['method2'];
  }
}
