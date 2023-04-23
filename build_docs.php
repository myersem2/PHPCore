<?php declare(strict_types=1);
/**
 * PHPCore - Build Docs
 *
 * @author    Everett Myers <Me@EverettMyers.com>
 * @copyright Copyright (c) 2023, PHPCore
 */

namespace PHPCore;

use \ReflectionClass;
use \ReflectionMethod;

// -------------------------------------------------------------------------------------------------

$ds = DIRECTORY_SEPARATOR;
$bootstrap = __DIR__ . "{$ds}src{$ds}bootstrap.php";
include $bootstrap;

$classes = [
    'files' => [
        //'Database',
        'Request',
        //'Session',
    ],
    'src_path' => realpath(__DIR__ . "{$ds}src"),
    'out_path' => realpath(__DIR__ . "{$ds}docs_new{$ds}classes"),
];

/**
* Create header string
*
* @param string $text Header text
* @param integer $level Header Level (e.g. 1 => H1, 2 => H2...)
* @return array
*/
function create_header(string $text, int $level): string
{
    switch ($level) {
        case 1:
            $bar = str_repeat('=', strlen($text));
            return "$bar\n$text\n$bar\n";
        break;
        case 2:
            $bar = str_repeat('#', strlen($text));
            return "$text\n$bar\n";
        break;
    }
}

/**
* Indent for Sphinx documentation
*
* @param integer $times Times to indent
* @return string
*/
function indent(int $times = 1): string
{
    return $indent = str_repeat(' ', 3*$times);
}

/**
* Display string of value by proper type
*
* @param mixed $value Value to display
* @return string
*/
function display_by_type(mixed $value): string
{
    switch (gettype($value)) {
        default:
            return strval($value);
        break;
        case 'boolean':
            return $value ? 'true' : 'false';
        break;
        case 'NULL':
            return 'null';
        break;
        case 'string':
            return "\"$value\"";
        break;
        case 'array':
            if (empty($value)) {
                return '[]';
            } else {
                trigger_error('Cannot have populated array as with display_by_type(), TODO: build this out', E_USER_ERROR);
            }
        break;
    }
}

// TODO: document
function nested_tag(string $tag, object $docblock, int $level = 1)
{
    $indent_base = indent($level-1);
    $indent_nest = indent($level);
    $rst = "{$indent_base}.. $tag::\n";
    if (is_array($docblock->$tag)) {
        if (count($docblock->$tag) == 1) {
            $rst .= "{$indent_nest}{$docblock->$tag[0]}\n";
        } else {
            foreach ($docblock->$tag as $item) {
                $rst .= "{$indent_nest}- $item\n";
            }
        }
    } else {
        $rst .= "{$indent_nest}{$docblock->$tag}\n";
    }
    $rst .= "\n";
    return $rst;
}

foreach ($classes['files'] as $file) {

    $reflection = new ReflectionClass(__NAMESPACE__."\\$file");
    $rst = '';
    $rst_indent = indent();
    $code_indent = indent(4);

    $class_short_name = $reflection->getShortName();

    $docblock = parse_docblock($reflection->getDocComment());
    $table_of_contents = '';

    $trait_inherited = (object)[
        'properties' => [],
        'methods' => [],
    ];
    $use = implode(', ', $reflection->getTraitNames());
    if ( ! empty($use)) {
        $use = "\n{$rst_indent}    use $use\n";
    }
    foreach($reflection->getTraits() as $trait) {
        foreach($trait->getProperties() as $property) {
            $trait_inherited->properties[] = $property->name;
        }
        foreach($trait->getMethods() as $method) {
            $trait_inherited->methods[] = $method->name;
        }
    }

    $final = $reflection->isFinal() ? 'final ' : '';

    $extends = $reflection->getExtensionName();
    if ( ! empty($extends)) {
        // TODO: find out why this is not working, tested with Session class
        $extends = " extends $extends";
    }

    $implements = implode(', ', $reflection->getInterfaceNames());
    if ( ! empty($implements)) {
        $implements = " implements $implements";
    }

    $constants = '';
    foreach ($reflection->getReflectionConstants() as $constant) {
       
        if (empty($constants)) {
            $constants = "\n{$rst_indent}    /* Constants */\n";
        }
        $value = display_by_type($reflection->getConstant($constant->name));
        $constant_visibility  = match(true) {
            $constant->isPublic() => 'public ',
            $constant->isPrivate() => 'private ',
            $constant->isProtected() => 'protected ',
        };
        $constants .= "{$rst_indent}    {$constant_visibility}const {$constant->name} = $value\n";
    }

    // Methods
    $properties = (object)[
        'instance' => '',
        'static' => '',
    ];
    foreach ($reflection->getProperties() as $property) {

        // Skip inherited traits
        if (in_array($property->name, $trait_inherited->properties)) {
            continue;
        }

        if ($property->isStatic()) {
            $property_scope = 'static';
            $property_scope_static = 'Static ';
        } else {
            $property_scope = 'instance';
            $property_scope_static = '';
        }

        if (empty($properties->$property_scope)) {
            $properties->$property_scope = "\n{$rst_indent}    /* {$property_scope_static}Properties */\n";
        }

        $property_visibility  = match(true) {
            $property->isPublic() => 'public ',
            $property->isPrivate() => 'private ',
            $property->isProtected() => 'protected ',
        };
        $property_static = $property->isStatic() ? 'static ' : '';

        $type = $property->getType();
        if (isset($type)) {
            $type = "$type ";
        } else {
            $type = '';
        }

        $default = '';
        if ($property->hasDefaultValue()) {
            $default = ' = '.display_by_type($property->getDefaultValue());
        }

        $properties->$property_scope .= "{$rst_indent}    {$property_visibility}{$property_static}{$type}\${$property->name}{$default};\n";
    }

    // Methods
    $methods = (object)[
        'instance' => '',
        'static' => '',
        'details' => [],
    ];
    foreach ($reflection->getMethods() as $method) {

        // Skip inherited traits
        if (in_array($method->name, $trait_inherited->methods)) {
            // TODO: see if this is needed, does not look like the getMethods() is pulling inherited traits
            continue;
        }

        $method_docblock = parse_docblock($method->getDocComment());
        if (empty($method_docblock) || ! empty($method_docblock->ignore)) {
            continue;
        }

        if ($method->isStatic()) {
            $method_scope = 'static';
            $method_scope_static = 'Static ';
        } else {
            $method_scope = 'instance';
            $method_scope_static = '';
        }

        if (empty($methods->$method_scope)) {
            $methods->$method_scope = "\n{$rst_indent}    /* {$method_scope_static}Methods */\n";
        }

        $method_visibility  = match(true) {
            $method->isPublic() => 'public ',
            $method->isPrivate() => 'private ',
            $method->isProtected() => 'protected ',
        };
        $method_final = $method->isFinal() ? 'final ' : '';
        $method_static = $method->isStatic() ? 'static ' : '';

        $params_arr = [];
        $param_return_details = [];
        foreach ($method->getParameters() as $param) {
            $type = $param->getType();
            $defult = '';
            if ($param->isDefaultValueAvailable()) {
                $defult = " = ".display_by_type($param->getDefaultValue());
            }
            $params_arr[] = "$type \${$param->name}$defult";

            $param_docblock = array_find($method_docblock->params, function($item) use($param) {
                return ($item->varable == "\${$param->name}");
            });
            if ( ! isset($param_docblock) || $param_docblock->type != strval($type)) {
                trigger_error("parameter doc block does not match declared params for \${$param->name} in $class_short_name::{$method->name}()", E_USER_ERROR);
            }
            $param_return_details[] = "{$rst_indent}:param $type \${$param->name}: {$param_docblock->description}";
        }

        if (isset($method_docblock->return)) {
            $param_return_details[] = "{$rst_indent}:returns: ``{$method_docblock->return->type}`` {$method_docblock->return->description}";
        }

        if (isset($method_docblock->example)) {
            $attributes = '';
            foreach ($method_docblock->example->attributes as $attribute=>$value) {
                if ($value == 'true') {
                    $value = '';
                } else {
                    $value = " $value";
                }
                $attributes .= "{$rst_indent}{$rst_indent}:$attribute:$value\n";
            }
            $example_code = "\n{$rst_indent}{$rst_indent}<?php";
            $example_code .= str_replace("\n", "\n{$rst_indent}{$rst_indent}", $method_docblock->example->code);
            $example_code .= "?>";
            $param_return_details[] = "\n{$rst_indent}.. code-block:: php\n{$rst_indent}{$rst_indent}:caption: {$method_docblock->example->caption}$attributes$example_code";
        }

        $params = implode(', ', $params_arr);

        $return_type = '';
        if ($method->hasReturnType()) {
            $return_type = ": {$method->getReturnType()}";
        }
        
        $methods->$method_scope .= "{$rst_indent}    {$method_final}{$method_visibility}{$method_static}function {$method->name}($params)$return_type\n";

        $anchor = strtolower($class_short_name).'-method-'.strtolower($method->name);
        $table_of_contents .= "* :ref:`$class_short_name::{$method->name}<$anchor>` - {$method_docblock->title}\n";

        $methods_details  = ".. _$anchor:\n";
        $methods_details .= ".. php:method:: {$method->name}($params)\n\n";
        $methods_details .= "{$rst_indent}{$method_docblock->title}\n\n";
        $description_indent = str_replace("\n\n", "\n\n{$rst_indent}", $method_docblock->description);
        $methods_details .= "{$rst_indent}$description_indent\n\n";
        
        foreach (['seealso','note','warning'] as $tag) {
            if (isset($method_docblock->$tag)) {
                $methods_details .= nested_tag($tag, $method_docblock, 2);
            }
        }

        $methods_details .= implode("\n", $param_return_details);
        $methods->details[] = $methods_details;
    }

    // Head
    $rst  = create_header($docblock->title, 1);
    $rst .= "\n{$docblock->description}\n\n";

    foreach (['seealso','note','warning'] as $tag) {
        if (isset($docblock->$tag)) {
            $rst .= nested_tag($tag, $docblock);
        }
    }

    // Synopsis
    $rst .= create_header("{$docblock->title} Synopsis", 2);
    $rst .= "
.. code-block:: php

   {$final}class {$class_short_name}{$extends}{$implements} {
{$use}{$constants}{$properties->instance}{$properties->static}{$methods->instance}{$methods->static}
   }

";

    $rst .= create_header("{$docblock->title} Table of Contents", 2);
    $rst .= "\n{$table_of_contents}\n";

    $back_link = "\n{$rst_indent}.. rst-class:: wy-text-right\n\n{$rst_indent}{$rst_indent}:ref:`Back to list<{$docblock->title} Table of Contents>`\n";

    $rst .= create_header("{$docblock->title} Methods", 2)."\n";
    $rst .= implode("\n{$back_link}\n-----\n\n", $methods->details);
    $rst .= "\n{$back_link}";

    if (isset($docblock->refences)) {
        $rst .= "\n";
        foreach ($docblock->refences as $refence) {
            $rst .= ".. _{$refence->description}: {$refence->link}\n";
        }
    }

    //var_dump(file_get_contents(__DIR__.'/docs/classes/request.rst'));exit;
    file_put_contents(__DIR__.'/docs/classes/request.rst', $rst);
    
    //echo $rst;
    //var_dump($db->title);
    //var_dump($db->description);
    //var_dump($db->title);
    //var_dump($db->return);
    //var_dump($db->all_params);
    //var_dump(trim(str_replace(array('/', '*'), '', substr($rc->getDocComment(), 0, strpos($rc->getDocComment(), '@')))));
}

// EOF /////////////////////////////////////////////////////////////////////////////////////////////
