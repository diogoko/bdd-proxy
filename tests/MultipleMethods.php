<?php

namespace diogoko\bdd\tests;

class MultipleMethods {
  public $calls;
  
  /**
   * @given my description 1
   */
  function given1() {
    $this->calls[] = ['given1'];
  }
  
  /**
   * @given my description 2
   */
  function given2() {
    $this->calls[] = ['given2'];
  }
  
  /**
   * @given my description 3
   */
  function given3() {
    $this->calls[] = ['given3'];
  }
  
  /**
   * @then my description 1
   */
  function then1() {
    $this->calls[] = ['then1'];
  }
  
  /**
   * @then my description 2
   */
  function then2() {
    $this->calls[] = ['then2'];
  }
  
  /**
   * @then my description 3
   */
  function then3() {
    $this->calls[] = ['then3'];
  }  
}
