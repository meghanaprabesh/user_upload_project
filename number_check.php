<?php
 $var = range(1, 100); ?>
<?php foreach ($var as &$number) {
if($number % 5 == 0 && $number % 3 == 0) {
    echo "foobar,";
}
elseif($number % 3 == 0)  {
    echo "foo,";
} elseif ($number % 5 == 0) {
    echo "bar,";
} 
else{
echo "$number,";
}
}
?>