<?php

namespace diogoko\bdd\tests;

class ExceptionFilter {
  function entao_minha_descricao_1($a, $b) {
    throw new \Exception('original message');
  }
}
