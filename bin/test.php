<?php

class Test {
  private $n;
  private $funcname;
  private $errors;
  
  public function __construct($funcname) {
    $this->n = 0;
    $this->errors = array();
    $this->funcname = $funcname;
  }
  
  public function test($ist, $soll, $msg) {
    $this->n++;
    
    if ($ist !== $soll) {
      $this->errors[] = array('ist'=>var_dup($ist), 'soll'=>var_dup($soll), 'msg'=>$msg);
    }
  }
  
  public function get_strresult() {
    if ($this->n == 0 || count($this->errors) == 0) {
      return 'no errors in '.$this->funcname . "!\n";
    }
    
    $result = 'errors in '.$this->funcname .': '.count($this->errors).'/'.$this->n.PHP_EOL;
    foreach ($this->errors as $error) {
      $result .= "  ".$error['msg'].': IS: '.$error['ist'].' | EXPECTED: '.$error['soll'].PHP_EOL;
    }
    return $result;
  }
}

function test_filter_arrayvalue_int() {
  $test = new Test('filter_arrayvalue_int');
  
  $a = array(
    'int'=>"23",
    'float'=>"2.3",
    'str'=>"abc",
    'big'=>"1e90"
  );
  
  $test->test(
    filter_arrayvalue_int($a, 'int'),
    23,
    'int as string');
  $test->test(
    filter_arrayvalue_int($a, 'float'),
    FALSE,
    'float as string');
  $test->test(
    filter_arrayvalue_int($a, 'str'),
    FALSE,
    'string');
  $test->test(
    filter_arrayvalue_int($a, 'big'),
    FALSE,
    'to big');
  $test->test(
    filter_arrayvalue_int($a, 'no'),
    FALSE,
    'not existing');
    
  Answer::addOutput('o', $test->get_strresult());
}

function test_str_find_suffix() {
  $test = new Test('str_find_suffix');
  
  $test->test(
    str_find_suffix('alles', 'alles'),
    'alles',
    'complete string');
  $test->test(
    str_find_suffix('fast alles', 'alles'),
    'alles',
    'part of string');
  $test->test(
    str_find_suffix('fast alles.', 'alles!'),
    FALSE,
    'nothing');
    
  Answer::addOutput('o', $test->get_strresult());
}

function test_is_prefix() {
  $test = new Test('is_prefix');
  
  $test->test(
    is_prefix('alles', 'alles'),
    TRUE,
    'complete string');
  $test->test(
    is_prefix('fast alles', 'fast'),
    TRUE,
    'part of string');
  $test->test(
    is_prefix('fast alles.', 'alles'),
    FALSE,
    'nothing');
  $test->test(
    is_prefix('fast alles.', ''),
    TRUE,
    'empty prefix');
    
  Answer::addOutput('o', $test->get_strresult());
}

test_filter_arrayvalue_int();
test_str_find_suffix();
test_is_prefix();
  
?>