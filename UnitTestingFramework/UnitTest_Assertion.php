<?php
class THEELEGA_UnitTest_Assertion extends THEELEGA_UnitTest
{
    public $assertion = true;

    public function __construct($label, $assertion)
    {
        $this->label = $label;
        $this->assertion = $assertion;
        $this->run();
    }

    public function run()
    {
        if ($this->result)
        {
            return;
        }

        $res = new THEELEGA_UnitTestResult();
        $res->label = $this->label;
        $res->type = 'Assertion';
        if (!$this->assertion)
        {
            //Good place to put a breakpoint.
            $res->passed = false;
        }

        $this->result = $res;
    }
}
?>