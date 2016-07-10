<?php

namespace diogoko\bdd;


/**
 * Proxy for calling methods using BDD style.
 */
class BDDProxy {
  /**
   * The proxied object.
   * 
   * @var object
   */
  private $target;
  
  /**
   * An associative array that maps a step kind and description to a method.
   * 
   * @var array
   */
  private $stepMap;
  
  /**
   * The name of the current step kind, if any.
   * 
   * @var string
   */
  private $currentStepKind;
  
  /**
   * A regular expression fragment that matches all step kinds.
   * 
   * @var string
   */
  private $stepsPattern;
  
  /**
   * A regular expression fragment that matches continuation of steps.
   * 
   * @var string
   */
  private $continuationPattern;
  
  /**
   * Configures a new proxy.
   * 
   * @param object $target The proxied object
   * @param string $stepsPattern A regular expression fragment that matches all 
   *                             step kinds
   * @param string $continuationPattern A regular expression fragment that 
   *                                    matches continuations of steps
   * 
   * @throws \InvalidArgumentException If two methods are registered with the 
   *                                   same kind and description
   */
  public function __construct($target, $stepsPattern, $continuationPattern) {
    $this->target = $target;
    $this->stepsPattern = $stepsPattern;
    $this->continuationPattern = $continuationPattern;
    $this->buildStepMap();
  }
  
  /**
   * @copydoc ::delegateCall
   */
  public function __call($name, $arguments) {
    return $this->delegateCall($name, $arguments);
  }
  
  /**
   * Calls a method in the target object using BDD style.
   * 
   * @param string $name The method name
   * @param array $arguments All the method arguments
   * @return BDDProxy This object
   */
  public function delegateCall($name, $arguments) {
    // TODO: validate args
    return $this->invokeStep($name, $arguments[0], array_slice($arguments, 1));
  }
  
  /**
   * Calls the method corresponding to a given step kind and description.
   * 
   * @param string $kind The step kind
   * @param string $description The step description
   * @param array $arguments The step arguments
   * @return \diogoko\bdd\BDDProxy This object
   * @throws \LogicException If a continuation is called before a step method
   * @throws \BadMethodCallException If no method is found with the given step 
   *                                 kind and description
   */
  private function invokeStep($kind, $description, $arguments) {
    $isContinuation = (preg_match('/^' . $this->continuationPattern . '$/', $kind));
    if ($isContinuation) {
      if (!$this->currentStepKind) {
        throw new \LogicException("Continuation method ({$kind}) must be called after step method ({$this->stepsPattern})");
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
  
  /**
   * Builds the map from step kind and description to method.
   * 
   * @throws \InvalidArgumentException If two methods are registered with the 
   *                                   same kind and description
   */
  private function buildStepMap() {
    $this->stepMap = [];
    
    $objectInfo = new \ReflectionObject($this->target);
    foreach ($objectInfo->getMethods() as $method) {
      $this->registerMethodDescriptions($method);
    }
  }
  
  /**
   * Searches all method descriptions in annotations that match the steps 
   * pattern.
   * 
   * @param \ReflectionMethod $method The method whose descriptions will be 
   *                                  registered
   * @throws \InvalidArgumentException If two methods are registered with the 
   *                                   same kind and description
   */
  private function registerMethodDescriptions($method) {
    $docblock = $method->getDocComment();
    
    $regexp = '/@(' . $this->stepsPattern . ')\s+(.+)\r?\n/';
    if (!preg_match_all($regexp, $docblock, $matches, PREG_SET_ORDER)) {
      return;
    }

    foreach ($matches as list(, $kind, $description)) {
      $this->registerMethod($method, $kind, $description);
    }
  }
  
  /**
   * Registers a method to the given step kind and description.
   * 
   * @param \ReflectionMethod $method The registered method
   * @param string $kind The step kind
   * @param string $description The step description
   * @throws \InvalidArgumentException If two methods are registered with the 
   *                                   same kind and description
   */
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
