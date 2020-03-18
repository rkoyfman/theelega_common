<?php
/**
 * Abstract superclass for an item in THEELEGA_UnitTestGroup.
 */
abstract class THEELEGA_UnitTest
{
    protected $label = '';
    protected $result = null;

    public abstract function run();
    
    /**
     * Represents a row in the table printed by THEELEGA_UnitTest.
     */
    public function resultRowString()
    {
        $r = $this->getResult();

        $row_class = ($r->passed ? '' : 'THEELEGA_UnitTest_Error');
        return "<tr class='$row_class'>
            <td>" . esc_html($r->label) . "</td>
            <td>" . esc_html($r->type) . "</td>
            <td>" . ($r->passed ? 'true' : 'false') . "</td>
            <td>" . esc_html($r->runtime) . "</td>
            <td>" . esc_html($r->exception) . "</td>
        </tr>";
    }


    /**
     * Gets result, but throws an exception if the test wasn't run.
     * I considered executing run() here, but I want the tests to be run in just one place,
     * in the order in which they were added.
     * 
     * @return THEELEGA_UnitTestResult
     */
    public function getResult()
    {
        $r = $this->result;

        if (!$r)
        {
            throw new Exception("Tried to get test result before test was run.");
        }
        return $r;
    }
}
?>
