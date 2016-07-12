<?php

namespace diogoko\bdd\tests;


class BindingAndCallNormalization {
  public $calls;
  
  function entao_minha_descricao_1() {
    $this->calls[] = ['method1'];
  }
  
  /**
   * @entao   mInha DESCRIÇÃO  2  
   */
  function method2() {
    $this->calls[] = ['method2'];
  }
}
