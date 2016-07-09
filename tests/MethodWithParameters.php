<?php

namespace diogoko\bdd\tests;

class MethodWithParameters {
  public $calls;
  
  /**
   * @given my description 1
   */
  function method1($a, $b, $c) {
    $this->calls[] = ['method1', $a, $b, $c];
  }
}
