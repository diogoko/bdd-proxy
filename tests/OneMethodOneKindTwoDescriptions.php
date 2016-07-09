<?php

namespace diogoko\bdd\tests;


class OneMethodOneKindTwoDescriptions {
  public $calls;
  
  /**
   * @given my description 1
   * @given my description 2
   */
  function method1() {
    $this->calls[] = ['method1'];
  }
}
