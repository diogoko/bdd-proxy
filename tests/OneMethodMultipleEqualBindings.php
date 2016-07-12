<?php

namespace diogoko\bdd\tests;


class OneMethodMultipleEqualBindings {
  public $calls;
  
  /**
   * @given my description 1
   * @given my description 1
   */
  function given_my_description_1() {
    $this->calls[] = ['method1'];
  }
}
