<?php

namespace diogoko\bdd\tests;

class MethodCallWithUnderscores {
  public $calls;
  
  function given_my_description_1() {
    $this->calls[] = ['method1'];
  }
}
