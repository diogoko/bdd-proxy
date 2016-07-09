<?php

namespace diogoko\bdd\tests;


class OneMethodTwoKindsOneDescription {
  public $calls;
  
  /**
   * @given my description 1
   * @then my description 1
   */
  function method1() {
    $this->calls[] = ['method1'];
  }
}
