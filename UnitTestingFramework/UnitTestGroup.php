<?php
/**
 * Groups together a group of tests. If converted to a string, prints a table of the results.
 */
class THEELEGA_UnitTestGroup
{
    /** @property string $label A label for the test group.*/
    public $label = '';
    /** @property THEELEGA_UnitTest[] $tests */
    private $tests = [];

    public function __construct($label, $tests = [])
    {
        $this->label = $label;
        $this->tests = $tests;
    }

    public function addAssertion($label, $assertion)
    {
        $this->tests[] = new THEELEGA_UnitTest_Assertion($label, $assertion);
    }

    public function addCallback($label, $callback, $arguments = [])
    {
        $this->tests[] = new THEELEGA_UnitTest_Callback($label, $callback, $arguments);
    }

    public function __toString()
    {
        $ret = [];
        $ret[] = "<h2>Test Group " . esc_html($this->label) . "<h2>";
        if (empty($this->tests))
        {
            $ret[] = "<h3 class='THEELEGA_UnitTest_Error'>This test group has no tests in it.<h3>";
        }
        $ret[] = "<table border=1 class='THEELEGA_UnitTest_Tbl'>";
        
        $ret[] = 
        "<tr>
            <th>Label</th>
            <th>Type</th>
            <th>Passed</th>
            <th>Runtime</th>
            <th>Exception</th>
        </tr>";

        foreach ($this->tests as $t)
        {
            $ret[] = $t->resultRowString();
        }
        $ret[] = "</table>";

        return implode(PHP_EOL, $ret);
    }

    /**
     * Return number of failed tests. If there are no tests, that is also an error.
     */
    public function failureCount()
    {
        if (empty($this->tests))
        {
            return 1;
        }

        /** @param THEELEGA_UnitTest $t */
        $ints = array_map(function($t)
        {
            $r = $t->getResult();
            return $r->passed ? 0 : 1;
        }, $this->tests);

        return array_sum($ints);
    }

    /**
     * Run all tests. Must happen before __toString() or failureCount()
     */
    public function runAll()
    {
        foreach ($this->tests as $t)
        {
            $t->run();
        }
    }
}
?>