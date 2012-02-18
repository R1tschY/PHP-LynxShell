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
      return 'keine Fehler in '.$this->funcname . "!\n";
    }
    
    $result = 'Fehler in '.$this->funcname .': '.count($this->errors).'/'.$this->n.PHP_EOL;
    foreach ($this->errors as $error) {
      $result .= "  ".$error['msg'].': IST: '.$error['ist'].' | SOLL: '.$error['soll'].PHP_EOL;
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
    'int als String');
  $test->test(
    filter_arrayvalue_int($a, 'float'),
    FALSE,
    'float als String');
  $test->test(
    filter_arrayvalue_int($a, 'str'),
    FALSE,
    'String');
  $test->test(
    filter_arrayvalue_int($a, 'big'),
    FALSE,
    'zu groß');
  $test->test(
    filter_arrayvalue_int($a, 'no'),
    FALSE,
    'nicht vorhanden');
    
  Answer::addOutput('o', $test->get_strresult());
}


test_filter_arrayvalue_int();
  
Answer::send();
return ;

?>