<?php

namespace diogoko\bdd;


class BDDProxy {
  private $target;
  
  private $stepMap;
  
  private $currentStepKind;
  
  private $expectedKinds;
  
  private $continuationKind;
  
  public function __construct($target, $expectedKinds, $continuationKind) {
    $this->target = $target;
    $this->expectedKinds = $expectedKinds;
    $this->continuationKind = $continuationKind;
    $this->buildStepMap();
  }
  
  public function __call($name, $arguments) {
    return $this->delegateCall($name, $arguments);
  }
  
  public function delegateCall($name, $arguments) {
    // TODO: validate args
    return $this->invokeStep($name, $arguments[0], array_slice($arguments, 1));
  }
  
  public function invokeStep($kind, $description, $arguments) {
    $isContinuation = (preg_match('/^' . $this->continuationKind . '$/', $kind));
    if ($isContinuation) {
      if (!$this->currentStepKind) {
        throw new \LogicException("Continuation method ({$kind}) must be called after step method ({$this->expectedKinds})");
      }
    } else {
      $this->currentStepKind = $kind;
    }
    
    if (isset($this->stepMap[$this->currentStepKind][$description])) {
      $method = $this->stepMap[$this->currentStepKind][$description];
      $method->invokeArgs($this->target, $arguments);
      return $this;
    } else {
      $className = get_class($this->target);
      throw new \BadMethodCallException("Class {$className} does not have any "
          . "method annotated with: @{$kind} {$description}");
    }
  }
  
  private function buildStepMap() {
    $this->stepMap = [];
    
    $objectInfo = new \ReflectionObject($this->target);
    foreach ($objectInfo->getMethods() as $method) {
      $this->registerMethodDescriptions($method);
    }
  }
  
  private function registerMethodDescriptions($method) {
    $docblock = $method->getDocComment();
    
    $regexp = '/@(' . $this->expectedKinds . '|' . $this->continuationKind . ')\s+(.+)\r?\n/';
    if (!preg_match_all($regexp, $docblock, $matches, PREG_SET_ORDER)) {
      return;
    }

    foreach ($matches as list(, $kind, $description)) {
      $this->registerMethod($method, $kind, $description);
    }
  }
  
  private function registerMethod($method, $kind, $description) {
    if (isset($this->stepMap[$kind][$description])) {
      $className = $method->getDeclaringClass()->getName();
      $previousMethod = $this->stepMap[$kind][$description]->getName();
      $newMethod = $method->getName();
      throw new \InvalidArgumentException("Class {$className} has two methods ({$previousMethod} and {$newMethod}) with the same annotation: @{$kind} {$description}"); // TODO: message
    }
    
    $this->stepMap[$kind][$description] = $method;
  }
}
