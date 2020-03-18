<?php
add_action('theelega_startup', function()
{
    new THEELEGA_UnitTestSuite('Full test suite', function($suite)
    {
        $tg = new THEELEGA_UnitTestGroup('Array Functions', 
        [
            new THEELEGA_UnitTest_Callback('theelega_arr_get', function()
            {
                $ret = true;
    
                $ret = $ret && theelega_arr_get([1, 4, 6], 0, 9) == 1;
                $ret = $ret && theelega_arr_get([1, 4, 6], 8, 9) == 9;
                $ret = $ret && theelega_arr_get([1, 4, [0,2]], [2, 1], 9) == 2;
                
                return $ret;
            }),
            new THEELEGA_UnitTest_Callback('theelega_arr_group_by', function()
            {
                $ret = true;
                $arr =
                [
                    ['a' => 1, 'b' => 2],
                    ['a' => 1, 'b' => 4],
                    ['a' => 3, 'b' => 4]
                ];

                $arr2 = theelega_arr_group_by($arr, 'a');
                $ret = $ret && ($arr2 == 
                    [
                        1 => [['a' => 1, 'b' => 2], ['a' => 1, 'b' => 4]],
                        3 => [['a' => 3, 'b' => 4]]
                    ]);

                $arr3 = theelega_arr_group_by($arr, 'a', 'b');
                $ret = $ret && ($arr3 == [1 => [2, 4], 3 => [4]]);

                $arr =
                [
                    ['b' => 1],
                    ['a' => 2]
                ];

                $arr4 = theelega_arr_group_by($arr, 'a', 'b');
                $ret = $ret && ($arr4 == [null => [1], 2 => [null]]);

                return $ret;
            }),
            new THEELEGA_UnitTest_Callback('theelega_remove_falsy', function()
            {
                $ret = true;
                $a = theelega_remove_falsy([3453, 5, 0, null, 3, [], '', -1]);
                $ret = $ret && $a == [0 => 3453, 1 => 5, 4 => 3, 7 => -1];
    
                return $ret;
            })
        ]);
        $suite->testGroups[] = $tg;
    });
});
?>