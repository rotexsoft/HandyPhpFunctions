<?php
namespace Rotexsoft\HandyPhpFunctions;

/**
 * 
 * This function generates an array with count($arr) <= $max_array_length.
 * It supports sub-arrays up to $depth levels deep and each sub-array can have
 * between 2 and $max_array_length elements. The generated array is not guaranteed 
 * to be symmetrical, some indices may have deeper level of nesting than others.
 * 
 * Each non-array value in this array is a random string.
 * 
 * For example, the array below has a depth of 1. (only one sub-array)
 *          [
 *              'group' => ['column_name_1', 'column_name_2']
 *          ]
 * 
 * For example, the array below has a depth of 2 because of $arr['item2']
 *  ($arr['a'] is 0 level deep. Ie. it contains a non-array value)
 *  ($arr['group'] contains only one array ie. $arr['group'] is only 1 level deep)
 *  ($arr['item2'] contains an array within an array; ie. $arr['item2'] is 2 levels deep)
 *          $arr = [
 *              'a' => 'yay',
 *              'group' => ['column_name_1', 'column_name_2'],
 *              'item2' => [
 *                  'group2' => ['column_name_1', 'column_name_2']
 *              ]
 *          ]
 * 
 * 
 * @param array $arr an empty array to hold the array to be generated
 * @param type $depth the level or depth of sub arrays allowed
 * @param type $max_array_length the overall length of the array to be generated.
 *                               sub-arrays can have a length between 2 and 
 *                               $max_array_length 
 * 
 * @author Rotimi Adegbamigbe
 * @copyright (c) 2015, Rotimi Adegbamigbe ( http://rotimis-corner.com/ )
 * 
 */
function create_n_dimensional_array_with_random_data(array &$arr, $depth, $max_array_length) {

    static $list_of_chars;
    static $size_of_list_of_chars;

    if(!$list_of_chars) {

        $all_chars = array_merge(range('a', 'z'), range('A', 'Z'), range(0, 9));
        $list_of_chars = array_combine($all_chars, $all_chars);
        $size_of_list_of_chars = count($list_of_chars) - 1;
    }

    $i = 0;

    while( $i < $max_array_length) {

        //use int key
        $key = $i;

        if( rand(3, $size_of_list_of_chars) % 2 === 0 ) {

           //use string key
           $key = implode ('', array_rand($list_of_chars , rand(2, $size_of_list_of_chars) ) );
        }

        if( rand(3, $size_of_list_of_chars) % 2 === 0 || $depth === 0) {

           //set a string value
           $arr[$key] = implode ('', array_rand($list_of_chars , rand(3, 10) ) );

        } else {

            //set an array value and recurse
            $arr[$key] = array();
            create_n_dimensional_array_with_random_data($arr[$key], ($depth -1), rand(2, $max_array_length) );
        }

        $i++;
    }
}

/**
 * 
 * This function recursively copies a multi-dimensional array. 
 * It is useful for situations where you want to re-order numeric keys at each 
 * level within the array. It will always preserver non-numeric keys, but when
 * you specify that numeric keys should be reordered, it will re-number numeric 
 * keys at each level starting from the outermost keys of the main array to the
 * keys at each level of sub-arrays.
 * 
 * This function should ONLY be used if you want to re-order numeric keys; ie.
 * recursively_copy_array($array_from, $array_to, true).
 * 
 * If you are not re-ordering numeric keys just use the assignment operator to
 * perform the copy operation; since recursively_copy_array($array_from, $array_to, false) 
 * does the same thing as ($array_from = $array_to) and ($array_from = $array_to)
 * is way faster and more memory efficient since it's native php and there is 
 * no recursion overhead).
 * 
 * For example
 * 
 * [
 *  6=>[ 'col'=>'column_name_1', 'operator'=>'>', 'val'=>58 ],
 *  1=>[ 'col'=>'column_name_2', 'operator'=>'>', 'val'=>58 ],
 *  'OR'=> [
 * 	    9=>[ 'col'=>'column_name_1', 'operator'=>'<', 'val'=>58 ],
 * 	    0=>[ 'col'=>'column_name_2', 'operator'=>'<', 'val'=>58 ]
 * 	],
 *  4=>[ 'col'=>'column_name_3', 'operator'=>'>=', 'val'=>58 ],
 *  'OR#2'=> [
 * 	    44=>[ 'col'=>'column_name_4', 'operator'=>'=', 'val'=>58 ],
 * 	    [ 'col'=>'column_name_5', 'operator'=>'=', 'val'=>58 ],
 * 	]
 * ]
 * 
 * will be copied to become like below if $reorder_numeric_keys === true
 * 
 * [
 *  0=>[ 'col'=>'column_name_1', 'operator'=>'>', 'val'=>58 ],
 *  1=>[ 'col'=>'column_name_2', 'operator'=>'>', 'val'=>58 ],
 *  'OR'=> [
 * 	    0=>[ 'col'=>'column_name_1', 'operator'=>'<', 'val'=>58 ],
 * 	    1=>[ 'col'=>'column_name_2', 'operator'=>'<', 'val'=>58 ]
 * 	],
 *  2=>[ 'col'=>'column_name_3', 'operator'=>'>=', 'val'=>58 ],
 *  'OR#2'=> [
 * 	    0=>[ 'col'=>'column_name_4', 'operator'=>'=', 'val'=>58 ],
 * 	    1=>[ 'col'=>'column_name_5', 'operator'=>'=', 'val'=>58 ],
 * 	]
 * ]
 * 
 * @param array $array_from array whose entries are to be copied
 * @param array $array_to empty array to hold the copy to be made
 * @param bool $reorder_numeric_keys true if numeric keys should be reordered at each level,
 *                                   else false to preserve keys as they are in $array_from.
 *                                   Non-numeric keys are always preserved.
 * 
 * @author Rotimi Adegbamigbe
 * @copyright (c) 2015, Rotimi Adegbamigbe ( http://rotimis-corner.com/ )
 * 
 */
function recursively_copy_array(array &$array_from, array &$array_to, $reorder_numeric_keys=false) {

    $numeric_index = 0;

    foreach($array_from as $key=>$value) {

        if( is_numeric($key) && $reorder_numeric_keys) {

            $key = $numeric_index++;
        }

        if( is_array($value)) {

            $array_to[$key] = array();
            recursively_copy_array($value, $array_to[$key], $reorder_numeric_keys);

        } else {

            $array_to[$key] = $value;
        }
    }
}

function array_get(array &$array, $key, $default_value=null) {
	
    if( array_key_exists($key, $array) ) {

        return $array[$key];

    } else {

        return $default_value;
    }
}

/**
 * 
 *  based on http://stackoverflow.com/questions/1019076/how-to-search-by-key-value-in-a-multidimensional-array-in-php
 * 
 */
function search_r(&$array, $key, $value, &$results) {
	
    if (!is_array($array)) {
        return;
    }

    if ( array_key_exists($key, $array) && $array[$key] === $value) {
    
        $results[] = $array;
    }

    foreach ($array as $subarray) {
    
        search_r($subarray, $key, $value, $results);
    }
}

function search_2d(&$array, $key, $value, &$results) {

    foreach ($array as &$avalue) {
    
        if ( array_key_exists($key, $avalue) && $avalue[$key] === $value) {

            $results[] = $avalue;
        }
    }
}

/*
$new = array();
recursively_copy_array($array['where'], $new);
print_r($new);
var_dump($array['where'] === $new);

$new = array();
recursively_copy_array($array['where'], $new, true);
print_r($new);
var_dump($array['where'] === $new);
*/

//$test =array();
//create_n_dimensional_array_with_random_data($test, 3, 4);
//print_r($test);

/*
ini_set('memory_limit', '1536M');
$test_array = array();
create_n_dimensional_array_with_random_data($test_array, 3, 199);

//print_r($test_array);
//echo "\n---------------------------------------------\n";

$start = microtime(true);
$mem = memory_get_usage();

$new = array();
recursively_copy_array($test_array, $new);
//print_r($new);

$start2 = microtime(true);
$mem2 = memory_get_usage();

echo "Duration: " . ($start2 - $start) . " secs\n";
echo "Memory: " . (($mem2 - $mem) / 1024 / 1024) . " MB\n";

var_dump($test_array === $new);

echo "\n---------------------------------------------\n";

$new2 = $test_array;//manual copy
var_dump($new2 === $new);

array_unshift($new2, 'yipee');

echo "Duration: " . (microtime(true) - $start2) . " secs\n";
echo "Memory: " . ((memory_get_usage() - $mem2) / 1024 / 1024) . " MB\n";
*/
?>
