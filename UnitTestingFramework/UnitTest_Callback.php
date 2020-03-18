<?php
class THEELEGA_UnitTest_Callback extends THEELEGA_UnitTest
{
    public $callback = true;
    public $arguments = true;

    public function __construct($label, $callback, $arguments = [])
    {
        $this->label = $label;
        $this->callback = $callback;
        $this->arguments = $arguments;
    }

    public function run()
    {
        if ($this->result)
        {
            return;
        }

        $start = microtime(true);

        $res = new THEELEGA_UnitTestResult();
        $res->label = $this->label;
        $res->type = 'Callback';

        try
        {
            $res->passed = call_user_func_array($this->callback, $this->arguments);
        }
        catch (Throwable $t)
        {
            $res->passed = false;
            $res->exception = $t;
        }

        $res->runtime = microtime(true) - $start;
        $this->result = $res;
    }
}
?>

