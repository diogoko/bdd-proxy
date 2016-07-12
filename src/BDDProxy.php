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
    $description = $this->normalizeKey($description);
    
    $isContinuation = preg_match('/^' . $this->continuationPattern . '$/', $kind);
    if ($isContinuation) {
      if (!$this->currentStepKind) {
        throw new \LogicException("Continuation method ({$kind}) must be called "
            . "after step method ({$this->stepsPattern})");
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
          . "method annotated with: @{$this->currentStepKind} {$description}");
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
      $this->registerAllMethodBindings($method);
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
  private function registerAllMethodBindings($method) {
    $bindings = array_merge(
      $this->searchAnnotationBindings($method),
      $this->searchNameBindings($method)
    );

    foreach ($bindings as list($kind, $description)) {
      $this->registerMethodBinding($method, $kind, $description);
    }
  }
  
  private function searchAnnotationBindings($method) {
    $docblock = $method->getDocComment();
    
    $regexp = '/@(' . $this->stepsPattern . ')\s+(.+)\r?\n/';
    if (!preg_match_all($regexp, $docblock, $matches, PREG_SET_ORDER)) {
      return [];
    }

    $bindings = [];
    foreach ($matches as list(, $kind, $description)) {
      $bindings[] = [$kind, $description];
    }
    
    return $bindings;
  }
  
  private function searchNameBindings($method) {
    $regexp = '/^(' . $this->stepsPattern . ')_(.+)$/';
    if (!preg_match($regexp, $method->getName(), $matches)) {
      return [];
    }
    
    $kind = $matches[1];
    $description = str_replace('_', ' ', $matches[2]);
    
    return [[$kind, $description]];
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
  private function registerMethodBinding($method, $kind, $description) {
    $description = $this->normalizeKey($description);
    
    if (isset($this->stepMap[$kind][$description])) {
      $className = $method->getDeclaringClass()->getName();
      $previousMethodName = $this->stepMap[$kind][$description]->getName();
      if ($previousMethodName != $method->getName()) {
        $newMethod = $method->getName();
        throw new \InvalidArgumentException("Class {$className} has two methods "
            . "({$previousMethodName} and {$newMethod}) with the same annotation: "
            . "@{$kind} {$description}");
      }
    }
    
    $this->stepMap[$kind][$description] = $method;
  }
  
  private function normalizeKey($s) {
    $s = strtr($s, [
      chr(195) . chr(128) => 'A',
      chr(195) . chr(136) => 'E',
      chr(195) . chr(140) => 'I',
      chr(195) . chr(146) => 'O',
      chr(195) . chr(153) => 'U',
      chr(195) . chr(160) => 'a',
      chr(195) . chr(168) => 'e',
      chr(195) . chr(172) => 'i',
      chr(195) . chr(178) => 'o',
      chr(195) . chr(185) => 'u',
      chr(195) . chr(130) => 'A',
      chr(195) . chr(138) => 'E',
      chr(195) . chr(142) => 'I',
      chr(195) . chr(148) => 'O',
      chr(195) . chr(155) => 'U',
      chr(195) . chr(162) => 'a',
      chr(195) . chr(170) => 'e',
      chr(195) . chr(174) => 'i',
      chr(195) . chr(180) => 'o',
      chr(195) . chr(187) => 'u',
      chr(195) . chr(129) => 'A',
      chr(195) . chr(137) => 'E',
      chr(195) . chr(141) => 'I',
      chr(195) . chr(147) => 'O',
      chr(195) . chr(154) => 'U',
      chr(195) . chr(161) => 'a',
      chr(195) . chr(169) => 'e',
      chr(195) . chr(173) => 'i',
      chr(195) . chr(179) => 'o',
      chr(195) . chr(186) => 'u',
      chr(195) . chr(132) => 'A',
      chr(195) . chr(139) => 'E',
      chr(195) . chr(143) => 'I',
      chr(195) . chr(150) => 'O',
      chr(195) . chr(156) => 'U',
      chr(195) . chr(164) => 'a',
      chr(195) . chr(171) => 'e',
      chr(195) . chr(175) => 'i',
      chr(195) . chr(182) => 'o',
      chr(195) . chr(188) => 'u',
      chr(195) . chr(131) => 'A',
      chr(195) . chr(163) => 'a',
      chr(195) . chr(149) => 'O',
      chr(195) . chr(181) => 'o',
      chr(195) . chr(135) => 'C',
      chr(195) . chr(167) => 'c',
      chr(195) . chr(145) => 'N',
      chr(195) . chr(177) => 'n',
    ]);
    $s = preg_replace('/[^A-Za-z0-9_]/', ' ', $s);
    $s = trim($s);
    $s = preg_replace('/\s+/', ' ', $s);
    $s = strtolower($s);
    
    return $s;
  }
}
