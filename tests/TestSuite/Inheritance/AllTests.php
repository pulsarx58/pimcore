<?php
//require_once 'PHPUnit/Framework.php';



class TestSuite_Inheritance_AllTests extends Test_SuiteBase
{
    public static function suite()
    {
        $suite = new TestSuite_Inheritance_AllTests('Inheritance');

        $tests = ['TestSuite_Inheritance_GeneralTest' , 'TestSuite_Inheritance_LocalizedFieldTest'];

        $success = shuffle($tests);
        print("Created the following execution order:\n");

        foreach ($tests as $test) {
            print("    - " . $test . "\n");
            $suite->addTestSuite($test);
        }

        return $suite;
    }

    protected function setUp()
    {
        parent::setUp();

        if (!Object_Class::getByName("inheritance")) {
            echo "Create class ...\n";
            $json = file_get_contents(TESTS_PATH . "/resources/objects/inheritance.json");

            $class = new Object_Class();
            $class->setName("inheritance");
            $class->setUserOwner(1);

            Object_Class_Service::importClassDefinitionFromJson($class, $json);
        }
    }
}
