<?php declare(strict_types=1);
/**
 * PHPCore - Code Check
 *
 * @package   PHPCore
 * @author    Everett Myers <Everett@MyersNetwork.com>
 * @copyright 2024-2025 Everett Myers
 * @license   MIT License
 * @link      https://PHPCore.org
 * @version   2024-12-31
 */

namespace PHPCore;

use \ReflectionClass;

// -----------------------------------------------------------------------------

/**
 * Code Check Class
 *
 * The Code Check class is used to check and validate code to the coding
 * standards.
 */
final class CodeCheck
{
    /**
     * Long Options
     *
     * @const array
     */
    const LONG_OPTIONS = [
      'format::',
      'help::',
      'syntax::',
      'version::',
    ];

    /**
     * Magic methods
     *
     * @const array
     */
    const MAGIC_METHODS = [
        '__construct',
        '__destruct',
        '__call',
        '__callStatic',
        '__get',
        '__set',
        '__isset',
        '__unset',
        '__sleep',
        '__wakeup',
        '__serialize',
        '__unserialize',
        '__toString',
        '__invoke',
        '__set_state',
        '__clone',
        '__debugInfo',
    ];

    /**
     * Short Options
     *
     * @const string
     */
    const SHORT_OPTIONS = 'p:f:c:e:hv';

    /**
     * Indent spaces
     *
     * @const integer
     */
    const INDENT_SAPCES = 4;

    /**
     * Code Checker Version
     *
     * @const string
     */
    const VERSION = '1.0';

    // ---------------------------------------------------------------------

    /**
     * Class doc block
     *
     * @property object
     */
    private object $ClassDocBlock;

    /**
     * Class name
     *
     * @property string
     */
    private string $ClassName;

    /**
     * Class reflection
     *
     * @property object
     */
    private object $ClassReflection;

    /**
     * File path
     *
     * @property string
     */
    private string $FilePath;

    /**
     * File contents
     *
     * @property string
     */
    private string $FileContents;

    /**
     * Options
     *
     * @property array
     */
    private mixed $Options;

    // ---------------------------------------------------------------------

    /**
     * Display Header
     *
     * This method is used to display the CLI help.
     *
     * @return void
     *
    public static function displayHeader(): void
    {
        $version = "\e[0;36;40m" . CodeCheck::VERSION . "\e[0m"; // cyan
        $copyright = '2022-' . date('Y');

        echo <<<CLI_STRING
PHPCore Code Check $version
Copyright (c) $copyright Everett Myers

CLI_STRING;
    }
*/

    /**
     * Display Help
     *
     * This method is used to display the CLI help.
     *
     * @return void
     *
    public static function displayHelp(): void
    {
        $g = "\e[0;32;40m";
        $s = "\e[0;33;40m";
        $c = "\e[0;36;40m";
        $e = "\e[0m";

        self::displayHeader();

        echo <<<CLI_STRING
{$s}Usage:{$e}
  codecheck [options]

{$s}Targeted:{$e}
  {$g}-p{$e} {$c}<path>{$e}      Path to check all files
  {$g}-f{$e} {$c}<file>{$e}      File path to check
  {$g}-c{$e} {$c}<class>{$e}     Class to check against code standards
  {$g}-e{$e} {$c}<file>{$e}      Exclude file (used with path)

{$s}Specific Checks:{$e}
  {$g}--syntax{$e}       Syntax check only
  {$g}--format{$e}       Formatting only

{$s}Miscellaneous:{$e}
  {$g}-h|--help{$e}      Display this help and exit
  {$g}-v|--version{$e}   Display version and exit


CLI_STRING;
    }
*/

    // ---------------------------------------------------------------------

    /**
     * Constructor
     *
     * @param array $parameters Parameters
     * @return void
     */
    public function __construct(array $parameters)
    {
        if ( ! isset($parameters['file_path'])) {
            $this->outputError("Missing `file_path` from parameters.");
        }
        $this->FilePath = realpath($parameters['file_path']);

        if ( ! is_readable($this->FilePath)) {
            $this->outputError("File {$this->FilePath} is not readable");
        }

        $this->FileContents = file_get_contents($this->FilePath);
        include_once $this->FilePath;

        if ( ! isset($parameters['class_name'])) {
            
            $this->outputError("Missing `class_name` from parameters.");
            // TODO: Build, look at $this->FileContents as get full name.
            //       Cannot use end(get_declared_classes()) because
            //       not relaiable due to bootstrap
            //$declared_classes = get_declared_classes();
            //$this->ClassName = end($declared_classes);
            
        } else {
            $this->ClassName = $parameters['class_name'];
        }
    }

    // ---------------------------------------------------------------------

    /**
     * Check class constants
     *
     * This method will perform checks on the class constants.
     *
     * @return void
     */
    public function checkClassConstants(): void
    {
        echo PHP_EOL . str_color('Checking class constants:', 'cyan') . PHP_EOL;

        $this->loadClassReflection();
        $reflection =& $this->ClassReflection;
        $short_name = $reflection->getShortName();
        $error_prefix = "Class `{$this->ClassName}`";

        $order = [];
        $constants = $reflection->getConstants();
        $flag_doc_block = null;
        foreach ($constants as $cons_name => $value) {

            if ( ! is_casing($cons_name, 'UPPER_CASE')) {
              $this->outputError("$error_prefix constant `$cons_name` does not use proper UPPER_CASE");
            }

            $constant = new \ReflectionClassConstant($reflection->name, $cons_name);
            $doc_block = $constant->getDocComment();

            if (empty($flag_doc_block)) {
                if (empty($doc_block)) {
                  $this->outputError("$error_prefix constant `$cons_name` is missing it's DocBlock");
                }
                $doc_block = parse_docblock($doc_block);

                if (empty($doc_block->title)) {
                    $this->outputError("$error_prefix constant `$cons_name` DocBlock is missing title");
                }

                if (empty($doc_block->const) && empty($doc_block->flag)) {
                    $this->outputError("$error_prefix constant `$cons_name` DocBlock is missing @const/@flag declaration");
                }

                if ( ! empty($doc_block->const) && ! empty($doc_block->flag)) {
                    $this->outputError("$error_prefix constant `$cons_name` DocBlock cannot have both @const & @flag declarations");
                }
            } else {
                $doc_block = $flag_doc_block;
            }

            $type = gettype($constant->getValue());

            if ( ! empty($doc_block->flag)) {
                if ($type != $doc_block->flag) {
                    $this->outputError("$error_prefix constant `$cons_name` DocBlock @const declaration does not match value type");
                }
                if ($type != 'integer') {
                    $this->outputError("$error_prefix constant `$cons_name` is not a integer type");
                }
                $binary = decbin($constant->getValue());
                if (substr_count($binary, '1') !== 1) {
                    $this->outputError("$error_prefix constant `$cons_name` is not a valid flag integer value");
                }
                $flag_doc_block = $doc_block;
                continue;
            }

            if ($type != $doc_block->const) {
                $this->outputError("$error_prefix constant `$cons_name` DocBlock @const declaration does not match value type");
            }
            $access = match(true) {
                $constant->isPublic() => 'a0',
                $constant->isProtected() => 'a1',
                $constant->isPrivate() => 'a2',
            };
            $final = $constant->isFinal() ? 'f1' : 'f0';
            $order[] = "{$final}{$access}$cons_name";
        }

        $test_order = $order;
        sort($test_order);
        if ($test_order !== $order) {
            $this->outputError("$error_prefix constants do not follow proper code standards order");
        }

        echo str_color('Passed', 'green') . PHP_EOL;
    }

    /**
     * Check class decleration
     *
     * This method will perform checks of the base decleration of the class and
     * it's DocBlock.
     *
     * @return void
     */
    public function checkClassDecleration(): void
    {
        echo PHP_EOL . str_color('Checking class decleration:', 'cyan') . PHP_EOL;

        $this->loadClassReflection();
        $reflection =& $this->ClassReflection;
        $short_name = $reflection->getShortName();
        $error_prefix = "Class `{$this->ClassName}`";
        $ds = DIRECTORY_SEPARATOR;

        if( ! is_casing($short_name, 'StudlyCaps')) {
            $this->outputError("$error_prefix does not use proper StudlyCaps");
        }

        if (empty($this->ClassDocBlock->title) or empty($this->ClassDocBlock->description)) {
            $this->outputError("$error_prefix DocBlock is missing title and/or description");
        }

        // Check documentation "Documentation" & "Test" attributes
        foreach (['Documentation', 'Test'] as $attr_name) {
          $attr = $reflection->getAttributes("{$reflection->getNamespaceName()}\\{$attr_name}");
          if (empty($attr)) {
              $this->outputError("$error_prefix is missing `$attr_name` attribute");
          }
          if (count($attr) > 1) {
              $this->outputError("$error_prefix has more than one `$attr_name` attribute");
          }
          $attribute =& $attr[0];
          $arguments = $attribute->getArguments();
          if (empty($arguments)) {
              $this->outputError("$error_prefix `$attr_name` attribute has no arguments");
          }
          if (count($arguments) > 1) {
              $this->outputError("$error_prefix `$attr_name` attribute can only have one argument");
          }
          $argument_file = dirname($this->FilePath) . "{$ds}..{$ds}{$arguments[0]}";
          if ( ! file_exists($argument_file)) {
              $this->outputError("$error_prefix `$attr_name` attribute path is not valid");
          }
        }

        echo str_color('Passed', 'green') . PHP_EOL;
    }

    /**
     * Check class formatting
     *
     * This method will perform checks on the class formatting.
     *
     * @return void
     */
    public function checkClassFormatting(): void
    {
        echo PHP_EOL . str_color('Checking formatting:', 'cyan') . PHP_EOL;

        $lines = explode("\n", $this->FileContents);

        foreach ($lines as $index => $line) {
            $line_number = $index + 1;

            if ($line_number === 1) {
                if ($line !== '<?php declare(strict_types=1);') {
                    $this->outputError("Missing strict_types decleration on line $line_number");
                }
                continue;
            }

            // check white space as end
            if (str_ends_with($line, ' ')) {
                $this->outputError("Line $line_number has extra white space at the end of the line");
            }

            // check \r\n
            if (str_ends_with($line, "\r")) {
                $this->outputError("Line $line_number has \\r\\n at the end of the line, should be \\n");
            }

            // check indentation
            preg_match('/^([ ]+).*/', $line, $matches);
            $indent_sapces = $matches[1] ?? '';
            if (isset($line[strlen($indent_sapces)]) && $line[strlen($indent_sapces)] === '*') {
                $indent_sapces = substr($indent_sapces, 0, -1);
            }
            if (strlen($indent_sapces) % self::INDENT_SAPCES !== 0) {
                $this->outputError("Line $line_number has improper indentation");
            }

            // check class decleration
            if (preg_match('/^((\s+)?(final\s+)?class|abstract|interface|trait)\s+\w+(\s+extends\s+\w+)?(\s+implements\s+\w+)?/', $line, $matches)) {
                $spaces_reemoved = trim(preg_replace('/\s+/', ' ', $line));
                if ($spaces_reemoved !== $line) {
                    $this->outputError("Line $line_number has extra white space");
                }
                if ($line[strlen($line) - 1] === '{') {
                    $this->outputError("Line $line_number ends with a curly bracket, should be on next line");
                }
            }

            // check method decleration
            if (preg_match('/^((\s+)?(final\s+)?(public|protected|private))\s+(static\s+)?(function).+/', $line, $matches)) {
                $spaces_reemoved = trim(preg_replace('/\s+/', ' ', $line));
                if ($spaces_reemoved !== trim($line)) {
                    $this->outputError("Line $line_number has extra white space");
                }
                if (strlen(trim($line)) !== strlen($line) - self::INDENT_SAPCES) {
                    $this->outputError("Line $line_number has improper indentation");
                }
                if ($line[strlen($line) - 1] === '{') {
                    $this->outputError("Line $line_number ends with a curly bracket, should be on next line");
                }
            }
        }

        echo str_color('Passed', 'green') . PHP_EOL;
    }

    /**
     * Check class methods
     *
     * This method will perform checks on the class methods.
     *
     * @return void
     */
    public function checkClassMethods(): void
    {
        echo PHP_EOL . str_color('Checking class methods:', 'cyan') . PHP_EOL;

        $this->loadClassReflection();
        $refl =& $this->ClassReflection;
        $short_name = $refl->getShortName();
        $error_prefix = "Class `{$this->ClassName}`";

        // Check Methods
        $order = [];
        $methods = array_filter(
            $refl->getMethods(),
            function($meth) use($refl) {
                // filter out properites from parent
                if ($meth->getDeclaringClass()->getName() !== $refl->getName()) {
                    return false;
                }
                // filter out properites from Trait
                foreach ($refl->getTraits() as &$traitRefl) {
                    foreach ($traitRefl->getMethods() as $reflProp) {
                        if ($reflProp->name === $meth->name) {
                            return false;
                        }
                    }
                }
                return true;
                unset($traitReflection);
            }
        );

        foreach ($methods as $method) {
            $method_name = $method->name;

            $method_name_clean = str_replace('_', '', $method_name);
            if ( ! is_casing($method_name_clean, 'camelCase')) {
                $this->outputError("$error_prefix method `$method_name` does not use proper camelCase");
            }
            if (str_starts_with($method_name, '_') && ! in_array($method_name, self::MAGIC_METHODS)) {
                $this->outputError("$error_prefix method `$method_name` cannot be prefixed with an underscore");
            }

            $doc_blk = $method->getDocComment();
            if (empty($doc_blk)) {
              $this->outputError("$error_prefix method `$method_name` is missing it's DocBlock");
            }
            $doc_blk = parse_docblock($doc_blk);

            if (empty($doc_blk->title) or empty($doc_blk->description)) {
                $this->outputError("$error_prefix method `$method_name` DocBlock is missing title and/or description");
            }

            if ( ! isset($doc_blk->return)) {
                $this->outputError("$error_prefix method `$method_name` DocBlock is missing @return declaration");
            }
            $return_type = strval($method->getReturnType());
            if (empty($return_type)) {
                $return_type = 'void';
            }
            if ($return_type !== $doc_blk->return->type) {
                $this->outputError("$error_prefix method `$method_name` DocBlock @return declaration \"{$doc_blk->return->type}\" does not match return type \"$return_type\"");
            }
            if ($return_type != 'void' && empty($doc_blk->return->description)) {
                $this->outputError("$error_prefix method `$method_name` DocBlock @return declaration does not have a description");
            }

            $params = $method->getParameters();
            $doc_params = isset($doc_blk->params) ? $doc_blk->params : [];

            if (count($params) !=count($doc_params)) {
                $this->outputError("$error_prefix method `$method_name` DocBlock @param declaration and method argument(s) do not match");
            }

            foreach ($params as $param) {

                $error_prefix_param = "$error_prefix method `$method_name` DocBlock @param `\${$param->name}`";

                if ( ! is_casing($param->name, 'snake_case')) {
                    $this->outputError("$error_prefix_param does not use proper snake_case");
                }

                $doc_param = array_find($doc_params, function($item) use($param) {
                    $clean_name = preg_replace('/[^a-z0-9_]/', '', $item->name);
                    return ($clean_name == $param->name);
                });
                if (empty($doc_param)) {
                    $this->outputError("$error_prefix_param declaration missing");
                }

                $passed_by_ref = $param->isPassedByReference();
                $doc_block_by_ref = ($doc_param->name[0] === '&');
                if ( ($passed_by_ref && ! $doc_block_by_ref) || ( ! $passed_by_ref && $doc_block_by_ref)) {
                    $this->outputError("$error_prefix_param declaration & passed by reference missmatch");
                }
                if ($doc_param->type != $param->getType()) {
                    $this->outputError("$error_prefix_param declaration does not match type {$param->getType()}");
                }
                if (empty($doc_param->description)) {
                    $this->outputError("$error_prefix_param declaration does not have a description");
                }
            }

            // TODO: Build
            if ($method->isAbstract()) {
                $this->outputError("$error_prefix method is an abstract, need to build this");
            }

            $access = match(true) {
                $method->isPublic() => 'a0',
                $method->isProtected() => 'a1',
                $method->isPrivate() => 'a2',
            };
            $static_magic = $method->isStatic() ? 'sm0' : 'sm2';
            $final = $method->isFinal() ? 'f1' : 'f0';
            if (in_array($method_name_clean, self::MAGIC_METHODS)) {
              $static_magic = 'sm1';
            }
            $order[] = "{$static_magic}{$final}{$access}$method_name";
        }
        $test_order = $order;
        sort($test_order);
        if ($test_order !== $order) {
            print_r($order);
            echo "\nSHOULD BE:\n";
            print_r($test_order);
            $this->outputError("$error_prefix methods do not follow proper code standards order");
        }

        echo str_color('Passed', 'green') . PHP_EOL;
    }

    /**
     * Check class properties
     *
     * This method will perform checks on the class properties.
     *
     * @return void
     */
    public function checkClassProperties(): void
    {
        echo PHP_EOL . str_color('Checking class properties:', 'cyan') . PHP_EOL;

        $this->loadClassReflection();
        $refl =& $this->ClassReflection;
        $short_name = $refl->getShortName();
        $error_prefix = "Class `{$this->ClassName}`";

        $order = [];
        $properties = array_filter(
            $refl->getProperties(),
            function($prop) use($refl) {
                // filter out properites from parent
                if ($prop->getDeclaringClass()->getName() !== $refl->getName()) {
                    return false;
                }
                // filter out properites from Trait
                foreach ($refl->getTraits() as &$traitRefl) {
                    foreach ($traitRefl->getProperties() as $reflProp) {
                        if ($reflProp->name === $prop->name) {
                            return false;
                        }
                    }
                }
                return true;
                unset($traitReflection);
            }
        );

        foreach ($properties as $property) {
            $prop_name = $property->name;

            if ( ! is_casing($prop_name, 'StudlyCaps')) {
                $this->outputError("$error_prefix property `\$$prop_name` does not use proper StudlyCaps");
            }

            $doc_block = $property->getDocComment();
            if (empty($doc_block)) {
                $this->outputError("$error_prefix property `\$$prop_name` is missing it's DocBlock");
            }
            $doc_block = parse_docblock($doc_block);

            if (empty($doc_block->title)) {
                $this->outputError("$error_prefix property `\$$prop_name` DocBlock is missing title");
            }

            if (empty($doc_block->prop)) {
                $this->outputError("$error_prefix property `\$$prop_name` DocBlock is missing @prop declaration");
            }

            if ($property->hasDefaultValue()) {
                if ( ! $property->hasType()) {
                    $this->outputError("$error_prefix property `\$$prop_name` does not have a type declaration");
                }
                if ($property->getType() != $doc_block->prop) {
                    $this->outputError("$error_prefix property `\$$prop_name` DocBlock @property declaration does not match value type");
                }
            }

            $access = match(true) {
                $property->isPublic() => 'a0',
                $property->isProtected() => 'a1',
                $property->isPrivate() => 'a2',
            };
            $static = $property->isStatic() ? 's0' : 's1';
            $order[] = "{$static}{$access}$prop_name";
        }

        $test_order = $order;
        sort($test_order);
        if ($test_order !== $order) {
            print_r($order);
            echo "\nSHOULD BE:\n";
            print_r($test_order);
            $this->outputError("$error_prefix properties do not follow proper code standards order");
        }

        echo str_color('Passed', 'green') . PHP_EOL;
    }

    /**
     * Check syntax
     *
     * This method will perform a syntax check using PHP built in syntax
     * checking ability `php -l`
     *
     * @return void
     */
    public function checkSyntax(): void
    {
        echo PHP_EOL . str_color('Checking file for errors:', 'cyan') . PHP_EOL;

        exec("php -l {$this->FilePath}", $output, $result_code);
        if ( ! empty($result_code)) {
            echo $output;
            $this->outputError('Found syntax error in file, code checking halted');
        }

        echo str_color('Passed', 'green') . PHP_EOL;
    }

    /**
     * Run all checks
     *
     * This method will perform all checks of the file.
     *
     * @return void
     */
    public function runAll(): void
    {
        $this->checkSyntax();
        $this->checkClassDecleration();
        $this->checkClassConstants();
        $this->checkClassProperties();
        $this->checkClassMethods();
        $this->checkClassFormatting();

        echo PHP_EOL . 'DONE: ' . str_color('All Tests Passed', 'green') . PHP_EOL;
    }

    /**
     * Output error
     *
     * This method will output an error message to the screen. If the additional
     * `$stop_processing` parameter is passed as false the script will continue
     * otherwise it will exit.
     *
     * @param string $message Error message
     * @param bool $stop_processing Stop processing and exit script
     * @return void
     */
    private function outputError(string $message, $stop_processing = true): void
    {
        echo str_color("ERROR: $message" . PHP_EOL, 'red');
        if ($stop_processing) {
          exit(1);
        }
    }

    /**
     * Load class reflection
     *
     * This method will load the ClassReflection and parse the doc block if not
     * already loaded. 
     *
     * @return void
     */
    private function loadClassReflection(): void
    {
        if ( ! isset($this->ClassReflection) || $this->ClassReflection->name != $this->ClassName) {
          
            if ( ! class_exists($this->ClassName)) {
                $this->outputError("The file `{$this->FilePath}` does not contain the decleration for class `$this->ClassName`");
            }
          
            $this->ClassReflection = new ReflectionClass($this->ClassName);
        
            $class_doc_blk = $this->ClassReflection->getDocComment();
            if (empty($class_doc_blk)) {
                $this->outputError("Class `$this->ClassName` is missing it's DocBlock");
            }
            $this->ClassDocBlock = parse_docblock($class_doc_blk);
        }
    }
}

// EOF /////////////////////////////////////////////////////////////////////////
