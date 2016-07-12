<?php

namespace diogoko\bdd\tests;


class NameBinding {
  public $calls;
  
  function given_my_description_1() {
    $this->calls[] = ['method1'];
  }
}
