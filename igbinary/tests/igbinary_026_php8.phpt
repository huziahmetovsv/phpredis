--TEST--
Cyclic array test
--INI--
report_memleaks=0
--SKIPIF--
<?php
if (!extension_loaded('igbinary')) {
	echo "skip no igbinary\n";
}
if (PHP_MAJOR_VERSION < 8) {
	echo "skip requires php 8.0+\n";
}
--FILE--
<?php

function test($type, $variable, $test) {
	$serialized = igbinary_serialize($variable);
	$unserialized = igbinary_unserialize($serialized);

	echo $type, "\n";
	echo substr(bin2hex($serialized), 8), "\n";
	echo !$test || $unserialized == $variable ? 'OK' : 'ERROR', "\n";
}

$a = array(
	'a' => array(
		'b' => 'c',
		'd' => 'e'
	),
);

$a['f'] = &$a;

test('array', $a, false);

$a = array("foo" => &$b);
$b = array(1, 2, $a);

$exp = $a;
$act = igbinary_unserialize(igbinary_serialize($a));

ob_start();
var_dump($exp);
$dump_exp = ob_get_clean();
ob_start();
var_dump($act);
$dump_act = ob_get_clean();

if ($dump_act !== $dump_exp) {
	echo "Var dump differs:\nActual:\n", $dump_act, "\nExpected:\n", $dump_exp, "\n";
} else {
	echo "Var dump OK\n";
}

$act['foo'][1] = 'test value';
$exp['foo'][1] = 'test value';
if ($act['foo'][1] !== $act['foo'][2]['foo'][1]) {
	echo "Recursive elements differ:\n";
	echo "Actual\n";
	var_dump($act);
	var_dump($act['foo']);
	echo "Expected\n";
	var_dump($exp);
	var_dump($exp['foo']);
}

?>
--EXPECT--
array
140211016114021101621101631101641101651101662514020e0001010e05250102
OK
Var dump differs:
Actual:
array(1) {
  ["foo"]=>
  &array(3) {
    [0]=>
    int(1)
    [1]=>
    int(2)
    [2]=>
    array(1) {
      ["foo"]=>
      *RECURSION*
    }
  }
}

Expected:
array(1) {
  ["foo"]=>
  &array(3) {
    [0]=>
    int(1)
    [1]=>
    int(2)
    [2]=>
    *RECURSION*
  }
}
