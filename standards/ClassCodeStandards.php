<?php declare(strict_types=1);
/**
 * PHPCore - Class Code Standards
 *
 * @author    Everett Myers <Me@EverettMyers.com>
 * @copyright Copyright (c) 2022, PHPCore
 */

namespace PHPCoreStandards;

// -------------------------------------------------------------------------------------------------

/**
 * Class Code Standards
 *
 * This is a class that contains some example code to explain the PHPCore code
 * standards.
 */
#[Test('tests/ClassCodeStandardsTest.php')]
#[Documentation('docs/class_code_standards.rst')]
class ClassCodeStandards
{
    // EXAMPLE: use SomeTrait, AnotherTrait;

    /**
     * Some public string
     *
     * @const string
     */
    public const SOME_PUBLIC_CONSTANT = 'some public string';

    /**
     * Some protected string
     *
     * @const string
     */
    protected const SOME_PROTECTED_CONSTANT = 'some protected string';

    /**
     * Some private string
     *
     * @const string
     */
    private const SOME_PRIVATE_CONSTANT = 'some private string';

    /**
     * Some final public string
     *
     * @const string
     */
    final public const SOME_FINAL_PUBLIC_CONSTANT = 'some final public string';

    /**
     * Some final protected string
     *
     * @const string
     */
    final protected const SOME_FINAL_PROTECTED_CONSTANT = 'some final protected string';

    /**
     * Constant flags
     *
     * @flag integer
     */
    const SOME_FLAG      = 1; // NOTE: flags are exempt from the alphabetical requirement
    const ANOTHER_FLAG   = 2; //       They should be grouped together with one doc block
    const ADDITINAL_FLAG = 4;

    // ---------------------------------------------------------------------

    /**
     * Public static protery name
     *
     * @prop string
     */
    public static string $PublicStaticProteryName = 'default value';

    /**
     * Protected static protery name
     *
     * @prop string
     */
    protected static string $ProtectedStaticProteryName = 'default value';

    /**
     * Private static protery name
     *
     * @prop string
     */
    private static string $PrivateStaticProteryName = 'default value';

    // ---------------------------------------------------------------------

    /**
     * Protected protery name
     *
     * @prop string
     */
    public string $PublicProteryName = 'default value';

    /**
     * Protected protery name
     *
     * @prop string
     */
    protected string $ProtectedProteryName = 'default value';

    /**
     * Protected protery name
     *
     * @prop string
     */
    private string $PrivateProteryName = 'default value';

    // ---------------------------------------------------------------------

    // NOTE: For abstract classes only

    /**
     * {ABSTRACT-STATIC-METHOD-NAME}
     *
     * {DESCRIPTION} // NOTE: Optional
     *
     * @param mixed $argument {ARGUMENT-DESCRIPTION} // NOTE: Optional
     * @return mixed {RETURN-DESCRIPTION}
     */
    // EXAMPLE: abstract public static function abstractStaticPublicMethod(mixed $argument = null): mixed; 
    // EXAMPLE: abstract protected static function abstractStaticProtectedMethod(mixed $argument = null): mixed; 

    /**
     * {ABSTRACT-INSTANCE-METHOD-NAME}
     *
     * {DESCRIPTION} // NOTE: Optional
     *
     * @param mixed $argument {ARGUMENT-DESCRIPTION} // NOTE: Optional
     * @return mixed {RETURN-DESCRIPTION}
     */
    // EXAMPLE: abstract public function abstractPublicMethod(mixed $argument = null): mixed; 
    // EXAMPLE: abstract protected function abstractProtectedMethod(mixed $argument = null): mixed; 

    // ---------------------------------------------------------------------

    /**
     * Static public method
     *
     * Method description
     *
     * @param string $argument Argument description
     * @return void
     */
    public static function staticPublicMethod(string $argument): void
    {
    }

    /**
     * Static protected method
     *
     * Method description
     *
     * @param string $argument Argument description
     * @return void
     */
    protected static function staticProtectedMethod(string $argument): void
    {
    }

    /**
     * Static private method
     *
     * Method description
     *
     * @param string $argument Argument description
     * @return void
     */
    private static function staticPrivateMethod(string $argument): void
    {
    }

    /**
     * Final Static Public Method
     *
     * Method description
     *
     * @param string $argument Argument description
     * @return void
     */
    final public static function finalStaticPublicMethod(string $argument): void
    {
    }

    /**
     * Final Static Protected Method
     *
     * Method description
     *
     * @param string $argument Argument description
     * @return void
     */
    final protected static function finalStaticProtectedMethod(string $argument): void
    {
    }

    // ---------------------------------------------------------------------

    /**
     * Magic Method
     *
     * Method description
     *
     * @param string $argument Argument description
     * @return void
     */
    public function __construct(string $argument)
    {
    }

    /**
     * Magic Method
     *
     * Method description
     *
     * @return void
     */
    public function __destruct()
    {
    }

    /**
     * Magic Method
     *
     * Method description
     *
     * @param string $argument Argument description
     * @return void
     */
    public static function __initialize(string $argument): void // NOTE: ALWAYS return void
    {
    }

    /**
     * Magic Method
     *
     * Method description
     *
     * @param string $argument Argument description
     * @return void
     */
    public static function __terminate(string $argument): void // NOTE: ALWAYS return void
    {
    }

    // ---------------------------------------------------------------------

    /**
     * Public Method
     *
     * Method description
     *
     * @param string $argument Argument description
     * @return void
     */
    public function instancePublicMethod(string $argument): void
    {
    }

    /**
     * Protected Method
     *
     * Method description
     *
     * @param string $argument Argument description
     * @return void
     */
    protected function instanceProtectedMethod(string $argument): void
    {
    }

    /**
     * Private Method
     *
     * Method description
     *
     * @param string $argument Argument description
     * @return void
     */
    private function instancePrivateMethod(string $argument): void
    {
    }

    /**
     * Final Public Method
     *
     * Method description
     *
     * @param string $argument Argument description
     * @return void
     */
    final public function finalInstancePublicMethod(string $argument): void
    {
    }

    /**
     * Final Protected Method
     *
     * Method description
     *
     * @param string $argument Argument description
     * @return void
     */
    final protected function finalInstanceProtectedMethod(string $argument): void
    {
    }
}

// EOF /////////////////////////////////////////////////////////////////////////////////////////////
