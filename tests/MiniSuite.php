<?php
add_action('theelega_startup', function()
{
    $missingdeps = theelega_missing_dependencies([]);
    if (!empty($missingdeps))
    {
        theelega_missing_dependencies_notification('TheElegantYou Common Functions Test', $missingdeps);
        return;
    }

    //A few tests to test the unit testing framework itself.
    new THEELEGA_UnitTestSuite('Small test suite', function($suite)
    {
        $tg = new THEELEGA_UnitTestGroup('Group 1');
        $tg->addAssertion('Big number', 245445);
        $tg->addAssertion('Null', null);
        $tg->addCallback('Success', function()
        {
            return 453;
        });
        $tg->addCallback('Failure', function()
        {
            throw new Exception('dkfdshg');
        });
    
        $suite->testGroups[] = $tg;
    });
});
?>