<?php
/**
 * I made this because I got frustrated with PHPUnit.
 * It's more primitive, but it works for me. No asynchronous operations, and such.
 * 
 * Just make a page where you run all your tests, and maybe another page where you run only one test.
 * Create THEELEGA_UnitTestSuite; Add a THEELEGA_UnitTestGroup to it;
 * To that, add actual tests - instances of THEELEGA_UnitTest_Assertion or THEELEGA_UnitTest_Callback.
 * These can be added using methods in THEELEGA_UnitTestGroup.
 * 
 * Then, display THEELEGA_UnitTestSuite->html().
 */

/**
 * Top-level grouping of tests. There should normally be just one per page.
 */
class THEELEGA_UnitTestSuite
{
    public $testGroups = [];

    /**
     * @param string $page_name A name that will be used to add the test suite to the admin sidebar.
     * @param callback $on_print_callback A function that is executed just before the page contents are printed.
     *    The intent is that the tests would be added in this function, and thus, only when we know that we need them.
     */
    public function __construct($page_name, $on_print_callback)
    {
        add_action('admin_menu', function() use ($page_name, $on_print_callback)
        {
            add_menu_page(
                $page_name,
                $page_name,
                'administrator',
                __CLASS__ . '_' .  sanitize_title($page_name),
                function() use ($on_print_callback)
                {
                    $on_print_callback($this);
                    $this->html();
                });
        }, 1000);
    }

    /**
     * Prints the page contents for a test suite.
     */
    public function html()
    {
        $fails = [];
        //First, run the tests.
        foreach ($this->testGroups as $tg)
        {
            $tg->runAll();
            $fc = $tg->failureCount();
            if ($fc > 0)
            {
                $fails[] = $fc;
            }
        }

        $groupsWithFailures = count($fails);
        $totalFailures = array_sum($fails);
        ?>

        <style>
            .THEELEGA_UnitTest_Tbl td
            {
                white-space: pre-wrap;
            }
            
            .THEELEGA_UnitTest_Error
            {
                background-color: rgb(255, 0, 0);
            }
        </style>
        
        <h2>Test suite results</h2>
        <p>There were <b><?=$totalFailures?></b> failed tests in <b><?=$groupsWithFailures?></b> test groups.</p>
        <hr/>
        <?php

        echo implode(PHP_EOL, $this->testGroups);
    }
}
?>